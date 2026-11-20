
<?php
include("connection/connect.php");

error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();

// Preserve the existing cart add/remove functionality.
include_once 'product-action.php';

function gursha_escape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Validate the restaurant ID.
$res_id = filter_input(INPUT_GET, 'res_id', FILTER_VALIDATE_INT);

if (!$res_id || $res_id < 1) {
    header("Location: restaurants.php");
    exit;
}

// Get restaurant information safely.
$restaurant = null;

$restaurantQuery = "SELECT * FROM restaurant WHERE rs_id = ?";
$restaurantStmt = mysqli_prepare($db, $restaurantQuery);

if ($restaurantStmt) {
    mysqli_stmt_bind_param($restaurantStmt, "i", $res_id);
    mysqli_stmt_execute($restaurantStmt);

    $restaurantResult = mysqli_stmt_get_result($restaurantStmt);
    $restaurant = mysqli_fetch_assoc($restaurantResult);

    mysqli_stmt_close($restaurantStmt);
}

if (!$restaurant) {
    header("Location: restaurants.php");
    exit;
}

// Get dishes belonging to this restaurant.
$dishes = [];

$dishQuery = "SELECT * FROM dishes WHERE rs_id = ?";
$dishStmt = mysqli_prepare($db, $dishQuery);

if ($dishStmt) {
    mysqli_stmt_bind_param($dishStmt, "i", $res_id);
    mysqli_stmt_execute($dishStmt);

    $dishResult = mysqli_stmt_get_result($dishStmt);

    while ($dish = mysqli_fetch_assoc($dishResult)) {
        $dishes[] = $dish;
    }

    mysqli_stmt_close($dishStmt);
}

// Calculate cart total.
$item_total = 0;
$cartItems = $_SESSION["cart_item"] ?? [];

if (!is_array($cartItems)) {
    $cartItems = [];
}

