<?php

namespace App\Http\Controllers;

use App\Models\Okimochi;
use App\Models\Save_okimochi;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Config;

class OkimochiController extends Controller
{

    protected $user;

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);

        $okimochi = Okimochi::orderBy('created_at', 'desc')->get();
        return response()->json(['okimochi' => $okimochi]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Validate data
        $validator = Validator::make($request->all(), [
            'who' => 'required',
            'title' => 'required',
            'message' => 'required',
            'open_time' => 'required',
            'open_place_name' => 'required',
            'open_place_latitude' => 'required',
            'open_place_longitude' => 'required',
            'public' => 'required',
            'token' => 'required',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        // //tag付けに関して
        // // #(ハッシュタグ)で始まる単語を取得。結果は、$matchに多次元配列で代入される。
        // preg_match_all('/#([a-zA-Z0-9０-９ぁ-んァ-ヶー一-龠 - 々 ー \']+)/u', $request->tags, $match);
        // // $match[0]に#(ハッシュタグ)あり、$match[1]に#(ハッシュタグ)なしの結果が入ってくるので、$match[1]で#(ハッシュタグ)なしの結果のみを使います。
        // $tags = [];
        // foreach ($match[1] as $tag) {
        //     $record = Tag::firstOrCreate(['tag_name' => $tag]); // firstOrCreateメソッドで、tags_tableのtag_nameカラムに該当のない$tagは新規登録される。
        //     array_push($tags, $record); // $recordを配列に追加します(=$tags)
        // };

        // // 投稿に紐付けされるタグのidを配列化
        // $tags_id = [];
        // foreach ($tags as $tag) {
        //     array_push($tags_id,
        //         $tag->id
        //     );
        // };

        //画像の扱いに関して
        if ($file = $request->file('pic_name')){
            $fileName = Storage::disk('s3')->putFile('/post',$file, 'public');
        } else {
            //画像が登録されなかった時はから文字をいれる
            $fileName = "";
        }

        //Request is valid, create new okimochi
        $okimochi = $this->user->okimochis()->create([
            'who' => $request->who,
            'title' => $request->title,
            'message' => $request->message,
            'user_name' => $this->user->name,
            'user_id' => $this->user->id,
            'pic_name' => $fileName,
            'open_time' => $request->open_time,
            'open_place_name' => $request->open_place_name,
            'open_place_latitude' => $request->open_place_latitude,
            'open_place_longitude' => $request->open_place_longitude,
            'public' => $request->public,
        ]);

        //Product created, return success response
        return response()->json([
            'success' => true,
            'message' => 'post success',
            'data' => $okimochi
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Okimochi  $okimochi
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $okimochi = $this->user->okimochis()->find($id);

        if (!$okimochi) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, product not found.'
            ], 400);
        }

        return $okimochi;
    }


    //mypageへのアクセス
    public function mypage(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);

        $okimochi = $this->user->okimochis()->get();

        //自分の保存した投稿を取得。
        $saves = Save_okimochi::where('user_id', $this->user->id)->get();
        foreach ($saves as $save) {
            $save->okimochi;
        }
        return response()->json([
            'success' => true,
            'myPost' => $okimochi,
            'saves' => $saves
        ], Response::HTTP_OK);
    }

    public function save_okimochi($id)
    {
        //同じデータの場合にはindexに返す
        $alls = Save_okimochi::all();
        foreach ($alls as $all) {
            if ($all->okimochi_id == $id && $all->user_id == $this->user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'already saved'
                ], 400);
            }
        }

        $save = Save_okimochi::firstOrCreate([
            'okimochi_id' => $id,
            'user_id' => $this->user->id

        ], [
            'okimochi_id'   => $id,
            'user_id'   => $this->user->id
        ]);

        // //テーブルへ値を入れる
        // $saves = new Pastel_user; //app/Pastelを入れる
        // $saves->pastel_id = $id;
        // $saves->user_id = Auth::user()->id;
        // $saves->save();
        // return redirect('/');


        return response()->json([
            'success' => true,
            'message' => 'success saved',
            'save' => $save,
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Okimochi  $okimochi
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Okimochi $okimochi)
    {
        //Validate data
        $validator = Validator::make($request->all(), [
            'who' => 'required',
            'title' => 'required',
            'message' => 'required',
            'open_time' => 'required',
            'open_place_name' => 'required',
            'open_place_latitude' => 'required',
            'open_place_longitude' => 'required',
            'public' => 'required',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        // //tag付けに関して
        // // #(ハッシュタグ)で始まる単語を取得。結果は、$matchに多次元配列で代入される。
        // preg_match_all('/#([a-zA-Z0-9０-９ぁ-んァ-ヶー一-龠 - 々 ー \']+)/u', $request->tags, $match);
        // // $match[0]に#(ハッシュタグ)あり、$match[1]に#(ハッシュタグ)なしの結果が入ってくるので、$match[1]で#(ハッシュタグ)なしの結果のみを使います。
        // $tags = [];
        // foreach ($match[1] as $tag) {
        //     $record = Tag::firstOrCreate(['tag_name' => $tag]); // firstOrCreateメソッドで、tags_tableのtag_nameカラムに該当のない$tagは新規登録される。
        //     array_push($tags, $record); // $recordを配列に追加します(=$tags)
        // };

        // // 投稿に紐付けされるタグのidを配列化
        // $tags_id = [];
        // foreach ($tags as $tag) {
        //     array_push($tags_id,
        //         $tag->id
        //     );
        // };

        //画像の扱いに関して
        if ($file = $request->file('pic_name')){
            $fileName = Storage::disk('s3')->putFile('/post',$file, 'public');
        } else {
            //画像が登録されなかった時はから文字をいれる
            $fileName = "";
        }

        $okimochi->who = $request->who;
        $okimochi->title = $request->title;
        $okimochi->message = $request->message;
        $okimochi->pic_name = $fileName;
        $okimochi->open_time = $request->open_time;
        $okimochi->open_place_name = $request->open_place_name;
        $okimochi->open_place_latitude = $request->open_place_latitude;
        $okimochi->open_place_longitude = $request->open_place_longitude;
        $okimochi->public = $request->public;
        $okimochi->save();

        //Product created, return success response
        return response()->json([
            'message' => 'Update has been success',
            'data' => $okimochi
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Okimochi  $okimochi
     * @return \Illuminate\Http\Response
     */
    public function destroy(Okimochi $okimochi)
    {
        $okimochi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ], Response::HTTP_OK);
    }

    public function save_delete(Save_okimochi $id)
    {
        $id->delete();

        return response()->json([
            'success' => true,
            'message' => 'Save item deleted successfully'
        ], Response::HTTP_OK);
    }



}
