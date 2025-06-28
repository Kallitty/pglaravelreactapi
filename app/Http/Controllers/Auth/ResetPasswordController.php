<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected function sendResetResponse(Request $request, $response)
    {
        return response()->json(['message' => trans($response)], 200);
    }

    protected function sendResetFailedResponse(Request $request, $response)
    {
        return response()->json(['message' => trans($response)], 422);
    }

    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $response = $this->broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        if ($response == Password::PASSWORD_RESET) {
            return $this->sendResetResponse($request, $response);
        } else {
            return $this->sendResetFailedResponse($request, $response);
        }
    }

    protected function resetPassword($user, $password)
    {
        $user->password = bcrypt($password);

        $user->setRememberToken(Str::random(60));

        $user->save();

        event(new \Illuminate\Auth\Events\PasswordReset($user));
    }
}
