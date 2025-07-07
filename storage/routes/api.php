<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\FrontendController;
use App\Http\Controllers\API\ProductController;
use App\Http\Kernel;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\API\CheckoutController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\ArticleController;




Route::middleware(['auth:sanctum', 'isAPIAdmin'])->group(function() {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Add your admin routes here
    Route::get('/admin/dashboard', [AuthController::class, 'dashboard']);

    //Category
    Route::post('/store-category', [CategoryController::class, 'store']);
    Route::get('/view-category', [CategoryController::class, 'index']);
    // In api.php
    Route::get('/edit-category/{id}', [CategoryController::class, 'edit']);
    Route::post('/update-category/{id}', [CategoryController::class, 'update']);
    Route::delete('/delete-category/{id}', [CategoryController::class, 'destroy']);
 

    //Products
    Route::post('/store-product', [ProductController::class, 'store']);
    Route::get('/view-product', [ProductController::class, 'index']);
    Route::get('/edit-product/{id}', [ProductController::class, 'edit']);
    Route::delete('/delete-product/{id}', [ProductController::class, 'delete']);
    Route::post('/update-product/{id}', [ProductController::class, 'update']);
    Route::get('/admin/orders', [OrderController::class, 'index']);
    Route::delete('/delete-product-image/{id}', [ProductController::class, 'deleteProductImage']);

     //Block Unlock User 
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users/{id}/block', [UserController::class, 'blockUser']);

            //Admin Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Admin blog routes
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::post('/articles/{id}', [ArticleController::class, 'update']);
    Route::delete('/articles/{id}', [ArticleController::class, 'destroy']);

    // Other admin routes...
});



Route::post('register',  [AuthController::class, 'register']);

Route::post('login',  [AuthController::class, 'login']);

Route::get('getCategory',  [FrontendController::class, 'category']);
Route::get('fetchproducts/{slug}', [FrontendController::class, 'product']);
Route::get('viewproductdetail/{category_slug}/{product_slug}', [FrontendController::class, 'viewproducts']);


        //Users Products
Route::get('/all-category', [CategoryController::class, 'allcategory']);
Route::get('/products/{category}', [ProductController::class, 'getProductsByCategory']);
Route::get('/product/{id}', [ProductController::class, 'showing']);
 Route::get('/product-by-slug/{slug}', [ProductController::class, 'showBySlug']);
Route::get('/related-products/{categoryId}/{productId}', [ProductController::class, 'getRelatedProducts']);
Route::get('/new-collections', [ProductController::class, 'getNewCollections']);
Route::get('/popular-products', [ProductController::class, 'getPopularProducts']);





Route::post('add-to-cart', [CartController::class, 'addtoCart']);
Route::get('cart', [CartController::class, 'viewcart']);
Route::put('cart-updatequntity/{cart_id}/{scope}', [CartController::class, 'updatequantity']);
Route::delete('delete-cartitem/{cart_id}', [CartController::class, 'deleteCartitem']);

Route::post('place-order', [CheckoutController::class, 'placeorder']);
Route::post('validate-order', [CheckoutController::class, 'validateorder']);

Route::get('orders', [FrontendController::class, 'orders']);


  // Public routes
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{id}', [ArticleController::class, 'show']);
    Route::get('/articles/{id}/view', [ArticleController::class, 'view']);
    Route::get('/articles/slug/{slug}', [ArticleController::class, 'showBySlug']);
             
    // Public blog routes
    // Route::get('/articles', [ArticleController::class, 'index']);
    // Route::get('/articles/{id}', [ArticleController::class, 'show']);
    Route::get('/articles/slug/{slug}', [ArticleController::class, 'showBySlug']);
    


// Route::get('trgetCategory', [FrontendController::class, 'trcategory']);
// Route::get('search/{key}', [FrontendController::class, 'search']);
// Route::post('viewproductdetails/{category_slug}/{product_name}/{id}/comment', [FrontendController::class, 'comment']);







// Route::post('add-to-wishlist', [WishlistController::class, 'addtoWishlist']);
// Route::get('wishlist', [WishlistController::class, 'wishlistcart']);
// Route::delete('delete-wishlistitem/{wishlist_id}', [WishlistController::class, 'deletewishlistcart']);




Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [ResetPasswordController::class, 'reset']);

Route::middleware(['auth:sanctum'])->group(function() {
Route::get('/checkingAuthenticated', function(){
    return response()->json(['message'=>'You are in', 'status'=>200], 200);
} );

    Route::post('logout',  [AuthController::class, 'logout']);
});

Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');



