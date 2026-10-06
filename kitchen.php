<?php
session_start();
include("database.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'kitchen') {
    header("Location: login.php");
    exit();
}


/* Update Kitchen Order Status */

if (isset($_POST['update_kitchen_status'])) {

    $order_id = $_POST['order_id'];
    $order_status = $_POST['order_status'];

    $sql = "UPDATE orders
            SET order_status='$order_status'
            WHERE id='$order_id'";

    if (mysqli_query($conn, $sql)) {

        header("Location: kitchen.php");
        exit();

    } else {

        echo "Error: " . mysqli_error($conn);
    }
}


/* Get Kitchen Orders */

$orders_result = mysqli_query($conn,

    "SELECT
        orders.id AS order_id,
        restaurant_tables.table_number,
        orders.order_status,
        orders.order_date

     FROM orders

     INNER JOIN restaurant_tables
     ON orders.table_id = restaurant_tables.id

     WHERE orders.order_status IN
     ('Pending', 'Preparing', 'Ready', 'Served')

     ORDER BY orders.id DESC"
);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Kitchen Management</title>

    <link rel="stylesheet" href="style.css">

    <style>

        .kitchen-card {

            background: white;

            margin-top: 20px;

            padding: 20px;

            border-radius: 10px;

            box-shadow: 0 2px 8px #ddd;
        }

        .kitchen-card h3 {

            margin-bottom: 10px;
        }

        .order-info {

            margin-bottom: 15px;

            color: #555;
        }

        .food-item {

            padding: 8px;

            background: #f5f5f5;

            margin-top: 5px;

            border-radius: 5px;
        }

        .status-form {

            margin-top: 15px;
        }

        .status-form select {

            padding: 8px;

            border-radius: 5px;

            border: 1px solid #ccc;
        }

        .update-btn {

            padding: 8px 15px;

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

    <?php if ($_SESSION['role'] == 'admin') { ?>

        <a href="tables.php">🪑 Tables</a>

        <a href="menu.php">🍔 Menu</a>

        <a href="orders.php">🧾 Orders</a>

    <?php } ?>

    <a href="kitchen.php">👨‍🍳 Kitchen</a>

    <?php if ($_SESSION['role'] == 'admin') { ?>

        <a href="payments.php">💳 Payments</a>

        <a href="reports.php">📊 Reports</a>

    <?php } ?>

    <a href="logout.php">🚪 Logout</a>

</div>

<div class="main">


    <div class="topbar">

        <h2>Kitchen Management</h2>

        <span>

            Welcome,
            <?php echo $_SESSION['username']; ?>

        </span>

    </div>


    <div class="welcome">

        <h2>Kitchen Orders</h2>

        <p>
            Orders that are currently being processed in the kitchen.
        </p>

    </div>


    <?php

    if (mysqli_num_rows($orders_result) == 0) {

        echo '
        <div class="kitchen-card">

            <h3>No Active Orders</h3>

            <p>
                There are currently no pending or preparing orders.
            </p>

        </div>';

    }


    while ($order = mysqli_fetch_assoc($orders_result)) {

        $order_id = $order['order_id'];


        /* Get Order Items */

        $items_result = mysqli_query($conn,

            "SELECT
                menu_items.item_name,
                order_items.quantity

             FROM order_items

             INNER JOIN menu_items
             ON order_items.menu_item_id = menu_items.id

             WHERE order_items.order_id='$order_id'"
        );

    ?>

        <div class="kitchen-card">

            <h3>
                Order #<?php echo $order['order_id']; ?>
            </h3>


            <div class="order-info">

                <strong>Table:</strong>

                <?php echo $order['table_number']; ?>

                <br>

                <strong>Status:</strong>

                <?php echo $order['order_status']; ?>

            </div>


            <h4>Food Items</h4>


            <?php

            while ($item = mysqli_fetch_assoc($items_result)) {

            ?>

                <div class="food-item">

                    <?php echo $item['item_name']; ?>

                    ×

                    <?php echo $item['quantity']; ?>

                </div>

            <?php

            }

            ?>


            <form
                method="POST"
                class="status-form">

                <input
                    type="hidden"
                    name="order_id"
                    value="<?php echo $order['order_id']; ?>"
                >


                <select name="order_status">

                    <option value="Pending"
                        <?php
                        if ($order['order_status'] == 'Pending')
                            echo 'selected';
                        ?>>
                        Pending
                    </option>


                    <option value="Preparing"
                        <?php
                        if ($order['order_status'] == 'Preparing')
                            echo 'selected';
                        ?>>
                        Preparing
                    </option>


                    <option value="Ready"
                        <?php
                        if ($order['order_status'] == 'Ready')
                            echo 'selected';
                        ?>>
                        Ready
                    </option>

                    <option value="Served"
                        <?php
                        if ($order['order_status'] == 'Served')
                            echo 'selected';
                        ?>>
                        Served
                    </option>


                </select>


                <button
                    type="submit"
                    name="update_kitchen_status"
                    class="update-btn">

                    Update Status

                </button>

            </form>

        </div>

    <?php

    }

    ?>


</div>


</body>

</html>