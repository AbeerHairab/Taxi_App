<?php

namespace App\Http\Controllers;

use App\Models\BookOrAdd;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\Driver;
use App\Models\TripCustomer;
use App\Models\TripDriver;
use DateTime;
use Hamcrest\Core\IsEqual;
use Illuminate\Validation\Rules\Exists;
use phpseclib3\File\ASN1\Maps\Trinomial;

use function PHPUnit\Framework\isEmpty;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::user()->role < 3) {
            $trips = Trip::all();
            return response()->json([
                "message" => "listed successfully",
                "data" => $trips
            ], 200);
        } elseif (Auth::user()->role == 3) {
            $trips = Trip::query();
            $user = User::where('id', Auth::id())->first();
            $driverTrips = $user->tripDrivers()->where('visible',true)->with('trip')->get()->pluck('trip');
            $wTrips = Trip::where('status', 'w')->get();
            $trips = $driverTrips->merge($wTrips);
            return response()->json([
                "message" => "listed successfully",
                "data" => $trips
            ], 200);
        } elseif (Auth::user()->role == 4) {
            $user = User::where('id', Auth::id())->first();
            $customerTrips = $user->tripCustomers()->where('visible',true)->with('trip')->get()->pluck('trip');
            $pTrips = Trip::where('status', 'a')->orWhere('status', 'p')->get();
            $trips = $customerTrips->merge($pTrips);
            return response()->json([
                "message" => "listed successfully",
                "data" => $trips
            ], 200);
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
                "to" => 'required',
                "from" => 'required',
                "bookedSeats" => 'required',
                "startTime" => 'required'
            ]);
            $input['NumOfSeats'] = 4;
            $input['bookedSeats'] = $request['bookedSeats'];
            $input['startTime'] = $request['startTime'];
            $input['endTime'] = now();
            $input['estimatedCost'] = TripController::estimatedCost($input['from'], $input['to']);
            $input['estimatedDuration'] = TripController::estimatedDuration($input['from'], $input['to']);
            $input['status'] = 'd';
            $trip = Trip::create($input);

            $trip->availableSeats = $trip->NumOfSeats - $trip->bookedSeats;
            $trip->save();

            $data['customer_id'] = Auth::id();
            $data['trip_id'] = $trip->id;
            $data['bookOrAdd'] = "add";
            $addTrip = TripCustomer::create($data);
            $addTrip->save();

            return response()->json([
                "message" => "Trip's created successfully.",
                "data" => $trip
            ]);
        } elseif (Auth::user()->role == 3) {
            $input = $request->validate([
                "to" => 'required',
                "from" => 'required',
                "bookedSeats" => 'required',
                "startTime" => 'required',
            ]);

            
            $input['NumOfSeats'] = 4;
            $input['bookedSeats'] = $request['bookedSeats'];
            $input['startTime'] = $request['startTime'];
            $input['endTime'] = now();
            $input['availableSeats'] = 4 - $request['bookedSeats'];
            $input['estimatedCost'] = TripController::estimatedCost($input['from'], $input['to']);
            $input['estimatedDuration'] = TripController::estimatedDuration($input['from'], $input['to']);
            $input['status'] = 'd';
            $trip = Trip::create($input);

            $data['driver_id'] = Auth::id();
            $data['trip_id'] = $trip->id;
            $data['acceptOrReject'] = 'a';
            $tripDriver = TripDriver::create($data);

            return response()->json([
                "message" => "Trip's created successfully.",
                "data" => $trip
            ],Response::HTTP_CREATED);
        } else {
            return response()->json([
                "message" => "Unauthorized",
            ],Response::HTTP_UNAUTHORIZED);
        }
    }



    public function submit(Trip $trip)
    {
        //Customer................................................................
        if (Auth::user()->role == 4) {
            $customer = TripCustomer::query()->where('trip_id', $trip->id)->get()->first();
            $customerId = $customer->customer_id;
            if (Auth::id() == $customerId) {
                $trip->status = 'w';
                $trip->save();
                return response()->json([
                    "message" => "Your trip has published successfully ..",
                    'data' => $trip
                ], 200);
            } else {
                return response()->json([
                    'message' => 'It is not your trip!!!',
                    'data' => $trip
                ], 403);
            }


            //Driver.............................................................
        } elseif (Auth::user()->role == 3) {
            $driver = TripDriver::query()->where('trip_id', $trip->id)->get()->first();
            $driverId = $driver->driver_id;
            if (Auth::id() == $driverId) {
                $trip->status = 'p';
                $trip->save();
                $message = TripController::send($trip);
                return response()->json([
                    "message" => "Your trip has sent successfully .." . $message,
                ], 200);
            } else {
                return response()->json([
                    "message" => "Unauthorized"
                ]);
            }
        } else {
            return response()->json([
                'message' => 'It is not your trip!!!',
                'data' => $trip
            ], 403);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Trip  $trip
     * @return \Illuminate\Http\Response
     */
    public function show(Trip $trip)
    {
        $trip = Trip::find($trip);

        if ($trip == null) {
            return response()->json([
                "message" => "not found",
            ]);
        } else  {
            return response()->json([
                "message" => "found",
                "data" => $trip
            ], 200);
        } 
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Trip  $trip
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Trip $trip)
    {
        if(Auth::user()->role==3){
        $tripDriver = TripDriver::query()->where('driver_id',Auth::id())->where('trip_id',$trip->id)->get()->first();
        if ($trip->status == 'p')
         {
            $trip->update($request->all());
            $trip->save();
            return response()->json([
                "message" => "Trip's updated successfully.",
                "data" => $trip
            ]);
        } else {
            return response()->json([
                "message" => "You can't edit this trip ,it has already been sent",
                "data" => $trip
            ],200);
        }
        if(Auth::user()->role==4){
            $tripDriver = TripDriver::query()->where('customer_id',Auth::id())->where('trip_id',$trip->id)->get()->first();
            if ($trip->status == 'w' && $tripDriver->bookOrAdd=="add")
             {
                $trip->update($request->all());
                $trip->save();
                return response()->json([
                    "message" => "Trip's updated successfully.",
                    "data" => $trip
                ]);
            } else {
                return response()->json([
                    "message" => "You can't edit this trip ,it has already been sent",
                    "data" => $trip
                ],200);
            }
    }
    }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Trip  $trip
     * @return \Illuminate\Http\Response
     */
    public function destroy(Trip $trip)
    {
        if ($trip->status == 'a') {
            return response()->json([
                'messege' => "You can't delete this trip",
                'data' => TripController::index()
            ]);
        }

        else{
            $trip->delete();
            return response()->json([
                'messege' => "deleted successfully",
                'data' => TripController::index()
            ],200);
        }
    }

    // public function addressNow()
    // {
    //     return 'myAddress';
    // }

    function calculate(Request $request) {
        
       $ec= TripController::estimatedCost($request->from,$request->to);
       $ed=TripController::estimatedDuration($request->from,$request->to);


       return response()->json([
        "message" => "successfully calculated your list!",
        "data" =>[
            'estimatedDuration'=>$ed,
            'estimatedCost'=>$ec
        ]
    ], 200);
        
    }
    public function estimatedCost(String $from, String $to)
    {
        return rand(10000,50000);
    }

    public function estimatedDuration(String $from, String $to)
    {
        return $to;
    }

    public function send(Trip $trip)
    {
        return 'send a notification to drivers[]';
    }

    function unvisibleAsDriver(Trip $trip){
        // to delete an order from your list but not from DB.....................
        $tripDriver = TripDriver::query()->where('driver_id',Auth::id())->where('trip_id',$trip->id)->get()->first();
        $tripDriver['visible'] = false;
        $tripDriver->save();
        return response()->json([
            "message" => "successfully deleted from your list ..",
            "data" => TripController::index()
        ], 200);
    }

    function unvisibleAsCustomer(Trip $trip){
        // to delete an order from your list but not from DB.....................
        $orderCustomer = TripCustomer::query()->where('customer_id',Auth::id())->where('trip_id',$trip->id)->get()->first();
        $orderCustomer['visible'] = false;
        $orderCustomer->save();
        return response()->json([
            "message" => "successfully deleted from your list ..",
            "data" => TripController::index()
        ], 200);
    }


    public function accept(Request $request, Trip $trip)
    {

        $data['driver_id'] = Auth::id();
        $data['trip_id'] = $trip->id;
        $data['acceptOrReject'] = "a";
        $acceptTrip = TripDriver::create($data);
        $acceptTrip->save();

        $trip->status = 'a';
        $trip->accepted = true;
        $trip->rejected = false;
        //     if($request['bookedSeats']){
        //     $trip->bookedSeats = $trip->bookedSeats + $request['bookedSeats'];
        //     $trip->availableSeats = $trip->NumOfSeats - $trip->bookedSeats;
        $trip->save();
        // }
        return response()->json([
            "message" => "send a notification to the customer",
            "data" => $trip
        ], 200);
    }

    public function book(Trip $trip, Request $request)
    {
        
            $request->validate([
                "bookedSeats" => 'required',
            ]);
            if ($request['bookedSeats'] > $trip->availableSeats) {
                return response()->json([
                    "message" => " Sorry... there aren't enough seats",
                    "data" => $trip
                ], 200);
            }
           else{
            $trip->status = 'a';
            $trip->save();
            $trip->availableSeats = $trip->availableSeats - $request['bookedSeats'];
            $trip->bookedSeats = $trip->bookedSeats + $request['bookedSeats'];
            $trip->save();
            if ($trip->availableSeats == 0) {
                $trip->status = 'b';
            }
            $trip->save();

            $data['customer_id'] = Auth::id(); 
            $data['trip_id']=$trip->id;
            $data['bookOrAdd'] = 'book';
            $tripCustomer = TripCustomer::create($data);

            return response()->json([
                "message" => "booked successfully",
                "data" => $trip
            ], 200);
           }
        
    }

    public function cancelBook(Request $request, Trip $trip) {

        // a customer calncels their reservation ........... status=>'c'(canceled)
        $tripCustomer = TripCustomer::query()->where('customer_id',Auth::id())->where('trip_id',$trip->id)->get()->first();




    
    }

    public function cancelAsDriver(Trip $trip) {

        // a driver calncels their trip ........... status=>'c'(canceled)

        $tripDriver = TripDriver::query()->where('driver_id',Auth::id())->where('trip_id',$trip->id)->get()->first();

        if ($tripDriver == null)
        {
            return response()->json([
                'messege' => "Not found",
                'data' => TripController::index()
            ],404);
        }
        if ( $tripDriver && $tripDriver->acceptOrReject == 'a' && $trip->bookedSeats == 0) {
            $trip->status ='c';
            $trip->save();
            return response()->json([
                'messege' => "canceled successfully",
                'data' => $trip
            ],200);
        } 
        else{
            return response()->json([
                'messege' => "You can't cancel this trip!",
                'data' => TripController::index()
            ],200);
        }
    
    }

    public function cancelAsCustomer(Trip $trip) {

        // a customer calncels their trip ........... status=>'c'(canceled)

        $tripCustomer = TripCustomer::query()->where('customer_id',Auth::id())->where('trip_id',$trip->id)->get()->first();

        if ( $tripCustomer->bookOrAdd == 'add' && $trip->status == 'w') {
            $trip->status ='c';
            $trip->save();
            return response()->json([
                'messege' => "canceled successfully",
                'data' => TripController::index()
            ],200);}

        else{
            return response()->json([
                'messege' => "You can't cancel this trip!",
                'data' => TripController::index()
            ],200);
        }
        
    
    }

    public function history(){

        // my previous trips..................
        
    }

    public function reject(Request $request, Trip $trip)
    {
        $input['driver_id'] =  Auth::id();
        $input['trip_id'] =  $trip->id;
        $input['acceptOrReject'] = 'r';
        $tripDriver = TripDriver::create($input);
        $tripDriver->save();

        $drivers = User::query()->where('role', 3)->get();

        $allRejected = true;

        foreach ($drivers as $driver) {
            $tripDriverDesision = TripDriver::query()->where('driver_id', $driver->id)->where('trip_id', $trip->id)->get()->first();
            if (!$tripDriverDesision || $tripDriverDesision->acceptOrReject != "r") {
                $allRejected = false;
                break;
            }
        }

        if ($allRejected) {
            $trip->status = 'r';
            $trip->rejected = true;
            $trip->accepted = false;
            $trip->save();
            return response()->json([
                "message" => "send a notification to other drivers and customer",
                "data" => $trip
            ], Response::HTTP_OK);
        }

        return response()->json([
            "message" => "rejected",
            "data" => $trip
        ], Response::HTTP_OK);
    }

    public function assurePayment(Request $request, Trip $trip)
    {
        $driver = TripDriver::query()->where('trip_id', $trip->id)->get()->first();
        $driverId = $driver->driver_id;
        if (Auth::id() == $driverId) {
            $trip->paid = true;
            $trip->save();
            return response()->json([
                'message' => 'paid',
                'data' => $trip
            ], 200);
        } else {
            return response()->json([
                'message' => 'It is not your trip!!!',
                'data' => $trip
            ], 403);
        }
    }

    public function observe()
    {
        # code...
    }

    public function begin(trip $trip)
    {
        $driver = tripDriver::query()->where('trip_id', $trip->id)->get()->first();
        $driverId = $driver->driver_id;
        if (Auth::id() == $driverId) {
            $trip->startTime = new DateTime();
            $trip->save();
            return response()->json([
                'message' => 'determined successfully',
                'data' => $trip
            ], 200);
        } else {
            return response()->json([
                'message' => 'It is not your trip!!!',
                'data' => $trip
            ], 403);
        }
    }

    public function end(trip $trip)
    {
        $driver = tripDriver::query()->where('trip_id', $trip->id)->get()->first();
        $driverId = $driver->driver_id;
        if (Auth::id() == $driverId) {
            $trip->endTime = new DateTime();
            $end = Carbon::parse($trip->endTime);
            $start = Carbon::parse($trip->startTime);
            $trip->realDuration = $end->diffForHumans($start);
            $trip->realCost = 1000 * (float) $trip->realDuration;
            $trip->save();
            return response()->json([
                'message' => 'determined successfully',
                'data' => $trip
            ], 200);
        } else {
            return response()->json([
                'message' => 'It is not your trip!!!',
                'data' => $trip
            ], 403);
        }
    }
}
