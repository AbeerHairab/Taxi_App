<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
            $customers = Customer::query()->with('user')->get();
            return response()->json([
                "message" => "listed successfully",
                "data" => $customers
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
            $input = $request->validate([
                "phone" => 'required|unique:users',
            ]);
            $customer = Customer::create($input);
            return response()->json([
                "message" => "customer's created successfully.",
                "data" => $customer
            ],Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function show(Customer $customer)
    {
        if(Auth::user()->role > 3){
            return response()->json([
                'message'=>'Unauthorized'
            ],Response::HTTP_UNAUTHORIZED);
        }else{
            return response()->json([
                'message'=>'found',
                "data" => $customer
            ],Response::HTTP_OK);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customer $customer)
    {
            $customer->update($request->all());
            $customer->save();
            return response()->json([
                "message" => "customer's updated successfully.",
                "data" => $customer
            ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer $customer)
    {
            $customer->delete();
            $customers = Customer::all();
            return response()->json([
                "messege" => "customer's deleted successfully",
                "data" => $customers

            ]);
    }

}
