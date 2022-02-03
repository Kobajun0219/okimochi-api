<?php

namespace App\Http\Controllers;

use App\Models\Okimochi;
use App\Models\Save_okimochi;
use Illuminate\Http\Request;
use App\Http\Requests\TokenValidator;
use App\Http\Requests\PostValidator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Config;

class OkimochiController extends Controller
{

    protected $user;

    /**
     * check token is valid
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        try {
            if (!$this->user = JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Could not recognize the token.',
            ], 500);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\TokenValidator  $request
     * @return \Illuminate\Http\Response
     */
    public function index(TokenValidator $request)
    {
        $okimochi = Okimochi::orderBy('created_at', 'desc')->get();
        return response()->json(['okimochi' => $okimochi]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostValidator $request)
    {
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
            'message' => 'posted successfully',
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
                'message' => 'Sorry, product not found.'
            ], 400);
        }

        return $okimochi;
    }


    /**
     * access to myPage
     *
     * @param  \App\Http\Requests\TokenValidator $request
     * @return \Illuminate\Http\Response
     */
    public function mypage(TokenValidator $request)
    {

        $okimochi = $this->user->okimochis()->get();

        //自分の保存した投稿を取得。
        $saves = Save_okimochi::where('user_id', $this->user->id)->get();
        foreach ($saves as $save) {
            $save->okimochi;
        }
        return response()->json([
            'my_posts' => $okimochi,
            'saves' => $saves
        ], Response::HTTP_OK);
    }


    /**
     * save others post(okimochi)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function save_okimochi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'save_id' => 'required',
            'token' => 'required',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->messages()
            ], 200);
        }

        $id = $request->save_id;

        //同じデータの場合には返す
        $alls = Save_okimochi::all();
        foreach ($alls as $all) {
            if ($all->okimochi_id == $id && $all->user_id == $this->user->id) {
                return response()->json([
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


        return response()->json([
            'message' => 'saved successfully',
            'data' => $save->okimochi,
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\PostValidator  $request
     * @param  \App\Models\Okimochi  $okimochi
     * @return \Illuminate\Http\Response
     */
    public function update(PostValidator $request, Okimochi $okimochi)
    {
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
     * Remove the specified post from Okimochi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required',
            'token' => 'required',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->messages()
            ], 200);
        }

        $okimochi = Okimochi::where("id",$request->post_id)->first();

        if (!$okimochi->user_id  == $this->user->id) {
            return response()->json([
                'message' => 'The post is not yours'
            ], Response::HTTP_OK);
        }

        $okimochi->delete();
        return response()->json([
            'message' => 'Product deleted successfully'
        ], Response::HTTP_OK);
    }

    /**
     * Remove specified save_post in Save_okimochi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function save_delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'save_id' => 'required',
            'token' => 'required',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->messages()
            ], 200);
        }

        if(!Save_okimochi::where("id",$request->save_id)->delete() == 1){
            return response()->json([
            'message' => 'Does not exist specific item'
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => 'Save item deleted successfully'
        ], Response::HTTP_OK);
    }



}
