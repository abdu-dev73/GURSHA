
<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);

include("connection/connect.php");

$message = "";
$success = "";

if (isset($_POST['submit'])) {

    $username  = trim($_POST['username'] ?? '');
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname  = trim($_POST['lastname'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $password  = $_POST['password'] ?? '';
    $cpassword = $_POST['cpassword'] ?? '';
    $address   = trim($_POST['address'] ?? '');

    // Validate required fields.
    if (
        $username === '' ||
        $firstname === '' ||
        $lastname === '' ||
        $email === '' ||
        $phone === '' ||
        $password === '' ||
        $cpassword === '' ||
        $address === ''
    ) {
        $message = "All fields are required. Please complete the form.";

    } elseif ($password !== $cpassword) {
        $message = "Your passwords do not match.";

    } elseif (strlen($password) < 6) {
        $message = "Password must contain at least 6 characters.";

    } elseif (strlen(preg_replace('/\D/', '', $phone)) < 10) {
        $message = "Please enter a valid phone number with at least 10 digits.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";

    } else {

        // Check whether the username or email already exists.
        $checkQuery = "SELECT username, email FROM users
                       WHERE username = ? OR email = ?
                       LIMIT 1";

        $checkStmt = mysqli_prepare($db, $checkQuery);

        if ($checkStmt) {

            mysqli_stmt_bind_param($checkStmt, "ss", $username, $email);
            mysqli_stmt_execute($checkStmt);
            mysqli_stmt_store_result($checkStmt);

            $existingUsername = "";
            $existingEmail = "";

            if (mysqli_stmt_num_rows($checkStmt) > 0) {

                mysqli_stmt_bind_result(
                    $checkStmt,
                    $existingUsername,
                    $existingEmail
                );

                mysqli_stmt_fetch($checkStmt);

                if ($existingUsername === $username) {
                    $message = "This username is already registered.";
                } elseif ($existingEmail === $email) {
                    $message = "This email address is already registered.";
                } else {
                    $message = "An account with these details already exists.";
                }
            }

            mysqli_stmt_close($checkStmt);

            // Insert new user if no duplicate was found.
            if ($message === "") {

                // Keep MD5 temporarily for compatibility with the login page.
                $hashedPassword = md5($password);

                $insertQuery = "INSERT INTO users
                    (username, f_name, l_name, email, phone, password, address)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

                $insertStmt = mysqli_prepare($db, $insertQuery);

                if ($insertStmt) {

                    mysqli_stmt_bind_param(
                        $insertStmt,
                        "sssssss",
                        $username,
                        $firstname,
                        $lastname,
                        $email,
                        $phone,
                        $hashedPassword,
                        $address
                    );

                    if (mysqli_stmt_execute($insertStmt)) {
                        mysqli_stmt_close($insertStmt);

                        header("Location: login.php?registered=1");
                        exit;

                    } else {
                        $message = "Registration failed. Please try again.";
                        mysqli_stmt_close($insertStmt);
                    }

                } else {
                    $message = "Something went wrong. Please try again.";
                }
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

    <title>Create Account | GURSHA</title>

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

        /* REGISTRATION SECTION */

        .registration-section {
            padding: 65px 15px;
            background:
                linear-gradient(rgba(16, 41, 31, .84), rgba(16, 41, 31, .84)),
                url('images/img/shiro.jpg') center/cover no-repeat;
        }

        .registration-card {
            width: 100%;
            max-width: 850px;
            margin: 0 auto;
            padding: 42px 45px;
            background: var(--gursha-white);
            border-radius: 18px;
            box-shadow: 0 18px 55px rgba(0,0,0,.20);
        }

        .registration-heading {
            margin: 0 0 9px;
            color: var(--gursha-green);
            font-size: 29px;
            font-weight: 800;
            text-align: center;
        }

        .registration-description {
            margin: 0 0 30px;
            color: var(--gursha-muted);
            font-size: 14px;
            line-height: 1.7;
            text-align: center;
        }

        .registration-form .form-group {
            margin-bottom: 20px;
        }

        .registration-form label {
            display: block;
            margin-bottom: 8px;
            color: var(--gursha-ink);
            font-size: 13px;
            font-weight: 700;
        }

        .registration-form .form-control {
            width: 100%;
            height: 48px;
            padding: 10px 14px;
            border: 1px solid #e0e5df;
            border-radius: 9px;
            outline: none;
            background: #fff;
            color: var(--gursha-ink);
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 14px;
            box-shadow: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .registration-form .form-control:focus {
            border-color: var(--gursha-green);
            box-shadow: 0 0 0 3px rgba(23,59,45,.10);
        }

        .registration-form textarea.form-control {
            height: auto;
            min-height: 105px;
            resize: vertical;
        }

        .registration-submit {
            display: inline-block;
            min-width: 190px;
            height: 49px;
            padding: 0 25px;
            border: none;
            border-radius: 30px;
            background: var(--gursha-orange);
            color: var(--gursha-green);
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            transition: .2s ease;
        }

        .registration-submit:hover {
            background: #ffc17d;
            transform: translateY(-2px);
        }

        .registration-error {
            margin-bottom: 22px;
            padding: 12px 15px;
            border: 1px solid #f0caca;
            border-radius: 8px;
            background: #fff1f1;
            color: #b42318;
            font-size: 13px;
            text-align: center;
        }

        .registration-cta {
            margin-top: 25px;
            color: var(--gursha-muted);
            font-size: 13px;
            text-align: center;
        }

        .registration-cta a {
            color: var(--gursha-green);
            font-weight: 800;
            text-decoration: none;
        }

        .registration-cta a:hover {
            color: var(--gursha-orange);
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

            .registration-section {
                padding: 45px 15px;
            }

            .registration-card {
                padding: 32px 22px;
            }

            .registration-heading {
                font-size: 25px;
            }

            .registration-submit {
                width: 100%;
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

    <!-- REGISTRATION FORM -->
    <main class="registration-section">

        <div class="registration-card">

            <h1 class="registration-heading">Create Your Account</h1>

            <p class="registration-description">
                Join GURSHA and discover delicious food from your favorite restaurants.
            </p>

            <?php if (!empty($message)) { ?>
                <div class="registration-error" role="alert">
                    <?php echo gursha_escape($message); ?>
                </div>
            <?php } ?>

            <form action="registration.php" method="post" class="registration-form">

                <div class="row">

                    <div class="form-group col-sm-12">
                        <label for="username">Username</label>
                        <input
                            type="text"
                            class="form-control"
                            name="username"
                            id="username"
                            placeholder="Choose a username"
                            value="<?php echo gursha_escape($_POST['username'] ?? ''); ?>"
                            autocomplete="username"
                            required>
                    </div>

                    <div class="form-group col-sm-6">
                        <label for="firstname">First Name</label>
                        <input
                            type="text"
                            class="form-control"
                            name="firstname"
                            id="firstname"
                            placeholder="Enter your first name"
                            value="<?php echo gursha_escape($_POST['firstname'] ?? ''); ?>"
                            autocomplete="given-name"
                            required>
                    </div>

                    <div class="form-group col-sm-6">
                        <label for="lastname">Last Name</label>
                        <input
                            type="text"
                            class="form-control"
                            name="lastname"
                            id="lastname"
                            placeholder="Enter your last name"
                            value="<?php echo gursha_escape($_POST['lastname'] ?? ''); ?>"
                            autocomplete="family-name"
                            required>
                    </div>

                    <div class="form-group col-sm-6">
                        <label for="email">Email Address</label>
                        <input
                            type="email"
                            class="form-control"
                            name="email"
                            id="email"
                            placeholder="Enter your email address"
                            value="<?php echo gursha_escape($_POST['email'] ?? ''); ?>"
                            autocomplete="email"
                            required>
                    </div>

                    <div class="form-group col-sm-6">
                        <label for="phone">Phone Number</label>
                        <input
                            type="tel"
                            class="form-control"
                            name="phone"
                            id="phone"
                            placeholder="Enter your phone number"
                            value="<?php echo gursha_escape($_POST['phone'] ?? ''); ?>"
                            autocomplete="tel"
                            required>
                    </div>

                    <div class="form-group col-sm-6">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            class="form-control"
                            name="password"
                            id="password"
                            placeholder="Create a password"
                            minlength="6"
                            autocomplete="new-password"
                            required>
                    </div>

                    <div class="form-group col-sm-6">
                        <label for="cpassword">Confirm Password</label>
                        <input
                            type="password"
                            class="form-control"
                            name="cpassword"
                            id="cpassword"
                            placeholder="Confirm your password"
                            minlength="6"
                            autocomplete="new-password"
                            required>
                    </div>

                    <div class="form-group col-sm-12">
                        <label for="address">Delivery Address</label>
                        <textarea
                            class="form-control"
                            name="address"
                            id="address"
                            rows="3"
                            placeholder="Enter your delivery address"
                            autocomplete="street-address"
                            required><?php echo gursha_escape($_POST['address'] ?? ''); ?></textarea>
                    </div>

                </div>

                <div class="row">
                    <div class="col-sm-12 text-center">
                        <button type="submit" name="submit" value="Register"
                                class="registration-submit">
                            Create Account <i class="fa fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

            </form>

            <div class="registration-cta">
                Already have an account?
                <a href="login.php">Login here</a>
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
