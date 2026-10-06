<?php
session_start();
include("database.php");

if (isset($_POST['login'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users
            WHERE username='$username'
            AND password='$password'";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {

        $user = mysqli_fetch_assoc($result);

        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'admin') {

            header("Location: dashboard.php");

        } elseif ($user['role'] == 'waiter') {

            header("Location: orders.php");

        } elseif ($user['role'] == 'kitchen') {

            header("Location: kitchen.php");

        }

        exit();

    } else {

        $error = "Invalid Username or Password";
    }
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Restaurant Login</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;

            display: flex;
            justify-content: center;
            align-items: center;

            min-height: 100vh;
        }

        .login-box {
            width: 380px;
            background: white;

            padding: 35px;

            border-radius: 12px;

            box-shadow: 0 4px 15px #ccc;

            text-align: center;
        }

        .login-box h1 {
            margin-bottom: 10px;
            color: #222;
        }

        .login-box p {
            color: #777;
            margin-bottom: 25px;
        }

        .input-box {
            width: 100%;
            margin-bottom: 18px;
        }

        .input-box input {
            width: 100%;

            padding: 13px;

            border: 1px solid #ccc;
            border-radius: 6px;

            font-size: 15px;

            outline: none;
        }

        .input-box input:focus {
            border-color: #28a745;
        }

        .login-btn {
            width: 100%;

            padding: 13px;

            background: #28a745;
            color: white;

            border: none;
            border-radius: 6px;

            font-size: 16px;
            font-weight: bold;

            cursor: pointer;
        }

        .login-btn:hover {
            background: #218838;
        }

        .error {
            color: red;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .restaurant-icon {
            font-size: 45px;
            margin-bottom: 10px;
        }

    </style>

</head>

<body>

    <div class="login-box">

        <div class="restaurant-icon">
            🍽️
        </div>

        <h1>Restaurant Login</h1>

        <p>Order & Table Management System</p>

        <?php
        if (isset($error)) {
            echo '<div class="error">' . $error . '</div>';
        }
        ?>

        <form method="POST">

            <div class="input-box">

                <input
                    type="text"
                    name="username"
                    placeholder="Username"
                    required
                >

            </div>

            <div class="input-box">

                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    required
                >

            </div>

            <button
                type="submit"
                name="login"
                class="login-btn">

                Login

            </button>

        </form>

    </div>

</body>

</html>