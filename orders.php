<?php
session_start();
include("database.php");
if(!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'waiter') {
    header("Location: login.php");
    exit();
}

/* Cancel Order */

if (isset($_POST['cancel_order'])) {

    $order_id = $_POST['order_id'];

    /* Get table ID of the order */

    $table_query = mysqli_query($conn,
        "SELECT table_id
         FROM orders
         WHERE id='$order_id'
         AND order_status='Pending'"
    );

    if (mysqli_num_rows($table_query) > 0) {

        $table_data = mysqli_fetch_assoc($table_query);

        $table_id = $table_data['table_id'];


        /* Cancel the order */

        $sql = "UPDATE orders
                SET order_status='Cancelled'
                WHERE id='$order_id'
                AND order_status='Pending'";

        if (mysqli_query($conn, $sql)) {


            /* Make table available again */

            mysqli_query($conn,
                "UPDATE restaurant_tables
                 SET status='Available'
                 WHERE id='$table_id'"
            );


            header("Location: orders.php");
            exit();

        } else {

            echo "Error: " . mysqli_error($conn);
        }

    } else {

        echo "Order cannot be cancelled.";
    }
}
/* Create Order */

if (isset($_POST['create_order'])) {

    $table_id = $_POST['table_id'];
    $menu_items = $_POST['menu_item_id'];
    $quantities = $_POST['quantity'];

    $user_id = $_SESSION['username'];

    /* Get logged-in user's ID */
    $user_query = mysqli_query($conn,
        "SELECT id FROM users
         WHERE username='$user_id'"
    );

    $user_data = mysqli_fetch_assoc($user_query);

    $user_id = $user_data['id'];


    /* Insert Order */

    $order_sql = "INSERT INTO orders
                  (table_id, user_id, order_status)
                  VALUES
                  ('$table_id', '$user_id', 'Pending')";

    if (mysqli_query($conn, $order_sql)) {

        $order_id = mysqli_insert_id($conn);


        /* Insert Order Items */

        for ($i = 0; $i < count($menu_items); $i++) {

            $menu_item_id = $menu_items[$i];
            $quantity = $quantities[$i];


            /* Get item price */

            $price_query = mysqli_query($conn,
                "SELECT price FROM menu_items
                 WHERE id='$menu_item_id'"
            );

            $price_data = mysqli_fetch_assoc($price_query);

            $price = $price_data['price'];


            /* Insert order item */

            mysqli_query($conn,
                "INSERT INTO order_items
                (order_id, menu_item_id, quantity, price)
                VALUES
                ('$order_id',
                 '$menu_item_id',
                 '$quantity',
                 '$price')"
            );
        }


        /* Change table status */

        mysqli_query($conn,
            "UPDATE restaurant_tables
             SET status='Occupied'
             WHERE id='$table_id'"
        );


        /* Go to orders page */

        header("Location: orders.php");
        exit();

    } else {

        echo "Order Error: " . mysqli_error($conn);
    }
}
/* Update Order Status */

if (isset($_POST['update_status'])) {

    $order_id = $_POST['order_id'];
    $order_status = $_POST['order_status'];

    $sql = "UPDATE orders
            SET order_status='$order_status'
            WHERE id='$order_id'";

    if (mysqli_query($conn, $sql)) {

        header("Location: orders.php");
        exit();

    } else {

        echo "Status Update Error: " . mysqli_error($conn);
    }
}

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

/* Get available tables */
$table_result = mysqli_query($conn,
    "SELECT * FROM restaurant_tables
     WHERE status != 'Occupied'
     ORDER BY table_number"
);

/* Get available menu items */
$menu_result = mysqli_query($conn,
    "SELECT * FROM menu_items
     WHERE availability = 'Available'
     ORDER BY item_name"
);
?>

<!DOCTYPE html>
<html>

