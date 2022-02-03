<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\OkimochiController;
use App\Http\Controllers\FriendController;

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

Route::post('login', [ApiController::class, 'authenticate']); //ログイン
Route::post('register', [ApiController::class, 'register']); //会員登録

Route::group(['middleware' => ['jwt.verify']], function () {
    Route::post('/index', [OkimochiController::class, 'index']); //一覧取得
    Route::post('post', [OkimochiController::class, 'store']); //投稿
    Route::put('update/{okimochi}', [OkimochiController::class, 'update']); //投稿の更新
    Route::delete('delete',  [OkimochiController::class, 'destroy']); //投稿の削除機能
    Route::post('mypage', [OkimochiController::class, 'mypage']); //自分のマイページでの情報を取得
    Route::post('detail/{id}', [OkimochiController::class, 'show']); //指定のidの投稿を表示(自分の投稿のみ)
    Route::post('save', [OkimochiController::class, 'save_okimochi']); //指定のidの投稿を保存
    Route::delete('save/delete', [OkimochiController::class, 'save_delete']); //指定のidの投稿を保存を削除

    Route::post('get_user', [ApiController::class, 'get_user']); //user情報の取得
    Route::post('get_all_user', [ApiController::class, 'get_all_user']); //全user情報の取得
    Route::put('update_profile', [ApiController::class, 'update_profile']); //プロフィールアップデート
    Route::post('logout', [ApiController::class, 'logout']); //ログアウト

    Route::post('request', [FriendController::class, 'request']); //友達申請
    Route::post('request_list', [FriendController::class, 'friend_request_list']); //来ているリクエストのリスト
    Route::post('friends_list', [FriendController::class, 'friends_list']); //友達一覧
    Route::post('request_list/{id}', [FriendController::class, 'accept_friend_request']); //友達申請を受け入れる
});
