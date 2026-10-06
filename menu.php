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

/* Add Menu Item */
if (isset($_POST['add_item'])) {

    $item_name = $_POST['item_name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $availability = $_POST['availability'];

    $sql = "INSERT INTO menu_items
            (item_name, category, price, availability)
            VALUES
            ('$item_name', '$category', '$price', '$availability')";

    mysqli_query($conn, $sql);

    header("Location: menu.php");
    exit();
}

/* Update Menu Item */
/* Update Menu Item */
if (isset($_POST['update_item'])) {

    $id = $_POST['id'];
    $item_name = $_POST['item_name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $availability = $_POST['availability'];

    $sql = "UPDATE menu_items
            SET item_name = '$item_name',
                category = '$category',
                price = '$price',
                availability = '$availability'
            WHERE id = '$id'";

    if (mysqli_query($conn, $sql)) {
        header("Location: menu.php");
        exit();
    } else {
        echo "Update Error: " . mysqli_error($conn);
    }
}

/* Delete Menu Item */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    mysqli_query($conn,
        "DELETE FROM menu_items WHERE id='$id'"
    );

    header("Location: menu.php");
    exit();
}

/* Get Item for Editing */
$edit_item = null;

if (isset($_GET['edit'])) {

    $id = $_GET['edit'];

    $edit_result = mysqli_query($conn,
        "SELECT * FROM menu_items WHERE id='$id'"
    );

    $edit_item = mysqli_fetch_assoc($edit_result);
}

/* Get All Menu Items */
$result = mysqli_query($conn,
    "SELECT * FROM menu_items ORDER BY id"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Menu Management</title>

    <link rel="stylesheet" href="style.css">

    <style>
        .form-box {
            background: white;
            padding: 25px;
            margin-top: 25px;
            border-radius: 10px;
        }

        .form-box input,
        .form-box select {
            padding: 10px;
            margin: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
        }

        .add-btn {
            background: green;
        }

        .update-btn {
            background: blue;
        }

        .cancel-btn {
            background: gray;
            text-decoration: none;
            padding: 10px 15px;
            color: white;
            border-radius: 5px;
        }

        table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 14px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        th {
            background: #222;
            color: white;
        }

        .available {
            color: green;
            font-weight: bold;
        }

        .unavailable {
            color: red;
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
        <h2>Menu Management</h2>

        <span>
            Welcome, <?php echo $_SESSION['username']; ?>
        </span>
    </div>


    <!-- Add / Edit Form -->

    <div class="form-box">

        <?php if ($edit_item) { ?>

            <h3>Edit Menu Item</h3>

            <form method="POST">

                <input type="hidden"
                       name="id"
                       value="<?php echo $edit_item['id']; ?>">

                <input type="text"
                       name="item_name"
                       value="<?php echo $edit_item['item_name']; ?>"
                       placeholder="Item Name"
                       required>

                <input type="text"
                       name="category"
                       value="<?php echo $edit_item['category']; ?>"
                       placeholder="Category"
                       required>

                <input type="number"
                       name="price"
                       value="<?php echo $edit_item['price']; ?>"
                       placeholder="Price"
                       step="0.01"
                       required>

                <select name="availability">

                    <option value="Available"
                        <?php
                        if ($edit_item['availability'] == 'Available')
                            echo 'selected';
                        ?>>
                        Available
                    </option>

                    <option value="Unavailable"
                        <?php
                        if ($edit_item['availability'] == 'Unavailable')
                            echo 'selected';
                        ?>>
                        Unavailable
                    </option>

                </select>

                <button type="submit"
                        name="update_item"
                        class="btn update-btn">
                    Update Item
                </button>

                <a href="menu.php"
                   class="cancel-btn">
                    Cancel
                </a>

            </form>

        <?php } else { ?>

            <h3>Add New Menu Item</h3>

            <form method="POST">

                <input type="text"
                       name="item_name"
                       placeholder="Item Name"
                       required>

                <input type="text"
                       name="category"
                       placeholder="Category"
                       required>

                <input type="number"
                       name="price"
                       placeholder="Price"
                       step="0.01"
                       required>

                <select name="availability">

                    <option value="Available">
                        Available
                    </option>

                    <option value="Unavailable">
                        Unavailable
                    </option>

                </select>

                <button type="submit"
                        name="add_item"
                        class="btn add-btn">
                    Add Item
                </button>

            </form>

        <?php } ?>

    </div>


    <!-- Menu Table -->

    <table>

        <tr>
            <th>ID</th>
            <th>Item Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Availability</th>
            <th>Action</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($result)) { ?>

        <tr>

            <td><?php echo $row['id']; ?></td>

            <td><?php echo $row['item_name']; ?></td>

            <td><?php echo $row['category']; ?></td>

            <td>
                ₹<?php echo number_format($row['price'], 2); ?>
            </td>

            <td>

                <?php
                if ($row['availability'] == 'Available') {

                    echo '<span style="color: green; font-weight: bold;">
                            Available
                          </span>';

                } else {

                    echo '<span style="color: red; font-weight: bold;">
                            Unavailable
                          </span>';
                }
                ?>

            </td>

            <td>

                <a href="menu.php?edit=<?php echo $row['id']; ?>"
                   class="edit-btn">
                    Edit
                </a>

                <a href="menu.php?delete=<?php echo $row['id']; ?>"
                   class="delete-btn"
                   onclick="return confirm('Are you sure you want to delete this item?');">
                    Delete
                </a>

            </td>

        </tr>

        <?php } ?>

    </table>

</div>

</body>
</html>