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

    //var_dump($this_transaction); exit;
    if(empty($this_transaction[0]) || empty($this_transaction[0]->transaction_sys_id)){
        //echo "here 2"; exit;
        $error = "We could not verify your payment";
    } else {
      if($this_transaction[0]->transaction_payment_type == "google" && $this_transaction[0]->transaction_payment_status == "verified_passed"){
        $verification_response = "google_passed";
      } else {
        $verification_response = UtilController::verifyPayStackPayment($reference);
      }
    }

    //var_dump($verification_response); exit;
    if(empty($error) && ((!empty($verification_response->data->status) && $verification_response->data->status == "success") || $verification_response = "google_passed")){
        $book = Book::where('book_sys_id', '=', $this_transaction[0]->transaction_referenced_item_id)->first();
        if($book == null || empty($book->book_sys_id)){
            $error = "Book not found. You can contact support if this is a problem";
        }

        if($this_transaction[0]->transaction_type == "book_full"){
        //echo "here 1"; 
            if(!empty($book->book_pdf) && file_exists(storage_path('app/public/books_fulls/' . $book->book_pdf))){
                //$reader_book_url = config('app.books_full_folder') . "/" . $book->book_pdf;
                $reader_book_url = "https://tafarri.com/storage/" . $trxref . "/" . $reference . "/" . $book->book_pdf;
        //echo "here 2"; exit;
            } else {
                $error = "Book not found. You can contact support if this is a problem";
            }
        } else if($this_transaction[0]->transaction_type == "book_summary"){
        //echo "here 3"; exit;
            if(!empty($book->book_summary_pdf) && file_exists(storage_path('app/public/books_summaries/' . $book->book_summary_pdf))){
                //$reader_book_url = config('app.books_summaries_folder') . "/" . $book->book_summary_pdf;
                $reader_book_url = "https://tafarri.com/storage/" . $trxref . "/" . $reference . "/" . $book->book_summary_pdf;

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
      if(!empty($book->book_pdf) && file_exists(storage_path('app/public/books_fulls/' . $book->book_pdf)) && $book->book_cost_usd <= 0){
        $reader_book_url = "https://tafarri.com/storage/" . $trxref . "/" . $reference . "/" . $book->book_pdf;
      //echo "here 2"; exit;
      } else {
          $error = "The free book not found. You can contact support if this is a problem";
      }
    } else if($summary_or_book == "2"){ //book_summary
      //echo "here 3"; exit;
      if(!empty($book->book_summary_pdf) && file_exists(storage_path('app/public/books_summaries/' . $book->book_summary_pdf))  && $book->book_summary_cost_usd <= 0){
        $reader_book_url = "https://tafarri.com/storage/" . $trxref . "/" . $reference . "/" . $book->book_summary_pdf;
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
}

//echo "error: " . $error;
//echo "<br><br><br><br><br><br>reader_book_url: " . $reader_book_url; //exit;
?>

<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="keywords" content="Reader,Tafarri,Tafarr.com,Summaries,Book Summaries">
    <link rel="apple-touch-icon" sizes="180x180" href="webapp/images/favico/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="webapp/images/favico/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="webapp/images/favico/favicon-16x16.png">
    <link rel="manifest" href="webapp/images/favico/site.webmanifest">
    <link rel="mask-icon" href="webapp/images/favico/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <title>Tafarri - Read</title>

    <link href="https://fonts.googleapis.com/css?family=Nunito:400,700&display=swap" rel="stylesheet">
    
    <!-- Template CSS -->
    <link rel="stylesheet" href="webapp/css/style-starter.css">
  </head>
  <body>
<!-- header -->
<header id="site-header" class="fixed-top">
  <div class="container">
      <nav class="navbar navbar-expand-lg stroke">
        <a class="navbar-brand theawalfont" style="font-size: 45px" href="/">
          Tafarri
        </a>
          <!-- if logo is image enable this   
      <a class="navbar-brand" href="#index.html">
          <img src="image-path" alt="Your logo" title="Your logo" style="height:35px;" />
      </a> -->
          <button class="navbar-toggler  collapsed bg-gradient" type="button" data-toggle="collapse"
              data-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false"
              aria-label="Toggle navigation">
              <span class="navbar-toggler-icon fa icon-expand fa-bars"></span>
              <span class="navbar-toggler-icon fa icon-close fa-times"></span>
              </span>
          </button>

          <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/">Search</a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="/reader">MyBooks <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item @@about__active">
                    <a class="nav-link" href="/how-to-pay">How To Buy</a>
                </li>
                 <!--
                  <li class="nav-item @@pages__active">
                      <a class="nav-link" href="features.html">App features</a>
                  </li>
                  -->
                  <li class="nav-item @@contact__active">
                      <a class="nav-link" href="/contact">Contact</a>
                  </li>
              </ul>
          </ul>
          </div>
          <!-- toggle switch for light and dark theme -->
          <div class="mobile-position">
              <nav class="navigation">
                  <div class="theme-switch-wrapper">
                      <label class="theme-switch" for="checkbox" style="cursor: pointer;">
                          <input type="checkbox" id="checkbox">
                          <div class="mode-container">
                              <i class="gg-sun"></i>
                              <i class="gg-moon"></i>
                          </div>
                      </label>
                  </div>
              </nav>
          </div>
          <!-- //toggle switch for light and dark theme -->
      </nav>
  </div>
</header>
<!-- //header -->
<section class="w3l-breadcrumb">
    <div class="container">

    </div>
</section>
<!-- contacts-5-grid -->
<?php if(empty($error) && !empty($reader_book_url)) { ?>
<div class="w3l-contact-10 py-5" id="contact">
    <div class="form-41-mian pt-lg-4 pt-md-3 pb-4">
        <div class="container">
            <div class="heading text-center mx-auto">
                <h3 class="mb"><?php echo $book->book_title; ?></h3>
                <p class="mb-5" id="fail_notice" style="font-size: 11px">If your book fails to show, please reload the page.</p>
            </div>

                <div class="col-lg-12">
                  @if ($agent->isMobile())
                  
                    <a href="path_to_file" download="proposed_file_name">Download Your Book</a>

                  @endif
                  <iframe id="theFrame" type="application/pdf" scrolling="auto" src="https://drive.google.com/viewerng/viewer?embedded=true&url=<?php echo $reader_book_url; ?>" frameborder="0" height="1500px" width="100%"></iframe>
                  <iframe id="theFrame" type="application/pdf" scrolling="auto" src="<?php echo $reader_book_url; ?>" frameborder="0" height="1500px" width="100%"></iframe>
                </div>

            </div>
        </div>
        <!-- //contacts-5-grid -->
    </div>
</div>

<div class="middle py-5">
  <div class="container py-xl-5 py-lg-3">
    <div class="welcome-left text-center py-md-3 mb-md-5">
        <h3 class="mb-4">Our Sentiment</h3>
        <p class="text-italic">We hope you enjoy and learn from our summaries half as much as we enjoy putting them together for you.</p>
				
        <!--
        <h3 class="mb-4">Search & Read On Our Mobile Apps</h3>
        <p class="text-italic">Find a summary on the mobile app, use the reference number to pay on our website and read on your mobile apps or on our website. Click the button below to download your desired mobile app</p>
				<a href="https://play.google.com/store/apps/details?id=com.tafarri.tafarri" target="_blank" class="btn btn-primary btn-style mt-md-5 mt-4 mr-2">Android App</a>
				<a href="https://apps.apple.com/us/app/id1670395865" target="_blank" class="btn btn-white btn-style mt-md-5 mt-4">iPhone App</a>
        -->
    </div>
  </div>
</div>
<?php } else { ?>

    <div class="w3l-contact-10 py-5" id="contact">
        <div class="form-41-mian pt-lg-4 pt-md-3 pb-4">
            <div class="container">
                <div class="heading text-center mx-auto">
                    <h5 class="title-small text-center mb-2"></h5>
                    <h3 class="title-big2 mb-2">READER - ACCESS YOUR SUMMARIES</h3>
                    <p class="mb-5" id="info_text">Enter your email to receive a login code</p>
                </div>
                <div class="row">

                    <div class="offset-lg-4 col-lg-4 form-inner-cont">                                   
                        <form action="" method="post" id="sendlogincodeform" class="signin-form">
                            <div class="">
                                <span id="buyform" >
                                <div class="form-input mb-4">
                                    <input type="email" name="user_email" id="user_email" placeholder="Email *" required />
                                    <input type="hidden" name="app_type" id="app_type" value="web" readonly />
                                    <input type="hidden" name="app_version_code" id="app_version_code" value="1" readonly />
                                </div>
                                <div class="text-center" id="buybtn">
                                   <button id="proceed_btn" type="submit"  class="btn btn-style btn-primary">Send Code</button>
                                </div>
                                </span>
                            </div>
                        </form>                        
                        <form action="" method="post" id="verifylogincodeform" class="signin-form" style="display: none">
                            <div class="">
                                <span id="buyform" >
                                <div class="form-input mb-4">
                                    <input type="email" name="user_email" id="user_email2" placeholder="Email *" readonly />
                                </div>
                                <div class="form-input mb-4">
                                    <input type="text" name="user_passcode" id="user_passcode"  placeholder="Login Code *" required />
                                    <input type="hidden" name="app_type" id="app_type" value="web" readonly />
                                    <input type="hidden" name="app_version_code" id="app_version_code" value="1" readonly />
                                </div>
                                <div class="text-center" id="buybtn">
                                   <button id="proceed_btn" type="submit"  class="btn btn-style btn-primary">Login</button>
                                </div>
                                </span>
                            </div>
                        </form>                        
                        <form action="" method="post" id="choosebookform" class="signin-form" style="display: none">
                            <div class="">
                                <span id="buyform" >
                                <div class="form-input mb-4">
                                  <select name="book_chosen" id="book_chosen" required>
                                    <option value="">Choose A Book</option>
                                  </select>
                                </div>
                                <div class="text-center" id="buybtn">
                                   <button id="proceed_btn" type="submit"  class="btn btn-style btn-primary">Read</button>
                                </div>
                                </span>
                            </div>
                        </form>
                        <div style="align-content: center; text-align: center;">
                            <div id="loader" class="lds-roller" style="display: none"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
                        </div>
                    </div>
    
                </div>
            </div>
            <!-- //contacts-5-grid -->
        </div>
    </div>
    
    <div class="middle py-5">
      <div class="container py-xl-5 py-lg-3">
        <div class="welcome-left text-center py-md-3 mb-md-5">
          <h3 class="mb-4">Our Sentiment</h3>
          <p class="text-italic">We hope you enjoy and learn from our summaries half as much as we enjoy putting them together for you.</p>
          
          <!--
          <h3 class="mb-4">Search & Read On Our Mobile Apps</h3>
          <p class="text-italic">Find a summary on the mobile app, use the reference number to pay on our website and read on your mobile apps or on our website. Click the button below to download your desired mobile app</p>
          <a href="https://play.google.com/store/apps/details?id=com.tafarri.tafarri" target="_blank" class="btn btn-primary btn-style mt-md-5 mt-4 mr-2">Android App</a>
          <a href="https://apps.apple.com/us/app/id1670395865" target="_blank" class="btn btn-white btn-style mt-md-5 mt-4">iPhone App</a>
          -->
          </div>
      </div>
    </div>
    

<?php } ?>
<!-- //middle -->
<!-- forms -->
<!-- //forms -->
<!-- footer-28 block -->
<section class="app-footer">
  <footer class="footer-28">
    <div class="footer-bg-layer">
      <div class="container py-lg-3">
        <div class="midd-footer-28 align-center py-4 mt-5">
          <p class="copy-footer-28 text-center"> &copy; <?php echo date('Y'); ?> Tafarri. All Rights Reserved. Design by <a
            href="https://w3layouts.com/">W3Layouts</a></p>
        </div>
      </div>


    </div>
  </footer>

  <!-- move top -->
    <button onclick="topFunction()" id="movetop" title="Go to top">
      &#10548;
    </button>
    <script>
      // When the user scrolls down 20px from the top of the document, show the button
      window.onscroll = function () {
        scrollFunction()
      };

      function scrollFunction() {
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
          document.getElementById("movetop").style.display = "block";
        } else {
          document.getElementById("movetop").style.display = "none";
        }
      }

      // When the user clicks on the button, scroll to the top of the document
      function topFunction() {
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
      }
    </script>
    <!-- /move top -->
  </section>
  <!-- //footer-28 block -->

  <!-- all js scripts and files here -->

  <script src="webapp/js/theme-change.js"></script><!-- theme switch js (light and dark)-->

  <script src="webapp/js/jquery-3.6.3.min.js"></script><!-- default jQuery -->

<!-- magnific popup -->
<script src="webapp/js/jquery.magnific-popup.min.js"></script>
<script>
  $(document).ready(function () {
    $('.popup-with-zoom-anim').magnificPopup({
      type: 'inline',

      fixedContentPos: false,
      fixedBgPos: true,

      overflowY: 'auto',

      closeBtnInside: true,
      preloader: false,

      midClick: true,
      removalDelay: 300,
      mainClass: 'my-mfp-zoom-in'
    });

    $('.popup-with-move-anim').magnificPopup({
      type: 'inline',

      fixedContentPos: false,
      fixedBgPos: true,

      overflowY: 'auto',

      closeBtnInside: true,
      preloader: false,

      midClick: true,
      removalDelay: 300,
      mainClass: 'my-mfp-slide-bottom'
    });
  });
</script>
<!-- magnific popup -->


<script src="webapp/js/owl.carousel.js"></script>
<!-- script for tesimonials carousel slider -->
<script>
  $(document).ready(function () {
    $("#owl-demo1").owlCarousel({
      loop: true,
      margin: 0,
      nav: false,
      responsiveClass: true,
      responsive: {
        0: {
          items: 2,
          nav: false
        },
        736: {
          items: 3,
          nav: false
        },
        1000: {
          items: 4,
          nav: false,
          loop: false
        }
      }
    })
  })
</script>
<!-- //script for tesimonials carousel slider -->

  <!-- stats number counter-->
  <script src="webapp/js/jquery.waypoints.min.js"></script>
  <script src="webapp/js/jquery.countup.js"></script>
  <script>
    $('.counter').countUp();
  </script>
  <!-- //stats number counter -->

  <!-- disable body scroll which navbar is in active -->
  <script>
    $(function () {
      $('.navbar-toggler').click(function () {
        $('body').toggleClass('noscroll');
      })
    });
  </script>
  <!-- disable body scroll which navbar is in active -->

  <!--/MENU-JS-->
  <script>
    $(window).on("scroll", function () {
      var scroll = $(window).scrollTop();

      if (scroll >= 80) {
        $("#site-header").addClass("nav-fixed");
      } else {
        $("#site-header").removeClass("nav-fixed");
      }
    });

    //Main navigation Active Class Add Remove
    $(".navbar-toggler").on("click", function () {
      $("header").toggleClass("active");
    });
    $(document).on("ready", function () {
      if ($(window).width() > 991) {
        $("header").removeClass("active");
      }
      $(window).on("resize", function () {
        if ($(window).width() > 991) {
          $("header").removeClass("active");
        }
      });
    });
  </script>
  <!--//MENU-JS-->

  <!-- bootstrap js -->
  <script src="webapp/js/bootstrap.min.js"></script>
  <script src="webapp/js/custom/config.js"></script>
  <script src="webapp/js/custom/reader/custom-reader.js"></script>
  <script>
    document.getElementById("theFrame").contentWindow.onload = function() {
        this.document.getElementsByTagName("img")[0].style.width="100%";
    };
    </script>
  </body>

  </html>
