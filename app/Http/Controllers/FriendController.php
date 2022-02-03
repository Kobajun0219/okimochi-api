<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateFriendRequest;
use App\Models\Friend;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\TokenValidator;

class FriendController extends Controller
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
     * send friend request
     *
     * @param  \App\Http\Requests\Request $request
     * @return \Illuminate\Http\Response
     */
    public function request(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'receive_user_id' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->messages()
            ], 200);
        }

        try {
        $friend = Friend::firstOrCreate([
            'request_user_id' => $this->user->id,
            'receive_user_id' => $request->receive_user_id,
        ], [
            'request_user_id' => $this->user->id,
            'receive_user_id' => $request->receive_user_id,
            'status' => 1,
        ]);
        } catch(\Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 200);
        }

        if ($friend->request_user_id == $this->user->id) {
            $friend->receive_user;
        }else {
            $friend->request_user;
        }

        return response()->json([
            'message' => 'request has been success',
            'data' => $friend
        ], Response::HTTP_OK);
    }

    /**
     * show all friend request list which specific user recieve
     *
     * @param  \App\Http\Requests\TokenValidator $request
     * @return \Illuminate\Http\Response
     */
    public function friends_list(TokenValidator $request)
    {
        $friends = Friend::where('receive_user_id', $this->user->id)
                            ->where('request_user_id', $this->user->id)
                            ->orWhere('status', 0)
                            ->get();

        foreach ($friends as $friend) {
            if ($friend->request_user_id == $this->user->id) {
                $friend->receive_user;
            }else {
                $friend->request_user;
            }
        }

        return response()->json(['friends_list' => $friends]);
    }

    /**
     * show all friend request list which specific user receive
     *
     * @param  \App\Http\Requests\Request $request
     * @return \Illuminate\Http\Response
     */
    public function friend_request_list(TokenValidator $request)
    {

        $friends= Friend::where('receive_user_id', $this->user->id)->where('status', 1)->get();

        // $user_id = $this->user->id;
        foreach ($friends as $friend) {
            if ($friend->request_user_id == $this->user->id) {
                $friend->receive_user;
            }else {
                $friend->request_user;
            }
        }

        return response()->json(['friend_request' => $friends]);
    }

    public function accept_friend_request(Friend $id)
    {
        $id->update([
            'status' => 0,
        ]);

        if ($id->request_user_id == $this->user->id) {
            $id->receive_user;
        }else {
            $id->request_user;
        }

        return response()->json([
            'message' => 'accept friend request',
            'data' => $id
        ], Response::HTTP_OK);
    }



}