foreach ($cartItems as $item) {
    $price = (float)($item["price"] ?? 0);
    $quantity = (int)($item["quantity"] ?? 0);

    $item_total += ($price * $quantity);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php echo gursha_escape($restaurant['title']); ?> | GURSHA Menu</title>

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

        body.home {
            margin: 0;
            background: var(--gursha-cream);
            color: var(--gursha-ink);
            font-family: "Segoe UI", Arial, sans-serif;
        }

        /* NAVBAR - MATCHING HOMEPAGE */

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

        /* ORDER PROGRESS */

        .gursha-order-progress {
            padding: 19px 0;
            background: #fff;
            border-bottom: 1px solid #eceee9;
        }

        .gursha-progress-list {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .gursha-progress-list li {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0 25px;
            color: var(--gursha-muted);
            font-size: 13px;
            font-weight: 600;
        }

        .gursha-progress-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            flex-shrink: 0;
            border-radius: 50%;
            background: #edf0eb;
            color: var(--gursha-muted);
            font-size: 13px;
            font-weight: 800;
        }

        .gursha-progress-list li.active {
            color: var(--gursha-green);
        }

        .gursha-progress-list li.active .gursha-progress-number {
            background: var(--gursha-orange);
            color: var(--gursha-green);
        }

        /* RESTAURANT PROFILE */

        .gursha-restaurant-hero {
            position: relative;
            padding: 65px 0;
            background:
                linear-gradient(rgba(16,41,31,.84), rgba(16,41,31,.84)),
                url('images/img/kitfo.jpg') center/cover no-repeat;
        }

        .gursha-restaurant-profile {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .gursha-restaurant-image {
            width: 175px;
            height: 145px;
            flex-shrink: 0;
            overflow: hidden;
            border: 4px solid rgba(255,255,255,.9);
            border-radius: 15px;
            background: #fff;
        }

        .gursha-restaurant-image img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .gursha-restaurant-details h1 {
            margin: 0 0 12px;
            color: #fff;
            font-size: 31px;
            font-weight: 850;
        }

        .gursha-restaurant-details p {
            margin: 0;
            color: #e1e9e2;
            font-size: 14px;
            line-height: 1.8;
        }

        .gursha-restaurant-details .restaurant-location {
            margin-top: 10px;
        }

        /* MAIN CONTENT */

        .gursha-menu-section {
            padding: 55px 0 75px;
        }

        .gursha-section-heading {
            margin: 0 0 8px;
            color: var(--gursha-green);
            font-size: 25px;
            font-weight: 850;
        }

        .gursha-section-description {
            margin: 0 0 28px;
            color: var(--gursha-muted);
            font-size: 13px;
            line-height: 1.7;
        }

        /* CART */

        .gursha-cart {
            position: sticky;
            top: 100px;
            overflow: hidden;
            border: 1px solid #e9e9e3;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 8px 28px rgba(25,45,32,.06);
        }

        .gursha-cart-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            border-bottom: 1px solid #eceee9;
        }

        .gursha-cart-heading h3 {
            margin: 0;
            color: var(--gursha-green);
            font-size: 19px;
            font-weight: 850;
        }

        .gursha-cart-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
            height: 28px;
            padding: 0 8px;
            border-radius: 20px;
            background: #f8eadb;
            color: var(--gursha-green);
            font-size: 12px;
            font-weight: 800;
        }

        .gursha-cart-items {
            padding: 8px 20px;
        }

        .gursha-cart-item {
            padding: 14px 0;
            border-bottom: 1px solid #f0f1ed;
        }

        .gursha-cart-item:last-child {
            border-bottom: 0;
        }

        .gursha-cart-item-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 10px;
            color: var(--gursha-ink);
            font-size: 13px;
            font-weight: 700;
        }

        .gursha-cart-item-title a {
            color: #bd5146;
            font-size: 15px;
        }

        .gursha-cart-item-title a:hover {
            color: #8f2d25;
        }

        .gursha-cart-item-info {
            display: flex;
            justify-content: space-between;
            color: var(--gursha-muted);
            font-size: 12px;
        }

        .gursha-cart-empty {
            padding: 30px 15px;
            color: var(--gursha-muted);
            font-size: 13px;
            text-align: center;
        }

        .gursha-cart-empty i {
            display: block;
            margin-bottom: 12px;
            color: #c5cec6;
            font-size: 30px;
        }

        .gursha-cart-summary {
            padding: 20px;
            background: #f8f8f4;
            border-top: 1px solid #eceee9;
        }

        .gursha-cart-total-label {
            margin: 0 0 5px;
            color: var(--gursha-muted);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .gursha-cart-total {
            margin: 0 0 8px;
            color: var(--gursha-green);
            font-size: 27px;
            font-weight: 900;
        }

        .gursha-cart-delivery {
            margin: 0 0 18px;
            color: var(--gursha-muted);
            font-size: 12px;
        }

        .gursha-checkout-button {
            display: block;
            width: 100%;
            padding: 14px 18px;
            border: 0;
            border-radius: 30px;
            background: var(--gursha-orange);
            color: var(--gursha-green) !important;
            font-size: 14px;
            font-weight: 850;
            text-align: center;
            text-decoration: none !important;
            transition: .2s ease;
        }

        .gursha-checkout-button:hover {
            background: #ffc17d;
            transform: translateY(-2px);
        }

        .gursha-checkout-button.disabled {
            background: #d8ddd7;
            color: #89928a !important;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* MENU */

        .gursha-menu-container {
            padding-left: 15px;
        }

        .gursha-menu-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
        }

        .gursha-menu-heading h2 {
            margin: 0;
            color: var(--gursha-green);
            font-size: 25px;
            font-weight: 850;
        }

        .gursha-menu-heading p {
            margin: 5px 0 0;
            color: var(--gursha-muted);
            font-size: 13px;
        }

        .gursha-food-card {
            display: flex;
            gap: 20px;
            margin-bottom: 18px;
            padding: 20px;
            border: 1px solid #e9e9e3;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 5px 20px rgba(25,45,32,.035);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .gursha-food-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(25,45,32,.08);
        }

        .gursha-food-image {
            width: 125px;
            height: 115px;
            flex-shrink: 0;
            overflow: hidden;
            border-radius: 11px;
            background: #f1f1ec;
        }

        .gursha-food-image img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .gursha-food-details {
            display: flex;
            flex: 1;
            min-width: 0;
            flex-direction: column;
            justify-content: center;
        }

        .gursha-food-details h3 {
            margin: 0 0 8px;
            color: var(--gursha-green);
            font-size: 17px;
            font-weight: 800;
        }

        .gursha-food-details p {
            margin: 0;
            color: var(--gursha-muted);
            font-size: 13px;
            line-height: 1.7;
        }

        .gursha-food-action {
            display: flex;
            min-width: 145px;
            flex-direction: column;
            align-items: flex-end;
            justify-content: center;
            gap: 10px;
        }

        .gursha-food-price {
            color: var(--gursha-green);
            font-size: 20px;
            font-weight: 900;
        }

        .gursha-food-quantity {
            width: 75px;
            height: 37px;
            padding: 5px 8px;
            border: 1px solid #e0e5df;
            border-radius: 7px;
            color: var(--gursha-ink);
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 13px;
        }

        .gursha-add-button {
            padding: 10px 17px;
            border: 0;
            border-radius: 25px;
            background: var(--gursha-green);
            color: #fff;
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            transition: .2s ease;
        }

        .gursha-add-button:hover {
            background: var(--gursha-orange);
            color: var(--gursha-green);
        }

        .gursha-empty-menu {
            padding: 45px 20px;
            border: 1px dashed #d8ded6;
            border-radius: 14px;
            background: #fff;
            color: var(--gursha-muted);
            text-align: center;
        }

        .gursha-empty-menu i {
            display: block;
            margin-bottom: 14px;
            color: #c5cec6;
            font-size: 35px;
        }

        .gursha-empty-menu h3 {
            margin: 0 0 8px;
            color: var(--gursha-green);
            font-size: 18px;
            font-weight: 800;
        }

        .gursha-empty-menu p {
            margin: 0;
            font-size: 13px;
        }

        /* FOOTER - MATCHING HOMEPAGE */

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
            text-decoration: none;
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

        /* RESPONSIVE */

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

            .gursha-progress-list {
                justify-content: space-between;
            }

            .gursha-progress-list li {
                flex: 1;
                flex-direction: column;
                gap: 6px;
                padding: 0 5px;
                font-size: 10px;
                text-align: center;
            }

            .gursha-restaurant-hero {
                padding: 40px 0;
            }

            .gursha-restaurant-profile {
                flex-direction: column;
                align-items: flex-start;
                gap: 18px;
            }

            .gursha-restaurant-image {
                width: 125px;
                height: 110px;
            }

            .gursha-restaurant-details h1 {
                font-size: 25px;
            }

            .gursha-menu-section {
                padding: 35px 0 50px;
            }

            .gursha-cart {
                position: static;
                margin-bottom: 35px;
            }

            .gursha-menu-container {
                padding-left: 15px;
                padding-right: 15px;
            }

            .gursha-food-card {
                flex-wrap: wrap;
                gap: 14px;
                padding: 15px;
            }

            .gursha-food-image {
                width: 95px;
                height: 95px;
            }

            .gursha-food-details {
                flex: 1;
            }

            .gursha-food-details h3 {
                font-size: 15px;
            }

            .gursha-food-action {
                width: 100%;
                min-width: 0;
                flex-direction: row;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                padding-top: 12px;
                border-top: 1px solid #f0f1ed;
            }

            .gursha-footer [class*="col-"] {
                margin-bottom: 25px;
            }
        }

        @media (max-width: 380px) {
            .gursha-food-image {
                width: 80px;
                height: 85px;
            }

            .gursha-food-details h3 {
                font-size: 14px;
            }

            .gursha-food-details p {
                font-size: 12px;
            }
        }
    </style>
