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

// Add new task
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $complete_by = $_POST['complete_by'] ?: null;

    if ($complete_by && strtotime($complete_by) <= time()) {
        $_SESSION['error'] = "Please select a future date and time for 'Complete By'.";
        header("Location: index.php");
        exit();
    }

    if (!empty($title)) {
        $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description, complete_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $title, $description, $complete_by);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php");
    exit();
}

// Update a task
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $complete_by = $_POST['complete_by'] ?: null;

    if ($complete_by && strtotime($complete_by) <= time()) {
        $_SESSION['error'] = "Please select a future date and time for 'Complete By'.";
        header("Location: index.php?edit=" . $id);
        exit();
    }

    $stmt = $conn->prepare("UPDATE tasks SET title = ?, description = ?, complete_by = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sssii", $title, $description, $complete_by, $id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit();
}

// Delete task
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit();
}

// Mark as completed
if (isset($_GET['complete'])) {
    $id = intval($_GET['complete']);
    $stmt = $conn->prepare("UPDATE tasks SET completed = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit();
}

// Edit task
$edit_task = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $edit_task = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Get active and completed tasks
$stmt = $conn->prepare("SELECT * FROM tasks WHERE completed = 0 AND user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$activeTasks = $stmt->get_result();
$stmt->close();

$stmt = $conn->prepare("SELECT * FROM tasks WHERE completed = 1 AND user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$completedTasks = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>To-Do App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.2/dist/lux/bootstrap.min.css" rel="stylesheet" />
    <script>
        function toggleCompleted() {
            const section = document.getElementById('completed-tasks');
            section.classList.toggle('d-none');
        }
    </script>
</head>

<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-light mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">To-Do App</a>
        <div class="collapse navbar-collapse justify-content-end">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item me-3">
                    <span class="navbar-text">Logged in as <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
                </li>
                <li class="nav-item me-2">
                    <a href="logout.php" class="btn btn-outline-dark btn-sm">Logout</a>
                </li>
                <li class="nav-item">
                    <form method="POST" action="delete_account.php" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.')">
                        <button type="submit" class="btn btn-outline-danger btn-sm">Delete Account</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <h1 class="mb-4 text-center"><?php echo $edit_task ? "Edit Task" : "To-Do List"; ?></h1>

    <!-- Error Message -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']); 
            ?>
        </div>
    <?php endif; ?>

    <!-- Task Form -->
    <form method="POST" class="mb-4 p-4 border rounded bg-white shadow-sm">
        <input type="hidden" name="id" value="<?php echo $edit_task['id'] ?? ''; ?>" />
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required value="<?php echo htmlspecialchars($edit_task['title'] ?? ''); ?>" />
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($edit_task['description'] ?? ''); ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Complete By</label>
            <input type="datetime-local" name="complete_by" class="form-control"
                min="<?php echo date('Y-m-d\TH:i'); ?>"
                value="<?php echo isset($edit_task['complete_by']) ? date('Y-m-d\TH:i', strtotime($edit_task['complete_by'])) : ''; ?>" />
        </div>
        <button class="btn btn-<?php echo $edit_task ? 'success' : 'primary'; ?>" type="submit"
            name="<?php echo $edit_task ? 'update' : 'add'; ?>">
            <?php echo $edit_task ? 'Update Task' : 'Add Task'; ?>
        </button>
        <?php if ($edit_task): ?>
            <a href="index.php" class="btn btn-secondary ms-2">Cancel</a>
        <?php endif; ?>
    </form>

    <!-- Show Completed Button -->
    <button class="btn btn-outline-secondary mb-3" onclick="toggleCompleted()">Show/Hide Completed Tasks</button>

    <!-- Active Tasks -->
    <ul class="list-group mb-4">
        <?php while ($task = $activeTasks->fetch_assoc()): ?>
            <li class="list-group-item d-flex justify-content-between align-items-start">
                <div>
                    <h5><?php echo htmlspecialchars($task['title']); ?></h5>
                    <p class="mb-1 text-muted"><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
                    <?php if ($task['complete_by']):
                        $isPast = strtotime($task['complete_by']) < time();
                        $color = $isPast ? 'text-danger' : 'text-success';
                        ?>
                        <small class="<?php echo $color; ?>">
                            Complete by: <?php echo date("M d, Y H:i", strtotime($task['complete_by'])); ?>
                            <?php if ($isPast): ?><strong>(Past Due)</strong><?php endif; ?>
                        </small>
                    <?php endif; ?>
                </div>
                <div>
                    <a href="?edit=<?php echo $task['id']; ?>" class="btn btn-sm btn-warning me-1">Edit</a>
                    <a href="?complete=<?php echo $task['id']; ?>" class="btn btn-sm btn-success me-1">Complete</a>
                    <a href="?delete=<?php echo $task['id']; ?>" class="btn btn-sm btn-danger"
                        onclick="return confirm('Delete this task?');">Delete</a>
                </div>
            </li>
        <?php endwhile; ?>
    </ul>

    <!-- Completed Tasks -->
    <div id="completed-tasks" class="d-none">
        <h3>Completed Tasks</h3>
        <ul class="list-group">
            <?php while ($task = $completedTasks->fetch_assoc()): ?>
                <li class="list-group-item d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="text-decoration-line-through"><?php echo htmlspecialchars($task['title']); ?></h5>
                        <p class="mb-1 text-muted"><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
                        <?php if ($task['complete_by']): ?>
                            <small class="text-muted">
                                Completed goal: <?php echo date("M d, Y H:i", strtotime($task['complete_by'])); ?>
                            </small>
                        <?php endif; ?>
                    </div>
                    <a href="?delete=<?php echo $task['id']; ?>" class="btn btn-sm btn-outline-danger"
                        onclick="return confirm('Delete this completed task?');">Delete</a>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</div>
</body>
</html>

<?php $conn->close(); ?>
