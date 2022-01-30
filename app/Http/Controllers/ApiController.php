<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{

    public function register(Request $request)
    {
        //Validate data
        $data = $request->only('name', 'email', 'password');
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is valid, create new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $credentials = $request->only('email', 'password');

        //Create token
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
            return $credentials;
            return response()->json([
                'success' => false,
                'message' => 'Could not create token.',
            ], 500);
        }

        //Token created, return with success response and jwt token
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user,
            'token' => $token,
        ], Response::HTTP_OK);
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is validated
        //Create token
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
            return $credentials;
            return response()->json([
                'success' => false,
                'message' => 'Could not create token.',
            ], 500);
        }

        //Token created, return with success response and jwt token
        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        //valid credential
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is validated, do logout
        try {
            JWTAuth::invalidate($request->token);

            return response()->json([
                'success' => true,
                'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get_user(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);

        $user = JWTAuth::authenticate($request->token);

        return response()->json(['user' => $user]);
    }

    public function get_all_user(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);

        $users = User::all();

        return response()->json(['user' => $users]);
    }

    public function update_profile(Request $request)
    {
        //Validate data
        $data = $request->only('name', 'email', 'password','pic_name','token');
        $validator = Validator::make($data, [
            'name' => 'string',
            'email' => 'email',
            'password' => 'string|min:6|max:50',
            'token' => 'required'
        ]);

        $user  = JWTAuth::parseToken()->authenticate();

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //画像の扱いに関して
        if ($file = $request->file('pic_name')){
            $fileName = Storage::disk('s3')->putFile('/post',$file, 'public');
        } else {
            //pic_nameが来なかった時には元の値を入れる
            $fileName = $user->pic_name;
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->pic_name = $fileName;
        $user->save();

        //User created, return success response
        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ], Response::HTTP_OK);
    }

    // public function check_and_put_data($model, $data){
    //     if($data){
    //         $model = $data;
    //     }
    // }


}
