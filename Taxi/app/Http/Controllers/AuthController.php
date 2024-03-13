<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Admin;
//use GuzzleHttp\Psr7\Response;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\FlareClient\Http\Response as HttpResponse;

class AuthController extends Controller
{
    public function index()
    {
        
        $users = User::all();
        return response()->json([
            "message" => "listed successfully",
            "data" => $users
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|unique:users',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->all(), 422);
        }

        $input = $request->all();
        $input['role'] = 4;
        $user = User::create($input);
        $success['token'] = $user->createToken('MyApp')->accessToken;
        $success['token_type'] = 'Bearer';

        //Customer..........................
        $inputCustomer['user_id']=$user->id;
        $customer = Customer::create($inputCustomer);
        return response()->json([
            'user' => $success,
            'customer'=>$customer,
            'message' => "created successfully ."
        ],Response::HTTP_CREATED);

        
    }

    // public function loginEmployee(Request $request)
    // {

    //         if (Auth::user()->role<=3){

    //         $validator = Validator::make($request->all(), [
    //             'phone' => 'required',
    //             'passowrd' => 'required',
    //         ]);
    
    //         if ($validator->fails()) {
    //             return response()->json($validator->errors()->all(), 422);
    //         }
    
    //         $credentials = request(['phone','password']); 
    //         $user = User::where('phone',$credentials['phone'])->first();
    
    //         $credentials = $request->validate([
    //             'phone' => 'required',
    //             'password' => 'required',
    //         ]);
    //         if (!$user) {
    //             return response()->json(['message'=>'invalid'],401);
    //         }
    
    //         Auth::login($user);
    //         $success['token_type'] = 'Bearer';
    //         $success['token'] = $user->createToken('MyApp')->accessToken;
    //         $success['user'] = $user;
    //         return response()->json([
    //             'data' => $success,
    //             'message' =>"logged in"
    //         ],200);
    //     }

       
    // }

        function login(Request $request) { 

            $validator = Validator::make($request->all(), [
                'phone' => 'required',
            ]);
        
            if ($validator->fails()) {
                return response()->json($validator->errors()->all(), 422);
            }
        
            $credentials = request(['phone']); 
            $user = User::where('phone',$credentials['phone'])->first();
        
            $credentials = $request->validate([
                'phone' => 'required',
            ]);
            if (!$user) {
                return response()->json(['message'=>'invalid'],401);
            }
        
            Auth::login($user);
            $success['token_type'] = 'Bearer';
            $success['token'] = $user->createToken('MyApp')->accessToken;
            $success['user'] = $user;
            return response()->json([
                'data' => $success,
                'message' =>"logged in"
            ],200);
        
    }

    public function logout(Request $request)
    {

        $token = $request->user()->token();
        $token->revoke();
        $response = ['message' => 'You have been successfully logged out!'];
        return response()->json($response,200);
    }


     /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $driver = Driver::query()->where('user_id','=',$user->id)->get();
        $admin = Admin::query()->where('user_id','=',$user->id)->get();
        $customer = Customer::query()->where('user_id','=',$user->id)->get();
        if($driver != "[]"){
            return response()->json([
                'messege' => "you have to delete the driver first",
                'data' => $driver
            ], Response::HTTP_OK);
        }

        if($admin != "[]"){
            return response()->json([
                'messege' => "you have to delete the admin first",
                'data' => $admin
            ], Response::HTTP_OK);
        }

        if($customer!="[]"){
            return response()->json([
                'messege' => "you have to delete the customer first",
                'data' => $customer
            ], Response::HTTP_OK);
        }

        $user->delete();
        $users = User::all();
        return response()->json([
            'messege' => "deleted successfully",
            'data'=>$users
        ],Response::HTTP_OK);
    }
}
