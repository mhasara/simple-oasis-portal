<?php include 'config.php'; ?>
<h2>Leaderboard</h2>
<table border="1">
  <tr><th>Team</th><th>Submissions</th></tr>
  <?php
  $res = $conn->query("SELECT teams.name, COUNT(submissions.id) as score FROM teams
                        LEFT JOIN submissions ON submissions.team_id = teams.id
                        GROUP BY teams.id ORDER BY score DESC");
  while ($row = $res->fetch_assoc()) {
    echo "<tr><td>{$row['name']}</td><td>{$row['score']}</td></tr>";
  }
  ?>
</table>
<a href="logout.php">Logout</a>
