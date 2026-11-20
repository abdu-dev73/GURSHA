
<?php

// Start the session if it has not already been started.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Make sure the database connection is available.
if (!isset($db)) {
    include_once("connection/connect.php");
}

if (empty($_GET['action'])) {
    return;
}

$action = $_GET['action'];

// Validate the dish ID.
$productId = isset($_GET['id'])
    ? filter_var($_GET['id'], FILTER_VALIDATE_INT)
    : false;

// Handle cart actions.
switch ($action) {

    case "add":

        // Validate dish ID.
        if (!$productId || $productId <= 0) {
            $_SESSION['cart_error'] = "Invalid dish selected.";
            break;
        }

        // Validate quantity.
        $quantity = isset($_POST['quantity'])
            ? filter_var($_POST['quantity'], FILTER_VALIDATE_INT)
            : false;

        if (!$quantity || $quantity < 1 || $quantity > 99) {
            $_SESSION['cart_error'] = "Please select a quantity between 1 and 99.";
            break;
        }

        // Retrieve the selected dish.
        $stmt = mysqli_prepare(
            $db,
            "SELECT d_id, title, price, rs_id FROM dishes WHERE d_id = ?"
        );

        if (!$stmt) {
            $_SESSION['cart_error'] = "Unable to process your request. Please try again.";
            break;
        }

        mysqli_stmt_bind_param($stmt, "i", $productId);
        mysqli_stmt_execute($stmt);

        mysqli_stmt_bind_result(
            $stmt,
            $dishId,
            $dishTitle,
            $dishPrice,
            $restaurantId
        );

        $dishFound = mysqli_stmt_fetch($stmt);

        mysqli_stmt_close($stmt);

        if (!$dishFound) {
            $_SESSION['cart_error'] = "The selected dish could not be found.";
            break;
        }

        // Ensure the dish belongs to the restaurant currently being viewed.
        if (isset($_GET['res_id']) && $_GET['res_id'] !== '') {
            $currentRestaurantId = filter_var(
                $_GET['res_id'],
                FILTER_VALIDATE_INT
            );

            if (
                !$currentRestaurantId ||
                (int)$restaurantId !== (int)$currentRestaurantId
            ) {
                $_SESSION['cart_error'] = "This dish does not belong to the selected restaurant.";
                break;
            }
        }

        // Check whether the cart already contains dishes from another restaurant.
        $cartRestaurantId = null;
        $cartHasDifferentRestaurant = false;

        if (!empty($_SESSION['cart_item']) && is_array($_SESSION['cart_item'])) {

            foreach ($_SESSION['cart_item'] as $cartItem) {

                if (empty($cartItem['d_id'])) {
                    continue;
                }

                // Use the saved restaurant ID when available.
                if (isset($cartItem['rs_id'])) {
                    $existingRestaurantId = (int)$cartItem['rs_id'];
                } else {
                    // Support older cart sessions that do not contain rs_id.
                    $existingDishId = (int)$cartItem['d_id'];

                    $existingStmt = mysqli_prepare(
                        $db,
                        "SELECT rs_id FROM dishes WHERE d_id = ?"
                    );

                    if (!$existingStmt) {
                        $_SESSION['cart_error'] = "Unable to verify your cart. Please try again.";
                        $cartHasDifferentRestaurant = true;
                        break;
                    }

                    mysqli_stmt_bind_param(
                        $existingStmt,
                        "i",
                        $existingDishId
                    );

                    mysqli_stmt_execute($existingStmt);
                    mysqli_stmt_bind_result(
                        $existingStmt,
                        $existingRestaurantId
                    );

                    $existingFound = mysqli_stmt_fetch($existingStmt);
                    mysqli_stmt_close($existingStmt);

                    if (!$existingFound) {
                        $_SESSION['cart_error'] = "An item in your cart is no longer available.";
                        $cartHasDifferentRestaurant = true;
                        break;
                    }

                    $existingRestaurantId = (int)$existingRestaurantId;
                }

                if ($cartRestaurantId === null) {
                    $cartRestaurantId = $existingRestaurantId;
                } elseif ($cartRestaurantId !== $existingRestaurantId) {
                    $_SESSION['cart_error'] = "Your cart contains dishes from different restaurants.";
                    $cartHasDifferentRestaurant = true;
                    break;
                }

                if ($existingRestaurantId !== (int)$restaurantId) {
                    $_SESSION['cart_error'] = "Please complete your current restaurant order before ordering from another restaurant.";
                    $cartHasDifferentRestaurant = true;
                    break;
                }
            }
        }

        if ($cartHasDifferentRestaurant) {
            break;
        }

        // Initialize the cart if necessary.
        if (
            empty($_SESSION['cart_item']) ||
            !is_array($_SESSION['cart_item'])
        ) {
            $_SESSION['cart_item'] = [];
        }

        // Add the dish or increase its quantity.
        if (isset($_SESSION['cart_item'][$dishId])) {

            $currentQuantity = (int)$_SESSION['cart_item'][$dishId]['quantity'];
            $newQuantity = $currentQuantity + $quantity;

            if ($newQuantity > 99) {
                $_SESSION['cart_error'] = "You can order a maximum of 99 units of one dish.";
                break;
            }

            $_SESSION['cart_item'][$dishId]['quantity'] = $newQuantity;

        } else {

            $_SESSION['cart_item'][$dishId] = [
                'title' => $dishTitle,
                'd_id' => $dishId,
                'quantity' => $quantity,
                'price' => $dishPrice,
                'rs_id' => $restaurantId
            ];
        }

        // Clear any previous cart error after a successful addition.
        unset($_SESSION['cart_error']);

        break;


    case "remove":

        if (
            !empty($_SESSION['cart_item']) &&
            is_array($_SESSION['cart_item']) &&
            $productId &&
            $productId > 0
        ) {
            unset($_SESSION['cart_item'][$productId]);

            // Remove the cart session if it becomes empty.
            if (empty($_SESSION['cart_item'])) {
                unset($_SESSION['cart_item']);
            }
        }

        break;


    case "empty":

        unset($_SESSION['cart_item']);
        unset($_SESSION['cart_error']);

        break;


    case "check":

        if (
            empty($_SESSION['cart_item']) ||
            !is_array($_SESSION['cart_item'])
        ) {
            header("Location: restaurants.php");
            exit;
        }

        header("Location: checkout.php");
        exit;


    default:

        // Ignore unsupported actions.
        break;
}

?>
