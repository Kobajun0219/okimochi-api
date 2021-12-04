<?php

namespace App\Http\Controllers;

use App\Models\Okimochi;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

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
    public function index()
    {
        //ランダムでタグを5つ表示
        // $tags = Tag::inRandomOrder()->take(5)->get();

        //自分の非公開/過去だけを取得
        $today = date("Y-m-d H:i:s");
        // $mypastels  = Pastel::where('u_id', Auth::user()->id)->where('open_time', '<=', $today)->where('public', 1)->orderBy('created_at', 'desc')->get();

        //みんなの公開/過去だけを取得
        // $pastels = Pastel::where('open_time', '<=', $today)->where('public', 0)->orderBy('created_at', 'desc')->paginate(6);

        //みんなの公開/過去だけを取得
        // $allpastels = Pastel::where('open_time', '<=', $today)->where('public', 0)->orderBy('created_at', 'desc')->get(); //created_atの降順（desc)で表示させる

        return $this->user
        ->okimochis()
        ->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
        $data = $request->only('name', 'sku', 'price', 'quantity');
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'sku' => 'required',
            'price' => 'required',
            'quantity' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is valid, create new product
        $okimochi = $this->user->products()->create([
            'name' => $request->name,
            'sku' => $request->sku,
            'price' => $request->price,
            'quantity' => $request->quantity
        ]);

        //Product created, return success response
        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
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
        $okimochi = $this->user->products()->find($id);

        if (!$okimochi) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, product not found.'
            ], 400);
        }

        return $okimochi;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Okimochi  $okimochi
     * @return \Illuminate\Http\Response
     */
    public function edit(Okimochi $okimochi)
    {
        //
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
        $data = $request->only('name', 'sku', 'price', 'quantity');
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'sku' => 'required',
            'price' => 'required',
            'quantity' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is valid, update product
        $okimochi = $okimochi->update([
            'name' => $request->name,
            'sku' => $request->sku,
            'price' => $request->price,
            'quantity' => $request->quantity
        ]);

        //Product updated, return success response
        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
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
}
