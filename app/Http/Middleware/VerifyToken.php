<?php

namespace App\Http\Middleware;

use Closure;
use App\Model\jy_token;

class VerifyToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,jy_token $token){
        $d_t = $request->get("d_t","");
        if(!NotEstr($d_t))
            jsonReturn(201,"无效的token");
        $result = $token->select("id")->where(["token"=>$d_t,"status"=>1])->get()->toArray();
        if(!is_arr($result))
            jsonReturn(201,"token错误");
        return $next($request);
    }
}
