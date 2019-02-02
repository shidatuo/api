<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Libraries\Functions\WxPay\WxPay;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule){
        $schedule->call(function (){
            $date = date("Y-m-d H:i:s");
            $result = DB::table("jy_sale_goods")
                ->select("id")
                ->where("end_time","<=",$date)
//                ->where(["state"=>1])
                ->get();
            foreach ($result as $values){
                DB::table("jy_sale_goods")->where(['id'=>$values->id])->update(['state'=>3]);
//                save("jy_sale_goods","id={$values->id}&state=3");
                $order_list = DB::table("jy_order")->where(["goods_id"=>$values->id])->get();
                foreach ($order_list as $value){
//                    $order_info = get("jy_order","id={$value->id}&single=true&fields=id,transaction_id");
                    $order_info = DB::table("jy_order")->select("id","transaction_id","amount")->where(['id'=>$value->id,'is_refund'=>0])->first();
                    DB::table("jy_order")->where(['id'=>$value->id])->update(['state'=>3]);
                    $log = "\n接收到的退款订单号为 : [{$order_info->id}]";
                    $refundOrder['refundNo'] = $order_info->id;//我们的订单id
                    $refundOrder['transactionId'] = $order_info->transaction_id;
                    $refundOrder['totalFee'] = (int)((string)($order_info->amount * 100));
                    $refundOrder['refundFee'] = (int)((string)($order_info->amount * 100)); //微信是以分为单位
                    $log .= "\n接收到的退款参数为 : ".json_encode($refundOrder) . PHP_EOL;
                    $config['AppId'] = "wx6e75e53e4a50bf41";
                    $config['wx_v3_key'] = "mykjsde34sdfmzf98342559kdshzx8as";
                    $config['wx_v3_mhcid'] = "1525038701";
                    $config['wx_v3_apiclient_cert_path'] = $_SERVER['DOCUMENT_ROOT'] . '/cert/apiclient_cert.pem';
                    $config['wx_v3_apiclient_key_path'] = $_SERVER['DOCUMENT_ROOT'] . '/cert/apiclient_key.pem';
                    $log .= "\n订单号[{$order_info->id}]  ------ config信息 ------" . PHP_EOL;
                    $pay = new WxPay($config);
                    $totalFee = (int)$refundOrder['totalFee'];//订单金额
                    $refundFee = (int)$refundOrder['refundFee'];//退款金额
                    $refundNo = $refundOrder['refundNo'];//商户退款单号
                    $transactionIdOrOutTradeNo = $refundOrder['transactionId'];//微信订单号
                    $return_refundOrder = $pay->refundOrder($totalFee,$refundFee,$refundNo,$transactionIdOrOutTradeNo);
                    log_ex('getOrderRefund.log',"\n调用退款接口返回值为 : ".json_encode($return_refundOrder) . PHP_EOL);
                    //返回这个代表请求成功
                    if(isset($return_refundOrder['result_code']) && $return_refundOrder['result_code'] == 'SUCCESS'){
                        $log .= "\n订单号[{$order_info->id}] ------ 退款成功 ------" . PHP_EOL;
                        log_ex('getOrderRefund.log',"$log\n=========== 进入到订单退款方法  END =============\n");
                        save("jy_order","id={$value->id}&is_refund=1");
                    }else{
                        $description = isset($return_refundOrder['err_code_des']) ? $return_refundOrder['err_code_des'] : '';
                        $log .= "\n订单号[{$order_info->id}] ------ 退款失败 {$order_info->amount} (单位:元) " . PHP_EOL;
                        $log .= "\n订单号[{$order_info->id}] ------ 失败原因 : {$description} " . PHP_EOL;
                        log_ex('getOrderRefund.log',"$log\n=========== 进入到订单退款方法  END =============\n");
                    }
                }
            }
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
       }
}
