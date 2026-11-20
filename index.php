<?php
include("connection/connect.php");
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);

function gursha_escape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Load homepage data using the existing database tables.
$categories = [];
$categoryResult = mysqli_query($db, "SELECT * FROM res_category ORDER BY c_id");

if ($categoryResult) {
    while ($category = mysqli_fetch_assoc($categoryResult)) {
        $categories[] = $category;
    }
}

$popularDishes = [];
$dishResult = mysqli_query($db, "SELECT * FROM dishes LIMIT 6");

if ($dishResult) {
    while ($dish = mysqli_fetch_assoc($dishResult)) {
        $popularDishes[] = $dish;
    }
}

$restaurants = [];
$restaurantResult = mysqli_query($db, "SELECT * FROM restaurant");

if ($restaurantResult) {
    while ($restaurant = mysqli_fetch_assoc($restaurantResult)) {
        $restaurants[] = $restaurant;
    }
}

// Build a category lookup so each restaurant can be filtered without
// running an additional SQL query for every restaurant.
$categoryNames = [];
foreach ($categories as $category) {
    $categoryNames[(string)$category['c_id']] = $category['c_name'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Discover restaurants and order delicious food with GURSHA. Good food. Good moments.">
    <meta name="author" content="GURSHA">
    <title>GURSHA | Good Food. Good Moments.</title>

    <!-- Keep the project's existing local libraries. -->
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

        html { scroll-behavior: smooth; }

        body.home {
            background: var(--gursha-cream);
            color: var(--gursha-ink);
            font-family: "Segoe UI", Arial, sans-serif;
        }

        body.home a { text-decoration: none; }

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

        /* Uploaded GURSHA logo */
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

        .gursha-nav-list li { list-style: none; }

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

        .gursha-hero {
            position: relative;
            display: flex;
            min-height: 570px;
            align-items: center;
            overflow: hidden;
            background:
                linear-gradient(90deg, rgba(13,34,24,.94) 0%, rgba(13,34,24,.79) 48%, rgba(13,34,24,.23) 100%),
                url("images/img/shiro.jpg") center center / cover no-repeat;
        }

        .gursha-hero-content {
            max-width: 700px;
            padding-top: 82px;
            padding-bottom: 82px;
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

        .gursha-hero h1 {
            margin: 0 0 22px;
            color: #fff;
            font-size: 62px;
            font-weight: 850;
            line-height: 1.08;
        }

        .gursha-hero h1 span { color: var(--gursha-orange); }

        .gursha-hero p {
            max-width: 570px;
            margin-bottom: 30px;
            color: #e2e9e3;
            font-size: 17px;
            line-height: 1.8;
        }

        .gursha-btn-primary,
        .gursha-btn-secondary {
            display: inline-block;
            margin: 5px 8px 5px 0;
            padding: 14px 25px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 800;
            transition: .2s ease;
        }

        .gursha-btn-primary {
            background: var(--gursha-orange);
            color: var(--gursha-green) !important;
        }

        .gursha-btn-primary:hover {
            background: #ffc17d;
            transform: translateY(-2px);
        }

        .gursha-btn-secondary { color: #fff !important; }

        .gursha-btn-secondary:hover {
            color: var(--gursha-orange) !important;
        }

        .gursha-section { padding: 78px 0; }
        .gursha-section-white { background: #fff; }
        .gursha-section-heading { margin-bottom: 35px; }

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

        .gursha-category-card {
            height: 100%;
            margin-bottom: 22px;
            padding: 25px 12px;
            border: 1px solid #eee9df;
            border-radius: 17px;
            background: #fff;
            text-align: center;
            transition: .25s ease;
        }

        .gursha-category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 14px 30px rgba(23,59,45,.09);
        }

        .gursha-category-icon {
            display: flex;
            width: 58px;
            height: 58px;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            border-radius: 18px;
            background: #f8eee1;
            color: var(--gursha-green);
            font-size: 25px;
        }

        .gursha-category-card h3 {
            margin: 0;
            color: var(--gursha-green);
            font-size: 15px;
            font-weight: 800;
        }

        .gursha-food-card,
        .gursha-restaurant-card {
            height: 100%;
            margin-bottom: 28px;
            overflow: hidden;
            border: 1px solid #eeeae3;
            border-radius: 18px;
            background: #fff;
            transition: .25s ease;
        }

        .gursha-food-card:hover,
        .gursha-restaurant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,.08);
        }

        .gursha-food-image,
        .gursha-restaurant-image {
            display: block;
            width: 100%;
            height: 220px;
            object-fit: cover;
            background: #eeeae3;
        }

        .gursha-card-content { padding: 21px; }

        .gursha-card-content h3 {
            margin: 0 0 9px;
            color: var(--gursha-green);
            font-size: 19px;
            font-weight: 850;
        }

        .gursha-card-content h3 a { color: inherit; }

        .gursha-card-content p {
            min-height: 40px;
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
        }

        .gursha-price {
            color: #c97835;
            font-size: 18px;
            font-weight: 850;
        }

        .gursha-small-btn {
            display: inline-block;
            padding: 9px 15px;
            border-radius: 25px;
            background: var(--gursha-green);
            color: #fff !important;
            font-size: 12px;
            font-weight: 750;
        }

        .gursha-small-btn:hover {
            background: var(--gursha-orange);
            color: var(--gursha-green) !important;
        }

        .gursha-restaurant-address {
            min-height: 40px;
            color: var(--gursha-muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .gursha-filter-nav { margin: 5px 0 25px; }

        .gursha-filter-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .gursha-filter-list li { list-style: none; }

        .gursha-filter-list a {
            display: inline-block;
            padding: 8px 15px;
            border: 1px solid #e5e4dc;
            border-radius: 25px;
            color: var(--gursha-green);
            font-size: 12px;
            font-weight: 750;
        }

        .gursha-filter-list a.selected,
        .gursha-filter-list a:hover {
            border-color: var(--gursha-green);
            background: var(--gursha-green);
            color: #fff;
        }

        .gursha-promo {
            padding: 62px 0;
            background: var(--gursha-green);
            color: #fff;
        }

        .gursha-promo h2 {
            margin: 0 0 15px;
            color: #fff;
            font-size: 36px;
            font-weight: 850;
        }

        .gursha-promo p {
            max-width: 650px;
            margin: 0 0 25px;
            color: #dce6de;
            font-size: 15px;
            line-height: 1.8;
        }

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

        .gursha-footer a:hover { color: var(--gursha-orange); }
        .gursha-footer ul { margin: 0; padding: 0; list-style: none; }
        .gursha-footer li { margin-bottom: 7px; }

        .gursha-footer-bottom {
            margin-top: 30px;
            padding-top: 18px;
            border-top: 1px solid rgba(255,255,255,.13);
            color: #aebbb2;
            font-size: 12px;
            text-align: center;
        }

        .gursha-empty {
            padding: 25px;
            border: 1px dashed #d9d4c9;
            border-radius: 12px;
            color: var(--gursha-muted);
            text-align: center;
        }

        @media (max-width: 767px) {
            .gursha-nav-inner { flex-wrap: wrap; }
            .gursha-nav-toggle { display: block; }

            .gursha-nav-collapse {
                flex-basis: 100%;
                width: 100%;
                padding-top: 12px;
            }

            .gursha-nav-list {
                display: block;
                width: 100%;
            }

            .gursha-nav-list li { display: block; }
            .gursha-nav-link { display: block; padding: 10px 0; }
            .gursha-nav-cta { margin: 7px 0; }

            .gursha-hero {
                min-height: 500px;
                background-position: center;
            }

            .gursha-hero h1 { font-size: 42px; }
            .gursha-hero p { font-size: 15px; }
            .gursha-section { padding: 58px 0; }

            .gursha-section-heading h2,
            .gursha-promo h2 {
                font-size: 29px;
            }
        }

        @media (max-width: 420px) {
            .gursha-brand-name { font-size: 20px; }
            .gursha-hero h1 { font-size: 36px; }
        }
    </style>
</head>

<body class="home">

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
        <a class="gursha-nav-link" href="#popular-dishes">Menu</a>
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
    <!-- HERO -->
    <section class="gursha-hero">
        <div class="container">
            <div class="gursha-hero-content">
                <div class="gursha-eyebrow">Welcome to GURSHA</div>
                <h1>Good Food.<br><span>Good Moments.</span></h1>
                <p>Discover delicious meals from restaurants you love. Explore the menu, choose your favorite dishes, and enjoy a better way to order food.</p>

                <!-- Hero button restored to its original text -->
                <a class="gursha-btn-primary" href="restaurants.php">
                    Explore Restaurants <i class="fa fa-arrow-right"></i>
                </a>

                <a class="gursha-btn-secondary" href="#popular-dishes">
                    Discover Food <i class="fa fa-angle-down"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- FOOD CATEGORIES -->
    <section class="gursha-section">
        <div class="container">
            <div class="gursha-section-heading">
                <div class="gursha-section-kicker">Explore the menu</div>
                <h2>What are you craving?</h2>
                <p>Browse our food categories and discover something delicious for your next meal.</p>
            </div>

            <div class="row">
                <?php if (!empty($categories)) { ?>
                    <?php foreach ($categories as $category) { ?>
                        <div class="col-xs-6 col-sm-4 col-md-3">
                            <a href="restaurants.php">
                                <div class="gursha-category-card">
                                    <div class="gursha-category-icon">
                                        <i class="fa fa-cutlery" aria-hidden="true"></i>
                                    </div>
                                    <h3><?php echo gursha_escape($category['c_name']); ?></h3>
                                </div>
                            </a>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="col-xs-12">
                        <div class="gursha-empty">No food categories are available yet.</div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>

    <!-- POPULAR DISHES -->
    <section class="gursha-section gursha-section-white" id="popular-dishes">
        <div class="container">
            <div class="gursha-section-heading">
                <div class="gursha-section-kicker">Customer favorites</div>
                <h2>Popular Dishes</h2>
                <p>Take a look at some of the dishes available from our restaurants.</p>
            </div>

            <div class="row">
                <?php if (!empty($popularDishes)) { ?>
                    <?php foreach ($popularDishes as $dish) {
                        $dishImage = "admin/Res_img/dishes/" . rawurlencode(basename((string)$dish['img']));
                        $dishRestaurantId = urlencode((string)$dish['rs_id']);
                    ?>
                        <div class="col-xs-12 col-sm-6 col-md-4">
                            <article class="gursha-food-card">
                                <img class="gursha-food-image"
                                     src="<?php echo gursha_escape($dishImage); ?>"
                                     alt="<?php echo gursha_escape($dish['title']); ?>"
                                     onerror="this.onerror=null;this.src='images/img/shiro.jpg';">

                                <div class="gursha-card-content">
                                    <h3>
                                        <a href="dishes.php?res_id=<?php echo $dishRestaurantId; ?>">
                                            <?php echo gursha_escape($dish['title']); ?>
                                        </a>
                                    </h3>

                                    <p><?php echo gursha_escape($dish['slogan']); ?></p>

                                    <div class="gursha-card-bottom">
                                        <span class="gursha-price">$<?php echo gursha_escape($dish['price']); ?></span>
                                        <a class="gursha-small-btn" href="dishes.php?res_id=<?php echo $dishRestaurantId; ?>">
                                            Order Now <i class="fa fa-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="col-xs-12">
                        <div class="gursha-empty">No dishes are available yet.</div>
                    </div>
                <?php } ?>
            </div>

            <div class="text-center">
                <a class="gursha-btn-primary" href="restaurants.php">
                    Browse Restaurants <i class="fa fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="gursha-section" style="background:var(--gursha-green); color:#fff;">
        <div class="container">
            <div class="gursha-section-heading">
                <div class="gursha-section-kicker">Simple and convenient</div>
                <h2 style="color:#fff;">Easy to Order</h2>
                <p style="color:#dce6de;">Your next meal is only a few simple steps away.</p>
            </div>

            <div class="row">
                <div class="col-xs-12 col-sm-4">
                    <div class="gursha-category-card">
                        <div class="gursha-category-icon"><i class="fa fa-cutlery"></i></div>
                        <h3>1. Choose a Restaurant</h3>
                        <p style="margin:12px 0 0;color:#747d75;">Explore restaurants and find a place you love.</p>
                    </div>
                </div>

                <div class="col-xs-12 col-sm-4">
                    <div class="gursha-category-card">
                        <div class="gursha-category-icon"><i class="fa fa-shopping-basket"></i></div>
                        <h3>2. Choose Your Food</h3>
                        <p style="margin:12px 0 0;color:#747d75;">Browse the menu and select your favorite dishes.</p>
                    </div>
                </div>

                <div class="col-xs-12 col-sm-4">
                    <div class="gursha-category-card">
                        <div class="gursha-category-icon"><i class="fa fa-truck"></i></div>
                        <h3>3. Enjoy Your Meal</h3>
                        <p style="margin:12px 0 0;color:#747d75;">Place your order and enjoy your food.</p>
                    </div>
                </div>
            </div>

            <p class="text-center" style="margin:25px 0 0;color:#dce6de;">
                Payment: Cash on Delivery
            </p>
        </div>
    </section>

    <!-- FEATURED RESTAURANTS -->
    <section class="gursha-section" id="featured-restaurants">
        <div class="container">
            <div class="gursha-section-heading">
                <div class="gursha-section-kicker">Discover great places</div>
                <h2>Featured Restaurants</h2>
                <p>Find your next favorite restaurant and explore its menu.</p>
            </div>

            <nav class="gursha-filter-nav" aria-label="Filter restaurants">
                <ul class="gursha-filter-list">
                    <li><a href="#" class="selected" data-filter="*">All</a></li>
                    <?php foreach ($categories as $index => $category) { ?>
                        <li>
                            <a href="#" data-filter=".gursha-category-<?php echo (int)$index; ?>">
                                <?php echo gursha_escape($category['c_name']); ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </nav>

            <div class="row gursha-restaurant-grid">
                <?php if (!empty($restaurants)) { ?>
                    <?php foreach ($restaurants as $restaurant) {
                        $restaurantCategoryClass = 'gursha-category-other';

                        foreach ($categories as $index => $category) {
                            if ((string)$category['c_id'] === (string)$restaurant['c_id']) {
                                $restaurantCategoryClass = 'gursha-category-' . (int)$index;
                                break;
                            }
                        }

                        $restaurantImage = "admin/Res_img/" . rawurlencode(basename((string)$restaurant['image']));
                        $restaurantUrl = "dishes.php?res_id=" . urlencode((string)$restaurant['rs_id']);
                    ?>
                        <div class="col-xs-12 col-sm-6 gursha-restaurant-item <?php echo gursha_escape($restaurantCategoryClass); ?>">
                            <article class="gursha-restaurant-card">
                                <a href="<?php echo gursha_escape($restaurantUrl); ?>">
                                    <img class="gursha-restaurant-image"
                                         src="<?php echo gursha_escape($restaurantImage); ?>"
                                         alt="<?php echo gursha_escape($restaurant['title']); ?>"
                                         onerror="this.onerror=null;this.src='images/img/shiro.jpg';">
                                </a>

                                <div class="gursha-card-content">
                                    <h3>
                                        <a href="<?php echo gursha_escape($restaurantUrl); ?>">
                                            <?php echo gursha_escape($restaurant['title']); ?>
                                        </a>
                                    </h3>

                                    <p class="gursha-restaurant-address">
                                        <?php echo gursha_escape($restaurant['address']); ?>
                                    </p>

                                    <div class="gursha-card-bottom">
                                        <span style="color:var(--gursha-muted);font-size:12px;">
                                            <?php echo gursha_escape($restaurant['o_hr']); ?>
                                            -
                                            <?php echo gursha_escape($restaurant['c_hr']); ?>
                                        </span>

                                        <a class="gursha-small-btn" href="<?php echo gursha_escape($restaurantUrl); ?>">
                                            View Menu <i class="fa fa-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="col-xs-12">
                        <div class="gursha-empty">No restaurants are available yet.</div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>

    <!-- CALL TO ACTION -->
    <section class="gursha-promo">
        <div class="container">
            <div class="gursha-eyebrow">Good food is just a click away</div>
            <h2>Your next favorite meal awaits.</h2>
            <p>Explore restaurants, discover delicious dishes, and enjoy a convenient way to order food with GURSHA.</p>
            <a class="gursha-btn-primary" href="restaurants.php">
                Find Your Food <i class="fa fa-arrow-right"></i>
            </a>
        </div>
    </section>
</main>

<!-- FOOTER -->
<footer class="gursha-footer">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-5">
                <div class="gursha-footer-brand">GURSHA</div>
                <p>Good food. Good moments. Discover delicious meals and order from restaurants you love.</p>
            </div>

            <div class="col-xs-6 col-sm-3">
                <h4>Explore</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="restaurants.php">Restaurants</a></li>
                    <li><a href="#popular-dishes">Popular Dishes</a></li>
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
                        <li><a href="javascript:void(0);" onclick="confirmLogout();">Logout</a></li>
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

<!-- Keep the local JavaScript dependencies used by this project. -->
<script src="js/jquery.min.js"></script>
<script src="js/tether.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/animsition.min.js"></script>
<script src="js/bootstrap-slider.min.js"></script>
<script src="js/jquery.isotope.min.js"></script>
<script src="js/headroom.js"></script>
<script src="js/foodpicky.min.js"></script>

<script>
    // Restaurant category filtering.
    $(function () {
        var $grid = $('.gursha-restaurant-grid');

        if ($.fn.isotope && $grid.length) {
            $grid.isotope({
                itemSelector: '.gursha-restaurant-item',
                layoutMode: 'fitRows'
            });

            $('.gursha-filter-list').on('click', 'a', function (event) {
                event.preventDefault();
                $('.gursha-filter-list a').removeClass('selected');
                $(this).addClass('selected');
                $grid.isotope({ filter: $(this).attr('data-filter') });
            });
        }
    });
</script>

</body>
</html>