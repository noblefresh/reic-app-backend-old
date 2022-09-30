<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Otp;
use App\Models\User;
use App\Models\investor;
use App\Models\Merchant;
use Illuminate\Http\Request;
use App\Notifications\sendOtp;
use App\Http\Controllers\Controller;
use App\Models\State;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->remember)) {
            $user = User::find(Auth::user()->id);
            // $wallet = Wallet::where('user_id', Auth::user()->id)->select('balance', 'currency')->first();
            if (isset($request->device_token)) {
                $user->device_token = $request->device_token;
            }
            if (isset($request->device_platform)) {
                $user->device_platform = $request->device_platform;
            }
            $user->update();
            $user->api_token = $this->getApiToken($user);
            $user->terms = filter_var($user->terms, FILTER_VALIDATE_BOOLEAN);
            return response()->json([
                'status' => 'success',
                'user' => $user,
                'message' => "Logged in successfully"
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => "Email or password is incorrect"
            ], 422);
        }
    } 

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string',
            'password' => 'required|string',
            'terms' => 'boolean|required_if:terms,true',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "Validation failed",
                "data" => $validator->errors()->all()
            ],422);
        }
 
        // if ($validator->fails()) {
        //     dd($validator->errors()->all());
        // }

        // $request->validate([
        //     'email' => 'required|string|email|max:255|unique:users',
        //     'phone' => 'required|string',
        //     'password' => 'required|string',
        //     'terms' => 'boolean|required_if:terms,true',
        // ]);

        $user = User::create([
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'terms' => filter_var($request->terms, FILTER_VALIDATE_BOOLEAN),
            'device_token' => isset($request->device_token) ? $request->device_token : null,
            'device_platform' => isset($request->device_platform) ? $request->device_platform : null,
        ]);

        event(new Registered($user));
        $user->api_token = $this->getApiToken($user);
        unset($user->device_token);
        unset($user->device_platform);
        // create wallet for user
        $this->sendOTP($user);
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'message' => "Registered successfully"
        ]);

        // if ($validator->fails()) {
        //     return response()->json([
        //         "status" => "success",
        //         "message" => "Validation failed",
        //         "data" => $validator->errors()->all()
        //     ],422);
        // } else {
        //     return response()->json([
        //         "status" => "success",
        //         "message" => "Registered successfully",
        //         "data" => $user
        //     ],200);
        // }
    }

    public function getApiToken(User $user)
    {
        if ($user->tokens()) {
            $user->tokens()->delete();
        }
        return $user->createToken($user->email)->plainTextToken;
    }

    public function sendOTP(User $user)
    {
        $otp = rand(10000, 99999);
        if (!empty($user->otp)) {
            $user->otp()->update([
                'user_id' => $user->id,
                'email' => $user->email,
                'otp' => $otp
            ]);
        } else {
            $user->otp()->create([
                'user_id' => $user->id,
                'email' => $user->email,
                'otp' => $otp
            ]);
        }
        $user->notify(new sendOtp($user, $otp));
    }

    public function resendOTP(Request $request)
    {
        $user_otp = Otp::where('user_id',$request->user_id)->first();
        $user = User::where('id',$request->user_id)->first();

        $otp = rand(10000, 99999);
        if (!empty($user_otp->otp)) {
            $user_otp->user_id = $request->user_id;
            $user_otp->email = $request->email;
            $user_otp->otp = $otp;
            $user_otp->update();
        } else {
            Otp::create([
                'user_id' => $request->user_id,
                'email' => $request->email,
                'otp' => $otp
            ]);
        }
        $user->notify(new sendOTP($user, $otp));
        return response()->json([
            'status' => 'success',
            'message' => "OTP has been sent to your email",
            'email' => $request->email,
            'user_id' => $request->user_id
        ], 200);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|integer',
            'email' => 'required|string|email'
        ]);

        if (User::where('email', $request->email)->exists()) {
            $user = User::where('email', $request->email)->first();
            if ($user->otp()->where('otp', $request->otp)->exists()) {
                $user->otp()->delete();
                $user->update([
                    'email_verified_at' => now()
                ]);
                return response()->json([
                    'status' => 'success',
                    'user' => $user,
                    'message' => "Email verified successfully"
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => "OTP is incorrect"
                ], 422);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => "User records not found"
            ], 400);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function add_merchant(Request $request)
    {
        // return $request;
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state_id' => 'required|int',
            'terms' => 'boolean|required_if:terms,true',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make(12345678),
            'terms' => filter_var($request->terms, FILTER_VALIDATE_BOOLEAN),
            'device_token' => isset($request->device_token) ? $request->device_token : null,
            'device_platform' => isset($request->device_platform) ? $request->device_platform : null,
            'role' => 'merchant'
        ]);

        
        event(new Registered($user));
        $merchant = Merchant::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state_id' => $request->state_id,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user,
                'merchant' => $merchant
            ],
            'message' => "Merchant Registered successfully"
        ]);

    }

    public function setup(Request $request)
    {
        // return $request;
        $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'dob' => 'required|date',
            'state_id' => 'required|int',
            'net_worth' => 'required|string',
            'annual_income' => 'required|string',
        ]);

        $investor = Investor::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'email' => $request->user()->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state_id' => $request->state_id,
            'dob' => $request->dob,
            'net_worth' => $request->net_worth,
            'annual_income' => $request->annual_income,
        ]);

        $user = User::where('id', $request->user()->id)->first();
        $user->update([
            'name' => $investor->name,
            'phone' => $investor->phone,
            'is_setup_completed' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user,
                'investor' => $investor
            ],
            'message' => "Investor Setup Completed successfully"
        ]);
    }

    public function states()
    {
        $states = State::where('country_id','161')->orderBy('name', 'ASC')->get();
        return response()->json([
            'status' => 'success',
            'states' => $states,
        ]);
    }
}
