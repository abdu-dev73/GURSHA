
<?php

session_start();

include("connection/connect.php");

error_reporting(0);

// Make sure the customer is logged in.
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Accept only valid positive order IDs.
$orderId = filter_input(INPUT_GET, 'order_del', FILTER_VALIDATE_INT);
$userId = (int) $_SESSION['user_id'];

if (!$orderId || $orderId <= 0) {
    header("Location: your_orders.php");
    exit;
}

// Delete only an order belonging to the currently logged-in customer.
$query = "DELETE FROM users_orders WHERE o_id = ? AND u_id = ?";

$stmt = mysqli_prepare($db, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $orderId, $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Return to the customer's order history.
header("Location: your_orders.php");
exit;

?>
