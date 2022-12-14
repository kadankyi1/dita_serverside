<?php

namespace App\Http\Controllers\version1;

use Illuminate\Http\Request;
use App\Models\version1\User;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\version1\LoginCodeMail;
use App\Http\Controllers\version1\UtilController;
use App\Mail\version1\UserMessageFromAppMail;
use Illuminate\Support\Facades\Auth;

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
    
    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION SENDS LIST OF BOOKS
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */

    public function getBookListing(Request $request)
    {
    
        // CHECKING THAT THE REQUEST FROM THE USER HAS A VALID TOKEN
        if (!Auth::guard('api')->check()) {
            return response([
                "status" => "error", 
                "message" => "Session closed. You have to login again"
            ]);
        }
    
        // CHECKING THAT USER TOKEN HAS THE RIGHT PERMISSION
        if (!$request->user()->tokenCan('get-info-on-apps')) {
            return response([
                "status" => "error", 
                "message" => "You do not have permission"
            ]);
        }
    
        // CHECKING IF USER FLAGGED
        if (auth()->user()->user_flagged) {
            $request->user()->token()->revoke();
            return response([
                "status" => "error", 
                "message" => "Account flagged."
            ]);
        }
    

        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "kw" => "",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer"
        ]);
    
        $like_keyword = '%' . $request->kw . '%';
    
    
        if(empty($request->kw)){
            $found_books = DB::table('books')
            ->select('books.book_id', 'books.book_cover_photo', 'books.book_sys_id', 'books.book_title', 'books.book_author', 'books.book_ratings', 'books.book_description_short', 'books.book_description_long', 'books.book_pages', 'books.book_pdf', 'books.book_summary_pdf', 'books.book_audio', 'books.book_summary_audio', 'books.book_cost_usd', 'books.book_summary_cost_usd')
            ->orderBy('created_at', 'desc')
            ->take(30)
            ->get();
        } else {
            $where_array = array(
                ['book_title', 'LIKE', $like_keyword],
            ); 
            $orwhere_array = array(
                ['book_author', 'LIKE', $like_keyword],
            ); 
    
            if(count($orwhere_array) > 0){
                $found_books = DB::table('books')
                    ->select('books.book_id', 'books.book_cover_photo', 'books.book_sys_id', 'books.book_title', 'books.book_author', 'books.book_ratings', 'books.book_description_short', 'books.book_description_long', 'books.book_pages', 'books.book_pdf', 'books.book_summary_pdf', 'books.book_audio', 'books.book_summary_audio', 'books.book_cost_usd', 'books.book_summary_cost_usd')
                    ->where($where_array)
                    ->orWhere($orwhere_array)
                    ->orderBy('read_count', 'desc')
                    ->take(30)
                    ->get();
                            
            } else {
                $found_books = DB::table('rates')
                ->select('books.book_id', 'books.book_cover_photo', 'books.book_sys_id', 'books.book_title', 'books.book_author', 'books.book_ratings', 'books.book_description_short', 'books.book_description_long', 'books.book_pages', 'books.book_pdf', 'books.book_summary_pdf', 'books.book_audio', 'books.book_summary_audio', 'books.book_cost_usd', 'books. book_summary_cost_usd')
                ->where($where_array)
                ->orderBy('read_count', 'desc')
                ->take(30)
                ->get();
            }
        }
        
        for ($i=0; $i < count($found_books); $i++) { 

            if(file_exists(public_path() . "/uploads/books_cover_arts/" . $found_books[$i]->book_cover_photo)){
                $found_books[$i]->book_cover_photo = config('app.books_cover_arts_folder') . "/" . $found_books[$i]->book_cover_photo;
            } else {
                $found_books[$i]->book_cover_photo = config('app.books_cover_arts_folder') . "/sample_cover_art.jpg";
            }
            if(file_exists(public_path() . "/uploads/books_fulls/" . $found_books[$i]->book_pdf)){
                $found_books[$i]->book_pdf = config('app.books_full_folder') . "/" . $found_books[$i]->book_pdf;
            } else {
                $found_books[$i]->book_pdf = "";
            }
            if(file_exists(public_path() . "/uploads/books_summaries/" . $found_books[$i]->book_summary_pdf)){
                $found_books[$i]->book_summary_pdf = config('app.books_summaries_folder') . "/" . $found_books[$i]->book_summary_pdf;
            } else {
                $found_books[$i]->book_summary_pdf = "";
            }
            if(file_exists(public_path() . "/uploads/books_audios/" . $found_books[$i]->book_audio)){
                $found_books[$i]->book_audio = config('app.url') . "/" . $found_books[$i]->book_audio;
            } else {
                $found_books[$i]->book_audio = "";
            }
            if(file_exists(public_path() . "/uploads/books_audios_summaries/" . $found_books[$i]->book_summary_audio)){
                $found_books[$i]->book_summary_audio = config('app.url') . "/" . $found_books[$i]->book_summary_audio;
            } else {
                $found_books[$i]->book_summary_audio = "";
            }
            $found_books[$i]->book_cost_usd = "$" . strval($found_books[$i]->book_cost_usd);
        }

        return response([
            "status" => "success", 
            "message" => "Operation successful", 
            "data" => $found_books, 
            "kw" => $request->kw
        ]);
    }
    

public function contactDitaTeam(Request $request){

    if (!Auth::guard('api')->check()) {
        return response(["status" => "fail", "message" => "Permission Denied. Please log out and login again"]);
    }

    if (auth()->user()->user_flagged) {
        $request->user()->token()->revoke();
        return response(["status" => "fail", "message" => "Account access restricted"]);
    }

    $validatedData = $request->validate([
        "message_text" => "bail|required|max:1000",
    ]);


        
    $email_data = array(
        'message_text' => $request->message_text,
        'user_name' => "Guest User",
        'user_email' => auth()->user()->user_email,
        'time' => date("F j, Y, g:i a")
    );

    Mail::to("fishpottcompany@gmail.com")->send(new UserMessageFromAppMail($email_data));

    return response(["status" => "success", "message" => "Sent successsfully."]);

}



    

}
