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

    public function update(Request $request){
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();

        $checkToken = $jwtAuth->checkToken($token);

        $json = $request->input('json',null);
        $params_array = json_decode($json, true);

        if($checkToken && !empty($params_array)){

            $user = $jwtAuth->checkToken($token, true);

            $validate = \Validator::make($params_array,[
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users,'.$user->sub
            ]);

            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['updated_ad']);
            unset($params_array['remember_token']);

            $user_update = User::where('id', $user->sub)->update($params_array);

            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user,
                'changes' => $params_array
            );
        }else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Invalid data'
            );
        }

        return response()->json($data, $data['code']);
    }
}
