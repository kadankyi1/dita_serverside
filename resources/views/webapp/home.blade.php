<!--
Author: W3layouts
Author URL: http://w3layouts.com
-->
@php($all_users_count = DB::table('users')->count())
@php($all_books_count = DB::table('books')->count())
@php($all_books_count = DB::table('books')->where(array(['book_summary_pdf', '<>', '']))->count())

<?php
use App\Models\version1\Book;
use App\Models\version1\Transaction;

    $found_books = DB::table('books')
    ->select('books.book_id', 'books.book_cover_photo', 'books.book_sys_id', 'books.book_title', 'books.book_author', 'books.book_ratings', 'books.book_description_short', 'books.book_description_long', 'books.book_pages', 'books.book_pdf', 'books.book_summary_pdf', 'books.book_audio', 'books.book_summary_audio', 'books.book_cost_usd', 'books.book_summary_cost_usd')
    ->orderBy('created_at', 'desc')
    ->take(4)
    ->get();
    
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
                $found_books[$i]->book_summary_cost_usd = "Summary available for Free";
            } else {
                $found_books[$i]->book_summary_available = "$" . $found_books[$i]->book_summary_cost_usd;
            }
        } else {
            $found_books[$i]->book_summary_pdf = "";
            $found_books[$i]->book_summary_available = "";
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

        $transaction = Transaction::where('transaction_type', '=', "book_full")->where('transaction_referenced_item_id', '=', $found_books[$i]->book_sys_id)->where('transaction_referenced_item_id', '=', $found_books[$i]->book_sys_id)->where('transaction_payment_status', '=', "verified_passed")->first();
        if($transaction == null || empty($transaction->transaction_referenced_item_id)){
            $found_books[$i]->book_full_purchased = "no";
        } else {
            $found_books[$i]->book_full_purchased = "yes";
        }

        $transaction = Transaction::where('transaction_type', '=', "book_summary")->where('transaction_referenced_item_id', '=', $found_books[$i]->book_sys_id)->where('transaction_referenced_item_id', '=', $found_books[$i]->book_sys_id)->where('transaction_payment_status', '=', "verified_passed")->first();
        if($transaction == null || empty($transaction->transaction_referenced_item_id)){
            $found_books[$i]->book_summary_purchased = "no";
        } else {
            $found_books[$i]->book_summary_purchased = "yes";
        }
    }

  //var_dump($found_books);
  //exit;
?>

<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <meta name="keywords" content="Tafarri,Tafarr.com,Summaries,Book Summaries">
    <link rel="apple-touch-icon" sizes="180x180" href="webapp/images/favico/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="webapp/images/favico/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="webapp/images/favico/favicon-16x16.png">
    <link rel="manifest" href="webapp/images/favico/site.webmanifest">
    <link rel="mask-icon" href="webapp/images/favico/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <title>Tafarri - Summaries</title>

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
                <li class="nav-item active">
                    <a class="nav-link" href="/">Search <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item @@about__active">
                    <a class="nav-link" href="/reader">Read</a>
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
          </div>
          <!-- toggle switch for light and dark theme -->
          
          <div class="mobile-position">
              <nav class="navigation">
                  <div class="theme-switch-wrapper">
                      <label class="theme-switch" for="checkbox">
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
<!-- banner -->
<section class="banner w3pvt-banner" id="home">
    <div class="container py-5">
        <div class="banner-text py-md-4">
            <div class="row banner-info">
                <div class="col-lg-7 w3pvt-logo align-self">
                    <h2>Summaries</h2>
                    <p class="mt-3">Search for a summary using a book title or a reference number you get from our mobile app. We want to make it easier to get the vital information books provide</p>
                    <form id="kw_form" method="post" class="">
                        <input type="text" id="kw" placeholder="book name or a reference number" required="">
                        <button onclick="goToSearch();" class="btn">Search</button>
                    </form>
                    <!--<p class="mt-2 link">*We will give a trial download link to your mail address</p>-->
                </div>
                <div class="col-lg-5 col-md-7 mt-lg-0 mt-4">
                    <img src="webapp/images/screenshot.png" alt="" class="img-fluid" />
                </div>
            </div>
        </div>
    </div>
