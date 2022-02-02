<?php

// use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;


if (! function_exists('__construct')) {
    /**
     * 関数の説明文
     *
     * @param  string  $xxx
     */
    function __construct()
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





// function get_user_data($friends, $user_id){

//     foreach ($friends as $friend) {
//         if ($friend->request_user_id == $user_id) {
//             $friend->receive_user;
//         }else {
//             $friend->request_user;
//         }
//     }
// }
}
