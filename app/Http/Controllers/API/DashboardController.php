<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\Orderitems;
use App\Models\Cart;

class DashboardController extends Controller
{
    public function index()
    {
        // Aggregate data for the dashboard

        $ordersCount = Order::count();
        $productsCount = Product::count();
        $categoriesCount = Category::count();
        $usersCount = User::count();

        // Fetch recent orders, products, categories, and users
        $recentOrders = Order::orderBy('created_at', 'desc')->take(5)->get();
        $recentProducts = Product::orderBy('created_at', 'desc')->take(5)->get();
        $recentCategories = Category::orderBy('created_at', 'desc')->take(5)->get();
        $recentUsers = User::orderBy('created_at', 'desc')->take(5)->get();

        // Return all the data
        return response()->json([
            'ordersCount' => $ordersCount,
            'productsCount' => $productsCount,
            'categoriesCount' => $categoriesCount,
            'usersCount' => $usersCount,
            'recentOrders' => $recentOrders,
            'recentProducts' => $recentProducts,
            'recentCategories' => $recentCategories,
            'recentUsers' => $recentUsers,
        ]);
    }
}
