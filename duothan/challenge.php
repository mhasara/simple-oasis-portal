<?php session_start(); include 'config.php'; ?>
<?php
if (!isset($_SESSION['team'])) die('Login Required');
$team = $_SESSION['team'];
$res = $conn->query("SELECT * FROM challenges LIMIT 1");
$row = $res->fetch_assoc();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $flag = $_POST['flag'];
  if ($flag === $row['flag']) {
    echo "<h3>Correct! Unlock: {$row['buildathon']}</h3>";
  } else echo '<h3>Incorrect flag</h3>';
}
?>
<h2>Algorithmic Challenge</h2>
<p><?php echo $row['problem']; ?></p>
<form method="post">
  Flag: <input name="flag"><br>
  <button type="submit">Submit Flag</button>
</form>
