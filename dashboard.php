<?php
session_start();
include("database.php");
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

/* Total tables */
$table_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM restaurant_tables");
$table_data = mysqli_fetch_assoc($table_query);
$total_tables = $table_data['total'];

/* Today's orders */
$order_query = mysqli_query($conn, 
    "SELECT COUNT(*) AS total FROM orders 
     WHERE DATE(order_date) = CURDATE()"
);
$order_data = mysqli_fetch_assoc($order_query);
$today_orders = $order_data['total'];

/* Today's sales */
$sales_query = mysqli_query($conn,
    "SELECT COALESCE(SUM(amount), 0) AS total 
     FROM payments
     WHERE payment_status = 'Paid'
     AND DATE(payment_date) = CURDATE()"
);
$sales_data = mysqli_fetch_assoc($sales_query);
$today_sales = $sales_data['total'];

/* Pending orders */
$pending_query = mysqli_query($conn,
    "SELECT COUNT(*) AS total 
     FROM orders
     WHERE order_status IN ('Pending', 'Preparing')"
);
$pending_data = mysqli_fetch_assoc($pending_query);
$pending_orders = $pending_data['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Restaurant Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="sidebar">

    <h2>🍽️ Restaurant</h2>

    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="tables.php">🪑 Tables</a>
    <a href="menu.php">🍔 Menu</a>
    <a href="orders.php">🧾 Orders</a>
    <a href="kitchen.php">👨‍🍳 Kitchen</a>
    <a href="payments.php">💳 Payments</a>
    <a href="reports.php">📊 Reports</a>
    <a href="logout.php">🚪 Logout</a>

</div>


<div class="main">

    <div class="topbar">
        <h1>Dashboard</h1>

        <div>
            Welcome,
            <strong><?php echo $_SESSION['username']; ?></strong>
        </div>
    </div>


    <div class="cards">

        <div class="card">
            <h3>🪑 Total Tables</h3>
            <p><?php echo $total_tables; ?></p>
            <span>Restaurant Tables</span>
        </div>

        <div class="card">
            <h3>🍽️ Today's Orders</h3>
            <p><?php echo $today_orders; ?></p>
            <span>Orders Received</span>
        </div>

        <div class="card">
            <h3>💰 Today's Sales</h3>
            <p>₹<?php echo number_format($today_sales, 2); ?></p>
            <span>Total Sales</span>
        </div>

        <div class="card">
            <h3>⏳ Pending Orders</h3>
            <p><?php echo $pending_orders; ?></p>
            <span>Pending Order</span>
        </div>

    </div>
    <!-- Quick Access -->
    <div class="quick-access">

    <h2>Quick Access</h2>

    <div class="quick-links">

        <a href="tables.php">
            <span class="quick-icon">🪑</span>
            <span>Manage Tables</span>
        </a>

        <a href="menu.php">
            <span class="quick-icon">🍛</span>
            <span>Manage Menu</span>
        </a>

        <a href="orders.php">
            <span class="quick-icon">🧾</span>
            <span>View Orders</span>
        </a>

        <a href="kitchen.php">
            <span class="quick-icon">👨‍🍳</span>
            <span>Kitchen Orders</span>
        </a>

    </div>

</div>
        
              


    <div class="welcome">

        <h2>Welcome to Restaurant Management System</h2>

        <p>
            Manage tables, menu items, customer orders,
            kitchen operations and payments from this dashboard.
        </p>

    </div>

</div>

</body>
</html>