</head>

<body class="home">

    <!-- NAVBAR -->
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

                <div class="collapse navbar-toggleable-md gursha-nav-collapse"
                     id="mainNavbarCollapse">

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
                            <li>
                                <a class="gursha-nav-link" href="registration.php">Register</a>
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

    <!-- ORDER PROGRESS -->
    <section class="gursha-order-progress">
        <div class="container">

            <ul class="gursha-progress-list">

                <li>
                    <span class="gursha-progress-number">1</span>
                    <span>Choose Restaurant</span>
                </li>

                <li class="active">
                    <span class="gursha-progress-number">2</span>
                    <span>Pick Your Favorite Food</span>
                </li>

                <li>
                    <span class="gursha-progress-number">3</span>
                    <span>Order and Pay</span>
                </li>

            </ul>

        </div>
    </section>

    <!-- RESTAURANT PROFILE -->
    <section class="gursha-restaurant-hero">
        <div class="container">

            <div class="gursha-restaurant-profile">

                <div class="gursha-restaurant-image">
                    <img
                        src="admin/Res_img/<?php echo rawurlencode($restaurant['image']); ?>"
                        alt="<?php echo gursha_escape($restaurant['title']); ?>">
                </div>

                <div class="gursha-restaurant-details">

                    <h1><?php echo gursha_escape($restaurant['title']); ?></h1>

                    <p>
                        <i class="fa fa-map-marker"></i>
                        <?php echo gursha_escape($restaurant['address']); ?>
                    </p>

                    <p class="restaurant-location">
                        <i class="fa fa-cutlery"></i>
                        Explore the menu and order your favorite dishes.
                    </p>

                </div>

            </div>

        </div>
    </section>

    <!-- MENU AND CART -->
    <main class="gursha-menu-section">

        <div class="container">

            <div class="row">

                <!-- CART SIDEBAR -->
                <div class="col-xs-12 col-sm-4 col-md-4 col-lg-3">

                    <aside class="gursha-cart">

                        <div class="gursha-cart-heading">

                            <h3>
                                <i class="fa fa-shopping-basket"></i>
                                Your Cart
                            </h3>

                            <span class="gursha-cart-count">
                                <?php
                                $cartCount = 0;

                                foreach ($cartItems as $cartItem) {
                                    $cartCount += (int)($cartItem['quantity'] ?? 0);
                                }

                                echo $cartCount;
                                ?>
                            </span>

                        </div>

                        <div class="gursha-cart-items">

                            <?php if (empty($cartItems)) { ?>

                                <div class="gursha-cart-empty">
                                    <i class="fa fa-shopping-basket"></i>
                                    <p>Your cart is empty.</p>
                                    <p>Add delicious food from the menu!</p>
                                </div>

                            <?php } else { ?>

                                <?php foreach ($cartItems as $item) { ?>

                                    <div class="gursha-cart-item">

                                        <div class="gursha-cart-item-title">

                                            <span>
                                                <?php echo gursha_escape($item["title"] ?? 'Food item'); ?>
                                            </span>

                                            <a
                                                href="dishes.php?res_id=<?php echo $res_id; ?>&action=remove&id=<?php echo (int)($item["d_id"] ?? 0); ?>"
                                                title="Remove item"
                                                aria-label="Remove <?php echo gursha_escape($item["title"] ?? 'item'); ?>">

                                                <i class="fa fa-trash"></i>
                                            </a>

                                        </div>

                                        <div class="gursha-cart-item-info">

                                            <span>
                                                $<?php echo number_format((float)($item["price"] ?? 0), 2); ?>
                                            </span>

                                            <span>
                                                Quantity:
                                                <?php echo (int)($item["quantity"] ?? 0); ?>
                                            </span>

                                        </div>

                                    </div>

                                <?php } ?>

                            <?php } ?>

                        </div>

                        <div class="gursha-cart-summary">

                            <p class="gursha-cart-total-label">ORDER TOTAL</p>

                            <h3 class="gursha-cart-total">
                                $<?php echo number_format($item_total, 2); ?>
                            </h3>

                            <p class="gursha-cart-delivery">
                                <i class="fa fa-truck"></i>
                                Free Delivery!
                            </p>

                            <?php if ($item_total <= 0) { ?>

                                <a
                                    href="checkout.php?res_id=<?php echo $res_id; ?>&action=check"
                                    class="gursha-checkout-button disabled"
                                    aria-disabled="true">
                                    Checkout <i class="fa fa-arrow-right"></i>
                                </a>

                            <?php } else { ?>

                                <a
                                    href="checkout.php?res_id=<?php echo $res_id; ?>&action=check"
                                    class="gursha-checkout-button">
                                    Proceed to Checkout
                                    <i class="fa fa-arrow-right"></i>
                                </a>

                            <?php } ?>

                        </div>

                    </aside>

                </div>

                <!-- FOOD MENU -->
                <div class="col-xs-12 col-sm-8 col-md-8 col-lg-9">

                    <div class="gursha-menu-container">

                        <div class="gursha-menu-heading">

                            <div>
                                <h2>Our Menu</h2>
                                <p>Choose your favorite dishes and add them to your cart.</p>
                            </div>

                        </div>

                        <?php if (!empty($dishes)) { ?>

                            <?php foreach ($dishes as $product) { ?>

                                <article class="gursha-food-card">

                                    <div class="gursha-food-image">

                                        <img
                                            src="admin/Res_img/dishes/<?php echo rawurlencode($product['img']); ?>"
                                            alt="<?php echo gursha_escape($product['title']); ?>">

                                    </div>

                                    <div class="gursha-food-details">

                                        <h3>
                                            <?php echo gursha_escape($product['title']); ?>
                                        </h3>

                                        <p>
                                            <?php echo gursha_escape($product['slogan']); ?>
                                        </p>

                                    </div>

                                    <form
                                        method="post"
                                        action="dishes.php?res_id=<?php echo $res_id; ?>&action=add&id=<?php echo (int)$product['d_id']; ?>"
                                        class="gursha-food-action">

                                        <span class="gursha-food-price">
                                            $<?php echo number_format((float)$product['price'], 2); ?>
                                        </span>

                                        <input
                                            type="number"
                                            name="quantity"
                                            class="gursha-food-quantity"
                                            value="1"
                                            min="1"
                                            max="99"
                                            required
                                            aria-label="Quantity for <?php echo gursha_escape($product['title']); ?>">

                                        <button type="submit" class="gursha-add-button">
                                            <i class="fa fa-plus"></i>
                                            Add To Cart
                                        </button>

                                    </form>

                                </article>

                            <?php } ?>

                        <?php } else { ?>

                            <div class="gursha-empty-menu">

                                <i class="fa fa-cutlery"></i>

                                <h3>No Dishes Available</h3>

                                <p>
                                    This restaurant has not added any dishes to its menu yet.
                                    Please check back later.
                                </p>

                            </div>

                        <?php } ?>

                    </div>

                </div>

            </div>

        </div>

    </main>

    <!-- FOOTER -->
    <footer class="gursha-footer">
        <div class="container">

            <div class="row">

                <div class="col-md-4">
                    <h3 class="gursha-footer-brand">GURSHA</h3>

                    <p>
                        Good food. Good moments. Discover delicious meals
                        from your favorite restaurants and enjoy every bite.
                    </p>
                </div>

                <div class="col-md-4">
                    <h4>Explore</h4>

                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="restaurants.php">Restaurants</a></li>
                        <li><a href="index.php#popular-dishes">Popular Dishes</a></li>
                    </ul>
                </div>

                <div class="col-md-4">
                    <h4>Your Account</h4>

                    <ul>
                        <?php if (empty($_SESSION["user_id"])) { ?>
                            <li><a href="login.php">Login</a></li>
                            <li><a href="registration.php">Create Account</a></li>
                        <?php } else { ?>
                            <li><a href="your_orders.php">My Orders</a></li>
                            <li><a href="logout.php">Logout</a></li>
                        <?php } ?>
                    </ul>
                </div>

            </div>

            <div class="gursha-footer-bottom">
                &copy; <?php echo date("Y"); ?> GURSHA. All Rights Reserved.
            </div>

        </div>
    </footer>

    <!-- SCRIPTS -->
    <script src="js/jquery.min.js"></script>
    <script src="js/tether.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/animsition.min.js"></script>
    <script src="js/bootstrap-slider.min.js"></script>
    <script src="js/jquery.isotope.min.js"></script>
    <script src="js/headroom.js"></script>
    <script src="js/foodpicky.min.js"></script>

    <script>
        function confirmLogout() {
            if (confirm('Are you sure you want to log out?')) {
                window.location.href = 'logout.php';
            }
        }
    </script>

</body>
</html>
