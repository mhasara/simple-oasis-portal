<?php
// Enhanced configuration file for The Oasis Protocol
$host = 'localhost';
$db = 'oasis_protocol';
$user = 'root';
$pass = '';

// Database connection with error handling
try {
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Judge0 API Configuration
define('JUDGE0_API_URL', 'http://10.3.5.139:2358');
define('JUDGE0_API_TOKEN', 'ZHVvdGhhbjUuMA==');

// Application settings
define('APP_NAME', 'The Oasis Protocol');
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_CODE_LENGTH', 10000);
define('UPLOAD_PATH', 'uploads/');

// Security functions
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validate_session() {
    if (!isset($_SESSION['last_activity']) || (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

function require_auth($type = 'team') {
    if (!validate_session()) {
        header('Location: ' . ($type === 'admin' ? 'admin_login.php' : 'team_login.php'));
        exit;
    }
    
    if ($type === 'admin' && !isset($_SESSION['admin'])) {
        header('Location: admin_login.php');
        exit;
    }
    
    if ($type === 'team' && !isset($_SESSION['team_id'])) {
        header('Location: team_login.php');
        exit;
    }
}

function get_team_progress($team_id, $challenge_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM team_progress WHERE team_id = ? AND challenge_id = ?");
    $stmt->bind_param("ii", $team_id, $challenge_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function update_team_progress($team_id, $challenge_id, $flag_solved = false, $buildathon_completed = false) {
    global $conn;
    
    $existing = get_team_progress($team_id, $challenge_id);
    
    if ($existing) {
        $stmt = $conn->prepare("UPDATE team_progress SET flag_solved = ?, buildathon_completed = ?, flag_solved_at = ?, buildathon_completed_at = ? WHERE team_id = ? AND challenge_id = ?");
        $flag_time = $flag_solved ? date('Y-m-d H:i:s') : $existing['flag_solved_at'];
        $buildathon_time = $buildathon_completed ? date('Y-m-d H:i:s') : $existing['buildathon_completed_at'];
        $stmt->bind_param("iissii", $flag_solved, $buildathon_completed, $flag_time, $buildathon_time, $team_id, $challenge_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO team_progress (team_id, challenge_id, flag_solved, buildathon_completed, flag_solved_at, buildathon_completed_at) VALUES (?, ?, ?, ?, ?, ?)");
        $flag_time = $flag_solved ? date('Y-m-d H:i:s') : null;
        $buildathon_time = $buildathon_completed ? date('Y-m-d H:i:s') : null;
        $stmt->bind_param("iiiiss", $team_id, $challenge_id, $flag_solved, $buildathon_completed, $flag_time, $buildathon_time);
    }
    
    return $stmt->execute();
}

// Language mapping for Judge0
function get_language_options() {
    return [
        50 => 'C (GCC 9.2.0)',
        54 => 'C++ (GCC 9.2.0)',
        62 => 'Java (OpenJDK 13.0.1)',
        71 => 'Python (3.8.1)',
        63 => 'JavaScript (Node.js 12.14.0)',
        51 => 'C# (Mono 6.6.0.161)',
        60 => 'Go (1.13.5)',
        72 => 'Ruby (2.7.0)',
        73 => 'Rust (1.40.0)',
        68 => 'PHP (7.4.1)'
    ];
}

// Error handling
function handle_error($message, $redirect = null) {
    error_log("Oasis Protocol Error: " . $message);
    if ($redirect) {
        header("Location: $redirect?error=" . urlencode($message));
        exit;
    }
    return false;
}

// Success message handling
function set_success_message($message) {
    $_SESSION['success_message'] = $message;
}

function get_success_message() {
    if (isset($_SESSION['success_message'])) {
        $message = $_SESSION['success_message'];
        unset($_SESSION['success_message']);
        return $message;
    }
    return null;
}

// API helper functions
function make_judge0_request($endpoint, $data = null, $method = 'GET') {
    $url = JUDGE0_API_URL . $endpoint;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-RapidAPI-Key: ' . JUDGE0_API_TOKEN
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 && $httpCode !== 201) {
        return false;
    }
    
    return json_decode($response, true);
}
?>