<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use App\Model\Config;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //定义全局的config 变量 使用 try catch 是为了解决 composer install 时候触发 php artisan optimize 但此时无数据库的问题
        try {
            // 获取配置项
            $config = Cache::remember('config', 10080, function () {
                return Config::pluck('value','name');
            });
            // 解决初次安装时候没有数据引起报错
            if (collect($config)->isEmpty()) {
                Artisan::call('cache:clear');
            } else {
                // 用 config 表中的配置项替换 /config/ 目录下文件中的配置项
                $serviceConfig = [
                    //>github 的授权id
                    'services.github.client_id' => $config['GITHUB_CLIENT_ID'],
                    'services.github.client_secret' => $config['GITHUB_CLIENT_SECRET'],
                    //>qq 的授权id
                    'services.qq.client_id' => $config['QQ_APP_ID'],
                    'services.qq.client_secret' => $config['QQ_APP_KEY'],
                    //>微博 的授权id
                    'services.weibo.client_id' => $config['SINA_API_KEY'],
                    'services.weibo.client_secret' => $config['SINA_SECRET'],
                ];
                config($serviceConfig);
            }
        } catch (QueryException $e) {
            // 此处清除缓存是为了解决上面无数据库时缓存时 config 缓存了空数据 db:seed 后 config 走了缓存为空的问题
            Artisan::call('cache:clear');
            $config = [];
        }
        // 分配全站通用的数据
        view()->composer('*', function ($view) use($config) {
            $assign = [
                'config' => $config
            ];
            // 获取赞赏捐款文章
//            if (!empty($config['QQ_QUN_ARTICLE_ID'])) {
//                $qqQunArticle = Cache::remember('qqQunArticle', 10080, function () use($config) {
//                    return Article::select('id', 'title')->where('id', $config['QQ_QUN_ARTICLE_ID'])->first();
//                });
//                $assign['qqQunArticle'] = $qqQunArticle;
//            }
            $view->with($assign);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
