<!--
Author: W3layouts
Author URL: http://w3layouts.com
-->
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>App flow - App launch Category Bootstrap Responsive Website Template - Contact : W3Layouts</title>

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
          Tafara
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
                <li class="nav-item active">
                    <a class="nav-link" href="/contact">Contact <span class="sr-only">(current)</span></a>
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
<section class="w3l-breadcrumb">
    <div class="container">
    </div>
</section>
<!-- contacts-5-grid -->
<div class="w3l-contact-10 py-5" id="contact">
    <div class="form-41-mian pt-lg-4 pt-md-3 pb-4">
        <div class="container">
            <div class="heading text-center mx-auto">
                <h3 class="title-big mb-2">Get in Touch with Us </h3>
                <p class="mb-5">If you have a question regarding our services, feel free
                    to contact us</p>
            </div>
            <div class="row">
                <div class="offset-4 col-lg-5 contacts-5-grid-main section-gap mt-lg-0 mt-4">
                    <div class="contacts-5-grid">
                        <div class="map-content-5">
                            <section class="tab-content">
                                <div class="contact-type">
                                    <div class="address-grid mb-3">
                                        <h6>Address</h6>
                                        <p>Hse C/11 Sraha-West, Ashaley Botwe, Accra, Ghana.</p><span
                                            class="pos-icon">
                                            <span class="fa fa-map"></span>
                                        </span>
                                    </div>
                                    <div class="address-grid mb-3">
                                        <h6>Email</h6>
                                        <a href="mailto:mailone@example.com" class="link1">info@fishpott.com</a>
                                        <span class="pos-icon">
                                            <span class="fa fa-envelope">

                                            </span>
                                        </span>
                                    </div>
                                    <div class="address-grid">
                                        <h6>Phone</h6>
                                        <a href="tel:+12 324-016-695" class="link1">+233 324-016-695</a><span
                                            class="pos-icon">
                                            <span class="fa fa-headphones"></span>
                                        </span>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- //contacts-5-grid -->
    </div>
</div>

  <!-- forms -->
  <!-- //forms -->
  <!-- footer-28 block -->
	<div class="middle py-5">
		<div class="container py-xl-5 py-lg-3">
			<div class="welcome-left text-center py-md-3 mb-md-5">
        <h3 class="mb-4">Search & Read on our mobile Apps</h3>
        <p class="text-italic">Find a book on the mobile app, use the reference number to pay on our website and read on your mobile apps or on our website. Click the button below to download your desired mobile app</p>
				<a href="#download" class="btn btn-primary btn-style mt-md-5 mt-4 mr-2">Android App</a>
				<a href="#download" class="btn btn-white btn-style mt-md-5 mt-4">iPhone App</a>
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

  </body>

  </html>