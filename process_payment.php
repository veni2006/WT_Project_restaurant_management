<?php

session_start();

include("database.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}


if (isset($_POST['pay'])) {

    $order_id = $_POST['order_id'];
    $payment_method = $_POST['payment_method'];


    /* Get Order Total */

    $sql = "SELECT SUM(quantity * price) AS total
            FROM order_items
            WHERE order_id='$order_id'";

    $result = mysqli_query($conn, $sql);

    $row = mysqli_fetch_assoc($result);

    $amount = $row['total'];


    /* Insert Payment */

    $sql = "INSERT INTO payments
            (order_id, amount, payment_method, payment_status)
            VALUES
            ('$order_id', '$amount', '$payment_method', 'Paid')";

    if (mysqli_query($conn, $sql)) {


        /* Get Table ID */

        $sql = "SELECT table_id
                FROM orders
                WHERE id='$order_id'";

        $result = mysqli_query($conn, $sql);

        $order = mysqli_fetch_assoc($result);

        $table_id = $order['table_id'];


        /* Make Table Available */

        $sql = "UPDATE restaurant_tables
                SET status='Available'
                WHERE id='$table_id'";

        mysqli_query($conn, $sql);


        /* Redirect */

        header("Location: payments.php");
        exit();

    } else {

        echo "Payment Error: " . mysqli_error($conn);
    }
}

?>