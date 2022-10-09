<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\App;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckFormDecode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // form 表单 base64 解码 ( 判断依据是 _encode 字段等于 base64 )
        if($request->input('_encode')=='base64'){
            foreach($request->input() as $k=>$v){
                if(in_array($k, ['api_token', '_token', '_encode'])) //排除
                    continue;
                if(is_array($v)){
                    foreach($v as $kk=>$vv)
                        $v[$kk] = base64_decode($vv);
                    $request[$k] = $v;
                }else{
                    $request[$k] = base64_decode($v);
                }
            }
        }
        return $next($request);
    }
}
