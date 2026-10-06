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


/* Today's Orders */

$result = mysqli_query($conn,
    "SELECT COUNT(*) AS total
     FROM orders
     WHERE DATE(order_date) = CURDATE()"
);

$row = mysqli_fetch_assoc($result);

$today_orders = $row['total'];


/* Today's Sales */

$result = mysqli_query($conn,
    "SELECT COALESCE(SUM(amount), 0) AS total
     FROM payments
     WHERE payment_status = 'Paid'
     AND DATE(payment_date) = CURDATE()"
);

$row = mysqli_fetch_assoc($result);

$today_sales = $row['total'];


/* Total Paid Orders */

$result = mysqli_query($conn,
    "SELECT COUNT(*) AS total
     FROM payments
     WHERE payment_status = 'Paid'"
);

$row = mysqli_fetch_assoc($result);

$paid_orders = $row['total'];


/* Total Revenue */

$result = mysqli_query($conn,
    "SELECT COALESCE(SUM(amount), 0) AS total
     FROM payments
     WHERE payment_status = 'Paid'"
);

$row = mysqli_fetch_assoc($result);

$total_revenue = $row['total'];

?>

<!DOCTYPE html>
<html>

<head>

    <title>Reports</title>

    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>

        .report-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 25px;
        }

        .report-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px #ddd;
        }

        .report-card h3 {
            margin-bottom: 10px;
            color: #555;
        }

        .report-card p {
            font-size: 25px;
            font-weight: bold;
            margin: 0;
        }

        .report-section {
            background: white;
            margin-top: 30px;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px #ddd;
        }

    </style>

</head>


<body>


<div class="sidebar">

    <h2>Restaurant</h2>

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

        <h2>Reports</h2>

        <span>
            Welcome, <?php echo $_SESSION['username']; ?>
        </span>

    </div>


    <div class="welcome">

        <h2>Restaurant Reports</h2>

        <p>
            View restaurant sales and order statistics.
        </p>

    </div>


    <div class="report-grid">


        <div class="report-card">

            <h3>Today's Orders</h3>

            <p>
                <?php echo $today_orders; ?>
            </p>

        </div>


        <div class="report-card">

            <h3>Today's Sales</h3>

            <p>
                ₹<?php echo number_format($today_sales, 2); ?>
            </p>

        </div>


        <div class="report-card">

            <h3>Paid Orders</h3>

            <p>
                <?php echo $paid_orders; ?>
            </p>

        </div>


        <div class="report-card">

            <h3>Total Revenue</h3>

            <p>
                ₹<?php echo number_format($total_revenue, 2); ?>
            </p>

        </div>


    </div>


    <div class="report-section">

        <h2>Payment Summary</h2>

        <p>
            Total paid orders:
            <strong><?php echo $paid_orders; ?></strong>
        </p>

        <p>
            Total revenue:
            <strong>
                ₹<?php echo number_format($total_revenue, 2); ?>
            </strong>
        </p>

    </div>
    <?php

/* Sales by Date */

$sales_result = mysqli_query($conn,

    "SELECT
        DATE(payment_date) AS sale_date,
        COUNT(*) AS total_orders,
        SUM(amount) AS total_sales

     FROM payments

     WHERE payment_status = 'Paid'

     GROUP BY DATE(payment_date)

     ORDER BY sale_date DESC"
);

?>

<div class="report-section">

    <h2>Sales by Date</h2>

    <table style="width:100%; border-collapse:collapse; margin-top:15px;">

        <tr>

            <th style="padding:10px; border-bottom:1px solid #ddd; text-align:left;">
                Date
            </th>

            <th style="padding:10px; border-bottom:1px solid #ddd; text-align:left;">
                Paid Orders
            </th>

            <th style="padding:10px; border-bottom:1px solid #ddd; text-align:left;">
                Sales
            </th>

        </tr>


        <?php

        while ($sale = mysqli_fetch_assoc($sales_result)) {

        ?>

            <tr>

                <td style="padding:10px; border-bottom:1px solid #ddd;">
                    <?php echo $sale['sale_date']; ?>
                </td>

                <td style="padding:10px; border-bottom:1px solid #ddd;">
                    <?php echo $sale['total_orders']; ?>
                </td>

                <td style="padding:10px; border-bottom:1px solid #ddd;">
                    ₹<?php echo number_format($sale['total_sales'], 2); ?>
                </td>

            </tr>

        <?php } ?>

    </table>

</div>
<?php

/* Payment Method Summary */

$method_result = mysqli_query($conn,

    "SELECT
        payment_method,
        COUNT(*) AS total_orders,
        SUM(amount) AS total_amount

     FROM payments

     WHERE payment_status = 'Paid'

     GROUP BY payment_method

     ORDER BY payment_method"
);

?>

