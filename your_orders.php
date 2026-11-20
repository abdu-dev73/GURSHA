
<?php
include("connection/connect.php");

error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();

if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];

function gursha_escape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Fetch only the current user's orders.
$orders = [];
$queryError = false;

$query = "SELECT * FROM users_orders WHERE u_id = ? ORDER BY o_id DESC";
$stmt = mysqli_prepare($db, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $userId);

    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $orders[] = $row;
            }
        } else {
            $queryError = true;
        }
    } else {
        $queryError = true;
    }

    mysqli_stmt_close($stmt);
} else {
    $queryError = true;
}

function getOrderStatus($status) {
    $status = strtolower(trim((string)$status));

    if ($status === '' || $status === 'null') {
        return [
            'label' => 'Pending',
            'class' => 'status-dispatch',
            'icon' => 'fa-clock-o'
        ];
    }

    if ($status === 'in process') {
        return [
            'label' => 'On The Way',
            'class' => 'status-process',
            'icon' => 'fa-truck'
        ];
    }

    if ($status === 'closed') {
        return [
            'label' => 'Delivered',
            'class' => 'status-delivered',
            'icon' => 'fa-check-circle'
        ];
    }

    if ($status === 'rejected') {
        return [
            'label' => 'Cancelled',
            'class' => 'status-cancelled',
            'icon' => 'fa-times-circle'
        ];
    }

    return [
        'label' => ucfirst($status),
        'class' => 'status-dispatch',
        'icon' => 'fa-info-circle'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>My Orders | GURSHA</title>

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
            text-decoration: none;
        }

        /* =========================
           PAGE HEADER
        ========================= */

        .orders-section {
            min-height: 650px;
            padding: 65px 0 85px;
        }

        .orders-heading {
            margin: 0 0 10px;
            color: var(--gursha-green);
            font-size: 34px;
            font-weight: 850;
        }

        .orders-description {
            margin: 0 0 38px;
            color: var(--gursha-muted);
            font-size: 15px;
            line-height: 1.7;
        }

        .orders-heading-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            margin-bottom: 15px;
            border-radius: 13px;
            background: #edf3ed;
            color: var(--gursha-green);
            font-size: 21px;
        }

        /* =========================
           ORDERS CARD
        ========================= */

        .orders-card {
            padding: 30px;
            border: 1px solid rgba(23,59,45,.05);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 10px 35px rgba(0,0,0,.06);
        }

        .orders-card-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 25px;
        }

        .orders-card-heading h3 {
            margin: 0;
            color: var(--gursha-green);
            font-size: 21px;
            font-weight: 800;
        }

        .orders-count {
            padding: 8px 14px;
            border-radius: 30px;
            background: #f4eee5;
            color: var(--gursha-green);
            font-size: 12px;
            font-weight: 800;
        }

        .orders-card-subtitle {
            margin-top: 6px;
            color: var(--gursha-muted);
            font-size: 13px;
        }

        /* =========================
           ORDERS TABLE
        ========================= */

        .orders-table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .orders-table {
            width: 100%;
            min-width: 700px;
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .orders-table thead th {
            padding: 16px 14px;
            border: none;
            background: var(--gursha-green);
            color: #fff;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .5px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .orders-table thead th:first-child {
            border-radius: 9px 0 0 0;
        }

        .orders-table thead th:last-child {
            border-radius: 0 9px 0 0;
        }

        .orders-table tbody td {
            padding: 19px 14px;
            border-bottom: 1px solid #edf0eb;
            color: var(--gursha-ink);
            font-size: 13px;
            vertical-align: middle;
        }

        .orders-table tbody tr {
            transition: background .2s ease;
        }

        .orders-table tbody tr:hover {
            background: #fafcf9;
        }

        .orders-table tbody tr:last-child td {
            border-bottom: none;
        }

        .order-item-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .order-item-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 39px;
            height: 39px;
            flex-shrink: 0;
            border-radius: 10px;
            background: #fff3e7;
            color: var(--gursha-orange);
            font-size: 17px;
        }

        .order-item-name {
            color: var(--gursha-green);
            font-size: 13px;
            font-weight: 750;
        }

        .order-item-reference {
            display: block;
            margin-top: 4px;
            color: var(--gursha-muted);
            font-size: 11px;
        }

        .order-quantity {
            display: inline-block;
            min-width: 32px;
            padding: 6px 9px;
            border-radius: 7px;
            background: #f2f6f1;
            color: var(--gursha-green);
            font-weight: 800;
            text-align: center;
        }

        .order-price {
            color: var(--gursha-green);
            font-weight: 800;
            white-space: nowrap;
        }

        .order-date {
            color: var(--gursha-muted);
            font-size: 12px;
            white-space: nowrap;
        }

        /* =========================
           STATUS
        ========================= */

        .order-status {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 12px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
        }

        .status-dispatch {
            background: #edf3f8;
            color: #35617d;
        }

        .status-process {
            background: #fff4df;
            color: #a66a12;
        }

        .status-delivered {
            background: #e7f5ec;
            color: #247345;
        }

        .status-cancelled {
            background: #fdeaea;
            color: #b42318;
        }

        /* =========================
           ACTION
        ========================= */

        .order-delete {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border: 0;
            border-radius: 9px;
            background: #fff0ef;
            color: #c0392b !important;
            font-size: 14px;
            transition: .2s ease;
        }

        .order-delete:hover {
            background: #c0392b;
            color: #fff !important;
            text-decoration: none;
            transform: translateY(-2px);
        }

        /* =========================
           EMPTY STATE
        ========================= */

        .orders-empty {
            padding: 60px 20px;
            text-align: center;
        }

        .orders-empty-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 90px;
            height: 90px;
            margin: 0 auto 22px;
            border-radius: 50%;
            background: #fff3e7;
            color: var(--gursha-orange);
            font-size: 39px;
        }

        .orders-empty h3 {
            margin: 0 0 12px;
            color: var(--gursha-green);
            font-size: 24px;
            font-weight: 850;
        }

        .orders-empty p {
            max-width: 430px;
            margin: 0 auto 24px;
            color: var(--gursha-muted);
            font-size: 14px;
            line-height: 1.8;
        }

        .orders-browse-btn {
            display: inline-block;
            padding: 13px 25px;
            border-radius: 30px;
            background: var(--gursha-orange);
            color: var(--gursha-green) !important;
            font-size: 14px;
            font-weight: 800;
            transition: .2s ease;
        }

        .orders-browse-btn:hover {
            background: #ffc17d;
            text-decoration: none;
            transform: translateY(-2px);
        }

        .orders-error {
            padding: 17px;
            border: 1px solid #f2cccc;
            border-radius: 9px;
            background: #fff1f1;
            color: #b42318;
            font-size: 14px;
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

            .orders-section {
                padding: 42px 0 55px;
            }

            .orders-heading {
                font-size: 28px;
            }

            .orders-card {
                padding: 20px 15px;
            }

            .orders-card-heading {
                align-items: flex-start;
            }

            .orders-card-heading h3 {
                font-size: 18px;
            }

            .orders-empty {
                padding: 45px 12px;
            }

            .gursha-footer [class*="col-"] {
                margin-bottom: 25px;
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

                        <li>
                            <a class="gursha-nav-link" href="your_orders.php">My Orders</a>
                        </li>

                        <li>
                            <a class="gursha-nav-link"
                               href="javascript:void(0);"
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

    <!-- ORDERS SECTION -->
    <main class="orders-section">

        <div class="container">

            <div class="text-center">

                <div class="orders-heading-icon">
                    <i class="fa fa-shopping-bag"></i>
                </div>

                <h1 class="orders-heading">My Orders</h1>

                <p class="orders-description">
                    Keep track of your orders and stay updated on your favorite meals.
                </p>

            </div>

            <div class="orders-card">

                <?php if ($queryError) { ?>

                    <div class="orders-error" role="alert">
                        <i class="fa fa-exclamation-circle"></i>
                        We couldn't load your orders right now. Please refresh the page and try again.
                    </div>

                <?php } else { ?>

                    <div class="orders-card-heading">

                        <div>
                            <h3>Your Order History</h3>
                            <div class="orders-card-subtitle">
                                View your previous orders and their current status.
                            </div>
                        </div>

                        <span class="orders-count">
                            <?php echo count($orders); ?>
                            <?php echo count($orders) === 1 ? 'ORDER' : 'ORDERS'; ?>
                        </span>

                    </div>

                    <?php if (empty($orders)) { ?>

                        <div class="orders-empty">

                            <div class="orders-empty-icon">
                                <i class="fa fa-shopping-basket"></i>
                            </div>

                            <h3>No Orders Yet</h3>

                            <p>
                                You haven't placed any orders yet.
                                Explore our restaurants and discover something delicious.
                            </p>

                            <a href="restaurants.php" class="orders-browse-btn">
                                Explore Restaurants
                                <i class="fa fa-arrow-right"></i>
                            </a>

                        </div>

                    <?php } else { ?>

                        <div class="orders-table-wrapper">

                            <table class="table orders-table">

                                <thead>
                                    <tr>
                                        <th>Food Item</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Order Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php foreach ($orders as $row) {
                                        $statusInfo = getOrderStatus($row['status'] ?? '');
                                    ?>

                                        <tr>

                                            <td>
                                                <div class="order-item-cell">

                                                    <div class="order-item-icon">
                                                        <i class="fa fa-cutlery"></i>
                                                    </div>

                                                    <div>
                                                        <span class="order-item-name">
                                                            <?php echo gursha_escape($row['title'] ?? 'Food Item'); ?>
                                                        </span>

                                                        <span class="order-item-reference">
                                                            Order #<?php echo gursha_escape($row['o_id'] ?? ''); ?>
                                                        </span>
                                                    </div>

                                                </div>
                                            </td>

                                            <td>
                                                <span class="order-quantity">
                                                    <?php echo gursha_escape($row['quantity'] ?? ''); ?>
                                                </span>
                                            </td>

                                            <td class="order-price">
                                                $<?php echo number_format((float)($row['price'] ?? 0), 2); ?>
                                            </td>

                                            <td>
                                                <span class="order-status <?php echo gursha_escape($statusInfo['class']); ?>">

                                                    <i class="fa <?php echo gursha_escape($statusInfo['icon']); ?>"
                                                       aria-hidden="true"></i>

                                                    <?php echo gursha_escape($statusInfo['label']); ?>

                                                </span>
                                            </td>

                                            <td class="order-date">
                                                <?php echo gursha_escape($row['date'] ?? ''); ?>
                                            </td>

                                            <td>
                                                <a href="delete_orders.php?order_del=<?php echo urlencode((string)($row['o_id'] ?? '')); ?>"
                                                   class="order-delete"
                                                   title="Delete order"
                                                   aria-label="Delete order"
                                                   onclick="return confirm('Are you sure you want to delete this order?');">

                                                    <i class="fa fa-trash-o"></i>

                                                </a>
                                            </td>

                                        </tr>

                                    <?php } ?>

                                </tbody>

                            </table>

                        </div>

                    <?php } ?>

                <?php } ?>

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
