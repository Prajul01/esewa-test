<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\appusers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\students;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Mail;


class AuthController extends Controller
{
    public function sendSmsNotificaition($number , $otp)
    {
        $basic  = new \Vonage\Client\Credentials\Basic("0a2b65fa", "BdI7Z4463UWJp12W");
        $client = new \Vonage\Client($basic);

        $response = $client->sms()->send(
            new \Vonage\SMS\Message\SMS("977$number","9779746459220", "Your otp for BIM Notes is $otp")
        );
        $message = $response->current();
        if ($message->getStatus() == 0) {
            return response()->json([
                'message' => "Sucessful"
            ]);
        } else {
            return response()->json([
                'message' => "Failed"
            ]);
        }
    }

    public function basic_email($otp, $emai) {
        try{
        Mail::raw("your otp is $otp", function($message) use ($emai){
            $message->to($emai)
                    ->subject("Your otp code");
        });
        return response()->json([
            'message' => "Sucessful"
        ]);
    }
    catch(Exception $e){
        return response()->json([
            'message' => "Failed"
        ]);
    }
     }
//google
public function requestTokenGoogle(Request $request) {
    // // Getting the user from socialite using token from google
    $usrdata = Socialite::driver('google')->stateless()->userFromToken($request->token);
    $theEmail = $usrdata->email;
    $theName = $usrdata->name;
    $emailExists = User::where('email', $usrdata->email)
            ->exists();
    if($emailExists==true){
   $user = User::where('email', $theEmail)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;
        $userde=$user->id;

    // Update user's device token
    $user->last_login = now();
    $user->save();
        $student = appusers::where('user_id', $userde)->first();
        return response()->json([
            'token' => $token,
            'message' => "successfull",
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email
        ]);
    }
    else{
        $randomId =   rand(1000,9999);
        $current_time = \Carbon\Carbon::now()->toDateTimeString();
        $user = User::create([
            'name' => $theName,
            'email' => $theEmail,
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;
        $appUsers = appusers::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'otp' => $randomId,
        ]);
        // $this->sendSmsNotificaition($request->phone, $randomId);
        return response()->json([
        'token' => $token,
        'message' => "successfull",
        'user_id' => $user->id,
        'user_name' => $appUsers->name,
        'user_email' => $user->email
        ]);
    }

    // // Returning response
    // $token = $userFromDb->createToken('Laravel Sanctum Client')->plainTextToken;
    // $response = ['token' => $token, 'message' => 'Google Login/Signup Successful'];
    // return response($response, 200);
    // $user->device_token = $request->input('device_token');

}


public function register(Request $request)
{
    // Validate request data
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users|max:255',
        'password' => 'required|min:10',
        // 'phone' => 'required|unique:students',
    ]);
    // Return errors if validation error occur.
    if ($validator->fails()) {
        $errors = $validator->errors();
        $formatted_error = $errors->first();
        return response()->json([
            'message' => $formatted_error
        ], 400);
    }
    // Check if validation pass then create user and auth token. Return the auth token
    if ($validator->passes()) {
        $randomId =   rand(1000,9999);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'email_verified'=> 0,
            'password' => Hash::make($request->password)
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;
        $appUsers = appusers::create([
            'otp' => $randomId,
            'user_id' => $user->id,
            'name' => $user->name,
            'phone' => $request->phone,
        ]);
                //  $this->sendSmsNotificaition($appUsers->phone,$appUsers->otp);

        $this->basic_email($randomId,$request->email);
        return response()->json([
        'token' => $token,
        'otp' => $randomId,
        'appUsers' => $appUsers->name,
        'message' => "successfull",
        'user_id' => $user->id,
        'email_verified' => $user->email_verified,
        'user_email' => $user->email
        ]);
    }
    else{
        return response()->json([
            'message' => "failed",
            ]);
        }
    }

        //send otp
        public function sendOtp(Request $request){
            $emailExists = User::where('email', $request->email)
            ->exists();
            if($emailExists == true){
            $id = User::where('email', $request->email)->pluck('id')->first();
            $users = appusers::where('user_id', $id)->first();
            $this->basic_email($users->otp,$request->email);
            return response()->json([
                'otp' => $users->otp,
                'success' => true,
                ]);
            }
            else{
                return response()->json([
                    'success' => false,
                    ]);
            }
        }

         //resend otp
         public function resedOtp(Request $request){
            $id = User::where('email', $request->email)->pluck('id')->first();
            $appUser = appusers::where('user_id', $id)->first();
            $this->basic_email($appUser->otp,$request->email);
            return response()->json([
                'success' => true,
                ]);
        }

        //changePassword
        public function changePassword(Request $request){
            try{
                $change = User::where('email', $request->email)->first();
            $change->password = Hash::make($request->password);
            $change->save();
            return response()->json([
                'success' => true,
                ]);
            }
            catch(e){
                return response()->json([
                    'success' => false,
                    ]);
            }
        }


     //edit profile
     public function editProfile(Request $request,$id){
        try{
            $verify = appusers::where('user_id','=',$id);
            $verify->update($request->all());
            $verif=User::find($id);
            $verif->name = $request->name;
            $verif->save();
            return response()->json([
                'success' => true,
                ]);
            }
            catch(e){
                return response()->json([

                    'success' => false,
                    ]);
                }
            }

    //getprofile
    public function getProfile(Request $request,$id)
{
        $data = appusers::where('user_id','=',$id)->with('User')->get();
        if (count($data) > 0) {
            return response()->json($data[0]);
        } else {
            return response()->json([]);
        }
}

//update email
    public function updateEmail(Request $request,$id)
    {
        //
        $verify=User::find($id);
        $verify->email_verified = 1;
        $verify->save();
        if($verify->email_verified == 0){
            return response()->json([

                'message' => "failed",

                ]);
        }
        else if($verify->email_verified == 1){
        return response()->json([

            'message' => "successfull",
            'verify' => $verify->email_verified

            ]);
        }

    }



    //login
    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 400);
        }

        $user = User::where('email', $request['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;
        $userde = auth()->user()->id;


        // Update user's device token
        $user->device_token = $request->input('device_token');
        $user->last_login = now();
        $user->save();

        $appusers = appusers::where('user_id', $userde)->first();
        if (auth()->user()->email_verified == 0) {
            $this->basic_email($appusers->otp, auth()->user()->email);
        }

        return response()->json([
            'otp' => $appusers->otp,
            'token' => $token,
            'message' => "successfull",
            'user_id' => auth()->user()->id,
            'user_name' => auth()->user()->name,
            'phone_verified' => auth()->user()->email_verified,
            'user_email' => auth()->user()->email
        ]);
    }


    //verifyPin
    public function verifyPin(Request $request){
        $user = appusers::where('user_id', $request['id'])->firstOrFail();

    if (!$user) {
        return "User not found"; // Handle the case where the user doesn't exist
    }
    if ($user->otp == $request['pin']) {
        try{
        User::where('id', $user->id)->update(['email_verified' => 1]);
        return response()->json(
            [
                'message'=>"yes"
            ]
        ); // PIN matches
        }catch (\Exception $e){
            return response()->json(
                [
                    'message'=>"error"
                ]
                );
        }


    } else {
        return response()->json(
            [
                'message'=>"no"
            ]
        );
    }
    }

}