<head>

    <title>Order Management</title>

    <link rel="stylesheet" href="style.css">

    <style>

        .form-box {
            background: white;
            padding: 25px;
            margin-top: 25px;
            border-radius: 10px;
        }

        .form-box select,
        .form-box input {
            padding: 10px;
            margin: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .order-item {
            margin-top: 15px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .add-btn {
            background: green;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        .remove-btn {
            background: red;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
        }

        .submit-btn {
            background: blue;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
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

        <h2>Order Management</h2>

        <span>
            Welcome, <?php echo $_SESSION['username']; ?>
        </span>

    </div>


    <div class="form-box">

        <h3>Create New Order</h3>

        <form method="POST">

            <!-- Select Table -->

            <label>Select Table:</label>

            <select name="table_id" required>

                <option value="">
                    -- Select Table --
                </option>

                <?php while ($table = mysqli_fetch_assoc($table_result)) { ?>

                    <option value="<?php echo $table['id']; ?>">

                        <?php echo $table['table_number']; ?>

                        -
                        Capacity:
                        <?php echo $table['capacity']; ?>

                    </option>

                <?php } ?>

            </select>


            <h3 style="margin-top:25px;">
                Select Food Items
            </h3>


            <div id="order-items">

                <div class="order-item">

                    <select name="menu_item_id[]" required>

                        <option value="">
                            -- Select Food Item --
                        </option>

                        <?php
                        mysqli_data_seek($menu_result, 0);

                        while ($menu = mysqli_fetch_assoc($menu_result)) {
                        ?>

                            <option value="<?php echo $menu['id']; ?>">

                                <?php echo $menu['item_name']; ?>

                                -
                                ₹<?php echo $menu['price']; ?>

                            </option>

                        <?php } ?>

                    </select>


                    <input
                        type="number"
                        name="quantity[]"
                        min="1"
                        value="1"
                        required
                    >

                    <button
                        type="button"
                        class="remove-btn"
                        onclick="removeItem(this)">
                        Remove
                    </button>

                </div>

            </div>


            <button
                type="button"
                class="add-btn"
                onclick="addItem()">
                + Add Another Item
            </button>


            <br>


            <button
                type="submit"
                name="create_order"
                class="submit-btn">
                Create Order
            </button>

        </form>

    </div>
    <div class="form-box">

        <h3>Existing Orders</h3>

        <table style="width:100%; margin-top:20px; border-collapse:collapse;">

            <tr style="background:#222; color:white;">

                <th style="padding:12px;">Order ID</th>
                <th style="padding:12px;">Table</th>
                <th style="padding:12px;">Order Items</th>
                <th style="padding:12px;">Total</th>
                <th style="padding:12px;">Status</th>
                <th style="padding:12px;">Action</th>

            </tr>


            <?php

            $orders_result = mysqli_query($conn,

                "SELECT
                    orders.id AS order_id,
                    restaurant_tables.table_number,
                    orders.order_status

                FROM orders

                INNER JOIN restaurant_tables
                ON orders.table_id = restaurant_tables.id

                ORDER BY orders.id DESC"
            );


            while ($order = mysqli_fetch_assoc($orders_result)) {

                $order_id = $order['order_id'];

                $items_result = mysqli_query($conn,

                    "SELECT
                        menu_items.item_name,
                        order_items.quantity,
                        order_items.price

                    FROM order_items

                    INNER JOIN menu_items
                    ON order_items.menu_item_id = menu_items.id

                    WHERE order_items.order_id = '$order_id'"
                );


                $total = 0;

                $items = "";


                while ($item = mysqli_fetch_assoc($items_result)) {

                    $item_total =
                        $item['quantity'] * $item['price'];

                    $total += $item_total;


                    $items .=
                        $item['item_name']
                        . " × "
                        . $item['quantity']
                        . "<br>";
                }

            ?>

            <tr>

                <td style="padding:12px;">
                    #<?php echo $order['order_id']; ?>
                </td>

                <td style="padding:12px;">
                    <?php echo $order['table_number']; ?>
                </td>

                <td style="padding:12px;">
                    <?php echo $items; ?>
                </td>

                <td style="padding:12px;">
                    ₹<?php echo number_format($total, 2); ?>
                </td>

                <td style="padding:12px;">

                    <form method="POST">

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

                            <option value="Cancelled"
                                <?php
                                if ($order['order_status'] == 'Cancelled')
                                    echo 'selected';
                                ?>>
                                Cancelled
                            </option>

                        </select>

                        <button
                            type="submit"
                            name="update_status"
                            style="margin-top:5px;
                                padding:6px 10px;
                                background:green;
                                color:white;
                                border:none;
                                border-radius:5px;
                                cursor:pointer;">

                            Update

                        </button>

                    </form>

                </td>
                                    </form>

                </td>

                <td style="padding:12px;">

                    <?php if ($order['order_status'] == 'Pending') { ?>

                        <form method="POST"
                              onsubmit="return confirm('Are you sure you want to cancel this order?');">

                            <input
                                type="hidden"
                                name="order_id"
                                value="<?php echo $order['order_id']; ?>"
                            >

                            <button
                                type="submit"
                                name="cancel_order"
                                style="
                                    padding:6px 10px;
                                    background:red;
                                    color:white;
                                    border:none;
                                    border-radius:5px;
                                    cursor:pointer;
                                ">

                                Cancel

                            </button>

                        </form>

                    <?php } else { ?>

                        <span style="color:#777;">
                            Not Available
                        </span>

                    <?php } ?>

                </td>

            </tr>

            </tr>

            <?php } ?>

        </table>

    </div>

</div>


<script>

function addItem() {

    let container = document.getElementById("order-items");

    let firstItem =
        document.querySelector(".order-item");

    let newItem =
        firstItem.cloneNode(true);

    newItem.querySelector("select").value = "";

    newItem.querySelector("input").value = 1;

    container.appendChild(newItem);
}


function removeItem(button) {

    let items =
        document.querySelectorAll(".order-item");

    if (items.length > 1) {

        button.parentElement.remove();

    } else {

        alert("At least one food item is required.");

    }

}

</script>

</body>

</html>