<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Libraries\Functions\WxPay\WxPay;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\ApiController;

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
            $a = new ApiController();
            $a->test_();
        })->everyMinute();

        $schedule->call(function (){
            $o_list = DB::table("jy_order")->where(['is_back_stock'=>0,'state'=>0])->get();
            if(count($o_list) > 0){
                foreach ($o_list as $item=>$value){
                    DB::table('jy_sale_goods')->where(['id'=>$value->goods_id])->increment('actual_stock',$value->num,['state'=>1]);
                    DB::table('jy_order')->where(["id"=>$value->id])->update(['is_back_stock'=>1]);
                }
            }
        })->everyMinute();

        $schedule->call(function(){
            $api = new ApiController();
            //自动确认收货
            $local_time = date('Y-m-d H:i:s', strtotime("-7 day",time()));
            $orderArr = DB::table('jy_order')
                ->select('id')
                ->where('state','=',2)
                ->where('created_at','<=',$local_time)
                ->get();
            if(count($orderArr) > 0){
                foreach ($orderArr as $item=>$value){
                    $data['id'] = $value->id ? $value->id : 0;
                    $data['state'] = 4;
                    save("jy_order",$data);
                    $api->market_brokerage($value->id);
                }
            }
        })->everyMinute();

        $schedule->call(function (){
            $date = date("Y-m-d H:i:s");
            DB::table("jy_sale_goods")
                ->where("end_time","<=",$date)
                ->where(["state"=>1])
                ->update(['state'=>3]);
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
