<?php

namespace App\Http\Controllers\MasterData;
use App\Helpers\CustomHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Equipment;
use App\Models\EquipmentPart;
use App\Models\EquipmentSparePart;
use App\Models\Place;
use App\Models\Area;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportEquipment;
use Illuminate\Support\Facades\Storage;

class EquipmentController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Peralatan Plant',
            'content'   => 'admin.master_data.equipment',
            'place'     => Place::where('status','1')->get(),
            'area'      => Area::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'place_id',
            'area_id',
            'item_id',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Equipment::count();
        
        $query_data = Equipment::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWherehas('place',function($query) use($search,$request){
                                $query->where('code','like',"$search")
                                    ->orWhere('name', 'like', "%$search%");
                            })
                            ->orWherehas('area',function($query) use($search,$request){
                                $query->where('code','like',"$search")
                                    ->orWhere('name', 'like', "%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = Equipment::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWherehas('place',function($query) use($search,$request){
                                $query->where('code','like',"$search")
                                    ->orWhere('name', 'like', "%$search%");
                            })
                            ->orWherehas('area',function($query) use($search,$request){
                                $query->where('code','like',"$search")
                                    ->orWhere('name', 'like', "%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->name,
                    $val->place->name.' - '.$val->place->company->name,
                    $val->area->name,
                    $val->item()->exists() ? $val->item->name : '-',
                    $val->note,
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    '
                        <a href="'.$request->url.'/part/'.$val->id.'" class="btn-floating btn-small mb-1 btn-flat waves-effect waves-light blue accent-2 white-text"><i class="material-icons">developer_board</i></a>
					',
                    $val->status(),
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
					'
                ];

                $nomor++;
            }
        }

        $response['recordsTotal'] = 0;
        if($total_data <> FALSE) {
            $response['recordsTotal'] = $total_data;
        }

        $response['recordsFiltered'] = 0;
        if($total_filtered <> FALSE) {
            $response['recordsFiltered'] = $total_filtered;
        }

        return response()->json($response);
    }

    public function create(Request $request){
        
        $validation = Validator::make($request->all(), [
            'name'          => 'required',
            'place_id'      => 'required',
            'area_id'       => 'required',
        ], [
            'name.required'         => 'Nama area tidak boleh kosong.',
            'place_id.required'     => 'Plant tidak boleh kosong.',
            'area_id.required'      => 'Area servis tidak boleh kosong.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = Equipment::find($request->temp);

                    if($request->has('file')) {
						if(Storage::exists($query->document)){
							Storage::delete($query->document);
						}
						$document = $request->file('file')->store('public/equipments');
					} else {
						$document = $query->document;
					}

                    $query->user_id             = session('bo_id');
                    $query->name                = $request->name;
                    $query->place_id            = $request->place_id;
                    $query->area_id             = $request->area_id;
                    $query->item_id             = $request->item_id ? $request->item_id : NULL;
                    $query->note                = $request->note;
                    $query->document            = $document;

                    $query->status              = $request->status ? $request->status : '2';
                    $query->save();

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{

                DB::beginTransaction();
                try {
                    $query = Equipment::create([
                        'code'              => Equipment::generateCode(),
                        'name'			    => $request->name,
                        'user_id'           => session('bo_id'),
                        'place_id'          => $request->place_id,
                        'area_id'           => $request->area_id,
                        'item_id'           => $request->item_id ? $request->item_id : NULL,
                        'note'              => $request->note,
                        'document'          => $request->file('file') ? $request->file('file')->store('public/equipments') : NULL,
                        'status'            => $request->status ? $request->status : '2',
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {               

                activity()
                    ->performedOn(new Equipment())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit equipment data.');

				$response = [
					'status'    => 200,
					'message'   => 'Data successfully saved.',
				];
			} else {
				$response = [
					'status'  => 500,
					'message' => 'Data failed to save.'
				];
			}
		}
		
		return response()->json($response);
    }

    public function show(Request $request){
        $equipment = Equipment::find($request->id);
        $equipment['item_name'] = $equipment->item()->exists() ? $equipment->item->name : '';
        
		return response()->json($equipment);
    }

    public function rowDetail(Request $request)
    {
        $data   = Equipment::where('code',CustomHelper::decrypt($request->id))->first();

        $string = '<h6>List Parts & Spare Parts</h6>
                    <ol>
        ';

        foreach($data->equipmentPart as $row){
            $string .= '<li>
                            '.$row->name.' - '.($row->status == '1' ? 'Active' : 'Non-active' ).'
                            <ol type="a">';
                            
            foreach($row->sparepart as $rowsp){
                $string .=  '<li>'.$rowsp->item->name.' Qty '.$rowsp->qty.' '.$rowsp->item->uomUnit->code.' - '.($rowsp->status == '1' ? 'Active' : 'Non-active' ).'</li>';
            }

            $string .= '</ol>
                        </li>';
        }

        $string .= '</ol>';
		
        return response()->json($string);
    }

    public function destroy(Request $request){
        $query = Equipment::find($request->id);
		
        if($query->delete()) {
            foreach($query->equipmentPart as $row){
                $row->sparepart()->delete();
                $row->delete();
            }

            activity()
                ->performedOn(new Equipment())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the equipment data');

            $response = [
                'status'  => 200,
                'message' => 'Data deleted successfully.'
            ];
        } else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }

        return response()->json($response);
    }

    public function print(Request $request){

        $validation = Validator::make($request->all(), [
            'arr_id'                => 'required',
        ], [
            'arr_id.required'       => 'Tolong pilih Item yang ingin di print terlebih dahulu.',
        ]);
        
        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            $pr=[];
            $currentDateTime = Date::now();
            $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
            foreach($request->arr_id as $key =>$row){
                $pr[]= Equipment::where('code',$row)->first();

            }
            $data = [
                'title'     => 'Master Equipment',
                'data'      => $pr
            ];  
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path);
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
            $pdf = Pdf::loadView('admin.print.master_data.equipment', $data)->setPaper('a5', 'landscape');
            $pdf->render();
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            $content = $pdf->download()->getOriginalContent();


            $randomString = Str::random(10); 

         
            $filePath = 'public/pdf/' . $randomString . '.pdf';
            

            Storage::put($filePath, $content);
            
            $document_po = asset(Storage::url($filePath));
            $var_link=$document_po;

            $response =[
                'status'=>200,
                'message'  =>$var_link
            ];
        }
        
		
		return response()->json($response);

    }

    public function export(Request $request){
        $search = $request->search ? $request->search : '';
        $status = $request->status ? $request->status : '';
		
		return Excel::download(new ExportEquipment($search,$status), 'equipment_'.uniqid().'.xlsx');
    }

    public function partIndex(Request $request, $id){
        $equipment = Equipment::find($id);

        if ($equipment) {
            $data = [
                'title'         => 'Part Alat '.$equipment->name,
                'equipment'     => $equipment,
                'content'       => 'admin.master_data.equipment_part'
            ];

            return view('admin.layouts.index', ['data' => $data]);
        }else{
            abort(404);
        }
    }

    public function partDatatable(Request $request,$id){
        $column = [
            'id',
            'code',
            'name',
            'specification',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = EquipmentPart::count();
        
        $query_data = EquipmentPart::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('specification', 'like', "%$search%");
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
                
            })
            ->where('equipment_id',$id)
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = EquipmentPart::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('specification', 'like', "%$search%");
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->where('equipment_id',$id)
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    $nomor,
                    $val->code,
                    $val->name,
                    $val->specification,
                    '
                        <a href="'.$request->url.'/sparepart/'.$val->id.'" class="btn-floating btn-small mb-1 btn-flat waves-effect waves-light blue accent-2 white-text"><i class="material-icons">developer_board</i></a>
					',
                    $val->status(),
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
					'
                ];

                $nomor++;
            }
        }

        $response['recordsTotal'] = 0;
        if($total_data <> FALSE) {
            $response['recordsTotal'] = $total_data;
        }

        $response['recordsFiltered'] = 0;
        if($total_filtered <> FALSE) {
            $response['recordsFiltered'] = $total_filtered;
        }

        return response()->json($response);
    }

    public function createPart(Request $request){
        
        $validation = Validator::make($request->all(), [
            'tempEq'            => 'required',
            'name'              => 'required',
            'specification'     => 'required',
        ], [
            'tempEq.required'           => 'Peralatan tidak boleh kosong.',
            'name.required'             => 'Nama part tidak boleh kosong.',
            'specification.required'    => 'Spesifikasi tidak boleh kosong.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = EquipmentPart::find($request->temp);

                    $query->user_id             = session('bo_id');
                    $query->name                = $request->name;
                    $query->specification       = $request->specification;
                    $query->status              = $request->status ? $request->status : '2';
                    $query->save();

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{

                DB::beginTransaction();
                try {
                    $query = EquipmentPart::create([
                        'user_id'           => session('bo_id'),
                        'equipment_id'      => $request->tempEq,
                        'code'              => EquipmentPart::generateCode(),
                        'name'			    => $request->name,
                        'specification'     => $request->specification,
                        'status'            => $request->status ? $request->status : '2',
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {               

                activity()
                    ->performedOn(new Equipment())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit equipment data.');

				$response = [
					'status'    => 200,
					'message'   => 'Data successfully saved.',
				];
			} else {
				$response = [
					'status'  => 500,
					'message' => 'Data failed to save.'
				];
			}
		}
		
		return response()->json($response);
    }

    public function showPart(Request $request){
        $ep = EquipmentPart::find($request->id);
        				
		return response()->json($ep);
    }

    public function destroyPart(Request $request){
        $query = EquipmentPart::find($request->id);
		
        if($query->delete()) {
            $query->sparepart()->delete();

            activity()
                ->performedOn(new EquipmentPart())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the equipment part data');

            $response = [
                'status'  => 200,
                'message' => 'Data deleted successfully.'
            ];
        } else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }

        return response()->json($response);
    }

    public function sparePartIndex(Request $request, $id, $idpart){
        $equipmentpart = EquipmentPart::find($idpart);

        if ($equipmentpart) {
            $data = [
                'title'         => 'Sparepart Alat '.$equipmentpart->equipment->name.' - Part '.$equipmentpart->name,
                'equipmentpart' => $equipmentpart,
                'content'       => 'admin.master_data.equipment_sparepart'
            ];

            return view('admin.layouts.index', ['data' => $data]);
        }else{
            abort(404);
        }
    }

    public function sparePartDatatable(Request $request ,$id, $idpart){
        $column = [
            'id',
            'code',
            'item_id',
            'qty',
            'specification',
            'description',
            'file',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = EquipmentSparePart::count();
        
        $query_data = EquipmentSparePart::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('specification', 'like', "%$search%")
                            ->orWhere('description', 'like', "%$search%")
                            ->orWhereHas('item',function($query) use ($search, $request){
                                $query->where('code','like',"%$search%")
                                    ->orWhere('name','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
                
            })
            ->where('equipment_part_id',$idpart)
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = EquipmentSparePart::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('specification', 'like', "%$search%")
                            ->orWhere('description', 'like', "%$search%")
                            ->orWhereHas('item',function($query) use ($search, $request){
                                $query->where('code','like',"%$search%")
                                    ->orWhere('name','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->where('equipment_part_id',$idpart)
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    $nomor,
                    $val->code,
                    $val->item->name,
                    $val->qty.' '.$val->item->uomUnit->code,
                    $val->specification,
                    $val->description,
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->status(),
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
					'
                ];

                $nomor++;
            }
        }

        $response['recordsTotal'] = 0;
        if($total_data <> FALSE) {
            $response['recordsTotal'] = $total_data;
        }

        $response['recordsFiltered'] = 0;
        if($total_filtered <> FALSE) {
            $response['recordsFiltered'] = $total_filtered;
        }

        return response()->json($response);
    }

    public function createSparePart(Request $request){
        
        $validation = Validator::make($request->all(), [
            'tempEq'            => 'required',
            'item_id'           => 'required',
            'qty'               => 'required',
            'specification'     => 'required',
            'description'       => 'required',
        ], [
            'tempEq.required'           => 'Part Peralatan tidak boleh kosong.',
            'item_id.required'          => 'Item tidak boleh kosong.',
            'qty.required'              => 'Jumlah spare part tidak boleh kosong',
            'specification.required'    => 'Spesifikasi tidak boleh kosong.',
            'description.required'      => 'Deskripsi tidak boleh kosong' 
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = EquipmentSparePart::find($request->temp);

                    if($request->has('file')) {
						if(Storage::exists($query->document)){
							Storage::delete($query->document);
						}
						$document = $request->file('file')->store('public/spareparts');
					} else {
						$document = $query->document;
					}

                    $query->user_id             = session('bo_id');
                    $query->item_id             = $request->item_id;
                    $query->qty                 = $request->qty;
                    $query->specification       = $request->specification;
                    $query->description         = $request->description;
                    $query->document            = $document;
                    $query->status              = $request->status ? $request->status : '2';
                    $query->save();

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{

                DB::beginTransaction();
                try {
                    $query = EquipmentSparePart::create([
                        'user_id'           => session('bo_id'),
                        'code'              => EquipmentSparePart::generateCode(),
                        'equipment_part_id' => $request->tempEq,
                        'item_id'           => $request->item_id,
                        'qty'			    => $request->qty,
                        'specification'     => $request->specification,
                        'description'       => $request->description,
                        'document'          => $request->file('file') ? $request->file('file')->store('public/spareparts') : NULL,
                        'status'            => $request->status ? $request->status : '2',
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {               

                activity()
                    ->performedOn(new EquipmentSparePart())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit equipment sparepart data.');

				$response = [
					'status'    => 200,
					'message'   => 'Data successfully saved.',
				];
			} else {
				$response = [
					'status'  => 500,
					'message' => 'Data failed to save.'
				];
			}
		}
		
		return response()->json($response);
    }

    public function showSparePart(Request $request){
        $esp = EquipmentSparePart::find($request->id);
        $esp['item_name'] = $esp->item->name;
        				
		return response()->json($esp);
    }

    public function destroySparePart(Request $request){
        $query = EquipmentSparePart::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new EquipmentSparePart())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the equipment sparepart data');

            $response = [
                'status'  => 200,
                'message' => 'Data deleted successfully.'
            ];
        } else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }

        return response()->json($response);
    }
}