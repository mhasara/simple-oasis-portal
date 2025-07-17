<?php
session_start();
include 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $team_name = sanitize_input($_POST['team_name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = sanitize_input($_POST['email']);
    
    // Validation
    if (empty($team_name) || empty($password) || empty($email)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        // Check if team name already exists
        $stmt = $conn->prepare("SELECT id FROM teams WHERE name = ?");
        $stmt->bind_param("s", $team_name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Team name already exists';
        } else {
            // Hash password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new team
            $stmt = $conn->prepare("INSERT INTO teams (name, password, email) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $team_name, $hashed_password, $email);
            
            if ($stmt->execute()) {
                $success = 'Team registered successfully! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
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
    <title>Team Registration - <?php echo APP_NAME; ?></title>
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
            max-width: 500px;
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
        input[type="email"],
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
        
        .alert-success {
            background: #efe;
            color: #363;
            border: 1px solid #cfc;
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
        
        .requirements {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .requirements ul {
            margin-left: 20px;
        }
        
        .requirements li {
            color: #666;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>🚀 OASIS</h1>
            <p>Ready Player One - Team Registration</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="team_name">Team Name:</label>
                <input type="text" id="team_name" name="team_name" required 
                       placeholder="Enter your unique team name" 
                       value="<?php echo isset($_POST['team_name']) ? htmlspecialchars($_POST['team_name']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Team Email:</label>
                <input type="email" id="email" name="email" required 
                       placeholder="team@example.com"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="requirements">
                <strong>Password Requirements:</strong>
                <ul>
                    <li>At least 6 characters long</li>
                    <li>Use a secure password</li>
                    <li>Remember this password for login</li>
                </ul>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Enter a secure password">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required 
                       placeholder="Confirm your password">
            </div>
            
            <button type="submit" class="btn">🚀 Register Team</button>
        </form>
        
        <div class="links">
            <a href="team_login.php">Already have a team? Login here</a>
            <span>|</span>
            <a href="index.php">Back to Home</a>
        </div>
    </div>
    
    <script>
        // Client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return;
            }
        });
    </script>
</body>
</html>