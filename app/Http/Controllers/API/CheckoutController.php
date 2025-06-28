<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Models\Cart;
use App\Models\Orderitems;

class CheckoutController extends Controller
{

     public function placeorder(Request $request)
    {
        if(auth('sanctum')->check())
        {
            $user_id = auth('sanctum')->user()->id;
        }
        else
        {
            // If not authenticated, use session ID or assign an 'unknowncustomer'
            if (session()->has('guest_user_id')) {
                $user_id = session('guest_user_id');
            } else {
                $user_id = 'visitor_' . rand(1000, 9999);  // You can replace this with a more robust unique ID generation method
                session(['guest_user_id' => $user_id]);  // Store in session
            }
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|max:191',
            'lastname' => 'required|max:191',
            'phone' => 'required|max:191',
            'email' => 'required|max:191',
            'address' => 'required|max:191',
            'city' => 'required|max:191',
            'state' => 'required|max:191',
            'zipcode' => 'required|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages(),
            ]);
        }

        // Create new order
        $order = new Order;
        $order->user_id = $user_id;
        $order->firstname = $request->firstname;
        $order->lastname = $request->lastname;
        $order->phone = $request->phone;
        $order->email = $request->email;
        $order->address = $request->address;
        $order->city = $request->city;
        $order->state = $request->state;
        $order->zipcode = $request->zipcode;
        $order->payment_mode = $request->payment_mode ?? "COD"; // Default to COD if not provided
        $order->payment_id = $request->payment_id;
        $order->tracking_no = "Hair" . rand(111111, 999999);
        $order->save();

        // Fetch cart for user or guest
        $cart = Cart::where('user_id', $user_id)->get();
        $orderitems = [];
        foreach ($cart as $item) {
            $orderitems[] = [
                'product_id' => $item->product_id,
                'qty' => $item->product_qty,
                'price' => $item->product->selling_price,
            ];
            $item->product->update([
                'qty' => $item->product->qty - $item->product_qty
            ]);
        }
        $order->orderitems()->createMany($orderitems);
        Cart::destroy($cart);

        return response()->json([
            'status' => 200,
            'messages' => 'Order Placed Successfully',
        ]);
    }


             public function validateorder( Request $request){

                     if(auth('sanctum')->check())
        {
            $validator = Validator::make($request->all(),[
                'firstname'=>'required|max:191',
                'lastname'=>'required|max:191',
                'phone'=>'required|max:191',
                'email'=>'required|max:191',
                'address'=>'required|max:191',
                'city'=>'required|max:191',
                'state'=>'required|max:191',
                'zipcode'=>'required|max:191',
                // 'cardname'=>'required',
                // 'cardnumber'=>'required|max:16',
                // 'expirationmonth'=>'required|max:2',
                // 'expirationyear'=>'required|max:2',
                // 'cvv'=>'required|max:3'
            ]);
            if($validator->fails())
            {
                return response()->json([
                    'status'=>422,
                    'errors'=>$validator->messages(),
                ]);
            }
            else
            {
                return response()->json([
                    'status'=>200,
                    'messages'=>'Form Validated Successfully',
                ]);
            }
        }
         
            else
            {
                $user_id = "unknowcustomer";
                $order = new Order;
                $order->user_id = $user_id;
                $order->firstname = $request->firstname;
                $order->lastname = $request->lastname;
                $order->phone = $request->phone;
                $order->email = $request->email;
                $order->address = $request->address;
                $order->city = $request->city;
                $order->state = $request->state;
                $order->zipcode = $request->zipcode;
                // $order->cardname = $request->cardname;
                // $order->cardnumber = $request->cardnumber;
                // $order->expirationmonth = $request->expirationmonth;
                // $order->expirationyear = $request->expirationyear;
    
                return response()->json([
                    'status' => 200,
                    'message' => 'Order Placed Successfully',
                ]);
            }
        }
    }

                