</section>
<!-- //banner -->

<section class="w3l-blog-block py-5">
  <div class="container py-lg-4 py-md-3">

    <div class="welcome-left text-center py-md-3 mb-md-5">
      <h3 class="mb-4" style="color: #2c3038">Latest Summaries</h3>
    </div>
    <div class="row">

      <?php foreach ($found_books as $key => $item) { ?>
        <div class="col-lg-3 col-md-6 item">
            <div class="card">
                <div class="card-header p-0 position-relative">
                    <a href="/buy?ref=<?php echo $item->book_sys_id ?>">
                        <img class="card-img-bottom d-block" src="<?php echo $item->book_cover_photo ?>" alt="Card image cap" height="300px">
                    </a>
                    <!--
                      <ul class="location-top">
                        <li class="tip"><?php //echo $item->book_summary_available ?></li>
                      </ul>
                    -->
                </div>
                <div class="card-body blog-details">
                    <a href="/buy?ref=<?php echo $item->book_sys_id ?>" class="blog-desc"><?php echo $item->book_title ?></a>
                    <p class="list-book-desc"><?php echo $item->book_description_short ?></p>
                    <div class="author align-items-center mt-3 mb-1">
                      <span class="meta-value">- By <?php echo $item->b.y1YLhBTZQoe3H!cook_author ?></span>
                    </div>
                </div>
            </div>
        </div>
      <?php } ?>


    </div>
  </div>
</section>
<!-- stats -->
<section class="w3l-stats py-5" id="stats">
    <div class="gallery-inner container py-md-5 py-4">
        <div class="row stats-con">
                <tr>    
                  <th></th>               
                </tr>
            <div class="offset-md-2"></div>
            <div class="col-md-4 col-sm-6 stats_info counter_grid1">
              <span class="fa fa-download"></span>
              <p class="counter">{{$all_books_count}}</p>
              <h3>Summaries</h3>
            </div>
            <div class="col-md-4 col-6 stats_info counter_grid mt-md-0 mt-5">
                <span class="fa fa-smile-o"></span>
                <p class="counter">{{$all_users_count}}</p>
                <h3>Users</h3>
            </div>
        </div>
    </div>
</section>
<!-- middle -->
	<div class="middle py-5">
		<div class="container py-xl-5 py-lg-3">
			<div class="welcome-left text-center py-md-3 mb-md-5">
        <h3 class="mb-4">Search & Read On Our Mobile Apps</h3>
        <p class="text-italic">Find a summary on the mobile app, use the reference number to pay on our website and read on your mobile apps or on our website. Click the button below to download your desired mobile app</p>
				<a href="https://play.google.com/store/apps/details?id=com.tafarri.tafarri" target="_blank" class="btn btn-primary btn-style mt-md-5 mt-4 mr-2">Android App</a>
				<a href="https://apps.apple.com/us/app/id1670395865" target="_blank" class="btn btn-white btn-style mt-md-5 mt-4">iPhone App</a>
			</div>
		</div>
	</div>
<!-- //middle -->
<!-- //stats -->
<!-- 
 <section class="w3l-clients py-5" id="clients">
    <div class="call-w3 py-lg-5 py-md-4">
        <div class="container">
            <h3 class="title-big text-center mb-2">Our Valuable customers</h3>
            <p class="text-head mb-5">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Natus reprehenderit expedita
                quos sunt neque! Ullam, ut? Quam nulla esse atque?</p>
            <div class="company-logos text-center">
                <div class="row logos mb-md-5">
                    <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                        <img src="webapp/images/logo1.png" alt="" class="img-fluid">
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                        <img src="webapp/images/logo2.png" alt="" class="img-fluid">
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-4 col-6 mt-sm-0 mt-4">
                        <img src="webapp/images/logo3.png" alt="" class="img-fluid">
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-4 col-6 mt-md-0 mt-4">
                        <img src="webapp/images/logo4.png" alt="" class="img-fluid">
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-4 col-6 mt-lg-0 mt-4">
                        <img src="webapp/images/logo5.png" alt="" class="img-fluid">
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-4 col-6 mt-lg-0 mt-4">
                        <img src="webapp/images/logo1.png" alt="" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
