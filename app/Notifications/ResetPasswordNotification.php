<?php

// namespace App\Notifications;

// use Illuminate\Bus\Queueable;
// use Illuminate\Notifications\Messages\MailMessage;
// use Illuminate\Notifications\Notification;

// class ResetPasswordNotification extends Notification
// {
//     use Queueable;

//     public $token;

//     public function __construct($token)
//     {
//         $this->token = $token;
//     }

//     public function via($notifiable)
//     {
//         return ['mail'];
//     }

//     public function toMail($notifiable)
//     {
//         $url = url('http://localhost:3000/reset-password/' . $this->token . '?email=' . urlencode($notifiable->getEmailForPasswordReset()));
//         return (new MailMessage)
//             ->greeting('Hello!')
//             ->subject('Reset Password Notification')
//             ->line('You are receiving this email because we received a password reset request for your account.')
//             ->action('Reset Password', $url)
//             ->line('If you did not request a password reset, no further action is required.');
//     }
// }


// namespace App\Http\Controllers\Auth;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Password;
// use Illuminate\Support\Str;

// class ResetPasswordNotification extends Controller
// {
//     public function showResetForm(Request $request, $token = null)
//     {
//         return view('auth.passwords.reset')->with(
//             ['token' => $token, 'email' => $request->email]
//         );
//     }

//     public function reset(Request $request)
//     {
//         $request->validate([
//             'token' => 'required',
//             'email' => 'required|email',
//             'password' => 'required|min:8|confirmed',
//         ]);

//         $status = Password::reset(
//             $request->only('email', 'password', 'password_confirmation', 'token'),
//             function ($user, $password) {
//                 $user->forceFill([
//                     'password' => bcrypt($password),
//                     'remember_token' => Str::random(60),
//                 ])->save();
//             }
//         );

//         return $status === Password::PASSWORD_RESET
//                     ? redirect()->route('login')->with('status', __($status))
//                     : back()->withErrors(['email' => [__($status)]]);
//     }
// }




namespace App\Notifications;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordNotification extends Controller
{
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withErrors(['email' => [__($status)]]);
    }
}

