<?php

namespace App\Jobs;

use App\Helpers\CustomHelper;
use App\Mail\SendMail;
use App\Models\HistoryEmailDocument;
use App\Models\MarketingOrderInvoice;
use Illuminate\Support\Str;
use App\Models\PurchaseOrder;
use App\Models\TransactionEmail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendMailWithAttachmenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $table_id,$table_name,$user_id;

    public function __construct(string $table_id = null,string $user_id = null,string $table_name = null)
    {
        $this->table_id = $table_id ? $table_id : '';
        $this->user_id = $user_id;
        $this->table_name = $table_name;
        $this->queue = 'email_transaction';
    }

    public function handle(): void
    {
        if($this->table_name == 'purchase_orders'){
            $po = PurchaseOrder::find($this->table_id);
            if($po->account->email){
                $data = [
                    'title'     => 'Print Purchase Order',
                    'data'      => $po
                ];
                $opciones_ssl=array(
                    "ssl"=>array(
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                    ),
                );
                $po_array = array_map('trim', explode(';', $po->account->email));
                $ccEmails = [
                    'livia@superior.co.id',
                    'david@superior.co.id',
                    'rmpurch@superiorporcelain.co.id'
                ];
                $img_path = public_path('website/logo_web_fix.png');
                $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                $image_temp = file_get_contents($img_path, false, stream_context_create($opciones_ssl));
                $img_base_64 = base64_encode($image_temp);
                $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                $data["image"]=$path_img;
                CustomHelper::addNewPrinterCounter($po->getTable(),$po->id);
                $pdf = Pdf::loadView('admin.print.purchase.order_individual', $data)->setPaper('a4', 'portrait');
                $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                $pdf->getCanvas()->page_text(495, 785, "Jumlah Print, ". $po->printCounter()->count(), $font, 10, array(0,0,0));
                $pdf->getCanvas()->page_text(505, 800, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));


                $content = $pdf->download()->getOriginalContent();

                $randomString = Str::random(10);


                $filePath = 'public/pdf/' . $randomString . '.pdf';


                Storage::put($filePath, $content);
                $document_po = asset(Storage::url($filePath));
                $fullPath = storage_path('app/' . $filePath);
                $fullPathRule = storage_path('app/public/rules/po_rules.pdf');
                $data = [
                    'subject' 	=> 'Dokumen Purchase Order',
                    'view' 		=> 'admin.mail.po_done',
                    'result' 	=> $po,
                    'supplier' 	=> $po->account->name,
                    'user' 		=> $po->user,
                    'company' 	=> $po->user->company,
                    'attachmentPath' => $fullPath,
                    'attachmentName' => 'attachment.pdf',
                    'newAttachmentPath' => $fullPathRule,
                    'newAttachmentName' => 'rule_porcelain.pdf',
                ];
                $status_send = '1';
                try {
                    Mail::to($po_array)->cc($ccEmails)->send(new SendMail($data));

                } catch (\Exception $e) {
                    $status_send = '2';

                    TransactionEmail::create([
                        'user_id'		=> $po->user_id,
                        'account_id'	=> $po->account_id,
                        'lookable_type'	=> $po->getTable(),
                        'lookable_id'	=> $po->id,
                        'status'		=> $status_send,
                        'email_to'		=> $po->account->email,
                        'cc_email_to'   => implode($ccEmails),
                    ]);
                    Log::error('Error sending email: ' . $e->getMessage());
                    throw $e;
                }

                TransactionEmail::create([
                    'user_id'		=> $po->user_id,
                    'account_id'	=> $po->account_id,
                    'lookable_type'	=> $po->getTable(),
                    'lookable_id'	=> $po->id,
                    'status'		=> $status_send,
                    'email_to'		=> $po->account->email,
                    'cc_email_to'   => implode($ccEmails),
                ]);

                HistoryEmailDocument::create([
                    'user_id'		=> $po->user_id,
                    'account_id'	=> $po->account_id,
                    'lookable_type'	=> $po->getTable(),
                    'lookable_id'	=> $po->id,
                    'status'		=> 1,
                    'email'			=> $po->account->email ?? '-',
                    'note'			=> $po->note,
                ]);
            }
        }elseif($this->table_name == 'marketing_order_invoices'){
            $moi = MarketingOrderInvoice::find($this->table_id);
            if($moi){
                if($moi->account->email){
                    $data = [
                        'title'     => 'Marketing Order Invoice',
                        'data'      => $moi
                    ];
                    $opciones_ssl=array(
                        "ssl"=>array(
                        "verify_peer"=>false,
                        "verify_peer_name"=>false,
                        ),
                    );
                    $po_array = array_map('trim', explode(';', $moi->account->email));
                    $ccEmails = [
                        'windykuro@gmail.com',
                    ];
                    $fullPathRule = $moi->document ? storage_path('app/' . $moi->document) : '';
                    $newAttachmentName = $moi->document ? 'dokumen_pajak.pdf' : '';
                    $data = [
                        'subject' 	=> 'Dokumen Invoice '.$moi->code,
                        'view' 		=> 'admin.mail.sales_invoice',
                        'result' 	=> $moi,
                        'supplier' 	=> $moi->account->name,
                        'user' 		=> $moi->user,
                        'company' 	=> $moi->user->company,
                        'attachmentPath' => $fullPathRule,
                        'attachmentName' => $newAttachmentName,
                        'newAttachmentPath' => '',
                        'newAttachmentName' => '',
                    ];
                    $status_send = '1';
                    try {
                        Mail::to($po_array)->cc($ccEmails)->send(new SendMail($data));
    
                    } catch (\Exception $e) {
                        $status_send = '2';
    
                        TransactionEmail::create([
                            'user_id'		=> $moi->user_id,
                            'account_id'	=> $moi->account_id,
                            'lookable_type'	=> $moi->getTable(),
                            'lookable_id'	=> $moi->id,
                            'status'		=> $status_send,
                            'email_to'		=> $moi->account->email,
                            'cc_email_to'   => implode($ccEmails),
                        ]);
                        Log::error('Error sending email: ' . $e->getMessage());
                        throw $e;
                    }
    
                    TransactionEmail::create([
                        'user_id'		=> $moi->user_id,
                        'account_id'	=> $moi->account_id,
                        'lookable_type'	=> $moi->getTable(),
                        'lookable_id'	=> $moi->id,
                        'status'		=> $status_send,
                        'email_to'		=> $moi->account->email,
                        'cc_email_to'   => implode($ccEmails),
                    ]);
    
                    HistoryEmailDocument::create([
                        'user_id'		=> $moi->user_id,
                        'account_id'	=> $moi->account_id,
                        'lookable_type'	=> $moi->getTable(),
                        'lookable_id'	=> $moi->id,
                        'status'		=> 1,
                        'email'			=> $moi->account->email ?? '-',
                        'note'			=> $moi->note,
                    ]);
                }
            }
        }
    }
}
