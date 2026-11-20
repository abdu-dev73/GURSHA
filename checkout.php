
<?php
include("connection/connect.php");

error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();

if (empty($_SESSION["user_id"])) {
    header("Location: login.php?redirect=checkout.php");
    exit;
}

$cartItems = $_SESSION["cart_item"] ?? [];

if (!is_array($cartItems)) {
    $cartItems = [];
}

$item_total = 0;
$message = "";
$orderPlaced = false;

// Calculate cart total.
foreach ($cartItems as $item) {
    $price = (float)($item["price"] ?? 0);
    $quantity = (int)($item["quantity"] ?? 0);

    if ($price >= 0 && $quantity > 0) {
        $item_total += $price * $quantity;
    }
}

// Process order submission.
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit"])) {

    if (empty($cartItems)) {
        $message = "Your cart is empty. Please add dishes before placing an order.";
    } else {
        $userId = (int)$_SESSION["user_id"];

        $SQL = "INSERT INTO users_orders (u_id, title, quantity, price)
                VALUES (?, ?, ?, ?)";

        $stmt = mysqli_prepare($db, $SQL);

        if ($stmt) {
            $orderSuccess = true;

            mysqli_begin_transaction($db);

            foreach ($cartItems as $item) {
                $title = (string)($item["title"] ?? "");
                $quantity = (int)($item["quantity"] ?? 0);
                $price = (float)($item["price"] ?? 0);

                if ($title === "" || $quantity < 1 || $price < 0) {
                    $orderSuccess = false;
                    break;
                }

                mysqli_stmt_bind_param(
                    $stmt,
                    "isid",
                    $userId,
                    $title,
                    $quantity,
                    $price
                );

                if (!mysqli_stmt_execute($stmt)) {
                    $orderSuccess = false;
                    break;
                }
            }

            mysqli_stmt_close($stmt);

            if ($orderSuccess) {
                mysqli_commit($db);

                unset($_SESSION["cart_item"]);

                $orderPlaced = true;
            } else {
                mysqli_rollback($db);
                $message = "We could not place your order. Please try again.";
            }
        } else {
            $message = "Something went wrong. Please try again.";
        }
    }
}