-->

  <!-- forms -->
<!-- 
<section class="container">
  <div class="w3l-forms-9 px-4" id="newsletter">
    <div class="main-w3 py-4">
      <div class="container-fluid py-lg-3 py-2">
        <div class="row align-items-center">
          <div class="main-midd col-lg-6">
            <h4 class="title-head">Subscribe our newsletter</h4>
            <p>We’re a team of non-cynics who truly care for our work.</p>
          </div>
          <div class="main-midd-2 col-lg-6 mt-lg-0 mt-4">
            <form action="#url" method="GET" class="rightside-form">
              <input type="email" class="form-control" name="email" placeholder="Enter your email">
              <button class="btn" type="submit">Subscribe</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
-->

  <!-- //forms -->
  <!-- footer-28 block -->
  <section class="app-footer">
    <footer class="footer-28">
      <div class="footer-bg-layer">
        <div class="container py-lg-3">
          <div class="row footer-top-28">
            <!--
            <div class="col-lg-4 footer-list-28 mt-5">
              <h6 class="footer-title-28">Contact information</h6>
              <p>Lorem ipsum dolor sit amet, consectetur elit. Nihil sit dicta, commodi maxime
                provident quidem non, amet.</p>

              <div class="main-social-footer-28 mt-3">
                <ul class="social-icons">
                  <li class="facebook">
                    <a href="#link" title="Facebook">
                      <span class="fa fa-facebook" aria-hidden="true"></span>
                    </a>
                  </li>
                  <li class="twitter">
                    <a href="#link" title="Twitter">
                      <span class="fa fa-twitter" aria-hidden="true"></span>
                    </a>
                  </li>
                  <li class="dribbble">
                    <a href="#link" title="Dribbble">
                      <span class="fa fa-dribbble" aria-hidden="true"></span>
                    </a>
                  </li>
                  <li class="google">
                    <a href="#link" title="Google">
                      <span class="fa fa-google" aria-hidden="true"></span>
                    </a>
                  </li>
                </ul>
              </div>
            </div>
            
            <div class="col-lg-8">
              <div class="row">
                <div class="col-md-4 col-6 footer-list-28 mt-5">
                  <h6 class="footer-title-28">Useful links</h6>
                  <ul>
                    <li><a href="features.html">App Features</a></li>
                    <li><a href="about.html">About us</a></li>
                    <li><a href="#blog">Blog posts</a></li>
                    <li><a href="#pricing">Pricing plans</a></li>
                  </ul>
                </div>
                <div class="col-md-4 col-6 footer-list-28 mt-5">
                  <h6 class="footer-title-28">Product help</h6>
                  <ul>
                    <li><a href="#privacy">Privacy policy</a></li>
                    <li><a href="#support"> Support</a></li>
                    <li><a href="#terms">Terms & conditions</a></li>
                    <li><a href="#license">License and uses</a></li>
                  </ul>
                </div>
                <div class="col-md-4 footer-list-28 mt-5">
                  <h6 class="footer-title-28">Download</h6>
                  <a href="#playstore"><img src="webapp/images/googleplay.png" class="img-fluid" alt=""></a>
                  <a href="#appstore"><img src="webapp/images/appstore.png" class="img-fluid mt-md-2" alt=""></a>
                </div>
              </div>
            </div>
            -->
          </div>
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

  <!-- bootstrap js https://p.w3layouts.com/demos_new/template_demo/11-08-2020/appflow-liberty-demo_Free/1795288211/web/blog-single.html -->
  <script src="webapp/js/bootstrap.min.js"></script>
  <script src="webapp/js/custom/config.js"></script>
  <script src="webapp/js/custom/home/custom-home.js"></script>
  </body>

  </html>