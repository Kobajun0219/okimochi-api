<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use Illuminate\Http\Request;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class FriendController extends Controller
{

    protected $user;

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function request($user_id)
    {

        $friend = Friend::firstOrCreate([
            'request_id' => $this->user->id,
            'receive_id' => $id,
        ], [
            'request_id' => $this->user->id,
            'receive_id' => $id,
            'status' => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'request has been success',
            'data' => $friend
        ], Response::HTTP_OK);
    }

    public function friends_list()
    {
        $friends = Friend::where('receive_id', $this->user->id)->where('request_id', $this->user->id)->orWhere('status', 0)->get();

        return response()->json(['friends_list' => $friends]);
    }

    public function friend_request_list()
    {

        $friends= Friend::where('receive_id', $this->user->id)->where('status', 1)->get();

        return response()->json(['friend_request' => $friends]);
    }

    public function accept_friend_request(Friend $id)
    {
        $id =
        $id->update([
            'status' => 0,
        ]);

        return response()->json([
            'message' => 'accept friend request',
            'data' => $id
        ], Response::HTTP_OK);
    }



}
