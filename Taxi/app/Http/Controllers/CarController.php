<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Car;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use function PHPSTORM_META\type;
use function PHPUnit\Framework\isEmpty;
use Illuminate\Support\Facades\Auth;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $cars = Car::all();
        return response()->json([
            "message" => "listed successfully",
            "data" => $cars
        ], Response::HTTP_OK);
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
            "number" => 'required|unique:cars',
            "type" => 'required',
            "photo" => 'required',
            "truckOrTaxi" => 'required'
        ]);
        $car = Car::create($input);
        return response()->json([
            "success" => true,
            "message" => "Car's created successfully.",
            "data" => $car
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Car  $car
     * @return \Illuminate\Http\Response
     */
    public function show(Car $car)
    {
        if (Auth::user()->role > 3) {
            return response()->json([
                "message" => "Unauthorized",
            ]);
        } else {
            
            return response()->json([
                'message' => 'found',
                'data' => $car
            ], Response::HTTP_OK);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Car  $car
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Car $car)
    {

        $car->update($request->all());
        $car->save();
        return response()->json([
            "message" => "Car's updated successfully.",
            "data" => $car
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Car  $car
     * @return \Illuminate\Http\Response
     */
    public function destroy(Car $car)
    {

        $driver = Driver::query()->where('car_id','=',$car->id)->get();
        if($driver){
            return response()->json([
                'messege' => "you have to delete the driver first",
                'data' => $driver
            ], Response::HTTP_OK);
        }
        
        $car->delete();
        $cars = Car::all();
        return response()->json([
            'messege' => "deleted successfully",
            'data' => $cars
        ], Response::HTTP_NO_CONTENT);
    }

    public function search(Request $request)
    {
        $cars = Car::query()->where('type', $request->query('type'))->get();
        return response()->json([
            'status' => true,
            'data' => $cars
        ], Response::HTTP_OK);
    }
}
