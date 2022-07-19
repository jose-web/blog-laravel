<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function register(Request $request){
        $json = $request->input('json', null);
        $params = json_decode($json); // object
        $params_array = json_decode($json, true); //array

        if(!empty($params) || !empty($params_array)){

            $params_array = array_map('trim', $params_array);

            // validate data
            $validate = \Validator::make($params_array,[
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users',
                'password' => 'required'
            ]);

            if($validate->fails()){
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'User not created',
                    'errors' => $validate->errors()
                );
            }else{

                //$pwd = password_hash($params->password, PASSWORD_BCRYPT, ['cost' => 5]); // password will be encrypted 5 times

                $pwd = hash('sha256', $params->password);

                $user = new User();
                $user->role = 'ROLE_USER';
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;

                $user->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'User created correctly',
                    'user' => $user
                );
            }
        }else{
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Invalid data'
            ); 
        }

        return response()->json($data, $data['code']);
    }


    public function login(Request $request){

        $jwtAuth = new \JwtAuth();

        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);


        $validate = \Validator::make($params_array,[
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validate->fails()){
            $signup = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'User not found',
                'errors' => $validate->errors()
            );
        }else{
            $pwd = hash('sha256', $params->password);

            $signup = $jwtAuth->signup($params->email, $pwd);

            if(!empty($params->gettoken)){
                $signup = $jwtAuth->signup($params->email, $pwd, true);
            }
        }

        return response()->json($signup, 200);
    }
}
