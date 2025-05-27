<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = "sql208.infinityfree.com";
$user = "if0_38615228";
$pass = "OgvxSKIaxSgH";
$dbname = "if0_38615228_todo_app";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Delete all tasks for the user first (optional but recommended)
$stmt = $conn->prepare("DELETE FROM tasks WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Delete user account
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

$conn->close();

// Destroy session and redirect to registration or login page
session_destroy();
header('Location: register.php');
exit;
?>
