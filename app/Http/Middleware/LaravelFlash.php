<?php

namespace App\Http\Middleware;

use Closure;

class LaravelFlash{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next){
        $response = $next($request);
        //>判断是否有需要弹出提示的内容如果有则添加 toastr 否则直接返回
        if(!session()->has("alert-message") && !session()->has("errors")){
            return $response;
        }
        //>获取页面内容
        $content = $response->getContent();
        if(strripos($content,'</body>') === false){
            return $response;
        }
        //>插入css标签
        $toastrCssPath = asset('statics/toastr-2.1.1/toastr.min.css');

        $toastrCss = <<<php
<link href="$toastrCssPath" rel="stylesheet" type="text/css" />\r\n</head>
php;
        // 插入js标签
        $toastrJsPath = asset('statics/toastr-2.1.1/toastr.min.js');
        $init = '';
        // 自定义提示信息
        if (session()->has('alert-message')) {
            $init = 'toastr.'.session('alert-type').'("'.session('alert-message').'");';
        }
        // Validate 表单验证的错误信息
        if (session()->has('errors')) {
            foreach (session('errors')->all() as $k => $v) {
                $init .= 'toastr.error'.'("'.$v.'");';
            }
        }
        //>插入js路径
        $jqueryJsPath = asset('statics/jquery-2.2.4/jquery.min.js');

        $toastrJs = <<<php
<script>
    (function(){
        window.jQuery || document.write('<script src="$jqueryJsPath"><\/script>');
    })();
</script>
<script src="$toastrJsPath"></script>
<script>
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "positionClass": "toast-top-center",
        "onclick": null,
        "showDuration": "1000",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut",
        "progressBar": true
    }
    $init
</script>
</body>
php;
        $seach = ['</head>','</body>'];
        $subject = [$toastrCss,$toastrJs];
        //>替换html
        $content = str_replace($seach, $subject, $content);
        //>更新内容并重置Content-Length
        $response->setContent($content);
        $response->headers->remove('Content-Length');
        return $response;
    }
}
