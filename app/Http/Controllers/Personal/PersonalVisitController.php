<?php

namespace App\Http\Controllers\Personal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Helpers\CustomHelper;
use App\Models\Menu;
use App\Models\PersonalVisit;
use App\Models\Place;
use App\Models\UsedData;
use Illuminate\Support\Str;

class PersonalVisitController extends Controller
{
    public function index()
    {
        $userCode = session('bo_id');
        $lastSegment = request()->segment(count(request()->segments()));

        $menu = Menu::where('url', $lastSegment)->first();
        $data = [
            'title'         => 'Visit',
            'place'         => Place::where('status','1')->get(),
            'newcode'       => $menu->document_code.date('y'),
            'content'       => 'admin.personal.personal_visit',
            'data_user'     => User::find(session('bo_id')),
            'serverTime' => Carbon::now()->toIso8601String(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function create(Request $request)
    {
        if($request->temp){
            $validation = Validator::make($request->all(), [
                'latitude'              => 'required',
                'longitude'			    => 'required',
                'img'                   => 'required',
                'note_out'                   => 'required',
            ], [
                'latitude.required' 	                => 'Latitude / Longitude tidak boleh kosong.',
                'longitude.required'                    => 'Longitude / Latitude tidak boleh kosong.',
                'img.required'                          => 'Gambar tidak boleh kosong.',
                'note_out'                   => 'Keterangan keluar tidak boleh kosong.',
            ]);

        }else{
            $validation = Validator::make($request->all(), [
                'latitude'              => 'required',
                'longitude'			    => 'required',
                'img'                   => 'required',
                'note_in'                   => 'required',
            ], [
                'latitude.required' 	                => 'Latitude / Longitude tidak boleh kosong.',
                'longitude.required'                    => 'Longitude / Latitude tidak boleh kosong.',
                'img.required'                          => 'Gambar tidak boleh kosong.',
                'note_in.required'                          => 'Keterangan masuk tidak boleh kosong.',
            ]);
        }
        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors(),
                'message'=> 'BELUM MEMILIKI GAMBAR/LOKASI'
            ];
        }else {
            DB::beginTransaction();
            $now = Carbon::now();
            $formattedDateTime = $now->format('Y-m-d\TH:i:s.uP');
            try {

                if($request->img){
                    // $image = $request->img;  // your base64 encoded
                    // $image = str_replace('data:image/png;base64,', '', $image);
                    // $image = str_replace(' ', '+', $image);
                    $imageName = Str::random(35).'.png';
                    $path=storage_path('app/public/attendances/'.$imageName);
                    $newFile = CustomHelper::compress($request->img,$path,30);
                    $basePath = storage_path('app');
                    $desiredPath = explode($basePath.'/', $newFile)[1];
                }

                if($request->temp){
                    $query = PersonalVisit::where('code',CustomHelper::decrypt($request->temp))->first();
                    if(in_array($query->status,['2','5'])){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Data sudah selesai atau sudah tervoid, anda tidak bisa melakukan perubahan.'
                        ]);
                    }else{
                        $query->date_out = $now;
                        $query->latitude_out = $request->latitude;
                        $query->longitude_out = $request->longitude;
                        $query->image_out = $desiredPath ? $desiredPath : NULL;
                        $query->note_out = $request->note_out;
                        $query->status = '2';
                        $query->save();
                    }

                }else{
                    $newCode=PersonalVisit::generateCode('P-V-'.date('y',strtotime($request->date_in)).'P1');
                    $query = PersonalVisit::create([
                        'code'			                => $newCode,
                        'user_id'		                => session( 'bo_id'),
                        'date_in'                          => $now,
                        'location'                         => $request->location,
                        'latitude_in'                      => $request->latitude,
                        'longitude_in'                     => $request->longitude,
                        'image_in'                         => $desiredPath ? $desiredPath : NULL,
                        'note_in'                          => $request->note_in,
                        'status'                           => '1',
                    ]);
                }


                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            }
			if($query) {

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

    public function getCode(Request $request){
        UsedData::where('user_id', session('bo_id'))->delete();
        $code = PersonalVisit::generateCode($request->val);

		return response()->json($code);
    }

    public function visitOut(Request $request){
        $po = PersonalVisit::where('code',CustomHelper::decrypt($request->id))->first();

		return response()->json($po);
    }

    public function datatable(Request $request){
        $column = [
            'code',
            'user_id',
            'image_in',
            'note_in',
            'image_out',
            'note_out',
            'date_in',
            'date_out',
            'location',
            'latitude_in',
            'longitude_in',
            'latitude_out',
            'longitude_out',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = PersonalVisit::count();

        $query_data = PersonalVisit::where(function($query) use ($search, $request) {
            if($search) {
                $query->where(function($query) use ($search, $request) {
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('note_in', 'like', "%$search%")
                        ->orWhere('note_out', 'like', "%$search%")
                        ->orWhereHas('user',function($query) use ($search, $request){
                            $query->where('name','like',"%$search%")
                                ->orWhere('employee_no','like',"%$search%");
                        });
                });
            }

            $query->where('user_id',session('bo_id'));

            if($request->status){
                $query->whereIn('status', $request->status);
            }

            if($request->start_date && $request->finish_date) {
                $query->whereDate('date_in', '>=', $request->start_date)
                    ->whereDate('date_in', '<=', $request->finish_date);
            } else if($request->start_date) {
                $query->whereDate('date_in','>=', $request->start_date);
            } else if($request->finish_date) {
                $query->whereDate('date_in','<=', $request->finish_date);
            }
        })
        ->offset($start)
        ->limit($length)
        ->orderBy($order, $dir)
        ->get();

        $total_filtered = PersonalVisit::where(function($query) use ($search, $request) {
            if($search) {
                $query->where(function($query) use ($search, $request) {
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('note_in', 'like', "%$search%")
                        ->orWhere('note_out', 'like', "%$search%")
                        ->orWhereHas('user',function($query) use ($search, $request){
                            $query->where('name','like',"%$search%")
                                ->orWhere('employee_no','like',"%$search%");
                        });
                });
            }

            if($request->status){
                $query->whereIn('status', $request->status);
            }

            if($request->start_date && $request->finish_date) {
                $query->whereDate('date_in', '>=', $request->start_date)
                    ->whereDate('date_in', '<=', $request->finish_date);
            } else if($request->start_date) {
                $query->whereDate('date_in','>=', $request->start_date);
            } else if($request->finish_date) {
                $query->whereDate('date_in','<=', $request->finish_date);
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
                    $val->user->name,
                    $val->date_in ?? '-',
                    $val->note_in,
                    $val->date_out ?? '-',
                    $val->note_out ?? '-',
                    $val->location ?? '-',
                    $val->image_in ? '<a href="'.$val->attachmentIn().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
                    $val->image_out ? '<a href="'.$val->attachmentOut().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
                    $val->status(),
                    '

						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light brown accent-2 white-text btn-small" data-popup="tooltip" title="Selesai Visit" onclick="doneVisit(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">rv_hookup</i></button>

                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup"  onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
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

    public function voidStatus(Request $request){
        $query = PersonalVisit::where('code',CustomHelper::decrypt($request->id))->first();

        if($query) {

            if(in_array($query->status,['5'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Tidak Bisa Cancel Karena Data Telah ditutup / Selesai.'
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                activity()
                    ->performedOn(new PersonalVisit())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Cancel Visit');

                CustomHelper::sendNotification('personal_visits',$query->id,'Personal Visit. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('personal_visits',$query->id);

                $response = [
                    'status'  => 200,
                    'message' => 'Data closed successfully.'
                ];
            }
        } else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to Void.'
            ];
        }

        return response()->json($response);
    }
}
