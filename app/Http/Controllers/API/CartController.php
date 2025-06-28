<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Cart;

class CartController extends Controller
{ 
    public function addtocart(Request $request)
{
    if(auth('sanctum')->check()) {
        $user_id = auth('sanctum')->user()->id;
    } else {
        if (!$request->session()->has('guest_user_id')) {
            $guest_user_id = uniqid('guest_');
            $request->session()->put('guest_user_id', $guest_user_id);
        }
        $user_id = $request->session()->get('guest_user_id');
    }

    $product_id = $request->product_id;
    $product_qty = $request->product_qty;

    $productCheck = Product::where('id', $product_id)->first();
    if ($productCheck) {
        if (Cart::where('product_id', $product_id)->where('user_id', $user_id)->exists()) {
            $cart_count = Cart::where('user_id', $user_id)->count();  // Cart count
            return response()->json([
                'status' => 409,
                'message' => $productCheck->name . " Already Added to Cart",
                'cart_count' => $cart_count
            ]);
        } else {
            $cartitem = new Cart;
            $cartitem->user_id = $user_id;
            $cartitem->product_id = $product_id;
            $cartitem->product_qty = $product_qty;
            $cartitem->save();
            
            $cart_count = Cart::where('user_id', $user_id)->count();  // Cart count
            return response()->json([
                'status' => 201,
                'message' => "Added to Cart",
                'cart_count' => $cart_count
            ]);
        }
    } else {
        return response()->json([
            'status' => 404,
            'message' => "Product Not Found",
        ]);
    }
}

    // public function viewcart(Request $request)
    // {
    //     if (auth('sanctum')->check()) {
    //         $user_id = auth('sanctum')->user()->id;
    //     } else {
    //         if (!$request->session()->has('guest_user_id')) {
    //             return response()->json([
    //                 'status' => 200,
    //                 'cart' => [],
    //             ]);
    //         }
    //         $user_id = $request->session()->get('guest_user_id');
    //     }

    //     $cartitems = Cart::where('user_id', $user_id)->get();
    //     return response()->json([
    //         'status' => 200,
    //         'cart' => $cartitems,
    //     ]);
    // }

    public function viewcart(Request $request)
{
    if (auth('sanctum')->check()) {
        $user_id = auth('sanctum')->user()->id;
    } else {
        if (!$request->session()->has('guest_user_id')) {
            return response()->json([
                'status' => 200,
                'cart' => [],
            ]);
        }
        $user_id = $request->session()->get('guest_user_id');
    }

    // Fetch cart items and include product images
    $cartitems = Cart::where('user_id', $user_id)->with('product.product_images')->get();
    
    // Return cart items with associated product images
    return response()->json([
        'status' => 200,
        'cart' => $cartitems,
    ]);
}



    public function updatequantity(Request $request, $cart_id, $scope)
{
    if(auth('sanctum')->check()) {
        $user_id = auth('sanctum')->user()->id;
    } else {
        if (!$request->session()->has('guest_user_id')) {
            return response()->json([
                'status' => 404,
                'message' => "Guest user not found",
            ]);
        }
        $user_id = $request->session()->get('guest_user_id');
    }

    $cartitem = Cart::where('id', $cart_id)->where('user_id', $user_id)->first();
    if ($cartitem) {
        if ($scope == "inc") {
            $cartitem->product_qty += 1;
        } else if ($scope == "dec" && $cartitem->product_qty > 1) {
            $cartitem->product_qty -= 1;
        }
        $cartitem->update();
        
        $cart_count = Cart::where('user_id', $user_id)->count();  // Cart count
        return response()->json([
            'status' => 200,
            'message' => "Quantity Updated",
            'cart_count' => $cart_count
        ]);
    } else {
        return response()->json([
            'status' => 404,
            'message' => "Cart Item Not Found",
        ]);
    }
}

   public function deleteCartitem(Request $request, $cart_id)
{
    if(auth('sanctum')->check()) {
        $user_id = auth('sanctum')->user()->id;
    } else {
        if (!$request->session()->has('guest_user_id')) {
            return response()->json([
                'status' => 404,
                'message' => "Guest user not found",
            ]);
        }
        $user_id = $request->session()->get('guest_user_id');
    }

    $cartitem = Cart::where('id', $cart_id)->where('user_id', $user_id)->first();
    if ($cartitem) {
        $cartitem->delete();
        
        $cart_count = Cart::where('user_id', $user_id)->count();  // Cart count
        return response()->json([
            'status' => 200,
            'message' => "Cart Item Removed Successfully",
            'cart_count' => $cart_count
        ]);
    } else {
        return response()->json([
            'status' => 404,
            'message' => "Cart Item Not Found",
        ]);
    }
}
}