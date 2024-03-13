<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {        
        $admins = Admin::query()->with('user')->get();
        return response()->json([
            "message" => "listed successfully",
            "data" => $admins
        ],Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->all(), 422);
        }

        $request['password'] = Hash::make($request['password']);

        $input = $request->validate([
            'name' => 'required',
            'phone' => 'required|unique:users',
            'password' => 'required',
            'role'=>'required' 
        ]);
        $user = User::create($input);
        $success['token'] = $user->createToken('MyApp')->accessToken;
        $success['token_type'] = 'Bearer';
        $success['name'] = $user->name;

        $data=$request->validate([
            "photo"=>'required',
        ]);
        $data['user_id']=$user->id;
        $admin = Admin::create($data);
        return response()->json([
            "success" => true,
            "message" => "Admin's created successfully.",
            "data" => [
                "admin"=>$admin,
                "user"=>$success
                ]
        ]);
    }
    

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function show(Admin $admin)
    {
        return response()->json([
            'message'=>'found',
            'data' => $admin
        ],Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Admin $admin)
    {
        $admin->update($request->all());            
        $admin->save();
        return response()->json([
            "message" => "admin updated successfully.",
            "data" => $admin
        ],Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function destroy(Admin $admin)
    {
        $admin->delete();
        $admins = Admin::all();
        return response()->json([
            'messege' => "deleted successfully",
            'data'=>$admins
        ],Response::HTTP_OK);
   
    }
}
