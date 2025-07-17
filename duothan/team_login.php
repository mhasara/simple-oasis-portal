<?php
session_start();
include 'config.php';

$error = '';

// Redirect if already logged in
if (isset($_SESSION['team_id']) && validate_session()) {
    header('Location: challenge.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $team_name = sanitize_input($_POST['team_name']);
    $password = $_POST['password'];
    
    if (empty($team_name) || empty($password)) {
        $error = 'Please enter both team name and password';
    } else {
        // Get team data
        $stmt = $conn->prepare("SELECT id, name, password FROM teams WHERE name = ?");
        $stmt->bind_param("s", $team_name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $team = $result->fetch_assoc();
            
            // Verify password (handle both hashed and plain text for backward compatibility)
            if (password_verify($password, $team['password']) || $password === $team['password']) {
                // Set session variables
                $_SESSION['team_id'] = $team['id'];
                $_SESSION['team_name'] = $team['name'];
                $_SESSION['last_activity'] = time();
                
                // Redirect to challenge page
                header('Location: challenge.php');
                exit;
            } else {
                $error = 'Invalid team name or password';
            }
        } else {
            $error = 'Invalid team name or password';
        }
    }
}

// Get error from URL if redirected
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Login - <?php echo APP_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: #333;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .logo p {
            color: #666;
            font-size: 1.1em;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: bold;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .links {
            text-align: center;
            margin-top: 20px;
        }
        
        .links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 10px;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
        
        .welcome-message {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .welcome-message h3 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .welcome-message p {
            color: #666;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>🎮 OASIS</h1>
            <p>Ready Player One - Team Login</p>
        </div>
        
        <div class="welcome-message">
            <h3>Welcome to The Oasis Protocol!</h3>
            <p>In 2045, the OASIS has gone dark. Your team is the last hope to restore the virtual world. Login to begin your journey and unlock the Master Key.</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="team_name">Team Name:</label>
                <input type="text" id="team_name" name="team_name" required 
                       placeholder="Enter your team name" 
                       value="<?php echo isset($_POST['team_name']) ? htmlspecialchars($_POST['team_name']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Enter your password">
            </div>
            
            <button type="submit" class="btn">🚀 Enter The OASIS</button>
        </form>
        
        <div class="links">
            <a href="team_signup.php">Don't have a team? Register here</a>
            <span>|</span>
            <a href="leaderboard.php">View Leaderboard</a>
            <span>|</span>
            <a href="index.php">Back to Home</a>
        </div>
    </div>
    
    <script>
        // Auto-focus on team name field
        document.getElementById('team_name').focus();
        
        // Add enter key support
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.querySelector('form').submit();
            }
        });
    </script>
</body>
</html>