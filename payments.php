<?php
session_start();
include("database.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'waiter') {
    header("Location: login.php");
    exit();
}


/* Get orders that are ready or served */

$orders_result = mysqli_query($conn,

    "SELECT
        orders.id AS order_id,
        restaurant_tables.table_number,
        orders.order_status

     FROM orders

     INNER JOIN restaurant_tables
     ON orders.table_id = restaurant_tables.id

     WHERE orders.order_status = 'Served'

     AND orders.id NOT IN (
         SELECT order_id
         FROM payments
         WHERE payment_status = 'Paid'
     )

     ORDER BY orders.id DESC"
);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Payments & Billing</title>

    <link rel="stylesheet" href="style.css">

    <style>

        .payment-card {
            background: white;
            margin-top: 20px;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px #ddd;
        }

        .payment-card h3 {
            margin-bottom: 15px;
        }

        .order-info {
            margin-bottom: 15px;
            color: #555;
        }

        .bill-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .bill-table th,
        .bill-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .total {
            font-size: 20px;
            font-weight: bold;
            margin-top: 15px;
        }

        .payment-method {
            margin-top: 20px;
        }

        .payment-method select {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .pay-btn {
            margin-top: 15px;
            padding: 10px 20px;
            background: green;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

    </style>

</head>


<body>


<div class="sidebar">

    <h2>Restaurant</h2>

    <?php if ($_SESSION['role'] == 'admin') { ?>

        <a href="dashboard.php">🏠 Dashboard</a>

    <?php } ?>

    <a href="tables.php">🪑 Tables</a>

    <a href="menu.php">🍔 Menu</a>

    <a href="orders.php">🧾 Orders</a>

    <?php if ($_SESSION['role'] == 'admin') { ?>

        <a href="kitchen.php">👨‍🍳 Kitchen</a>

    <?php } ?>

    <a href="payments.php">💳 Payments</a>

    <?php if ($_SESSION['role'] == 'admin') { ?>

        <a href="reports.php">📊 Reports</a>

    <?php } ?>

    <a href="logout.php">🚪 Logout</a>

</div>


<div class="main">


    <div class="topbar">

        <h2>Payments & Billing</h2>

        <span>
            Welcome, <?php echo $_SESSION['username']; ?>
        </span>

    </div>


    <div class="welcome">

        <h2>Pending Bills</h2>

        <p>
            Select a completed order to process its payment.
        </p>

    </div>


    <?php

    if (mysqli_num_rows($orders_result) == 0) {

        echo '
        <div class="payment-card">

            <h3>No Pending Bills</h3>

            <p>
                There are currently no orders waiting for payment.
            </p>

        </div>';
    }


    while ($order = mysqli_fetch_assoc($orders_result)) {

        $order_id = $order['order_id'];


        /* Get order items */

        $items_result = mysqli_query($conn,

            "SELECT
                menu_items.item_name,
                order_items.quantity,
                order_items.price

             FROM order_items

             INNER JOIN menu_items
             ON order_items.menu_item_id = menu_items.id

             WHERE order_items.order_id='$order_id'"
        );


        $total = 0;

    ?>

        <div class="payment-card">

            <h3>
                Bill for Order #<?php echo $order_id; ?>
            </h3>


            <div class="order-info">

                <strong>Table:</strong>

                <?php echo $order['table_number']; ?>

                <br>

                <strong>Status:</strong>

                <?php echo $order['order_status']; ?>

            </div>


            <table class="bill-table">

                <tr>

                    <th>Item</th>

                    <th>Quantity</th>

                    <th>Price</th>

                    <th>Amount</th>

                </tr>


                <?php

                while ($item = mysqli_fetch_assoc($items_result)) {

                    $amount =
                        $item['quantity'] * $item['price'];

                    $total += $amount;

                ?>

                    <tr>

                        <td>
                            <?php echo $item['item_name']; ?>
                        </td>

                        <td>
                            <?php echo $item['quantity']; ?>
                        </td>

                        <td>
                            ₹<?php echo number_format($item['price'], 2); ?>
                        </td>

                        <td>
                            ₹<?php echo number_format($amount, 2); ?>
                        </td>

                    </tr>

                <?php } ?>

            </table>


            <div class="total">

                Total:
                ₹<?php echo number_format($total, 2); ?>

            </div>


            <form method="POST" action="process_payment.php">

                <input
                    type="hidden"
                    name="order_id"
                    value="<?php echo $order_id; ?>"
                >

                <input
                    type="hidden"
                    name="amount"
                    value="<?php echo $total; ?>"
                >


                <div class="payment-method">

                    <label>
                        Payment Method:
                    </label>

                    <select
                        name="payment_method"
                        required>

                        <option value="">
                            -- Select Method --
                        </option>

                        <option value="Cash">
                            Cash
                        </option>

                        <option value="UPI">
                            UPI
                        </option>

                        <option value="Card">
                            Card
                        </option>

                    </select>

                </div>


                <button
                    type="submit"
                    name="pay"
                    class="pay-btn">

                    Mark as Paid

                </button>

            </form>

        </div>

    <?php } ?>


</div>


</body>

</html>