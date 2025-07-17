<?php
session_start();
include 'config.php';

$error = '';

// Redirect if already logged in
if (isset($_SESSION['admin']) && validate_session()) {
    header('Location: admin_dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // Check admin credentials
        $stmt = $conn->prepare("SELECT id, username, password FROM admin_users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            // For backward compatibility, check both hashed and plain text passwords
            if (password_verify($password, $admin['password']) || $password === $admin['password']) {
                $_SESSION['admin'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['last_activity'] = time();
                
                header('Location: admin_dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password';
            }
        } else {
            $error = 'Invalid username or password';
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
    <title>Admin Login - <?php echo APP_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .logo p {
            color: #7f8c8d;
            font-size: 1.1em;
        }
        
        .admin-badge {
            background: #e74c3c;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
            margin-top: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
            font-weight: bold;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #bdc3c7;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
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
            color: #3498db;
            text-decoration: none;
            margin: 0 10px;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
        
        .security-notice {
            background: #f39c12;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .security-notice h4 {
            margin-bottom: 5px;
        }
        
        .default-credentials {
            background: #ecf0f1;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
        
        .default-credentials strong {
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>🔐 Admin Portal</h1>
            <p>The Oasis Protocol</p>
            <div class="admin-badge">ADMINISTRATOR ACCESS</div>
        </div>
        
        <div class="security-notice">
            <h4>⚠️ Restricted Access</h4>
            <p>This area is restricted to authorized administrators only.</p>
        </div>
        
        <div class="default-credentials">
            <strong>Default Credentials:</strong><br>
            Username: admin<br>
            Password: admin123
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required 
                       placeholder="Enter admin username" 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Enter admin password">
            </div>
            
            <button type="submit" class="btn">🔓 Admin Login</button>
        </form>
        
        <div class="links">
            <a href="team_login.php">Team Login</a>
            <span>|</span>
            <a href="index.php">Back to Home</a>
        </div>
    </div>
    
    <script>
        // Auto-focus on username field
        document.getElementById('username').focus();
        
        // Security: Clear form on page load
        window.addEventListener('load', function() {
            document.getElementById('password').value = '';
        });
        
        // Security: Disable right-click context menu
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
    </script>
</body>
</html>