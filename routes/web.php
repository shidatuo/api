<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/{all}', function () {
//    return view('welcome');
//    $a = string_between("zhouwang",'z','a');
//    $a = replace_once("zhouwang","b","zhouwangzzhouwang");
//    dd($a);
//    $url = URL::current();
//    $url = Request::getRequestUri();
//    dump($url);
//    dd($url);

    $aaaa = get("table=app&fields=id,name&name=[like]哈&single=true");
    dd($aaaa);
    $aa = DB::table("appaa")->find(555);
    dump($aa);
    dd(Schema::hasTable('app'));
    if (Schema::hasTable('app'))
    {
        //
    }

//
//    dd(DB::table("app"));

//    dd($aa);
});

Route::any('/apis/{all}', 'ApiController@api');
