<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = "sql208.infinityfree.com";
$user = "if0_38615228";
$pass = "OgvxSKIaxSgH";
$dbname = "if0_38615228_todo_app";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->bind_result($id, $password_hash);
    if ($stmt->fetch()) {
        if (password_verify($password, $password_hash)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            header('Location: index.php');
            exit;
        } else {
            $message = 'Invalid username or password.';
        }
    } else {
        $message = 'Invalid username or password.';
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.2/dist/lux/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Login</h2>
    <?php if ($message): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form method="POST" class="p-4 bg-white border rounded shadow-sm">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input id="username" name="username" class="form-control" required />
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" type="password" name="password" class="form-control" required />
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
        <a href="register.php" class="btn btn-link">Create an account</a>
    </form>
</div>
</body>
</html>
