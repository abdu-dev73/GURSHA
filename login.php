
<?php
include("connection/connect.php");

error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();

$message = "";

// Only allow the checkout page as a return destination.
// This prevents unsafe external redirects.
$redirectTo = $_GET['redirect'] ?? $_POST['redirect'] ?? 'index.php';

if ($redirectTo !== 'checkout.php') {
    $redirectTo = 'index.php';
}

if (isset($_POST['submit'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $message = "Please enter both your username and password.";
    } else {
        // Retained for compatibility with existing database records.
        $hashedPassword = md5($password);

        $loginquery = "SELECT u_id FROM users WHERE username = ? AND password = ?";
        $stmt = mysqli_prepare($db, $loginquery);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $username, $hashedPassword);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);

            if ($row) {
                $_SESSION["user_id"] = $row['u_id'];

                mysqli_stmt_close($stmt);

                // Return the user to the page they originally requested.
                header("Location: " . $redirectTo);
                exit;
            } else {
                $message = "Invalid username or password!";
            }

            mysqli_stmt_close($stmt);
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
    <title>Login | GURSHA</title>

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

        .login-section {
            min-height: 650px;
            padding: 75px 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                linear-gradient(rgba(16, 41, 31, .84), rgba(16, 41, 31, .84)),
                url('images/img/shiro.jpg') center/cover no-repeat;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            padding: 42px 38px;
            background: var(--gursha-white);
            border-radius: 18px;
            box-shadow: 0 18px 55px rgba(0,0,0,.20);
        }

        .login-heading {
            margin: 0 0 9px;
            color: var(--gursha-green);
            font-size: 27px;
            font-weight: 800;
            text-align: center;
        }

        .login-description {
            margin: 0 0 28px;
            color: var(--gursha-muted);
            font-size: 14px;
            line-height: 1.7;
            text-align: center;
        }

        .login-form-group {
            margin-bottom: 19px;
        }

        .login-form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--gursha-ink);
            font-size: 13px;
            font-weight: 700;
        }

        .login-input {
            width: 100%;
            height: 49px;
            padding: 0 15px;
            border: 1px solid #e0e5df;
            border-radius: 9px;
            outline: none;
            background: #fff;
            color: var(--gursha-ink);
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 14px;
            transition: border-color .2s, box-shadow .2s;
        }

        .login-input:focus {
            border-color: var(--gursha-green);
            box-shadow: 0 0 0 3px rgba(23,59,45,.10);
        }

        .login-submit {
            width: 100%;
            height: 49px;
            margin-top: 7px;
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

        .login-submit:hover {
            background: #ffc17d;
            transform: translateY(-2px);
        }

        .login-error {
            margin-bottom: 20px;
            padding: 11px 13px;
            border: 1px solid #f0caca;
            border-radius: 8px;
            background: #fff1f1;
            color: #b42318;
            font-size: 13px;
            text-align: center;
        }

        .login-cta {
            margin-top: 25px;
            color: var(--gursha-muted);
            font-size: 13px;
            text-align: center;
        }

        .login-cta a {
            color: var(--gursha-green);
            font-weight: 800;
            text-decoration: none;
        }

        .login-cta a:hover {
            color: var(--gursha-orange);
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

            .login-section {
                min-height: 600px;
                padding: 55px 15px;
            }

            .login-card {
                padding: 35px 24px;
            }

            .gursha-footer [class*="col-"] {
                margin-bottom: 25px;
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

                    <?php if (empty($_SESSION["user_id"])) { ?>
                        <li><a class="gursha-nav-link" href="registration.php">Register</a></li>
                    <?php } else { ?>
                        <li><a class="gursha-nav-link" href="your_orders.php">My Orders</a></li>
                        <li>
                            <a class="gursha-nav-link" href="javascript:void(0);"
                               onclick="confirmLogout();">Logout</a>
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

<main class="login-section">
    <div class="login-card">

        <h1 class="login-heading">Welcome Back!</h1>

        <p class="login-description">
            Login to your GURSHA account and enjoy your favorite food.
        </p>

        <?php if (!empty($message)) { ?>
            <div class="login-error" role="alert">
                <?php echo gursha_escape($message); ?>
            </div>
        <?php } ?>

        <form action="login.php" method="post">

            <!-- Preserve the destination when the login form is submitted. -->
            <input type="hidden" name="redirect"
                   value="<?php echo gursha_escape($redirectTo); ?>">

            <div class="login-form-group">
                <label for="username">Username</label>
                <input type="text"
                       id="username"
                       name="username"
                       class="login-input"
                       placeholder="Enter your username"
                       autocomplete="username"
                       required>
            </div>

            <div class="login-form-group">
                <label for="password">Password</label>
                <input type="password"
                       id="password"
                       name="password"
                       class="login-input"
                       placeholder="Enter your password"
                       autocomplete="current-password"
                       required>
            </div>

            <button type="submit" name="submit" value="Login"
                    class="login-submit">
                Login to Your Account
                <i class="fa fa-arrow-right"></i>
            </button>

        </form>

        <div class="login-cta">
            Don't have an account?
            <a href="registration.php">Create an account</a>
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
