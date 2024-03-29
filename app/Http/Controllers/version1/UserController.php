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
use App\Models\version1\Book;
use App\Models\version1\Transaction;
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
            "user_email_or_phone" => "bail|required|max:100",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer"
        ]);

        // MAKING SURE VERSION CODE IS ALLOWED
        if($request->app_type == "ANDROID" && 
        ($request->app_version_code < intval(config('app.androidminvc')) || $request->app_version_code > intval(config('app.androidmaxvc')))
        ){
            return response([
                "status" => "error", 
                "app_version_code" => $request->app_version_code , 
                "androidminvc" => config('app.androidminvc'), 
                "androidmaxvc" => config('app.androidmaxvc'), 
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

        if($request->app_type != "IOS" && $request->app_type == "ANDROID" && $request->app_type == "WEB"){
            return response([
                "status" => "error", 
                "message" => "Please update your app."
                ]);
        }


        // Check phone number for 10 digits
        if(is_numeric($request->user_email_or_phone)){
            if(!preg_match('/^\d{10}$/',$request->user_email_or_phone)) {
                return response([
                    "status" => "error", 
                    "message" => "Invalid phone number."
                ]);
            }
            $final_phone = $request->user_email_or_phone;
            $final_email = $request->user_email_or_phone . "@tafarri.com";

            return UserController::verifyPhoneNumberAndLogin($request);
        } else {  
            if (!filter_var($request->user_email_or_phone, FILTER_VALIDATE_EMAIL)) {
                return response([
                    "status" => "error", 
                    "message" => "Invalid email address."
                ]);
            }
            $final_phone = $request->user_email_or_phone;
            $final_email = $request->user_email_or_phone;
            // SENDING LOGIN CODE
            $passcode = UtilController::getRandomString(10);

            $email_data = array(
                'reset_code' => $passcode,
                'time' => date("F j, Y, g:i a")
            );

            //CHECKING IF USER EXISTS
            $user1 = User::where('user_email', '=', $request->user_email_or_phone)->first();
            if($user1 === null){
                $userData["user_sys_id"] = date("Y-m-d-H-i-s") . UtilController::getRandomString(91);
                $userData["user_email"] = $validatedData["user_email_or_phone"];
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

            Mail::to($request->user_email_or_phone)->send(new LoginCodeMail($email_data));

            return response([
                "status" => "success", 
                "message" => "Passcode sent",
            ]);

        }
    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION REGISTER EMAIL AND SENDS LOGIN CODE
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */

    function verifyPhoneNumberAndLogin(Request $request)
    {

        // MAKING SURE THE INPUT HAS THE EXPECTED VALUES
        $validatedData = $request->validate([
            "user_email_or_phone" => "bail|required|min:10|max:15",
            "app_type" => "bail|required|max:8",
            "app_version_code" => "bail|required|integer"
        ]);

        
        //CHECKING IF USER EXISTS
        $user1 = User::where('user_phone', '=', $request->user_email_or_phone)->where('user_flagged', '=', 0)->first();
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

            $where_array = array(
                ['transaction_buyer_email', '=', $user1->user_email],
                ['transaction_payment_status', '=', 'verified_passed']
            ); 

            $purchases_books_transactions = DB::table('transactions')
            ->select('transactions.transaction_referenced_item_id', 'transactions.transaction_payment_ref_id', 'transactions.transaction_type')
            ->where($where_array)
            ->orderBy('created_at', 'desc')
            ->get();
    
    
        $found_books = array();

        for ($i=0; $i < count($purchases_books_transactions); $i++) { 
            
            $where_array = array(
                ['book_sys_id', '=', $purchases_books_transactions[$i]->transaction_referenced_item_id]
            ); 
            
            $this_book = DB::table('books')
            ->select('books.book_sys_id', 'books.book_title')
            ->where($where_array)
            ->orderBy('read_count', 'desc')
            ->take(1)
            ->get();


            if(!empty($this_book[0])){
                $this_book[0]->transaction_payment_ref_id =  $purchases_books_transactions[$i]->transaction_payment_ref_id;
                
                if($purchases_books_transactions[$i]->transaction_type == "book_full"){
                    $this_book[0]->book_title =  $this_book[0]->book_title . "(Full Book)";
                } else if($purchases_books_transactions[$i]->transaction_type == "book_summary"){
                    $this_book[0]->book_title =  $this_book[0]->book_title . "(Summary)";
                } else {
                    continue;
                }
                array_push($found_books, $this_book);
            }
        }


            return response([
                "status" => "success", 
                "message" => "Login successful",
                "user_email" => $user1->user_email,
                "user_id" => $user1->user_sys_id,
                "access_token" => $accessToken,
                "data" => $found_books, 
            ]);
    
    
        }
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
        $user1 = User::where('user_email', '=', $request->user_email)->where('passcode', '=', trim($request->user_passcode))->where('user_flagged', '=', 0)->first();
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

            $where_array = array(
                ['transaction_buyer_email', '=', $user1->user_email],
                ['transaction_payment_status', '=', 'verified_passed']
            ); 

            $purchases_books_transactions = DB::table('transactions')
            ->select('transactions.transaction_referenced_item_id', 'transactions.transaction_payment_ref_id', 'transactions.transaction_type')
            ->where($where_array)
            ->orderBy('created_at', 'desc')
            ->get();
    
    
        $found_books = array();

        for ($i=0; $i < count($purchases_books_transactions); $i++) { 
            
            $where_array = array(
                ['book_sys_id', '=', $purchases_books_transactions[$i]->transaction_referenced_item_id]
            ); 
            
            $this_book = DB::table('books')
            ->select('books.book_sys_id', 'books.book_title')
            ->where($where_array)
            ->orderBy('read_count', 'desc')
            ->take(1)
            ->get();


            if(!empty($this_book[0])){
                $this_book[0]->transaction_payment_ref_id =  $purchases_books_transactions[$i]->transaction_payment_ref_id;
                
                if($purchases_books_transactions[$i]->transaction_type == "book_full"){
                    $this_book[0]->book_title =  $this_book[0]->book_title . "(Full Book)";
                } else if($purchases_books_transactions[$i]->transaction_type == "book_summary"){
                    $this_book[0]->book_title =  $this_book[0]->book_title . "(Summary)";
                } else {
                    continue;
                }
                array_push($found_books, $this_book);
            }
        }


            return response([
                "status" => "success", 
                "message" => "Login successful",
                "user_email" => $user1->user_email,
                "user_id" => $user1->user_sys_id,
                "access_token" => $accessToken,
                "data" => $found_books, 
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
            ->select('books.book_id', 'books.book_cover_photo', 'books.book_sys_id', 'books.book_title', 'books.book_author', 'books.book_ratings', 'books.book_description_short', 'books.book_description_long', 'books.book_pages', 'books.book_pdf', 'books.book_summary_pdf', 'books.book_audio', 'books.book_summary_audio', 'books.book_cost_usd', 'books.book_summary_cost_usd', 'books.bookfull_flagged', 'books.booksummary_flagged')
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
                    ->select('books.book_id', 'books.book_cover_photo', 'books.book_sys_id', 'books.book_title', 'books.book_author', 'books.book_ratings', 'books.book_description_short', 'books.book_description_long', 'books.book_pages', 'books.book_pdf', 'books.book_summary_pdf', 'books.book_audio', 'books.book_summary_audio', 'books.book_cost_usd', 'books.book_summary_cost_usd', 'books.bookfull_flagged', 'books.booksummary_flagged')
                    ->where($where_array)
                    ->orWhere($orwhere_array)
                    ->orderBy('read_count', 'desc')
                    ->take(30)
                    ->get();
                            
            } else {
                $found_books = DB::table('books')
                ->select('books.book_id', 'books.book_cover_photo', 'books.book_sys_id', 'books.book_title', 'books.book_author', 'books.book_ratings', 'books.book_description_short', 'books.book_description_long', 'books.book_pages', 'books.book_pdf', 'books.book_summary_pdf', 'books.book_audio', 'books.book_summary_audio', 'books.book_cost_usd', 'books.book_summary_cost_usd', 'books.bookfull_flagged', 'books.booksummary_flagged')
                ->where($where_array)
                ->orderBy('read_count', 'desc')
                ->take(30)
                ->get();
            }
        }
        
        for ($i=0; $i < count($found_books); $i++) { 

            if(!empty($found_books[$i]->book_cover_photo) && file_exists(public_path() . "/uploads/books_cover_arts/" . $found_books[$i]->book_cover_photo)){
                $found_books[$i]->book_cover_photo = config('app.books_cover_arts_folder') . "/" . $found_books[$i]->book_cover_photo;
            } else {
                $found_books[$i]->book_cover_photo = config('app.books_cover_arts_folder') . "/sample_cover_art.jpg";
            }

            if(!$found_books[$i]->bookfull_flagged && !empty($found_books[$i]->book_pdf) && file_exists(public_path() . "/uploads/books_fulls/" . $found_books[$i]->book_pdf)){
                $found_books[$i]->book_pdf = config('app.books_full_folder') . "/" . $found_books[$i]->book_pdf;
                if($found_books[$i]->book_cost_usd <=  0){
                    $found_books[$i]->book_cost_usd = "Free";
                } else {
                    $found_books[$i]->book_cost_usd = "$" . strval($found_books[$i]->book_cost_usd);
                }
            } else {
                $found_books[$i]->book_pdf = "";
                $found_books[$i]->book_cost_usd = "";
            }
            if(!$found_books[$i]->booksummary_flagged && !empty($found_books[$i]->book_summary_pdf) && file_exists(public_path() . "/uploads/books_summaries/" . $found_books[$i]->book_summary_pdf)){
                $found_books[$i]->book_summary_pdf = config('app.books_summaries_folder') . "/" . $found_books[$i]->book_summary_pdf;
                if($found_books[$i]->book_summary_cost_usd <=  0){
                    $found_books[$i]->book_summary_cost_usd = "Free";
                } else {
                    $found_books[$i]->book_summary_cost_usd = "$" . strval($found_books[$i]->book_summary_cost_usd);
                }
            } else {
                $found_books[$i]->book_summary_pdf = "";
                $found_books[$i]->book_summary_cost_usd = "";
            }
            if(!empty($found_books[$i]->book_audio) && file_exists(public_path() . "/uploads/books_audios/" . $found_books[$i]->book_audio)){
                $found_books[$i]->book_audio = config('app.url') . "/" . $found_books[$i]->book_audio;
            } else {
                $found_books[$i]->book_audio = "";
            }
            if(!empty($found_books[$i]->book_summary_audio) && file_exists(public_path() . "/uploads/books_audios_summaries/" . $found_books[$i]->book_summary_audio)){
                $found_books[$i]->book_summary_audio = config('app.url') . "/" . $found_books[$i]->book_summary_audio;
            } else {
                $found_books[$i]->book_summary_audio = "";
            }
            $found_books[$i]->book_cost_cedi_info = "You will be charged the cedi equivalent of the listed price at $1 to Ghc" .  strval(config('app.dollartocedirate'));

            $transaction = Transaction::where('transaction_type', '=', "book_full")->where('transaction_referenced_item_id', '=', $found_books[$i]->book_sys_id)->where('transaction_buyer_email', '=', auth()->user()->user_email)->where('transaction_payment_status', '=', "verified_passed")->first();
            if($transaction == null || empty($transaction->transaction_referenced_item_id)){
                $found_books[$i]->book_full_purchased = "no";
            } else {
                $found_books[$i]->book_full_purchased = "yes";
            }

            $transaction = Transaction::where('transaction_type', '=', "book_summary")->where('transaction_referenced_item_id', '=', $found_books[$i]->book_sys_id)->where('transaction_buyer_email', '=', auth()->user()->user_email)->where('transaction_payment_status', '=', "verified_passed")->first();
            if($transaction == null || empty($transaction->transaction_referenced_item_id)){
                $found_books[$i]->book_summary_purchased = "no";
            } else {
                $found_books[$i]->book_summary_purchased = "yes";
            }

            $found_books[$i]->book_reference_url = config('app.url') . "/buy?ref=" . $found_books[$i]->book_sys_id;

        }

        return response([
            "status" => "success", 
            "message" => "Operation successful", 
            "data" => $found_books, 
            "kw" => $request->kw
        ]);
    }
    
    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION SENDS LIST OF BOOKS
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */

    public function getBookSummariesListing(Request $request)
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
            $where_array = array(
                ['book_summary_pdf', '<>', ''],
            ); 
            $found_books = DB::table('books')
            ->select('books.book_id', 'books.book_cover_photo', 'books.book_sys_id', 'books.book_title', 'books.book_author', 'books.book_ratings', 'books.book_description_short', 'books.book_description_long', 'books.book_pages', 'books.book_pdf', 'books.book_summary_pdf', 'books.book_audio', 'books.book_summary_audio', 'books.book_cost_usd', 'books.book_summary_cost_usd', 'books.bookfull_flagged', 'books.booksummary_flagged')
            ->where($where_array)
            ->orderBy('created_at', 'desc')
            ->take(30)
            ->get();
        } else {
            $where_array = array(
                ['book_summary_pdf', '<>', ''],
                ['book_title', 'LIKE', $like_keyword],
            ); 
            $orwhere_array = array(
                ['book_author', 'LIKE', $like_keyword],
            ); 
    
            if(count($orwhere_array) > 0){
                $found_books = DB::table('books')
                    ->select('books.book_id', 'books.book_cover_photo', 'books.book_sys_id', 'books.book_title', 'books.book_author', 'books.book_ratings', 'books.book_description_short', 'books.book_description_long', 'books.book_pages', 'books.book_pdf', 'books.book_summary_pdf', 'books.book_audio', 'books.book_summary_audio', 'books.book_cost_usd', 'books.book_summary_cost_usd', 'books.bookfull_flagged', 'books.booksummary_flagged')
                    ->where($where_array)
                    ->orWhere($orwhere_array)
                    ->orderBy('read_count', 'desc')
                    ->take(30)
                    ->get();
                            
            } else {
                $found_books = DB::table('books')
                ->select('books.book_id', 'books.book_cover_photo', 'books.book_sys_id', 'books.book_title', 'books.book_author', 'books.book_ratings', 'books.book_description_short', 'books.book_description_long', 'books.book_pages', 'books.book_pdf', 'books.book_summary_pdf', 'books.book_audio', 'books.book_summary_audio', 'books.book_cost_usd', 'books.book_summary_cost_usd', 'books.bookfull_flagged', 'books.booksummary_flagged')
                ->where($where_array)
                ->orderBy('read_count', 'desc')
                ->take(30)
                ->get();
            }
        }
        
        for ($i=0; $i < count($found_books); $i++) { 

            if(!empty($found_books[$i]->book_cover_photo) && file_exists(public_path() . "/uploads/books_cover_arts/" . $found_books[$i]->book_cover_photo)){
                $found_books[$i]->book_cover_photo = config('app.books_cover_arts_folder') . "/" . $found_books[$i]->book_cover_photo;
            } else {
                $found_books[$i]->book_cover_photo = config('app.books_cover_arts_folder') . "/sample_cover_art.jpg";
            }
            if(!empty($found_books[$i]->book_pdf) && file_exists(public_path() . "/uploads/books_fulls/" . $found_books[$i]->book_pdf)){
                $found_books[$i]->book_pdf = config('app.books_full_folder') . "/" . $found_books[$i]->book_pdf;
                if($found_books[$i]->book_cost_usd <=  0){
                    $found_books[$i]->book_cost_usd = "Free";
                } else {
                    $found_books[$i]->book_cost_usd = "$" . strval($found_books[$i]->book_cost_usd);
                }
            } else {
                $found_books[$i]->book_pdf = "";
                $found_books[$i]->book_cost_usd = "";
            }
            if(!empty($found_books[$i]->book_summary_pdf) && file_exists(public_path() . "/uploads/books_summaries/" . $found_books[$i]->book_summary_pdf)){
                $found_books[$i]->book_summary_pdf = config('app.books_summaries_folder') . "/" . $found_books[$i]->book_summary_pdf;
                if($found_books[$i]->book_summary_cost_usd <=  0){
                    $found_books[$i]->book_summary_cost_usd = "Free";
                } else {
                    $found_books[$i]->book_summary_cost_usd = "$" . strval($found_books[$i]->book_summary_cost_usd);
                }
            } else {
                $found_books[$i]->book_summary_pdf = "";
                $found_books[$i]->book_summary_cost_usd = "";
            }
            if(!empty($found_books[$i]->book_audio) && file_exists(public_path() . "/uploads/books_audios/" . $found_books[$i]->book_audio)){
                $found_books[$i]->book_audio = config('app.url') . "/" . $found_books[$i]->book_audio;
            } else {
                $found_books[$i]->book_audio = "";
            }
            if(!empty($found_books[$i]->book_summary_audio) && file_exists(public_path() . "/uploads/books_audios_summaries/" . $found_books[$i]->book_summary_audio)){
                $found_books[$i]->book_summary_audio = config('app.url') . "/" . $found_books[$i]->book_summary_audio;
            } else {
                $found_books[$i]->book_summary_audio = "";
            }
            $found_books[$i]->book_cost_cedi_info = "You will be charged the cedi equivalent of the listed price at $1 to Ghc" .  strval(config('app.dollartocedirate'));

            $transaction = Transaction::where('transaction_type', '=', "book_full")->where('transaction_referenced_item_id', '=', $found_books[$i]->book_sys_id)->where('transaction_buyer_email', '=', auth()->user()->user_email)->where('transaction_payment_status', '=', "verified_passed")->first();
            if($transaction == null || empty($transaction->transaction_referenced_item_id)){
                $found_books[$i]->book_full_purchased = "no";
            } else {
                $found_books[$i]->book_full_purchased = "yes";
            }

            $transaction = Transaction::where('transaction_type', '=', "book_summary")->where('transaction_referenced_item_id', '=', $found_books[$i]->book_sys_id)->where('transaction_buyer_email', '=', auth()->user()->user_email)->where('transaction_payment_status', '=', "verified_passed")->first();
            if($transaction == null || empty($transaction->transaction_referenced_item_id)){
                $found_books[$i]->book_summary_purchased = "no";
            } else {
                $found_books[$i]->book_summary_purchased = "yes";
            }
            $found_books[$i]->book_reference_url = config('app.url') . "/buy?ref=" . $found_books[$i]->book_sys_id;
        }

        return response([
            "status" => "success", 
            "message" => "Operation successful", 
            "data" => $found_books, 
            "kw" => $request->kw
        ]);
    }
    

public function contactTafarriTeam(Request $request){

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

    Mail::to("support@tafarri.com")->send(new UserMessageFromAppMail($email_data));

    return response(["status" => "success", "message" => "Sent successsfully."]);

}

public function recordPurchase(Request $request){
    if (!Auth::guard('api')->check()) {
        return response(["status" => "fail", "message" => "Permission Denied. Please log out and login again"]);
    }

    if (auth()->user()->user_flagged) {
        $request->user()->token()->revoke();
        return response(["status" => "fail", "message" => "Account access restricted"]);
    }

    $validatedData = $request->validate([
        "item_id" => "bail|required|max:100",
        "item_type" => "bail|required|max:100",
        "payment_type" => "bail|required|max:100",
        "payment_ref_number" => "bail|required|max:100",
        "payment_date" => "bail|required|max:100",
        "app_type" => "bail|required|max:8",
        "app_version_code" => "bail|required|integer"
    ]);

    if($request->item_type != "book_full" && $request->item_type != "book_summary"){
        return response([
            "status" => "error", 
            "message" => "Book error"
        ]);
    } 
    
    if($request->payment_type != "momo" && $request->payment_type != "card"){
        return response([
            "status" => "error", 
            "message" => "Payment error"
        ]);
    } 

    $book = Book::where('book_sys_id', '=', $request->item_id)->first();
    if($book == null || empty($book->book_sys_id)){
        return response([
            "status" => "error", 
            "message" => "Book not found"
        ]);
    }
    

    $transactionData["transaction_sys_id"] =  auth()->user()->user_id . "_" . date("YmdHis") . UtilController::getRandomString(4);
    $transactionData["transaction_type"] = $request->item_type;
    $transactionData["transaction_referenced_item_id"] = $book->book_sys_id;
    $transactionData["transaction_buyer_email"] = auth()->user()->user_email;
    $transactionData["transaction_payment_type"] = $request->payment_type;
    $transactionData["transaction_payment_ref_id"] = $request->payment_ref_number;
    $transactionData["transaction_payment_date"] = $request->payment_date;
    $transactionData["transaction_payment_status"] = "unverified";
    $transaction = Transaction::create($transactionData);


    return response([
        "status" => "success", 
        "message" => "You can start reading your book while we verify the payment."
    ]);

}


public function getPaymentUrl(Request $request){
    $validatedData = $request->validate([
        "user_email_or_phone" => "bail|required|max:100",
        "item_id" => "bail|required|max:100",
        "item_type" => "bail|required|max:100"
    ]);


    // Check phone number for 10 digits
    if(is_numeric($request->user_email_or_phone)){
        if(!preg_match('/^\d{10}$/',$request->user_email_or_phone)) {
            return response([
                "status" => "error", 
                "message" => "Invalid phone number."
            ]);
        }
        $final_phone = $request->user_email_or_phone;
        $final_email = $request->user_email_or_phone . "@tafarri.com";
    } else {  
        if (!filter_var($request->user_email_or_phone, FILTER_VALIDATE_EMAIL)) {
            return response([
                "status" => "error", 
                "message" => "Invalid email address."
            ]);
        }
        $final_phone = $request->user_email_or_phone;
        $final_email = $request->user_email_or_phone;
    }

    if($request->item_type == "book_full"){
        $where_array = array(
            ['book_sys_id', '=', $request->item_id],
        ); 
    } else if($request->item_type == "book_summary"){
        $where_array = array(
            ['book_sys_id', '=', $request->item_id],
            ['book_summary_pdf', '<>', ''],
        ); 
    } else {
        return response([
            "status" => "error", 
            "message" => "Book error"
        ]);
    }
    $found_books = DB::table('books')
                  ->select('book_sys_id', 'books.book_cost_usd', 'books.book_summary_cost_usd', 'books.bookfull_flagged', 'books.booksummary_flagged', 'books.book_title')
                  ->where($where_array)
                  ->orderBy('read_count', 'desc')
                  ->take(1)
                  ->get();
  
    if(empty($found_books[0])){
        return response([
            "status" => "error", 
            "message" => "Item not found"
        ]);
    }

    $url = "https://api.paystack.co/transaction/initialize";
    $book_name = $found_books[0]->book_title;
    if($request->item_type == "book_full"){
        $amt = $found_books[0]->book_cost_usd*100*config('app.dollartocedirate');
    } else if($request->item_type == "book_summary"){
        $amt = $found_books[0]->book_summary_cost_usd*100*config('app.dollartocedirate');
    } else {
        return response([
            "status" => "error", 
            "message" => "Book error"
        ]);
    }
    
    /*
    $book_details_array = array(
        'display_name' => 'Book Name',
        'variable_name' => 'Book Name',
        'value' => $book_name,
    );
    
    $custom_fields_array = array(
        'custom_fields' => $book_details_array
    );

    $fields = array(
        'email' => $final_email,
        'amount' => $amt,
        //'currency' => "USD",
        'metadata' => $custom_fields_array
    );
    */



    $fields = [
        'email' => $final_email,
        'amount' => $amt,
        //'currency' => "USD",
        'callback_url' => config('app.paystackpaymentcallback'),
    ];

    $authorization =  "Authorization: Bearer " . config('app.paystacksecretkey');

    $fields_string = http_build_query($fields);

    //open connection
    $ch = curl_init();
    
    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POST, true);
    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        $authorization,
        "Cache-Control: no-cache",
    ));
    
    //So that curl_exec returns the contents of the cURL; rather than echoing it
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
    
    //execute post
    $result = curl_exec($ch);
    $result = json_decode($result);

    if($result->status == true && !empty($result->data->reference)){
        $transactionData["transaction_sys_id"] =  $final_email . "_" . date("YmdHis") . UtilController::getRandomString(4);
        $transactionData["transaction_type"] = $request->item_type;
        $transactionData["transaction_referenced_item_id"] = $found_books[0]->book_sys_id;
        $transactionData["transaction_buyer_email"] = $final_email;
        $transactionData["transaction_buyer_email"] = $final_phone;
        $transactionData["transaction_payment_type"] = "paystack";
        $transactionData["transaction_payment_ref_id"] = $result->data->reference;
        $transactionData["transaction_payment_date"] = date("Y-m-d");
        $transactionData["transaction_payment_status"] = "unverified";
        $transaction = Transaction::create($transactionData);
    } 

    return $result;

}

