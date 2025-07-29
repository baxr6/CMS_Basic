<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

global $settings;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        flash('error', 'Username already taken.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $password]);
        flash('success', 'Registration successful. You can now log in.');
        header('Location: login.php');
        exit;
    }
}
?>

<?php include __DIR__ . '/../themes/' . $siteTheme . '/header.php';
 ?>
<h2>Register</h2>
<?php if ($msg = flash('error')) echo "<p style='color:red;'>$msg</p>"; ?>
<?php if ($msg = flash('success')) echo "<p style='color:green;'>$msg</p>"; ?>
<form method="post">
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Register</button>
</form>
<a href="login.php">Already have an account?</a>
<?php include __DIR__ . '/../themes/' . siteTheme . '/footer.php';
 ?>
