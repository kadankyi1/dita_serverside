<?php
//var_dump($_GET["kw"]);
use App\Models\version1\Book;
use App\Models\version1\Transaction;
use App\Http\Controllers\version1\UtilController;

//var_dump($_GET); exit;
if(!empty($_GET["trxref"]) && !empty($_GET["reference"])){

    $trxref = $_GET["trxref"];
    $reference = $_GET["reference"];

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

} else if(!empty($_GET["ref"]) && !empty($_GET["type"]) ){ 
    
    $summary_or_book = $_GET["type"];
    $book_sys_id = $_GET["ref"];

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
    $header_disposition = "Content-Disposition: attachment; filename=" . $reader_book_file_name;
    header("Content-type: application/pdf");
    header($header_disposition);
    //header("Content-Disposition: inline; filename=" . $reader_book_file_name);
    @readfile($reader_book_url);

    //echo "<br><br><br>reader_book_url: " . $reader_book_url;
    //echo "<br><br><br>reader_book_file_name: " . $reader_book_file_name;
    //echo "<br><br><br>header_disposition: " . $header_disposition;
}

?>
