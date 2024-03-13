<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\OrderDriver;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Type\Time;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::user()->role < 3) {
            $orders = Order::all();
            return response()->json([
                "message" => "listed successfully",
                "data" => $orders
            ], Response::HTTP_OK);
        } elseif (Auth::user()->role == 3) {
            $user = User::where('id', Auth::id())->first();
            $driverOrders = $user->orderDrivers()->where('visible',1)->with('order')->get()->pluck('order');
            $waitedOrders = Order::query()->where('status', 'w')->get();
            $orders = $waitedOrders->merge($driverOrders);
            return response()->json([
                "message" => "listed successfully",
                "data" => $orders
            ], Response::HTTP_OK);
        } elseif (Auth::user()->role == 4) {
            $orders = Order::query()->where('customer_id', Auth::id())->where('visible',1)->get();
            return response()->json([
                "message" => "listed successfully",
                "data" => $orders
            ], Response::HTTP_OK);
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
        $input = $request->validate([
            "to" => 'required'
        ]);
        $input['customer_id'] = Auth::id();
        $input['from'] = OrderController::addressNow();
        $input['estimatedCost'] = OrderController::estimatedCost($input['from'], $input['to']);
        $input['estimatedDuration'] = OrderController::estimatedDuration($input['from'], $input['to']);
        $input['status'] = 'd';
        $order = Order::create($input);
        $notification = OrderController::send($order);
        return response()->json([
            "message" => "Order's created successfully.",
            "data" => $order,
            "notification"=>$notification
        ],201);
    }

    public function submit(Request $request, Order $order)
    {
        if ($order->customer_id == Auth::id()) {
            $order->status = 'w';
            $order->save();
            $message = OrderController::send($order);
            return response()->json([
                "message" => "Your order has sent successfully .." . $message,
            ], 200);
        } else {
            return response()->json([
                "message" => "Unauthorized"
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        $order = Order::find($order);

        if ($order == null) {
            return response()->json([
                "message" => "not found",
            ]);
        } elseif (
            Auth::user()->role < 3
            || (Auth::user()->role == 3 && $order->driver_id == Auth::id())
            || (Auth::user()->role == 4 && $order->customer_id == Auth::id())
        ) {
            return response()->json([
                "message" => "found",
                "data" => $order
            ], 200);
        } else {
            return response()->json([
                "message" => "unauthorized",
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        if ($order->status == 'w' || $order->status == 'd') {
            $order->to = $request->to;
            $order->save();
            return response()->json([
                "message" => "Order's updated successfully.",
                "data" => $order
            ]);
        } else {
            return response()->json([
                "message" => "You can't edit this order ,it has already been sent",
                "data" => $order
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        if (
            Auth::user()->role == 4
            && $order->customer_id == Auth::id()
            && $order->status == 'w'
        ) {
            $order->delete();
            return response()->json([
                'messege' => "deleted successfully",
                'data' => OrderController::index()
            ]);
        } elseif (
            Auth::user()->role == 3
            && $order->driver_id == Auth::id()
            && $order->status != 'w'
        ) {
            $order->delete();
            return response()->json([
                'messege' => "deleted successfully",
                'data' => OrderController::index()
            ]);
        } elseif (
            Auth::user()->role < 3
            && $order->status != 'w'
        ) {
            $order->delete();
            return response()->json([
                'messege' => "deleted successfully",
                'data' => OrderController::index()
            ]);
        }
    }

    public function estimatedCost(String $from, String $to)
    {
        return rand(100,500)*100;
    }

    public function estimatedDuration(String $from, String $to)
    {
        return rand(1,10)*10;
    }

    public function addressNow()
    {
        return 'myAddress';
    }

    public function send(Order $order)
    {
        return 'send a notification to drivers[] from laravel';
    }

    public function accept(Request $request, Order $order)
    {
        $input['driver_id'] =  Auth::id();
        $input['order_id'] =  $order->id;
        $input['acceptOrReject'] = 'a';
        $orderDriver = OrderDriver::create($input);
        $orderDriver->save();

        $order->status = 'a';
        $order->accepted = true;
        $order->save();
        return response()->json([
            "message" => "send a notification to the customer",
            "data" => $orderDriver
        ], Response::HTTP_OK);
    }

    public function reject(Order $order)
    {

        $input['driver_id'] =  Auth::id();
        $input['order_id'] =  $order->id;
        $input['acceptOrReject'] = 'r';
        $orderDriver = OrderDriver::create($input);
        $orderDriver->save();

        $order->status = 'r';
        $order->rejected = true;
        $order->save();


        $drivers = User::query()->where('role', 3)->get();


        $allRejected = true;

        foreach ($drivers as $driver) {
            $orderDriverDesision = OrderDriver::query()->where('driver_id', $driver->id)->where('order_id', $order->id)->get()->first();
            if (!$orderDriverDesision || $orderDriverDesision->acceptOrReject != "r") {
                $allRejected = false;
                break;
            }
        }

        if ($allRejected) {
            $order->status = 'r';
            $order->rejected = true;
            $order->save();
            return response()->json([
                "message" => "send a notification to other drivers and customer",
                "data" => $order
            ], Response::HTTP_OK);
        }

        return response()->json([
            "message" => "rejected",
            "data" => $order
        ], Response::HTTP_OK);
    }


    public function assurePayment(Request $request, Order $order)
    {
        $driver = OrderDriver::query()->where('order_id', $order->id)->get()->first();
        $driverId = $driver->driver_id;
        if (Auth::id() == $driverId) {
            $order->paid = true;
            $order->status='e';
            $order->save();
            return response()->json([
                'message' => 'paid',
                'data'=>$order
            ], 200);
        } else {
            return response()->json([
                'message' => 'It is not your order!!!',
                'data' => $order
            ], 403);
        }
    }

    public function observe()
    {
        # code...
    }

    function invisibleAsCustomer(Order $order){
        // to delete an order from your list but not from DB.....................
        $order->visible=false;
        $order->save();
        return response()->json([
            "message" => "successfully deleted from your list ..",
            "data" => OrderController::index()
        ], 200);
    }

    function invisibleAsDriver(Order $order){
        // to delete an order from your list but not from DB.....................
        $orderDriver = OrderDriver::query()->where('driver_id',Auth::id())->where('order_id',$order->id)->get()->first();
        $orderDriver['visible'] = false;
        $orderDriver->save();
        return response()->json([
            "message" => "successfully deleted from your list ..",
            "data" => $orderDriver
        ], 200);
    }


    public function begin(Order $order)
    {
        $driver = OrderDriver::query()->where('order_id', $order->id)->get()->first();
        $driverId = $driver->driver_id;
        if (Auth::id() == $driverId) {
            $order->startTime = new DateTime();
            $order->save();
            return response()->json([
                'message' => 'determined successfully',
                'data' => $order
            ], 200);
        } else {
            return response()->json([
                'message' => 'It is not your order!!!',
                'data' => $order
            ], 403);
        }
    }

    public function end(Order $order)
    {
        $driver = OrderDriver::query()->where('order_id', $order->id)->get()->first();
        $driverId = $driver->driver_id;
        if (Auth::id() == $driverId) {
            $order->endTime = new DateTime();
            $end = Carbon::parse($order->endTime);
            $start = Carbon::parse($order->startTime);
            $order->realDuration = $end->diffForHumans($start);
            $order->realCost = 1000 * (float) $order->realDuration;
            $order->save();
            return response()->json([
                'message' => 'determined successfully',
                'data' => $order
            ], 200);
        } else {
            return response()->json([
                'message' => 'It is not your order!!!',
                'data' => $order
            ], 403);
        }
    }



    function calculate(Request $request) {
        
        $ec= OrderController::estimatedCost($request->from,$request->to);
        $ed=OrderController::estimatedDuration($request->from,$request->to);
 
 
        return response()->json([
         "message" => "successfully calculated your list!",
         "data" =>[
             'estimatedDuration'=>$ed,
             'estimatedCost'=>$ec
         ]
     ], 200);
         
     }



}
