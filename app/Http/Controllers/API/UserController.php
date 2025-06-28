<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

    class UserController extends Controller
    {
            public function index() {
                $users = User::all();
                return response()->json(['users' => $users]);
            }

            public function blockUser($id) {
                $user = User::findOrFail($id);

                if ($user->role_as == 1) {
                    return response()->json(['message' => 'Cannot block an admin user'], 403);
                }

                $user->status = !$user->status; // Toggle status between active (1) and blocked (0)
                $user->save();

                return response()->json(['message' => 'User status updated']);
            }
    }
