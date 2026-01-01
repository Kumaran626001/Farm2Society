<?php
include 'includes/db_connect.php';
session_start();

// Capture redirect URL if present
if (isset($_GET['redirect'])) {
    $_SESSION['redirect_url'] = $_GET['redirect'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $name, $hashed_password, $role);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['name'] = $name;
            $_SESSION['role'] = $role;

            if ($role == 'admin') {
                header("Location: admin/dashboard.php");
            } elseif ($role == 'farmer') {
                header("Location: farmer/dashboard.php");
            } else {
                if (isset($_SESSION['redirect_url'])) {
                    $redirect = $_SESSION['redirect_url'];
                    unset($_SESSION['redirect_url']);
                    header("Location: " . $redirect);
                } else {
                    header("Location: consumer/dashboard.php");
                }
            }
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with this email.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Farm2Society</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <header>
        <nav>
            <a href="index.php" class="brand"><i class="fas fa-leaf"></i> Farm2Society</a>
            <div class="menu-toggle" onclick="document.querySelector('header nav ul').classList.toggle('nav-active')">
                <i class="fas fa-bars"></i>
            </div>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            </ul>
        </nav>
    </header>

    <div class="form-container">
        <h2>Login</h2>
        <?php
        if (isset($_SESSION['success'])) {
            echo "<p style='color:green;text-align:center;'>" . $_SESSION['success'] . "</p>";
            unset($_SESSION['success']);
        }
        if (isset($error))
            echo "<p class='error-msg'>$error</p>";
        ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
    <script src="assets/js/veggies.js"></script>
</body>

</html>