<?php
session_start();
include 'config.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Get dashboard statistics
$stats = [];

// Total teams
$result = $conn->query("SELECT COUNT(*) as total FROM teams");
$stats['total_teams'] = $result->fetch_assoc()['total'];

// Active challenges
$result = $conn->query("SELECT COUNT(*) as total FROM challenges WHERE is_active = 1");
$stats['active_challenges'] = $result->fetch_assoc()['total'];

// Total submissions
$result = $conn->query("SELECT COUNT(*) as total FROM submissions");
$stats['total_submissions'] = $result->fetch_assoc()['total'];

// Recent submissions
$result = $conn->query("SELECT COUNT(*) as total FROM submissions WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
$stats['recent_submissions'] = $result->fetch_assoc()['total'];

// Top teams (leaderboard snapshot)
$leaderboard_query = "SELECT t.name, t.total_points, 
                      COUNT(DISTINCT tp.challenge_id) as challenges_completed
                      FROM teams t 
                      LEFT JOIN team_progress tp ON t.id = tp.team_id AND tp.buildathon_completed = 1
                      GROUP BY t.id 
                      ORDER BY t.total_points DESC, challenges_completed DESC 
                      LIMIT 10";
$leaderboard_result = $conn->query($leaderboard_query);

// Recent activity
$activity_query = "SELECT t.name as team_name, c.title as challenge_title, 
                   s.submission_type, s.is_correct, s.submitted_at
                   FROM submissions s
                   JOIN teams t ON s.team_id = t.id
                   JOIN challenges c ON s.challenge_id = c.id
                   ORDER BY s.submitted_at DESC
                   LIMIT 10";
$activity_result = $conn->query($activity_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - The Oasis Protocol</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            color: #333;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 600;
        }
        
        .header-nav {
            display: flex;
            gap: 20px;
        }
        
        .header-nav a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .header-nav a:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            color: #667eea;
            font-size: 36px;
            margin-bottom: 10px;
        }
        
        .stat-card p {
            color: #666;
            font-size: 16px;
            font-weight: 500;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            font-size: 18px;
            font-weight: 600;
        }
        
        .card-content {
            padding: 20px;
        }
        
        .leaderboard-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .leaderboard-item:last-child {
            border-bottom: none;
        }
        
        .team-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .team-rank {
            background: #667eea;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }
        
        .team-name {
            font-weight: 600;
            color: #333;
        }
        
        .team-points {
            color: #667eea;
            font-weight: 600;
        }
        
        .activity-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-team {
            font-weight: 600;
            color: #333;
        }
        
        .activity-challenge {
            color: #667eea;
            font-size: 14px;
        }
        
        .activity-type {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            margin-top: 5px;
        }
        
        .activity-type.algorithmic {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .activity-type.buildathon {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        .activity-status {
            float: right;
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 12px;
        }
        
        .activity-status.correct {
            background: #e8f5e8;
            color: #2e7d32;
        }
        
        .activity-status.incorrect {
            background: #ffebee;
            color: #c62828;
        }
        
        .activity-time {
            color: #999;
            font-size: 12px;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .header-nav {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>Admin Dashboard</h1>
            <nav class="header-nav">
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="admin_challenges.php">Challenges</a>
                <a href="admin_teams.php">Teams</a>
                <a href="admin_logout.php">Logout</a>
            </nav>
        </div>
    </div>
    
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $stats['total_teams']; ?></h3>
                <p>Registered Teams</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['active_challenges']; ?></h3>
                <p>Active Challenges</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['total_submissions']; ?></h3>
                <p>Total Submissions</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['recent_submissions']; ?></h3>
                <p>Recent Submissions (24h)</p>
            </div>
        </div>
        
        <div class="content-grid">
            <div class="card">
                <div class="card-header">
                    Top Teams (Leaderboard)
                </div>
                <div class="card-content">
                    <?php if ($leaderboard_result->num_rows > 0): ?>
                        <?php $rank = 1; ?>
                        <?php while ($team = $leaderboard_result->fetch_assoc()): ?>
                            <div class="leaderboard-item">
                                <div class="team-info">
                                    <div class="team-rank"><?php echo $rank++; ?></div>
                                    <div>
                                        <div class="team-name"><?php echo htmlspecialchars($team['name']); ?></div>
                                        <div style="font-size: 12px; color: #666;">
                                            <?php echo $team['challenges_completed']; ?> challenges completed
                                        </div>
                                    </div>
                                </div>
                                <div class="team-points"><?php echo $team['total_points']; ?> pts</div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #666;">No teams registered yet</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    Recent Activity
                </div>
                <div class="card-content">
                    <?php if ($activity_result->num_rows > 0): ?>
                        <?php while ($activity = $activity_result->fetch_assoc()): ?>
                            <div class="activity-item">
                                <div class="activity-team"><?php echo htmlspecialchars($activity['team_name']); ?></div>
                                <div class="activity-challenge"><?php echo htmlspecialchars($activity['challenge_title']); ?></div>
                                <span class="activity-type <?php echo $activity['submission_type']; ?>">
                                    <?php echo ucfirst($activity['submission_type']); ?>
                                </span>
                                <span class="activity-status <?php echo $activity['is_correct'] ? 'correct' : 'incorrect'; ?>">
                                    <?php echo $activity['is_correct'] ? 'Correct' : 'Incorrect'; ?>
                                </span>
                                <div class="activity-time"><?php echo date('M j, Y H:i', strtotime($activity['submitted_at'])); ?></div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #666;">No recent activity</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>