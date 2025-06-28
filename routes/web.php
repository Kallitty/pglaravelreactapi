<?php

use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Auth\ForgotPasswordController;
// use App\Http\Controllers\Auth\ResetPasswordController;


//this goes back to the default localhost 300 app
// Route::get('/{any}', function () {
//     return file_get_contents(public_path('index.html'));
// })->where('any', '.*');



//this returns the default laravel page
Route::get('/', function () {
    return view('welcome');
});


Route::get('/app/{any}', function () {
    return file_get_contents(public_path('react/index.html'));
})->where('any', '.*');

Route::get('/dbconn', function () {
    return view('dbconn');
});

// this shows a blank page and returns that < error.
// Route::get('/{any}', function () {
//     return view('app');
// })->where('any', '.*');

//  Route::view('/{path?}', 'layouts.app')
//     ->where('path', '.*')
//     ->name('react');


// |--------------------------------------------------------------------------
// | Web Routes
// |--------------------------------------------------------------------------
// |
// | Here is where you can register web routes for your application. These
// | routes are loaded by the RouteServiceProvider and all of them will
// | be assigned to the "web" middleware group. Make something great!
// |
// */

// Default welcome route
// Route::get('/', function () {
//     return view('welcome');
// });

// Authentication Routes

Auth::routes(['verify' => true]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Password Reset Routes
// Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
// Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
// Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
// Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Catch-all route for React Router to point to the react frontend


// 30/11/2024 do we really need this here?
// Route::get('password/reset/{token}', 'App\Http\Controllers\Auth\ResetPasswordController@showResetForm')->name('password.reset');
// Route::post('password/reset', 'App\Http\Controllers\Auth\ResetPasswordController@reset')->name('password.update');

//