<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\OkimochiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('login', [ApiController::class, 'authenticate']);
Route::post('register', [ApiController::class, 'register']);


Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('/index', [OkimochiController::class, 'index']); //一覧取得
    Route::post('post', [OkimochiController::class, 'store']); //投稿

    Route::get('mypage', [OkimochiController::class, 'mypage']); //自分の投稿情報を取得


    Route::get('detail/{id}', [OkimochiController::class, 'show']); //指定のidの投稿を表示
    Route::get('save/{id}', [OkimochiController::class, 'save_okimochi']); //指定のidの投稿を表示
    Route::get('search', [OkimochiController::class, 'search']);

    Route::put('update/{okimochi}',  [OkimochiController::class, 'update']);
    Route::delete('delete/{okimochi}',  [OkimochiController::class, 'destroy']); //削除機能

    Route::get('logout', [ApiController::class, 'logout']);
    Route::get('get_user', [ApiController::class, 'get_user']);

});
