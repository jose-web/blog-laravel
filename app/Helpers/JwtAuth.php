<?php
namespace APP\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\db;
use App\Models\User;

class JwtAuth{

    public static $key = '1234567890';

    public function signup($email, $password, $get_token = null){

        // Check if user exists
        $user = User::where([
            'email' => $email,
            'password' => $password
        ])->first();

        $signup = is_object($user);

        if($signup){

            // Generate token

            $token = array(
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'surname' => $user->surname,
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60)
            );

            $jwt = JWT::encode($token, self::$key, 'HS256');
            $decoded = JWT::decode($jwt, self::$key, ['HS256']);

            if(is_null($get_token)){
                $data = $jwt;
            }else{
                $data = $decoded;
            }

        }else{
            $data = array(
                'status' => 'error',
                'message' => 'wrong email or password'
            );
        }

        return $data;
    }
}