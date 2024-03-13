<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Car;

class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $drivers = Driver::query()->with('user')->get();
        return response()->json([
            "message" => "listed successfully",
            "data" => $drivers
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
        //User..................
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
            'password' => 'required'
        ]);

        $request['password'] = Hash::make($request['password']);


        if ($validator->fails()) {
            return response()->json($validator->errors()->all(), 422);
        }
        $inputUser = $request->validate([
            'name' => 'required',
            'phone' => 'required|unique:users',
            'password' => 'required'
        ]);
        $inputUser['role'] = 3;
        $user = User::create($inputUser);
        $success['token'] = $user->createToken('MyApp')->accessToken;
        $success['token_type'] = 'Bearer';
        $success['name'] = $user->name;

        //Car.....................
        $inputCar = $request->validate([
            "number" => 'required|unique:cars',
            "type" => 'required',
            "photo" => 'required',
            "truckOrTaxi" => 'required'
        ]);
        $car = Car::create($inputCar);
        if($car->truckOrTaxi=='truck')
        {
            $car->size = $request->size;
            $car->save();
        }

        //Driver.......................
        $inputDriver = $request->validate([
            "photo" => 'required',
            "drivingCertificate" => 'required'
        ]);
        $inputDriver['user_id'] = $user->id;
        $inputDriver['car_id'] = $car->id;
        $driver = Driver::create($inputDriver);

        return response()->json([
            "message" => "driver's created successfully.",
            "data" => [
                "driver" => $driver,
                "user" => $success,
                "car" => $car
            ]
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Driver  $driver
     * @return \Illuminate\Http\Response
     */
    public function show(Driver $driver)
    {
        return response()->json([
            'message' => 'found',
            "data" => $driver
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Driver  $driver
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Driver $driver)
    {
        $driver->update($request->all());
        $driver->save();
        return response()->json([
            "message" => "driver's updated successfully.",
            "data" => $driver
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Driver  $driver
     * @return \Illuminate\Http\Response
     */
    public function destroy(Driver $driver)
    {
        $car = $driver->car;
        $driver->delete();
        $driverCar = Car::query()->where('id',$car->id);
        $driverCar->delete();
        $drivers = Driver::all();
        return response()->json([
            "messege" => "deleted successfully",
            "data" => $drivers
        ],Response::HTTP_OK);
    }

    function setAvailabilityTrue() {
        $me = Driver::where('user_id',Auth::id())->get()->first();
        $me->availability == true;
        return response()->json([
            "messege" => "Done....",
            "data" => $me
        ],Response::HTTP_OK);        
    }

    function setAvailabilityFalse() {
        $me = Driver::where('user_id',Auth::id())->get()->first();
        $me->availability == false;
        return response()->json([
            "messege" => "Done....",
            "data" => $me
        ],Response::HTTP_OK);        
    }

}
