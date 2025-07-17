<?php
session_start();
include 'config.php';

// Admin auth check
if (!isset($_SESSION['admin'])) {
    die('Unauthorized access. <a href="admin_login.php">Login here</a>.');
}

$message = "";

// Handle challenge submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $problem = trim($_POST['problem']);
    $flag = trim($_POST['flag']);
    $build = trim($_POST['build']);

    if ($problem && $flag && $build) {
        $stmt = $conn->prepare("INSERT INTO challenges (problem, flag, buildathon) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $problem, $flag, $build);
        if ($stmt->execute()) {
            $message = "✅ Challenge added successfully!";
        } else {
            $message = "❌ Error adding challenge.";
        }
        $stmt->close();
    } else {
        $message = "⚠️ Please fill in all fields.";
    }
}

// Get all challenges
$challenges = $conn->query("SELECT id, problem, flag FROM challenges ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Challenges</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 40px;
      background: #f4f6f8;
    }

    h2 {
      color: #333;
    }

    form {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      margin-bottom: 30px;
      max-width: 600px;
    }

    textarea, input[type="text"] {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 14px;
    }

    button {
      background: #667eea;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      font-size: 14px;
      cursor: pointer;
    }

    button:hover {
      background: #5a67d8;
    }

    .message {
      margin-bottom: 15px;
      padding: 10px;
      background: #e3fcef;
      color: #2c7a7b;
      border-left: 5px solid #38a169;
      border-radius: 5px;
    }

    ul {
      list-style-type: none;
      padding: 0;
      max-width: 600px;
    }

    li {
      background: #fff;
      padding: 15px;
      margin-bottom: 10px;
      border-left: 5px solid #667eea;
      border-radius: 5px;
      box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    }

    .logout {
      display: inline-block;
      margin-top: 20px;
      color: #e53e3e;
      text-decoration: none;
    }

    .logout:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <h2>🛠️ Manage Challenges</h2>

  <?php if ($message): ?>
    <div class="message"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <form method="post">
    <label>Problem:</label>
    <textarea name="problem" required></textarea>

    <label>Flag:</label>
    <input type="text" name="flag" required>

    <label>Buildathon Task:</label>
    <textarea name="build" required></textarea>

    <button type="submit" name="add">➕ Add Challenge</button>
  </form>

  <h3>📋 Existing Challenges</h3>
  <ul>
    <?php while ($row = $challenges->fetch_assoc()): ?>
      <li>
        <strong>Problem:</strong> <?php echo htmlspecialchars($row['problem']); ?><br>
        <strong>Flag:</strong> <?php echo htmlspecialchars($row['flag']); ?>
      </li>
    <?php endwhile; ?>
  </ul>

  <a class="logout" href="logout.php">🚪 Logout</a>

</body>
</html>