function gursha_escape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Checkout | GURSHA</title>

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

        /* =========================
           NAVBAR
        ========================= */

        .gursha-header {
            position: sticky;
            top: 0;
            z-index: 1000;
            width: 100%;
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

        /* =========================
           CHECKOUT HEADER
        ========================= */

        .checkout-section {
            min-height: 600px;
            padding: 65px 15px 85px;
        }

        .checkout-heading {
            margin: 0 0 10px;
            color: var(--gursha-green);
            font-size: 34px;
            font-weight: 850;
        }

        .checkout-description {
            margin-bottom: 35px;
            color: var(--gursha-muted);
            font-size: 15px;
        }

        .checkout-progress {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin: 0 0 38px;
            padding: 0;
            list-style: none;
        }

        .checkout-progress li {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 11px 17px;
            border-radius: 30px;
            background: #fff;
            color: var(--gursha-muted);
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 3px 12px rgba(0,0,0,.035);
        }

        .checkout-progress li.active {
            background: var(--gursha-green);
            color: #fff;
        }

        .checkout-progress li i {
            font-size: 14px;
        }

        /* =========================
           CHECKOUT CARD
        ========================= */

        .checkout-card {
            max-width: 850px;
            margin: 0 auto;
            padding: 35px;
            border: 1px solid rgba(23,59,45,.06);
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 12px 40px rgba(0,0,0,.055);
        }

        .checkout-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 25px;
        }

        .checkout-card h3 {
            margin: 0;
            color: var(--gursha-green);
            font-size: 21px;
            font-weight: 800;
        }

        .checkout-item-count {
            padding: 7px 13px;
            border-radius: 30px;
            background: #f2f6f1;
            color: var(--gursha-green);
            font-size: 12px;
            font-weight: 750;
            white-space: nowrap;
        }

        /* =========================
           ORDER ITEMS
        ========================= */

        .checkout-items {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }

        .checkout-items thead th {
            padding: 13px 10px;
            border-bottom: 1px solid #e9eee8;
            color: var(--gursha-muted);
            font-size: 12px;
            font-weight: 750;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .checkout-items tbody td {
            padding: 17px 10px;
            border-bottom: 1px solid #edf0eb;
            color: var(--gursha-ink);
            font-size: 14px;
            vertical-align: middle;
        }

        .checkout-items tbody tr:last-child td {
            border-bottom: 0;
        }

        .checkout-item-name {
            color: var(--gursha-green);
            font-weight: 750;
        }

        .checkout-item-price {
            color: var(--gursha-muted);
            font-size: 13px;
        }

        .checkout-quantity {
            display: inline-block;
            min-width: 34px;
            padding: 5px 9px;
            border-radius: 7px;
            background: #f2f6f1;
            color: var(--gursha-green);
            font-weight: 750;
            text-align: center;
        }

        .checkout-line-total {
            color: var(--gursha-green);
            font-weight: 800;
            white-space: nowrap;
        }

        /* =========================
           PRICE SUMMARY
        ========================= */

        .checkout-summary {
            margin-top: 10px;
            padding: 22px;
            border-radius: 12px;
            background: #fafcf9;
        }

        .checkout-summary-row {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 9px 0;
            color: var(--gursha-muted);
            font-size: 14px;
        }

        .checkout-summary-row strong {
            color: var(--gursha-ink);
            font-weight: 750;
        }

        .checkout-summary-total {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-top: 10px;
            padding-top: 17px;
            border-top: 1px solid #e2e9e0;
            color: var(--gursha-green);
            font-size: 17px;
            font-weight: 850;
        }

        .checkout-summary-total span:last-child {
            font-size: 22px;
        }

        /* =========================
           PAYMENT
        ========================= */

        .checkout-payment {
            margin-top: 30px;
        }

        .checkout-payment-heading {
            margin-bottom: 13px !important;
        }

        .payment-option {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 19px;
            border: 1px solid #dfe8dd;
            border-radius: 12px;
            background: #fafcf9;
            transition: .2s ease;
            cursor: pointer;
        }

        .payment-option:hover {
            border-color: var(--gursha-orange);
            background: #fffaf4;
        }

        .payment-option input {
            width: 17px;
            height: 17px;
            margin: 0;
            accent-color: var(--gursha-green);
            cursor: pointer;
        }

        .payment-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 43px;
            height: 43px;
            border-radius: 10px;
            background: #edf3ed;
            color: var(--gursha-green);
            font-size: 19px;
            flex-shrink: 0;
        }

        .payment-details {
            flex: 1;
        }

        .payment-details strong {
            display: block;
            margin-bottom: 3px;
            color: var(--gursha-green);
            font-size: 14px;
            font-weight: 800;
        }

        .payment-details small {
            color: var(--gursha-muted);
            font-size: 12px;
        }

        .payment-badge {
            padding: 6px 10px;
            border-radius: 20px;
            background: #edf3ed;
            color: var(--gursha-green);
            font-size: 11px;
            font-weight: 750;
        }

        /* =========================
           CHECKOUT BUTTON
        ========================= */

        .checkout-submit {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            margin-top: 25px;
            padding: 16px 20px;
            border: 0;
            border-radius: 30px;
            background: var(--gursha-orange);
            color: var(--gursha-green);
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 15px;
            font-weight: 850;
            cursor: pointer;
            transition: .2s ease;
        }

        .checkout-submit:hover {
            background: #ffc17d;
            transform: translateY(-2px);
            box-shadow: 0 7px 18px rgba(232,144,69,.18);
        }

        .checkout-secure-note {
            margin: 15px 0 0;
            color: var(--gursha-muted);
            font-size: 12px;
            text-align: center;
        }

        .checkout-secure-note i {
            margin-right: 5px;
            color: var(--gursha-green);
        }

        /* =========================
           MESSAGES & EMPTY STATE
        ========================= */

        .checkout-message {
            margin-bottom: 22px;
            padding: 14px 17px;
            border: 1px solid #f2cccc;
            border-radius: 10px;
            background: #fff5f5;
            color: #b42318;
            font-size: 14px;
        }

        .checkout-empty {
            padding: 35px 15px;
            text-align: center;
        }

        .checkout-success-icon,
        .checkout-empty-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 85px;
            height: 85px;
            margin: 0 auto 22px;
            border-radius: 50%;
            font-size: 37px;
        }

        .checkout-success-icon {
            background: #edf7ed;
            color: var(--gursha-green);
        }

        .checkout-empty-icon {
            background: #fff3e7;
            color: var(--gursha-orange);
        }

        .checkout-empty h3 {
            margin-bottom: 10px;
            color: var(--gursha-green);
            font-size: 24px;
        }

        .checkout-empty p {
            max-width: 420px;
            margin: 0 auto 20px;
            color: var(--gursha-muted);
            font-size: 14px;
            line-height: 1.8;
        }

        .checkout-back-link {
            display: inline-block;
            margin-top: 5px;
            padding: 13px 24px;
            border-radius: 30px;
            background: var(--gursha-green);
            color: #fff !important;
            font-size: 14px;
            font-weight: 750;
            transition: .2s ease;
        }

        .checkout-back-link:hover {
            background: var(--gursha-green-dark);
            text-decoration: none;
            transform: translateY(-2px);
        }

        /* =========================
           FOOTER
        ========================= */

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

        /* =========================
           RESPONSIVE
        ========================= */

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

            .checkout-section {
                padding: 40px 12px 55px;
            }

            .checkout-heading {
                font-size: 28px;
            }

            .checkout-progress {
                flex-wrap: wrap;
                gap: 8px;
                margin-bottom: 25px;
            }

            .checkout-progress li {
                padding: 9px 12px;
                font-size: 11px;
            }

            .checkout-card {
                padding: 23px 17px;
            }

            .checkout-card h3 {
                font-size: 19px;
            }

            .checkout-items thead th {
                padding: 11px 5px;
                font-size: 10px;
            }

            .checkout-items tbody td {
                padding: 14px 5px;
                font-size: 12px;
            }

            .checkout-summary {
                padding: 17px;
            }

            .payment-option {
                padding: 14px;
                gap: 10px;
            }

            .payment-badge {
                display: none;
            }

            .gursha-footer [class*="col-"] {
                margin-bottom: 25px;
            }
        }

        @media (max-width: 380px) {
            .checkout-items thead th,
            .checkout-items tbody td {
                padding-left: 3px;
                padding-right: 3px;
                font-size: 11px;
            }

            .checkout-progress li {
                font-size: 10px;
            }
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

            <div class="collapse navbar-toggleable-md gursha-nav-collapse"
                 id="mainNavbarCollapse">

                <ul class="gursha-nav-list">
                    <li><a class="gursha-nav-link" href="index.php">Home</a></li>
                    <li><a class="gursha-nav-link" href="restaurants.php">Restaurants</a></li>
                    <li><a class="gursha-nav-link" href="index.php#popular-dishes">Menu</a></li>
                    <li><a class="gursha-nav-link" href="your_orders.php">My Orders</a></li>

                    <li>
                        <a class="gursha-nav-link" href="javascript:void(0);"
                           onclick="confirmLogout();">Logout</a>
                    </li>

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

<main class="checkout-section">

    <div class="container">

        <div class="text-center">
            <h1 class="checkout-heading">Complete Your Order</h1>
            <p class="checkout-description">
                You're just one step away from enjoying something delicious.
            </p>
        </div>

        <ul class="checkout-progress">
            <li>
                <i class="fa fa-check-circle"></i>
                1. Choose Restaurant
            </li>

            <li>
                <i class="fa fa-check-circle"></i>
                2. Pick Your Food
            </li>

            <li class="active">
                <i class="fa fa-shopping-bag"></i>
                3. Checkout
            </li>
        </ul>

        <div class="checkout-card">

            <?php if ($orderPlaced) { ?>

                <div class="checkout-empty">

                    <div class="checkout-success-icon">
                        <i class="fa fa-check"></i>
                    </div>

                    <h3>Thank You for Your Order!</h3>

                    <p>
                        Your order has been placed successfully.
                        Get ready to enjoy delicious food from GURSHA!
                    </p>

                    <a href="your_orders.php" class="checkout-back-link">
                        View My Orders
                        <i class="fa fa-arrow-right"></i>
                    </a>

                </div>

            <?php } else { ?>

                <?php if (!empty($message)) { ?>
                    <div class="checkout-message" role="alert">
                        <i class="fa fa-exclamation-circle"></i>
                        <?php echo gursha_escape($message); ?>
                    </div>
                <?php } ?>

                <?php if (!empty($cartItems)) { ?>

                    <div class="checkout-card-header">
                        <h3>
                            <i class="fa fa-shopping-basket"></i>
                            Your Order
                        </h3>

                        <span class="checkout-item-count">
                            <?php echo count($cartItems); ?>
                            <?php echo count($cartItems) === 1 ? "Item" : "Items"; ?>
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="checkout-items">

                            <thead>
                                <tr>
                                    <th>Dish</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($cartItems as $item) {
                                    $title = $item["title"] ?? "Food Item";
                                    $price = (float)($item["price"] ?? 0);
                                    $quantity = (int)($item["quantity"] ?? 0);
                                    $lineTotal = $price * $quantity;
                                ?>
                                    <tr>
                                        <td>
                                            <div class="checkout-item-name">
                                                <?php echo gursha_escape($title); ?>
                                            </div>

                                            <div class="checkout-item-price">
                                                $<?php echo number_format($price, 2); ?> each
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            <span class="checkout-quantity">
                                                <?php echo $quantity; ?>
                                            </span>
                                        </td>

                                        <td class="text-right checkout-line-total">
                                            $<?php echo number_format($lineTotal, 2); ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>

                        </table>
                    </div>

                    <div class="checkout-summary">

                        <div class="checkout-summary-row">
                            <span>Cart Subtotal</span>
                            <strong>
                                $<?php echo number_format($item_total, 2); ?>
                            </strong>
                        </div>

                        <div class="checkout-summary-row">
                            <span>Delivery Charges</span>
                            <strong>Free</strong>
                        </div>

                        <div class="checkout-summary-total">
                            <span>Total Amount</span>
                            <span>
                                $<?php echo number_format($item_total, 2); ?>
                            </span>
                        </div>

                    </div>

                    <form action="checkout.php" method="post">

                        <div class="checkout-payment">

                            <h3 class="checkout-payment-heading">
                                <i class="fa fa-credit-card"></i>
                                Payment Method
                            </h3>

                            <label class="payment-option">

                                <input type="radio"
                                       name="mod"
                                       value="COD"
                                       checked>

                                <span class="payment-icon">
                                    <i class="fa fa-money"></i>
                                </span>

                                <span class="payment-details">
                                    <strong>Cash on Delivery</strong>
                                    <small>Pay when your food arrives.</small>
                                </span>

                                <span class="payment-badge">
                                    Available
                                </span>

                            </label>

                        </div>

                        <button type="submit"
                                name="submit"
                                value="Order Now"
                                class="checkout-submit"
                                onclick="return confirm('Do you want to confirm the order?');">

                            Confirm Your Order
                            <i class="fa fa-arrow-right"></i>

                        </button>

                        <p class="checkout-secure-note">
                            <i class="fa fa-lock"></i>
                            Review your order before confirming.
                        </p>

                    </form>

                <?php } else { ?>

                    <div class="checkout-empty">

                        <div class="checkout-empty-icon">
                            <i class="fa fa-shopping-basket"></i>
                        </div>

                        <h3>Your Cart Is Empty</h3>

                        <p>
                            Looks like you haven't added anything to your cart yet.
                            Explore our restaurants and discover something delicious!
                        </p>

                        <a href="restaurants.php" class="checkout-back-link">
                            Browse Restaurants
                            <i class="fa fa-arrow-right"></i>
                        </a>

                    </div>

                <?php } ?>

            <?php } ?>

        </div>
    </div>
</main>

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
                    <li><a href="your_orders.php">My Orders</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>

        </div>

        <div class="gursha-footer-bottom">
            &copy; <?php echo date("Y"); ?> GURSHA. All Rights Reserved.
        </div>

    </div>
</footer>

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
