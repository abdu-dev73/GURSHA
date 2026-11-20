
<?php
include("connection/connect.php");
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);

function gursha_restaurant_escape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$restaurants = [];

$restaurantResult = mysqli_query($db, "SELECT * FROM restaurant");

if ($restaurantResult) {
    while ($restaurant = mysqli_fetch_assoc($restaurantResult)) {
        $restaurants[] = $restaurant;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Discover restaurants and order delicious food with GURSHA.">
    <meta name="author" content="GURSHA">

    <title>Restaurants | GURSHA</title>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/animsition.min.css" rel="stylesheet">
    <link href="css/animate.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

    <style>
        :root {
            --gursha-green: #173b2d;
            --gursha-green-dark: #10291f;
            --gursha-orange: #e89045;
            --gursha-cream: #faf7f0;
            --gursha-ink: #202820;
            --gursha-muted: #747d75;
            --gursha-white: #fff;
        }

        html {
            scroll-behavior: smooth;
        }

        body.home {
            background: var(--gursha-cream);
            color: var(--gursha-ink);
            font-family: "Segoe UI", Arial, sans-serif;
        }

        body.home a {
            text-decoration: none;
        }

        /* =================================
           GURSHA NAVBAR
        ================================= */

        .gursha-header {
            position: sticky;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            background: var(--gursha-green);
            box-shadow: 0 5px 22px rgba(0,0,0,.10);
        }

        .gursha-navbar {
            min-height: 78px;
            margin: 0;
            padding: 12px 0;
            border: 0;
            border-radius: 0;
            background: var(--gursha-green);
        }

        .gursha-nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .gursha-brand {
            display: inline-flex;
            align-items: center;
            gap: 11px;
            color: #fff !important;
        }

        .gursha-brand-logo {
            display: block;
            width: 58px;
            height: 58px;
            object-fit: contain;
            flex-shrink: 0;
            background: #fff;
            border-radius: 50%;
        }

        .gursha-brand-name {
            display: block;
            color: #fff;
            font-size: 23px;
            font-weight: 850;
            letter-spacing: 2px;
            line-height: 1.1;
        }

        .gursha-brand-tagline {
            display: block;
            margin-top: 4px;
            color: #d9e4dc;
            font-size: 9px;
            font-weight: 600;
            letter-spacing: 1.3px;
        }

        .gursha-nav-toggle {
            display: none;
            padding: 9px 13px;
            border: 1px solid rgba(255,255,255,.35);
            border-radius: 10px;
            background: transparent;
            color: #fff;
            font-size: 21px;
        }

        .gursha-nav-list {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .gursha-nav-list li {
            list-style: none;
        }

        .gursha-nav-link {
            display: inline-block;
            padding: 12px 15px;
            color: #edf3ee !important;
            font-size: 14px;
            font-weight: 600;
            transition: color .2s ease;
        }

        .gursha-nav-link:hover,
        .gursha-nav-link:focus {
            color: var(--gursha-orange) !important;
        }

        .gursha-nav-cta {
            display: inline-block;
            margin-left: 5px;
            padding: 12px 21px;
            border-radius: 30px;
            background: var(--gursha-orange);
            color: var(--gursha-green) !important;
            font-size: 14px;
            font-weight: 800;
            transition: .2s ease;
        }

        .gursha-nav-cta:hover {
            background: #ffc17d;
            transform: translateY(-2px);
        }

        /* =================================
           RESTAURANT HERO
           Uses the homepage typography,
           colors and hero treatment.
        ================================= */

        .gursha-restaurant-hero {
            position: relative;
            display: flex;
            min-height: 390px;
            align-items: center;
            overflow: hidden;
            background:
                linear-gradient(
                    90deg,
                    rgba(13,34,24,.94) 0%,
                    rgba(13,34,24,.79) 48%,
                    rgba(13,34,24,.23) 100%
                ),
                url("images/img/shiro.jpg") center center / cover no-repeat;
        }

        .gursha-restaurant-hero-content {
            max-width: 700px;
            padding-top: 65px;
            padding-bottom: 65px;
            color: #fff;
        }

        .gursha-eyebrow {
            margin-bottom: 17px;
            color: var(--gursha-orange);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 2.8px;
            text-transform: uppercase;
        }

        .gursha-restaurant-hero h1 {
            margin: 0 0 20px;
            color: #fff;
            font-size: 48px;
            font-weight: 850;
            line-height: 1.12;
        }

        .gursha-restaurant-hero h1 span {
            color: var(--gursha-orange);
        }

        .gursha-restaurant-hero p {
            max-width: 570px;
            margin-bottom: 0;
            color: #e2e9e3;
            font-size: 16px;
            line-height: 1.8;
        }

        /* =================================
           ORDERING STEPS
        ================================= */

        .gursha-steps-section {
            padding: 28px 0;
            background: #fff;
            border-bottom: 1px solid #eeeae3;
        }

        .gursha-steps {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        .gursha-step {
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .gursha-step-number {
            display: flex;
            width: 46px;
            height: 46px;
            min-width: 46px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #f8eee1;
            color: var(--gursha-green);
            font-size: 16px;
            font-weight: 850;
        }

        .gursha-step.active .gursha-step-number {
            background: var(--gursha-green);
            color: #fff;
        }

        .gursha-step h3 {
            margin: 0 0 5px;
            color: var(--gursha-green);
            font-size: 14px;
            font-weight: 800;
        }

        .gursha-step p {
            margin: 0;
            color: var(--gursha-muted);
            font-size: 12px;
        }

        /* =================================
           RESTAURANT LISTING
           Reuses homepage card styling.
        ================================= */

        .gursha-section {
            padding: 78px 0;
        }

        .gursha-section-heading {
            margin-bottom: 35px;
        }

        .gursha-section-kicker {
            margin-bottom: 10px;
            color: #c97835;
            font-size: 11px;
            font-weight: 850;
            letter-spacing: 2.2px;
            text-transform: uppercase;
        }

        .gursha-section-heading h2 {
            margin: 0 0 12px;
            color: var(--gursha-green);
            font-size: 36px;
            font-weight: 850;
        }

        .gursha-section-heading p {
            max-width: 620px;
            margin: 0;
            color: var(--gursha-muted);
            font-size: 15px;
            line-height: 1.8;
        }

        .gursha-restaurant-count {
            display: inline-block;
            margin-top: 18px;
            padding: 8px 15px;
            border-radius: 25px;
            background: #f8eee1;
            color: var(--gursha-green);
            font-size: 12px;
            font-weight: 800;
        }

        .gursha-restaurant-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 28px;
        }

        .gursha-restaurant-card {
            height: 100%;
            overflow: hidden;
            border: 1px solid #eeeae3;
            border-radius: 18px;
            background: #fff;
            transition: .25s ease;
        }

        .gursha-restaurant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,.08);
        }

        .gursha-restaurant-image-link {
            display: block;
            overflow: hidden;
        }

        .gursha-restaurant-image {
            display: block;
            width: 100%;
            height: 230px;
            object-fit: cover;
            background: #eeeae3;
            transition: transform .3s ease;
        }

        .gursha-restaurant-card:hover .gursha-restaurant-image {
            transform: scale(1.04);
        }

        .gursha-card-content {
            padding: 21px;
        }

        .gursha-card-content h3 {
            margin: 0 0 9px;
            color: var(--gursha-green);
            font-size: 19px;
            font-weight: 850;
        }

        .gursha-card-content h3 a {
            color: inherit;
        }

        .gursha-restaurant-address {
            min-height: 42px;
            margin: 0 0 17px;
            color: var(--gursha-muted);
            font-size: 13px;
            line-height: 1.65;
        }

        .gursha-card-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding-top: 15px;
            border-top: 1px solid #eeeae3;
        }

        .gursha-card-label {
            color: var(--gursha-muted);
            font-size: 12px;
            font-weight: 700;
        }

        .gursha-small-btn {
            display: inline-block;
            padding: 10px 17px;
            border-radius: 25px;
            background: var(--gursha-green);
            color: #fff !important;
            font-size: 12px;
            font-weight: 750;
            transition: .2s ease;
        }

        .gursha-small-btn:hover {
            background: var(--gursha-orange);
            color: var(--gursha-green) !important;
        }

        /* =================================
           EMPTY STATE
        ================================= */

        .gursha-empty {
            padding: 45px 25px;
            border: 1px dashed #d9d4c9;
            border-radius: 12px;
            background: #fff;
            color: var(--gursha-muted);
            text-align: center;
        }

        .gursha-empty i {
            margin-bottom: 15px;
            color: var(--gursha-orange);
            font-size: 38px;
        }

        .gursha-empty h3 {
            margin: 0 0 10px;
            color: var(--gursha-green);
            font-size: 22px;
            font-weight: 850;
        }

        .gursha-empty p {
            margin: 0;
            font-size: 14px;
            line-height: 1.7;
        }

        /* =================================
           FOOTER
           Matches homepage footer.
        ================================= */

        .gursha-footer {
            padding: 48px 0 22px;
            background: var(--gursha-green-dark);
            color: #d0d9d2;
        }

        .gursha-footer-brand {
            margin: 0 0 15px;
            color: var(--gursha-orange);
            font-size: 24px;
            font-weight: 900;
            letter-spacing: 2px;
        }

        .gursha-footer h4 {
            margin: 0 0 17px;
            color: #fff;
            font-size: 15px;
            font-weight: 800;
        }

        .gursha-footer p,
        .gursha-footer a {
            color: #c5d0c8;
            font-size: 13px;
            line-height: 1.9;
        }

        .gursha-footer a:hover {
            color: var(--gursha-orange);
        }

        .gursha-footer ul {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .gursha-footer li {
            margin-bottom: 7px;
        }

        .gursha-footer-bottom {
            margin-top: 30px;
            padding-top: 18px;
            border-top: 1px solid rgba(255,255,255,.13);
            color: #aebbb2;
            font-size: 12px;
            text-align: center;
        }

        /* =================================
           RESPONSIVE DESIGN
        ================================= */

        @media (max-width: 767px) {
            .gursha-nav-inner {
                flex-wrap: wrap;
            }

            .gursha-nav-toggle {
                display: block;
            }

            .gursha-nav-collapse {
                flex-basis: 100%;
                width: 100%;
                padding-top: 12px;
            }

            .gursha-nav-list {
                display: block;
                width: 100%;
            }

            .gursha-nav-list li {
                display: block;
            }

            .gursha-nav-link {
                display: block;
                padding: 10px 0;
            }

            .gursha-nav-cta {
                margin: 7px 0;
            }

            .gursha-restaurant-hero {
                min-height: 350px;
                background-position: center;
            }

            .gursha-restaurant-hero h1 {
                font-size: 38px;
            }

            .gursha-restaurant-hero p {
                font-size: 15px;
            }

            .gursha-steps {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .gursha-section {
                padding: 58px 0;
            }

            .gursha-section-heading h2 {
                font-size: 29px;
            }

            .gursha-restaurant-grid {
                grid-template-columns: 1fr;
                gap: 22px;
            }

            .gursha-restaurant-image {
                height: 215px;
            }
        }

        @media (max-width: 420px) {
            .gursha-brand-name {
                font-size: 20px;
            }

            .gursha-restaurant-hero h1 {
                font-size: 33px;
            }

            .gursha-card-content {
                padding: 17px;
            }
        }
    </style>
</head>

<body class="home">

<!-- GURSHA NAVBAR: SAME DESIGN AS HOMEPAGE -->
<header class="gursha-header">
    <nav class="navbar navbar-dark gursha-navbar">
        <div class="container gursha-nav-inner">

            <a class="gursha-brand" href="index.php">
                <img class="gursha-brand-logo"
                     src="images/gursha-logo.png"
                     alt="GURSHA Logo">

                <span>
                    <span class="gursha-brand-name">GURSHA</span>
                    <span class="gursha-brand-tagline">GOOD FOOD. GOOD MOMENTS.</span>
                </span>
            </a>

            <button class="navbar-toggler gursha-nav-toggle" type="button"
                    data-toggle="collapse" data-target="#mainNavbarCollapse"
                    aria-controls="mainNavbarCollapse" aria-expanded="false"
                    aria-label="Toggle navigation">
                <i class="fa fa-bars" aria-hidden="true"></i>
            </button>

            <div class="collapse navbar-toggleable-md gursha-nav-collapse" id="mainNavbarCollapse">
                <ul class="gursha-nav-list">

                    <li>
                        <a class="gursha-nav-link" href="index.php">Home</a>
                    </li>

                    <li>
                        <a class="gursha-nav-link" href="restaurants.php">Restaurants</a>
                    </li>

                    <li>
                        <a class="gursha-nav-link" href="index.php#popular-dishes">Menu</a>
                    </li>

                    <?php if (empty($_SESSION["user_id"])) { ?>
                        <li>
                            <a class="gursha-nav-link" href="login.php">Login</a>
                        </li>
                    <?php } else { ?>
                        <li>
                            <a class="gursha-nav-link" href="your_orders.php">My Orders</a>
                        </li>

                        <li>
                            <a class="gursha-nav-link" href="javascript:void(0);" onclick="confirmLogout();">Logout</a>
                        </li>
                    <?php } ?>

                    <li>
                        <a class="gursha-nav-cta" href="restaurants.php">
                            Order Now <i class="fa fa-arrow-right"></i>
                        </a>
                    </li>

                </ul>
            </div>

        </div>
    </nav>
</header>


<main>

    <!-- RESTAURANT HERO -->
    <section class="gursha-restaurant-hero">
        <div class="container">
            <div class="gursha-restaurant-hero-content">

                <div class="gursha-eyebrow">
                    DISCOVER GREAT FOOD
                </div>

                <h1>
                    Find Your Next<br>
                    <span>Favorite Restaurant.</span>
                </h1>

                <p>
                    Explore restaurants, discover delicious meals, and choose
                    your favorite food experience. Your next great meal starts here.
                </p>

            </div>
        </div>
    </section>


    <!-- ORDERING STEPS -->
    <section class="gursha-steps-section">
        <div class="container">

            <div class="gursha-steps">

                <div class="gursha-step active">
                    <div class="gursha-step-number">1</div>
                    <div>
                        <h3>Choose Restaurant</h3>
                        <p>Explore available restaurants</p>
                    </div>
                </div>

                <div class="gursha-step">
                    <div class="gursha-step-number">2</div>
                    <div>
                        <h3>Pick Your Favorite Food</h3>
                        <p>Discover delicious dishes</p>
                    </div>
                </div>

                <div class="gursha-step">
                    <div class="gursha-step-number">3</div>
                    <div>
                        <h3>Order and Pay</h3>
                        <p>Enjoy your favorite meals</p>
                    </div>
                </div>

            </div>

        </div>
    </section>


    <!-- RESTAURANT LISTING -->
    <section class="gursha-section">
        <div class="container">

            <div class="gursha-section-heading">
                <div class="gursha-section-kicker">
                    EXPLORE WITH GURSHA
                </div>

                <h2>Discover Our Restaurants</h2>

                <p>
                    Find a restaurant you love, explore its menu, and get ready
                    to enjoy your favorite dishes.
                </p>

                <span class="gursha-restaurant-count">
                    <i class="fa fa-cutlery"></i>
                    <?php echo count($restaurants); ?>
                    <?php echo count($restaurants) === 1 ? 'Restaurant Available' : 'Restaurants Available'; ?>
                </span>
            </div>


            <?php if (!empty($restaurants)) { ?>

                <div class="gursha-restaurant-grid">

                    <?php foreach ($restaurants as $restaurant) {
                        $restaurantUrl = "dishes.php?res_id=" . urlencode((string)$restaurant['rs_id']);
                        $restaurantImage = "admin/Res_img/" . rawurlencode(basename((string)$restaurant['image']));
                    ?>

                        <article class="gursha-restaurant-card">

                            <a class="gursha-restaurant-image-link"
                               href="<?php echo gursha_restaurant_escape($restaurantUrl); ?>">

                                <img
                                    class="gursha-restaurant-image"
                                    src="<?php echo gursha_restaurant_escape($restaurantImage); ?>"
                                    alt="<?php echo gursha_restaurant_escape($restaurant['title']); ?>"
                                    loading="lazy"
                                    onerror="this.onerror=null;this.src='images/img/shiro.jpg';"
                                >

                            </a>

                            <div class="gursha-card-content">

                                <h3>
                                    <a href="<?php echo gursha_restaurant_escape($restaurantUrl); ?>">
                                        <?php echo gursha_restaurant_escape($restaurant['title']); ?>
                                    </a>
                                </h3>

                                <p class="gursha-restaurant-address">
                                    <i class="fa fa-map-marker" style="color:var(--gursha-orange);"></i>
                                    <?php echo gursha_restaurant_escape($restaurant['address']); ?>
                                </p>

                                <div class="gursha-card-bottom">

                                    <span class="gursha-card-label">
                                        <i class="fa fa-cutlery"></i>
                                        Explore menu
                                    </span>

                                    <a class="gursha-small-btn"
                                       href="<?php echo gursha_restaurant_escape($restaurantUrl); ?>">
                                        View Menu
                                        <i class="fa fa-arrow-right"></i>
                                    </a>

                                </div>

                            </div>

                        </article>

                    <?php } ?>

                </div>

            <?php } else { ?>

                <div class="gursha-empty">
                    <i class="fa fa-cutlery"></i>
                    <h3>No Restaurants Available Yet</h3>
                    <p>
                        We're working on bringing delicious restaurants to you.
                        Please check back soon.
                    </p>
                </div>

            <?php } ?>

        </div>
    </section>

</main>


<!-- FOOTER: SAME DESIGN AS HOMEPAGE -->
<footer class="gursha-footer">
    <div class="container">

        <div class="row">

            <div class="col-xs-12 col-sm-5">
                <div class="gursha-footer-brand">GURSHA</div>
                <p>
                    Good food. Good moments. Discover delicious meals and
                    order from restaurants you love.
                </p>
            </div>

            <div class="col-xs-6 col-sm-3">
                <h4>Explore</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="restaurants.php">Restaurants</a></li>
                    <li><a href="index.php#popular-dishes">Popular Dishes</a></li>
                </ul>
            </div>

            <div class="col-xs-6 col-sm-4">
                <h4>Your Account</h4>
                <ul>
                    <?php if (empty($_SESSION["user_id"])) { ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="registration.php">Create Account</a></li>
                    <?php } else { ?>
                        <li><a href="your_orders.php">My Orders</a></li>
                        <li>
                            <a href="javascript:void(0);" onclick="confirmLogout();">
                                Logout
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>

        </div>

        <div class="gursha-footer-bottom">
            &copy; <?php echo date("Y"); ?> GURSHA. All rights reserved.
        </div>

    </div>
</footer>


<script>
    function confirmLogout() {
        if (confirm('Are you sure you want to log out?')) {
            window.location.href = 'logout.php';
        }
    }
</script>

<script src="js/jquery.min.js"></script>
<script src="js/tether.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/animsition.min.js"></script>
<script src="js/bootstrap-slider.min.js"></script>
<script src="js/jquery.isotope.min.js"></script>
<script src="js/headroom.js"></script>
<script src="js/foodpicky.min.js"></script>

</body>
</html>
