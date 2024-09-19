<?php

namespace App\Http\Controllers\MasterData;

use App\Exceptions\RowImportException;
use App\Exports\ExportItemFGPicture;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\itemFGPicture;
use App\Models\User;
use App\Helpers\CustomHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ItemFGPictureController extends Controller
{
    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];

    }

    public function index()
    {
        $data = [
            'title'     => 'Gambar Item Finish Good',
            'content'   => 'admin.master_data.item_fg_picture',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'other_name',
            'item_group_id',
            'uom_unit',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Item::count();
        $query_data = Item::where(function($query) use ($search, $request) {
            if($search) {
                $query->where('code', 'like', "%$search%")
                ->orWhere('name', 'like', "%$search%");
            }
        })
        ->whereNotNull('is_sales_item')
        ->whereHas('parentFg')
        ->offset($start)
        ->limit($length)
        ->orderBy($order, $dir)
        ->get();

        $total_filtered = Item::where(function($query) use ($search, $request) {
            if($search) {
                $query->where('code', 'like', "%$search%")
                ->orWhere('name', 'like', "%$search%");
            }
        })
        ->whereNotNull('is_sales_item')
        ->whereHas('parentFg')
        ->count();
        
        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				if($val->itemFgPicture()->exists()){
                    $src = asset(Storage::url($val->itemFgpicture->image));
                }else{
                    $src = asset('website/empty.png');
                }
                $response['data'][] = [
                    $nomor,
                    $val->code,
                    $val->name??'',
                    $val->other_name??'',
                    $val->itemGroup->name??'',
                    '<img src="' . $src . '" style="width:40px;height:auto;">',
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
            'file'                  => 'required',
           
        ], [
            'file.required'         => 'Harap masukkan file jpeg apabila ingin melakukan penyimpanan.',
            
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            DB::beginTransaction();
            try {
                if($request->temp){
                    
                    $query = itemFGPicture::where('item_id',$request->temp)->first();
                   
                    if($request->has('file')) {
                        if($query){
                            if($query->image){
                                if(Storage::exists($query->image)){
                                    Storage::delete($query->image);
                                }
                            }
                            $extension = $request->file->getClientOriginalExtension();
                            if (in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'bmp'])) {
                                $document_name = Str::random(35).'.png';
                                $path_document=storage_path('app/public/item_fg_picture/'.$document_name);
                                $newFile_document = CustomHelper::compress($request->file,$path_document,30);
                                $basePath = storage_path('app');
                            
                                $document = explode($basePath.'/', $newFile_document)[1];
                            }else{
                                $response = [
                                    'status'  => 500,
                                    'message' => 'Data bukan merupakan gambar'
                                ];
                                return response()->json($response);
                            }
                            // $document = $request->file('file')->store('public/item_fg_picture');
                        }
                    }
                    if($query){
                        $query->user_id         = session('bo_id');
                        $query->image           = $document;
                        $query->save();
                    }else{
                        $extension = $request->file->getClientOriginalExtension();
                        if (in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'bmp'])) {
                            $document_name = Str::random(35).'.png';
                            $path_document=storage_path('app/public/item_fg_picture/'.$document_name);
                            $newFile_document = CustomHelper::compress($request->file,$path_document,30);
                            $basePath = storage_path('app');
                        
                            $document = explode($basePath.'/', $newFile_document)[1];
                        }else{
                            $response = [
                                'status'  => 500,
                                'message' => 'Data bukan merupakan gambar'
                            ];
                            return response()->json($response);
                        }
                        $query = itemFGPicture::create([
                            'code'              => strtoupper(Str::random(15)),
                            'user_id'           => session('bo_id'),
                            'item_id'			=> $request->temp,
                            'image'             => $request->file('file') ? $request->file('file')->store('public/item_fg_picture') : NULL,
                        ]);
                    }
                }

                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            }
			
			if($query) {

                activity()
                    ->performedOn(new itemFGPicture())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit item fg picture data.');

				$response = [
					'status'  => 200,
					'message' => 'Data successfully saved.'
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

    public function saveMulti(Request $request){
        
        $validation = Validator::make($request->all(), [
            'arr_file'                  => 'required',
           
        ], [
            'arr_file.required'         => 'Harap masukkan file jpeg apabila ingin melakukan penyimpanan.',
            
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            DB::beginTransaction();
            try {
                foreach($request->arr_id as $index=>$row_code){
                    $query = itemFGPicture::whereHas('item', function($query) use ($row_code) {
                        $query->where('code', $row_code);
                    })->first();
                   
                    $extension = $request->arr_file[$index]->getClientOriginalExtension();
                    if (in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'bmp'])) {
                        $document_name = Str::random(35).'.png';
                        $path_document=storage_path('app/public/item_fg_picture/'.$document_name);
                        $newFile_document = CustomHelper::compress($request->arr_file[$index],$path_document,30);
                        $basePath = storage_path('app');
                    
                        $document = explode($basePath.'/', $newFile_document)[1];
                    }else{
                        $response = [
                            'status'  => 500,
                            'message' => 'Data no'.$index.' bukan merupakan gambar'
                        ];
                        return response()->json($response);
                    }

                    if($query){
                        if($query->image){
                            if(Storage::exists($query->image)){
                                Storage::delete($query->image);
                            }
                        }
                        if($query){
                            $query->user_id         = session('bo_id');
                            $query->image           = $document;
                            $query->save();
                        }
                        
                        
                    }else{
                        $item = Item::where('code',$row_code)->first();
                        $extension = $request->arr_file[$index]->getClientOriginalExtension();
                        if (in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'bmp'])) {
                            $document_name = Str::random(35).'.png';
                            $path_document=storage_path('app/public/item_fg_picture/'.$document_name);
                            $newFile_document = CustomHelper::compress($request->arr_file[$index],$path_document,30);
                            $basePath = storage_path('app');
                        
                            $document = explode($basePath.'/', $newFile_document)[1];
                        }else{
                            $response = [
                                'status'  => 500,
                                'message' => 'Data no'.$index.' bukan merupakan gambar'
                            ];
                            return response()->json($response);
                        }
                        if($item){
                            $query = itemFGPicture::create([
                                'code'              => strtoupper(Str::random(15)),
                                'user_id'           => session('bo_id'),
                                'item_id'			=> $item->id,
                                'image'             => $document,
                            ]);
                        }else{
                            $response = [
                                'status'  => 500,
                                'message' => 'Data no'.$index.' belum ada'
                            ];
                            return response()->json($response);
                        }
                        
                    }

                }
                

                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            }
			
			if($query) {

                activity()
                    ->performedOn(new itemFGPicture())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit item fg picture data.');

				$response = [
					'status'  => 200,
					'message' => 'Data successfully saved.'
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
        $bp = itemFGPicture::where('item_id',$request->id)->first();
        if($bp){
            $bp['images'] = 	asset(Storage::url($bp->image)) ?? asset('website/empty.png');			
        }else{
            $bp['images'] = 	asset('website/empty.png');			
        }
       
		return response()->json($bp);
    }

    public function destroy(Request $request){
        $query = itemFGPicture::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new itemFGPicture())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the item fg picture data');

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

    public function getImportExcel(){
        return Excel::download(new ExportTemplatePriceList(), 'format_template_price_list'.uniqid().'.xlsx');
    }

    public function import(Request $request)
    {
        try {
            Excel::import(new ImportPriceList, $request->file('file'));
            return response()->json(['status' => 200, 'message' => 'Import successful']);
        } catch (RowImportException $e) {
            return response()->json([
                'message' => 'Import failed',
                'error' => $e->getMessage(),
                'row' => $e->getRowNumber(),
                'column' => $e->getColumn(),
                'sheet' => $e->getSheet(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Import failed', 'error' => $e->getMessage()], 400);
        }
    }

    public function exportFromTransactionPage(Request $request){
        $search = $request->search? $request->search : '';
        $status = $request->status ? $request->status : '';
		return Excel::download(new ExportItemFGPicture($search,$status), 'standar_harga_pelanggan_'.uniqid().'.xlsx');
    }
}