<div class="report-section">

    <h2>Payment Method Summary</h2>

    <table style="width:100%; border-collapse:collapse; margin-top:15px;">

        <tr>

            <th style="padding:10px; border-bottom:1px solid #ddd; text-align:left;">
                Payment Method
            </th>

            <th style="padding:10px; border-bottom:1px solid #ddd; text-align:left;">
                Paid Orders
            </th>

            <th style="padding:10px; border-bottom:1px solid #ddd; text-align:left;">
                Amount
            </th>

        </tr>


        <?php

        while ($method = mysqli_fetch_assoc($method_result)) {

        ?>

            <tr>

                <td style="padding:10px; border-bottom:1px solid #ddd;">
                    <?php echo $method['payment_method']; ?>
                </td>

                <td style="padding:10px; border-bottom:1px solid #ddd;">
                    <?php echo $method['total_orders']; ?>
                </td>

                <td style="padding:10px; border-bottom:1px solid #ddd;">
                    ₹<?php echo number_format($method['total_amount'], 2); ?>
                </td>

            </tr>

        <?php } ?>

    </table>

</div>
<?php

/* Top Selling Food Items */

$top_items_result = mysqli_query($conn,

    "SELECT
        menu_items.item_name,
        SUM(order_items.quantity) AS quantity_sold,
        SUM(order_items.quantity * order_items.price) AS total_sales

     FROM order_items

     INNER JOIN menu_items
     ON order_items.menu_item_id = menu_items.id

     INNER JOIN orders
     ON order_items.order_id = orders.id

     INNER JOIN payments
     ON orders.id = payments.order_id

     WHERE payments.payment_status = 'Paid'

     GROUP BY menu_items.id, menu_items.item_name

     HAVING SUM(order_items.quantity) > 0

     ORDER BY quantity_sold DESC"
);

?>

<div class="report-section">

    <h2>Top Selling Food Items</h2>

    <table style="width:100%; border-collapse:collapse; margin-top:15px;">

        <tr>

            <th style="padding:10px; border-bottom:1px solid #ddd; text-align:left;">
                Food Item
            </th>

            <th style="padding:10px; border-bottom:1px solid #ddd; text-align:left;">
                Quantity Sold
            </th>

            <th style="padding:10px; border-bottom:1px solid #ddd; text-align:left;">
                Sales
            </th>

        </tr>


        <?php

        while ($item = mysqli_fetch_assoc($top_items_result)) {

        ?>

            <tr>

                <td style="padding:10px; border-bottom:1px solid #ddd;">
                    <?php echo $item['item_name']; ?>
                </td>

                <td style="padding:10px; border-bottom:1px solid #ddd;">
                    <?php echo $item['quantity_sold']; ?>
                </td>

                <td style="padding:10px; border-bottom:1px solid #ddd;">
                    ₹<?php echo number_format($item['total_sales'], 2); ?>
                </td>

            </tr>

        <?php } ?>

    </table>

</div>
<?php

/* Order Status Summary */

$status_result = mysqli_query($conn,

    "SELECT
        order_status,
        COUNT(*) AS total_orders

     FROM orders

     GROUP BY order_status

     ORDER BY
        FIELD(
            order_status,
            'Pending',
            'Preparing',
            'Ready',
            'Served',
            'Cancelled'
        )"
);

?>

<div class="report-section">

    <h2>Order Status Summary</h2>

    <table style="width:100%; border-collapse:collapse; margin-top:15px;">

        <tr>

            <th style="padding:10px; border-bottom:1px solid #ddd; text-align:left;">
                Order Status
            </th>

            <th style="padding:10px; border-bottom:1px solid #ddd; text-align:left;">
                Number of Orders
            </th>

        </tr>


        <?php

        while ($status = mysqli_fetch_assoc($status_result)) {

        ?>

            <tr>

                <td style="padding:10px; border-bottom:1px solid #ddd;">
                    <?php echo $status['order_status']; ?>
                </td>

                <td style="padding:10px; border-bottom:1px solid #ddd;">
                    <?php echo $status['total_orders']; ?>
                </td>

            </tr>

        <?php } ?>

    </table>

</div>

<?php

/* Sales Chart Data */

$chart_result = mysqli_query($conn,

    "SELECT
        DATE(payment_date) AS sale_date,
        SUM(amount) AS total_sales

     FROM payments

     WHERE payment_status = 'Paid'

     GROUP BY DATE(payment_date)

     ORDER BY sale_date ASC"
);

$chart_dates = [];
$chart_sales = [];

while ($chart = mysqli_fetch_assoc($chart_result)) {

    $chart_dates[] = $chart['sale_date'];
    $chart_sales[] = $chart['total_sales'];

}

?>
<div class="report-section">

    <h2>Sales Chart</h2>

    <div style="width:100%; max-width:800px;">

        <canvas id="salesChart"></canvas>

    </div>

</div>


<script>

const chartDates = <?php echo json_encode($chart_dates); ?>;

const chartSales = <?php echo json_encode($chart_sales); ?>;


const ctx = document.getElementById('salesChart');


new Chart(ctx, {

    type: 'bar',

    data: {

        labels: chartDates,

        datasets: [{

            label: 'Sales (₹)',

            data: chartSales,

            borderWidth: 1

        }]

    },

    options: {

        responsive: true,

        scales: {

            y: {

                beginAtZero: true,

                title: {

                    display: true,

                    text: 'Sales (₹)'

                }

            },

            x: {

                title: {

                    display: true,

                    text: 'Date'

                }

            }

        }

    }

});

</script>
</div>


</body>

</html>