public function verifyPayStackPayment(Request $request){

    $validatedData = $request->validate([
        "reference" => "bail|required|max:100",
    ]);
    return UtilController::verifyPayStackPayment($request->reference);
}




public function recordGoogleInAppPurchase(Request $request){

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

    $validatedData = $request->validate([
        "user_email" => "bail|required|max:100",
        "item_id" => "bail|required|max:100",
        "item_type" => "bail|required|max:100",
        "payment_type" => "bail|required|max:100",
        "payment_ref_number" => "bail|required|max:100",
        "payment_date" => "bail|required|max:100",
        "app_type" => "bail|required|max:8",
        "app_version_code" => "bail|required|integer"
    ]);

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
        $userData["passcode"] = uniqid();        
        $userData["user_android_app_version_code"] = "1";
        $userData["user_ios_app_version_code"] = "1";
        $user1 = User::create($userData);
    } 


    if($request->item_type != "book_full" && $request->item_type != "book_summary"){
        return response([
            "status" => "error", 
            "message" => "Book error"
        ]);
    } 
    
    if($request->payment_type != "momo" && $request->payment_type != "card" && $request->payment_type != "google"){
        return response([
            "status" => "error", 
            "message" => "Payment error"
        ]);
    } 

    $book = Book::where('book_sys_id', '=', $request->item_id)->first();
    if($book == null || empty($book->book_sys_id)){
        return response([
            "status" => "error", 
            "message" => "Book not found"
        ]);
    }
    

    $transactionData["transaction_sys_id"] =  auth()->user()->user_id . "_" . date("YmdHis") . UtilController::getRandomString(4);
    $transactionData["transaction_type"] = $request->item_type;
    $transactionData["transaction_referenced_item_id"] = $book->book_sys_id;
    $transactionData["transaction_buyer_email"] = auth()->user()->user_email;
    $transactionData["transaction_payment_type"] = $request->payment_type;
    $transactionData["transaction_payment_ref_id"] = $request->payment_ref_number;
    $transactionData["transaction_payment_date"] = $request->payment_date;
    $transactionData["transaction_payment_status"] = "verified_passed";
    $transaction = Transaction::create($transactionData);


    return response([
        "status" => "success", 
        "message" => "You can start reading your book while we verify the payment."
    ]);

}

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION SENDS LIST OF BOOKS
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */

    public function getMyBooksListing(Request $request)
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
    
    
        $where_array = array(
            ['transaction_buyer_email', '=', $request->user()->user_email],
            ['transaction_payment_status', '=', "verified_passed"]
        ); 

        $found_transactions = DB::table('transactions')
        ->select('transactions.transaction_referenced_item_id')
        ->where($where_array)
        ->orderBy('created_at', 'desc')
        //->take(100)
        ->get();

        $found_books = array();
        for ($i=0; $i < count($found_transactions); $i++) { 

            $where_array2 = array(
                ['books.book_sys_id', '=', $found_transactions[$i]->transaction_referenced_item_id],
                ['books.booksummary_flagged', '=', 0]
            ); 
            
            $this_book = DB::table('books')
            ->select('books.book_id', 'books.book_cover_photo', 'books.book_sys_id', 'books.book_title', 'books.book_author', 'books.book_ratings', 'books.book_description_short', 'books.book_description_long', 'books.book_pages', 'books.book_pdf', 'books.book_summary_pdf', 'books.book_audio', 'books.book_summary_audio', 'books.book_cost_usd', 'books.book_summary_cost_usd', 'books.bookfull_flagged', 'books.booksummary_flagged')
            ->where($where_array2)
            ->orderBy('created_at', 'desc')
            //->take(100)
            ->get();
            
    
            if(!empty($this_book[0]->book_sys_id)){
                if(!empty($this_book[0]->book_cover_photo) && file_exists(public_path() . "/uploads/books_cover_arts/" . $this_book[0]->book_cover_photo)){
                    $this_book[0]->book_cover_photo = config('app.books_cover_arts_folder') . "/" . $this_book[0]->book_cover_photo;
                } else {
                    $this_book[0]->book_cover_photo = config('app.books_cover_arts_folder') . "/sample_cover_art.jpg";
                }

                if(!$this_book[0]->bookfull_flagged && !empty($this_book[0]->book_pdf) && file_exists(public_path() . "/uploads/books_fulls/" . $this_book[0]->book_pdf)){
                    $this_book[0]->book_pdf = config('app.books_full_folder') . "/" . $this_book[0]->book_pdf;
                    if($this_book[0]->book_cost_usd <=  0){
                        $this_book[0]->book_cost_usd = "Free";
                    } else {
                        $this_book[0]->book_cost_usd = "$" . strval($this_book[0]->book_cost_usd);
                    }
                } else {
                    $this_book[0]->book_pdf = "";
                    $this_book[0]->book_cost_usd = "";
                }
                if(!$this_book[0]->booksummary_flagged && !empty($this_book[0]->book_summary_pdf) && file_exists(public_path() . "/uploads/books_summaries/" . $this_book[0]->book_summary_pdf)){
                    $this_book[0]->book_summary_pdf = config('app.books_summaries_folder') . "/" . $this_book[0]->book_summary_pdf;
                    if($this_book[0]->book_summary_cost_usd <=  0){
                        $this_book[0]->book_summary_cost_usd = "Free";
                    } else {
                        $this_book[0]->book_summary_cost_usd = "$" . strval($this_book[0]->book_summary_cost_usd);
                    }
                } else {
                    $this_book[0]->book_summary_pdf = "";
                    $this_book[0]->book_summary_cost_usd = "";
                }
                if(!empty($this_book[0]->book_audio) && file_exists(public_path() . "/uploads/books_audios/" . $this_book[0]->book_audio)){
                    $this_book[0]->book_audio = config('app.url') . "/" . $this_book[0]->book_audio;
                } else {
                    $this_book[0]->book_audio = "";
                }
                if(!empty($this_book[0]->book_summary_audio) && file_exists(public_path() . "/uploads/books_audios_summaries/" . $this_book[0]->book_summary_audio)){
                    $this_book[0]->book_summary_audio = config('app.url') . "/" . $this_book[0]->book_summary_audio;
                } else {
                    $this_book[0]->book_summary_audio = "";
                }
                $this_book[0]->book_cost_cedi_info = "You will be charged the cedi equivalent of the listed price at $1 to Ghc" .  strval(config('app.dollartocedirate'));

                $transaction = Transaction::where('transaction_type', '=', "book_full")->where('transaction_referenced_item_id', '=', $this_book[0]->book_sys_id)->where('transaction_buyer_email', '=', auth()->user()->user_email)->where('transaction_payment_status', '=', "verified_passed")->first();
                if($transaction == null || empty($transaction->transaction_referenced_item_id)){
                    $this_book[0]->book_full_purchased = "no";
                } else {
                    $this_book[0]->book_full_purchased = "yes";
                }

                $transaction = Transaction::where('transaction_type', '=', "book_summary")->where('transaction_referenced_item_id', '=', $this_book[0]->book_sys_id)->where('transaction_buyer_email', '=', auth()->user()->user_email)->where('transaction_payment_status', '=', "verified_passed")->first();
                if($transaction == null || empty($transaction->transaction_referenced_item_id)){
                    $this_book[0]->book_summary_purchased = "no";
                } else {
                    $this_book[0]->book_summary_purchased = "yes";
                }

                $this_book[0]->book_reference_url = config('app.url') . "/buy?ref=" . $this_book[0]->book_sys_id;

                array_push($found_books, $this_book[0]);
            }

        }

        return response([
            "status" => "success", 
            "message" => "Operation successful", 
            "data" => $found_books
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION SENDS LIST OF BOOKS
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */

    public function verifyUserCanAccessBook($trxref, $reference, $book_sys_id, $summary_or_book)
    {
        if(!empty($trxref) && !empty($reference)){
        
            $this_transaction = DB::table('transactions')
                    ->select('transaction_sys_id', 'transaction_referenced_item_id', 'transaction_type', 'transaction_payment_status', 'transaction_payment_type')
                    ->where([['transaction_payment_ref_id', '=', $reference]])
                    ->orderBy('created_at', 'desc')
                    ->take(1)
                    ->get();
        
            //var_dump($this_transaction[0]); exit;
            if(empty($this_transaction[0]) || empty($this_transaction[0]->transaction_sys_id)){
                //echo "here 2"; exit;
                $error = "We could not verify your payment";
            } 
        
            if($this_transaction[0]->transaction_payment_type == "google" && $this_transaction[0]->transaction_payment_status == "verified_passed"){
              $verification_response = "google_passed";
            } else {
              $verification_response = UtilController::verifyPayStackPayment($reference);
            }
            //var_dump($verification_response); exit;
            if(
              (!empty($verification_response->data->status) && $verification_response->data->status == "success")
              || 
              $verification_response = "google_passed"
              ){
                $book = Book::where('book_sys_id', '=', $this_transaction[0]->transaction_referenced_item_id)->first();
                if($book == null || empty($book->book_sys_id)){
                    $error = "Book not found. You can contact support if this is a problem";
                }
        
                if($this_transaction[0]->transaction_type == "book_full"){
                //echo "here 1"; 
                    if(!empty($book->book_pdf) && file_exists(public_path() . "/uploads/books_fulls/" . $book->book_pdf)){
                        $reader_book_url = "uploads/books_fulls/" . $book->book_pdf;
                        $reader_book_file_name = $book->book_pdf;
                //echo "here 2"; exit;
                    } else {
                        $error = "Book not found. You can contact support if this is a problem";
                    }
                } else if($this_transaction[0]->transaction_type == "book_summary"){
                //echo "here 3"; exit;
                    if(!empty($book->book_summary_pdf) && file_exists(public_path() . "/uploads/books_summaries/" . $book->book_summary_pdf)){
                        $reader_book_url = "uploads/books_summaries/" . $book->book_summary_pdf;
                        $reader_book_file_name = $book->book_summary_pdf;
                //echo "here 4"; exit;
                    } else {
                        $error = "Book not found. You can contact support if this is a problem";
                    }
                } else {
                    $error = "Book not found. You can contact support if this is a problem";
                }
            } else {
                $error = "We could not verify your payment";
            }
        
        } else if(!empty($book_sys_id) && !empty($summary_or_book)){ 
        
            $book = Book::where('book_sys_id', '=', $book_sys_id)->first();
            if($book == null || empty($book->book_sys_id)){
              $error = "The free book not found. You can contact support if this is a problem";
            }
        
            if($summary_or_book == "1"){ // book_full
              //echo "here 1"; 
              if(!empty($book->book_pdf) && file_exists(public_path() . "/uploads/books_fulls/" . $book->book_pdf) && $book->book_cost_usd <= 0){
                  $reader_book_url = "uploads/books_fulls/" . $book->book_pdf;
                  $reader_book_file_name = $book->book_pdf;
              //echo "here 2"; exit;
              } else {
                  $error = "The free book not found. You can contact support if this is a problem";
              }
            } else if($summary_or_book == "2"){ //book_summary
              //echo "here 3"; exit;
              if(!empty($book->book_summary_pdf) && file_exists(public_path() . "/uploads/books_summaries/" . $book->book_summary_pdf)  && $book->book_summary_cost_usd <= 0){
                $reader_book_url = "uploads/books_summaries/" . $book->book_summary_pdf;
                $reader_book_file_name = $book->book_summary_pdf;
                //echo "here 4"; exit;
              } else {
                  $error = "The free book was not found. You can contact support if this is a problem";
              }
            } else {
              $error = "The free book not found. You can contact support if this is a problem";
            }
          
        } else {
            $error = "Please buy this book to read";
            $reader_book_url = "";
            $reader_book_file_name = "";
        }
        
        if(!empty($error)){
            //echo "error: " . $error;
        }
        if(!empty($reader_book_url) && !empty($reader_book_file_name)){
            header("Content-type: application/pdf");
            //header("Content-Disposition: attachment; filename=" . $reader_book_file_name);
            header("Content-Disposition: inline; filename=" . $reader_book_file_name);
        
            //@readfile($reader_book_url);
        
            echo "<br><br><br>reader_book_url: " . $reader_book_url;
            echo "<br><br><br>reader_book_file_name: " . $reader_book_file_name;
            //echo "<br><br><br>final_url: " . $final_url;
        }
        

    }

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | THIS FUNCTION CREATES A PLAN ON PAYSTACK FOR SUBSCRIPTIONS
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */

    public function sendCreatePayStackPaymentPlan(Request $request){

        $validatedData = $request->validate([
            "plan_name" => "bail|required|max:100",
            "plan_interval" => "bail|required|integer",
            "plan_benefits_description" => "bail|required|max:2000",
        ]);
        return UtilController::createPayStackPaymentPlan($request->plan_name, $request->plan_interval, $request->plan_benefits_description);
    }


}
