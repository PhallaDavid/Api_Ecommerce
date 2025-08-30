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

    // Generate OTP
    $otp = rand(100000, 999999);

    // Create user
    $user = User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'password' => Hash::make($request->password),
        'otp'      => $otp, // save OTP to user table
    ]);

    // Send OTP to user email
    Mail::raw("Your OTP code is: $otp", function ($message) use ($user) {
        $message->to($user->email)
                ->subject('Verify your email with OTP');
    });

    // Generate token
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'status'  => true,
        'message' => 'User registered successfully. Please verify your email with OTP.',
        'token'   => $token,
        'user'    => $user,
    ], 201);
}
public function updateProfile(Request $request)
{
    $user = $request->user();

    // Validate input
    $request->validate([
        'name'    => 'sometimes|string|max:255',
        'email'   => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
        'phone'   => 'sometimes|string|max:20',
        'address' => 'sometimes|string|max:500',
        'city'    => 'sometimes|string|max:100',
        'state'   => 'sometimes|string|max:100',
        'zip'     => 'sometimes|string|max:20',
        'avatar'  => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        'images'  => 'sometimes|array',
        'images.*'=> 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
    ]);

    // Update basic info
    $user->name    = $request->name ?? $user->name;
    $user->email   = $request->email ?? $user->email;
    $user->phone   = $request->phone ?? $user->phone;
    $user->address = $request->address ?? $user->address;
    $user->city    = $request->city ?? $user->city;
    $user->state   = $request->state ?? $user->state;
    $user->zip     = $request->zip ?? $user->zip;

    // Handle avatar upload
    if ($request->hasFile('avatar')) {
        $avatarPath = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = '/storage/' . $avatarPath;
    }

    // Handle multiple images upload
    if ($request->hasFile('images')) {
        $imagePaths = [];
        foreach ($request->file('images') as $image) {
            $path = $image->store('user_images', 'public');
            $imagePaths[] = '/storage/' . $path;
        }
        $user->images = $imagePaths;
    }

    $user->save();

    return response()->json([
        'status' => true,
        'message' => 'Profile updated successfully',
        'user' => $user,
    ]);
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
