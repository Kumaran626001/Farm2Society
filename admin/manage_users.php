<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle Verification / Disable
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $uid = $_GET['id'];

    if ($action == 'verify') {
        $conn->query("UPDATE users SET is_verified = 1 WHERE user_id = $uid");
    } elseif ($action == 'delete') {
        $conn->query("DELETE FROM users WHERE user_id = $uid"); // Danger: Deletes related data due to cascades
    }
    header("Location: manage_users.php");
    exit();
}

$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Users - Farm2Society</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <header>
        <nav>
            <a href="../index.php" class="brand"><i class="fas fa-leaf"></i> Farm2Society</a>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_orders.php">All Orders</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h2>Manage Users</h2>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['user_id']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo ucfirst($row['role']); ?></td>
                        <td><?php echo $row['location']; ?></td>
                        <td>
                            <?php
                            if ($row['role'] == 'farmer') {
                                echo $row['is_verified'] ? '<span style="color:green">Verified</span>' : '<span style="color:orange">Pending</span>';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($row['role'] == 'farmer' && !$row['is_verified']): ?>
                                <a href="manage_users.php?action=verify&id=<?php echo $row['user_id']; ?>" class="btn"
                                    style="padding: 5px; width: auto; background: green;">Verify</a>
                            <?php endif; ?>
                            <?php if ($row['role'] != 'admin'): ?>
                                <a href="manage_users.php?action=delete&id=<?php echo $row['user_id']; ?>" class="btn"
                                    style="padding: 5px; width: auto; background: red;"
                                    onclick="return confirm('Are you sure?');">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>