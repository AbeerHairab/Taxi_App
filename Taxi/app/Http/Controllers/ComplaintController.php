<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplaintController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::user()->role < 3) {
            $complaints = Complaint::all();
            return response()->json([
                "message" => "listed successfully",
                "data" => $complaints
            ]);
        } else {
            return response()->json([
                "message" => "Unauthorized",
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Auth::user()->role == 4) {
            $input = $request->validate([
                "content" => 'required'
            ]);
            $input['customer_id']=Auth::id();
            $complaint = Complaint::create($input);
            return response()->json([
                "message" => "Complaint's created successfully.",
                "data" => $complaint
            ]);
        } else {
            return response()->json([
                "message" => "Unauthorized",
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Complaint  $complaint
     * @return \Illuminate\Http\Response
     */
    public function show(Complaint $complaint)
    {
        if (Auth::user()->role < 3) {
            return response()->json([
                'message'=>'found',
                'data' => $complaint
            ]);
        } else {
            return response()->json([
                "message" => "Unauthorized",
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Complaint  $complaint
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Complaint $complaint)
    {
        if (Auth::id() == $complaint->customer_id) {
            $complaint->update($request->all());
            $complaint->save();
            return response()->json([
                "message" => "Complaint's updated successfully.",
                "data" => $complaint
            ]);
        } else {
            return response()->json([
                "message" => "Unauthorized",
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Complaint  $complaint
     * @return \Illuminate\Http\Response
     */
    public function destroy(Complaint $complaint)
    {
        if (Auth::user()->role < 3 || Auth::id() == $complaint->customer_id) {
            $complaint->delete();
            $complaints = Complaint::all();
            return response()->json([
                'messege' => "deleted successfully",
                'data' => $complaints

            ]);
        } else {
            return response()->json([
                "message" => "Unauthorized",
            ]);
        }
    }
}
