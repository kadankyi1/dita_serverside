<?php

namespace App\Http\Controllers\version1;

use Illuminate\Http\Request;
use App\Models\version1\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\version1\LoginCodeMail;
use App\Http\Controllers\version1\UtilController;

ini_set('memory_limit','1024M');
ini_set("upload_max_filesize","100M");
ini_set("max_execution_time",60000); //--- 10 minutes
ini_set("post_max_size","135M");
ini_set("file_uploads","On");

class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION REGISTER EMAIL AND SENDS LOGIN CODE
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */

    public function sendLoginVerificationCode(Request $request)
    {

        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "user_email" => "bail|required|email|min:4|max:50",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer"
        ]);

        // MAKING SURE VERSION CODE IS ALLOWED
        if($request->app_type == "ANDROID" && 
        ($request->app_version_code < intval(config('app.androidminvc')) || $request->app_version_code > intval(config('app.androidmaxvc')))
        ){
            return response([
                "status" => "error", 
                "message" => "Please update your app from the Google Play Store."
            ]);
        }
        if($request->app_type == "IOS" && 
        ($request->app_version_code < intval(config('app.iosminvc')) || $request->app_version_code > intval(config('app.iosmaxvc')))
        ){
            return response([
            "status" => "error", 
            "message" => "Please update your app from the Apple App Store."
            ]);
        }

        // SENDING LOGIN CODE
        $passcode = UtilController::getRandomString(10);

        $email_data = array(
            'reset_code' => $passcode,
            'time' => date("F j, Y, g:i a")
        );

        //CHECKING IF USER EXISTS
        $user1 = User::where('user_email', '=', $request->user_email)->first();
        if($user1 === null){
            $userData["user_sys_id"] = date("Y-m-d-H-i-s") . UtilController::getRandomString(91);
            $userData["user_email"] = $validatedData["user_email"];
            $userData["user_fcm_token_android"] = "";
            $userData["user_fcm_token_web"] = "";
            $userData["user_fcm_token_ios"] = "";
            $userData["user_flagged"] = false;
            $userData["user_flagged_reason"] = "";
            $userData["passcode_set_time"] = date("Y-m-d H:i:s");
            $userData["passcode"] = $passcode;        
            // SAVING APP TYPE VERSION CODE
            if($request->app_type == "ANDROID"){
                $userData["user_android_app_version_code"] = $validatedData["app_version_code"];
            } else if($request->app_type == "IOS"){
                $userData["user_ios_app_version_code"] = $validatedData["app_version_code"];
            } 
            $user1 = User::create($userData);
        } else {
            $user1->passcode_set_time = date("Y-m-d H:i:s");
            $user1->passcode = $passcode;
            $user1->save();    
        }

        //Mail::to($request->user_email)->send(new LoginCodeMail($email_data));

        return response([
            "status" => "success", 
            "message" => "Passcode sent",
        ]);

    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION REGISTER EMAIL AND SENDS LOGIN CODE
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */

    function verifyLoginCode(Request $request)
    {

        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "user_email" => "bail|required|email|min:4|max:50",
            "user_passcode" => "bail|required|min:4|max:10",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer"
        ]);

        
        //CHECKING IF USER EXISTS
        $user1 = User::where('user_email', '=', $request->user_email)->where('passcode', '=', $request->user_passcode)->where('user_flagged', '=', 0)->first();
        if($user1 === null){
            return response([
                "status" => "error", 
                "message" => "Login failed sent",
            ]);
        } else {
            // GENERATING THE ACCESS TOKEN FOR THE REGISTERED USER
            //$accessToken = $user1->createToken("authToken")->accessToken;
            $accessToken = $user1->createToken("authToken", ["get-info-on-apps get-info-in-background"])->accessToken;

            $user1->passcode_set_time = null;
            $user1->passcode = "";
            $user1->save();  

            return response([
                "status" => "success", 
                "message" => "Login successful",
                "user_email" => $user1->user_email,
                "user_id" => $user1->user_sys_id,
                "access_token" => $accessToken,
            ]);
    
    
        }



    }
    
}
