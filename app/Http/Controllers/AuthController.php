<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Mail\OtpMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        // Validate input
        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email|max:255|unique:users',
            'password'              => 'required|string|min:8|confirmed', // password_confirmation required
        ]);

        // Create user
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'User registered successfully',
            'token'   => $token,
            'user'    => $user,
        ], 201);
    }
    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user  = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'verify_status' => $user->verify_status,
                'created_at'    => $user->created_at,
                'updated_at'    => $user->updated_at,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Logged out successfully',
        ]);
    }
    public function profile(Request $request)
    {
        return response()->json([
            'status' => true,
            'user'   => $request->user(),
        ]);
    }
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Provide a default name and password for users created via OTP
        $user = User::firstOrCreate(
            ['email' => $request->email],
            [
                'name' => 'Guest',
                'password' => bcrypt('password123') // default password
            ]
        );

        // Generate OTP
        $code = rand(100000, 999999);

        // Store OTP
        Otp::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        // Send OTP via plain text email
        \Mail::raw("Your OTP code is: $code", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Your OTP Code');
        });

        return response()->json(['message' => 'OTP sent to your email']);
    }
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        $otp = Otp::where('user_id', $user->id)
            ->where('code', $request->otp)
            ->where('expires_at', '>=', now())
            ->first();

        if (!$otp) {
            return response()->json(['message' => 'Invalid or expired OTP'], 400);
        }
        $otp->delete();
        $user->verify_status = 'complete';
        $user->save();

        return response()->json([
            'message' => 'OTP verified successfully',
            'verify_status' => $user->verify_status,
        ]);
    }
}
