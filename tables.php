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

/* Add Table */
if (isset($_POST['add_table'])) {

    $table_number = $_POST['table_number'];
    $capacity = $_POST['capacity'];

    $sql = "INSERT INTO restaurant_tables (table_number, capacity)
            VALUES ('$table_number', '$capacity')";

    mysqli_query($conn, $sql);

    header("Location: tables.php");
    exit();
}


/* Update Table */
if (isset($_POST['update_table'])) {

    $id = $_POST['id'];
    $table_number = $_POST['table_number'];
    $capacity = $_POST['capacity'];
    $status = $_POST['status'];

    $sql = "UPDATE restaurant_tables
            SET table_number='$table_number',
                capacity='$capacity',
                status='$status'
            WHERE id='$id'";

    mysqli_query($conn, $sql);

    header("Location: tables.php");
    exit();
}


/* Delete Table */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    mysqli_query($conn,
        "DELETE FROM restaurant_tables WHERE id='$id'"
    );

    header("Location: tables.php");
    exit();
}


/* Get table for editing */
$edit_table = null;

if (isset($_GET['edit'])) {

    $id = $_GET['edit'];

    $edit_result = mysqli_query($conn,
        "SELECT * FROM restaurant_tables WHERE id='$id'"
    );

    $edit_table = mysqli_fetch_assoc($edit_result);
}


/* Get all tables */
$result = mysqli_query($conn,
    "SELECT * FROM restaurant_tables ORDER BY id"
);
?>

<!DOCTYPE html>
<html>

<head>

    <title>Table Management</title>

    <link rel="stylesheet" href="style.css">

    <style>

        .content {
            background: white;
            padding: 25px;
            margin-top: 25px;
            border-radius: 10px;
        }

        .form-box {
            margin-bottom: 30px;
        }

        .form-box input,
        .form-box select {
            padding: 10px;
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .form-box button {
            padding: 10px 20px;
            background: green;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .cancel-btn {
            padding: 10px 20px;
            background: gray;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f2f2f2;
        }

        .available {
            color: green;
            font-weight: bold;
        }

        .occupied {
            color: red;
            font-weight: bold;
        }

        .reserved {
            color: orange;
            font-weight: bold;
        }

        .edit-btn {
            color: blue;
            text-decoration: none;
            margin-right: 10px;
        }

        .delete-btn {
            color: red;
            text-decoration: none;
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

        <h1>Table Management</h1>

        <div>
            Welcome,
            <strong><?php echo $_SESSION['username']; ?></strong>
        </div>

    </div>


    <div class="content">


        <?php if ($edit_table) { ?>

            <h2>Edit Table</h2>

            <div class="form-box">

                <form method="POST">

                    <input type="hidden"
                           name="id"
                           value="<?php echo $edit_table['id']; ?>">

                    <input type="text"
                           name="table_number"
                           value="<?php echo $edit_table['table_number']; ?>"
                           required>

                    <input type="number"
                           name="capacity"
                           value="<?php echo $edit_table['capacity']; ?>"
                           min="1"
                           required>

                    <select name="status">

                        <option value="Available"
                            <?php if ($edit_table['status'] == 'Available') echo 'selected'; ?>>
                            Available
                        </option>

                        <option value="Occupied"
                            <?php if ($edit_table['status'] == 'Occupied') echo 'selected'; ?>>
                            Occupied
                        </option>

                        <option value="Reserved"
                            <?php if ($edit_table['status'] == 'Reserved') echo 'selected'; ?>>
                            Reserved
                        </option>

                    </select>

                    <button type="submit" name="update_table">
                        Update Table
                    </button>

                    <a href="tables.php" class="cancel-btn">
                        Cancel
                    </a>

                </form>

            </div>

        <?php } else { ?>


            <h2>Add New Table</h2>

            <div class="form-box">

                <form method="POST">

                    <input type="text"
                           name="table_number"
                           placeholder="Table Number"
                           required>

                    <input type="number"
                           name="capacity"
                           placeholder="Capacity"
                           min="1"
                           required>

                    <button type="submit" name="add_table">
                        Add Table
                    </button>

                </form>

            </div>

        <?php } ?>


        <h2>Restaurant Tables</h2>

        <br>

        <table>

            <tr>

                <th>ID</th>
                <th>Table Number</th>
                <th>Capacity</th>
                <th>Status</th>
                <th>Action</th>

            </tr>


            <?php while ($row = mysqli_fetch_assoc($result)) { ?>

            <tr>

                <td><?php echo $row['id']; ?></td>

                <td><?php echo $row['table_number']; ?></td>

                <td>
                    <?php echo $row['capacity']; ?> Persons
                </td>

                <td>
                    <?php
                    if ($row['status'] == 'Available') {
                        echo '<span style="color: green; font-weight: bold;">Available</span>';
                    } 
                    elseif ($row['status'] == 'Occupied') {
                        echo '<span style="color: red; font-weight: bold;">Occupied</span>';
                    } 
                    elseif ($row['status'] == 'Reserved') {
                        echo '<span style="color: orange; font-weight: bold;">Reserved</span>';
                    }
                    ?>
                </td>

                <td>

                    <a class="edit-btn"
                       href="tables.php?edit=<?php echo $row['id']; ?>">
                        Edit
                    </a>

                    <a class="delete-btn"
                       href="tables.php?delete=<?php echo $row['id']; ?>"
                       onclick="return confirm('Are you sure you want to delete this table?');">
                        Delete
                    </a>

                </td>

            </tr>

            <?php } ?>

        </table>

    </div>

</div>

</body>
</html>