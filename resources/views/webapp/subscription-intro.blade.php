<!--
Author: W3layouts
Author URL: http://w3layouts.com
-->
<?php
use App\Models\version1\Book;
use App\Models\version1\Transaction;

if(!empty($_GET["ref"])){
  $id = $_GET["ref"];
  $where_array = array(
      ['book_sys_id', '=', $id],
      ['book_summary_pdf', '<>', ''],
  ); 
  $found_books = DB::table('books')
                ->select('books.book_id', 'books.book_cover_photo', 'books.book_sys_id', 'books.book_title', 'books.book_author', 'books.book_ratings', 'books.book_description_short', 'books.book_description_long', 'books.book_pages', 'books.book_pdf', 'books.book_summary_pdf', 'books.book_cost_usd', 'books.book_summary_cost_usd')
                ->where($where_array)
                ->orderBy('read_count', 'desc')
                ->take(1)
                ->get();

      if(!empty($found_books[0])){
        
        if(!empty($found_books[0]->book_summary_pdf) && file_exists(storage_path('app/public/books_summaries/' . $found_books[0]->book_summary_pdf))){
            $found_books[0]->book_summary_pdf = config('app.books_summaries_folder') . "/" . $found_books[0]->book_summary_pdf;
            $found_books[0]->book_summary_available_option = '<option selected value="book_summary">Summary</option>';
            if($found_books[0]->book_summary_cost_usd >  0){
              $found_books[0]->book_string_summary_cost_usd = "$" . strval($found_books[0]->book_summary_cost_usd);
            } else {
              $found_books[0]->book_string_summary_cost_usd = "Free";
            }
        } else {
            $found_books[0]->book_summary_pdf = "";
            $found_books[0]->book_summary_available_option = "";
            $found_books[0]->book_string_summary_cost_usd = "Free";
        }

        if(!empty($found_books[0]->book_cover_photo) && file_exists(public_path() . "/uploads/books_cover_arts/" . $found_books[0]->book_cover_photo)){
            $found_books[0]->book_cover_photo = config('app.books_cover_arts_folder') . "/" . $found_books[0]->book_cover_photo;
        } else {
            $found_books[0]->book_cover_photo = config('app.books_cover_arts_folder') . "/sample_cover_art.jpg";
        }
         
      } else {
        $found_books = array();
      }
} else {
  $found_books = array();
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

    <meta name="keywords" content="Buy <?php echo $found_books[0]->book_title ?>, Tafarri, Tafarri.com, Ebooks, Summaries, Ghana EBooks, Ghana Summaries">
    <meta name="description" content="Buy <?php echo $found_books[0]->book_title ?> - Tafarri Ebooks & Summaries">
    <meta name="author" content="Dankyi Anno Kwaku">
    <link rel="canonical" href="<?php echo $_SERVER['REQUEST_URI']; ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="webapp/images/favico/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="webapp/images/favico/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="webapp/images/favico/favicon-16x16.png">
    <link rel="manifest" href="webapp/images/favico/site.webmanifest">
    <link rel="mask-icon" href="webapp/images/favico/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <title><?php echo $found_books[0]->book_title ?></title>


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
              <li class="nav-item">
                  <a class="nav-link" href="/categories">Categories</a>
              </li>
              <li class="nav-item @@about__active">
                  <a class="nav-link" href="/reader">MyBooks</a>
              </li>
              <li class="nav-item">
                  <a class="nav-link" href="/how-to-pay">How To Buy</span></a>
              </li>
               <!--
                <li class="nav-item @@pages__active">
                    <a class="nav-link" href="features.html">App features</a>
                </li>
                -->
                <li class="nav-item ">
                    <a class="nav-link" href="/contact">Contact</a>
                </li>
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
<?php if(!empty($found_books[0])){ ?>
<div class="w3l-contact-10 py-5" id="contact">
    <div class="form-41-mian pt-lg-4 pt-md-3 pb-4">
        <div class="container">
            <div class="heading text-center mx-auto">
                <h5 class="title-small text-center mb-2">You Are Buying</h5>
                <h3 class="title-big2 mb-2"><?php echo $found_books[0]->book_title ?></h3>
                <br>
                <!--<p class="mb-5">You can read this summary here on the web, on androids or iphones</p>-->
            </div>
            <div class="row">

              <div class="col-lg-3 col-md-6 item">
                <div class="card">
                    <div class="card-header p-0 position-relative">
                        <a href="/buy?ref=<?php echo $found_books[0]->book_sys_id ?>">
                            <img class="card-img-bottom d-block" src="<?php echo $found_books[0]->book_cover_photo ?>" alt="Card image cap" height="300px">
                        </a>
                        <ul class="location-top">
                            <li class="tip">
                              <?php if($found_books[0]->book_summary_cost_usd <=  0){ ?>
                                Free
                              <?php } else { echo "$" . $found_books[0]->book_summary_cost_usd; }?>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body blog-details">
                        <a href="/buy?ref=<?php echo $found_books[0]->book_sys_id ?>" class="blog-desc"><?php echo $found_books[0]->book_title ?></a>
                        <div class="author align-items-center mt-3 mb-1">
                          <span class="meta-value">- By <?php echo $found_books[0]->book_author ?></span>
                        </div>
                    </div>
                </div>
              </div>


              <div class="col-lg-5 contacts-5-grid-main section-gap mt-lg-0 mt-4">
                <div class="contacts-5-grid">
                  <div class="map-content-5">
                    <section class="tab-content">
                      <div class="contact-type">
                        <div class="address-grid mb-3">
                          <h6>Description</h6>
                          <p class="list-book-desc"><?php echo nl2br($found_books[0]->book_description_long); ?></p>
                        </div>
                      </div>
                    </section>
                  </div>
                </div>
              </div>


              <div class="col-lg-4 contacts-5-grid-main section-gap mt-lg-0 mt-4">
                <div class="contacts-5-grid">
                  <div class="map-content-5">
                    <section class="tab-content">
                      <div class="contact-type">
                        <div class="address-grid mb-3">
                          <h6>Subscription Required</h6>
                          <p class="list-book-desc">
                            You need an active subscription to read this book
                          </p>
                          <br>


                          <div class="text-right" id="buybtn">
                            <button style="width: 100%;" id="proceed_btn" type="submit"  class="btn btn-style btn-primary">Buy Subscription</button>
                          </div>
                        </div>
                      </div>
                    </section>
                  </div>
                </div>
              </div>

            <!--
                <div class="col-lg-4 form-inner-cont  section-gap mt-lg-0 mt-4">
                  <div style="align-content: center; text-align: center;">
                    <div id="loader" class="lds-roller" style="display: none"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
                  </div>          
                  <form action="" method="post" id="real_buy_form" class="signin-form">
                      <div class="">
                        <?php //echo "book_summary_cost_usd: " . $found_books[0]->book_summary_cost_usd; ?>
                        <?php //echo "book_summary_pdf: " . $found_books[0]->book_summary_pdf; ?>
                        <?php if($found_books[0]->book_summary_cost_usd <= 0 && $found_books[0]->book_summary_pdf != ""){ ?>
                          <div  style="align-content: center; text-align: center;" class="text-right mt-2" id="readsum">
                            <a id="proceed_btn" href="/reader?type=2&ref=<?php echo $found_books[0]->book_sys_id ?>"   class="btn btn-style btn-primary">Read Summary Book</a>
                          </div>
                        <?php } ?>
                          

                        <?php if($found_books[0]->book_summary_cost_usd > 0 ){ ?>
                          <div class="form-input mb-4" >
                            <select name="item_type" id="item_type" onchange="setBuyform(this, '$<?php echo $found_books[0]->book_cost_usd ?>', '$<?php echo $found_books[0]->book_summary_cost_usd ?>')">
                              <option value="">Choose Preference</option>
                              <?php //echo $found_books[0]->book_full_available_option ?>
                              <?php echo $found_books[0]->book_summary_available_option ?>
                            </select>
                          </div>

                          <span id="buyform" style="display: block">
                            <div class="form-input mb-4">
                                <input type="hidden" name="item_id" id="item_id" value="<?php echo $found_books[0]->book_sys_id ?>"
                                    readonly />
                            </div>
                            <div class="form-input mb-4" style="display:none">
                                <input type="text" name="book_amt" id="book_amt" value="<?php echo $found_books[0]->book_string_summary_cost_usd; ?>" readonly />
                            </div>
                            <div class="form-input mb-4">
                              <?php if(!empty($_GET["em"]) && filter_var($_GET["em"], FILTER_VALIDATE_EMAIL)) { ?>
                                <input type="text" name="user_email" id="user_email" placeholder="Email or Phone Number *" value="<?php echo $_GET["em"]; ?>" required />
                              <?php } else { ?>
                                <input type="text" name="user_email" id="user_email" placeholder="Email or Phone Number *" required />
                              <?php } ?>
                            </div>
                            <div class="text-right" id="buybtn">
                              <button id="proceed_btn" type="submit"  class="btn btn-style btn-primary">Proceed</button>
                            </div>
                          </span>
                        <?php } ?>
                      </div>
                  </form>
              </div>
            -->
              

            </div>
        </div>
        <!-- //contacts-5-grid -->
    </div>
</div>
<?php } else { ?>
  <div class="w3l-contact-10 py-5" id="contact">
    <div class="form-41-mian pt-lg-4 pt-md-3 pb-4">
        <div class="container">
            <div class="heading text-center mx-auto">
                <h3 class="title-big2 mb-2">Book Not Found</h3>
                <p class="mb-5">We don't know how you got here.. or do we now?</p>
            </div>
        </div>
        <!-- //contacts-5-grid -->
    </div>
</div>


<?php } ?>

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
  <script src="webapp/js/custom/buy/custom-buy.js"></script>

  </body>

  </html>