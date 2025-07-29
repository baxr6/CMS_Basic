<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

global $settings;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: /index.php");
        exit;
    } else {
        flash('error', 'Invalid username or password.');
    }
}
?>

<?php include __DIR__ . '/../themes/' . $siteTheme . '/header.php';
 ?>
<h2>Login</h2>
<?php if ($msg = flash('error')) echo "<p style='color:red;'>$msg</p>"; ?>
<form method="post">
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Login</button>
</form>
<a href="register.php">Don't have an account?</a>
<?php include __DIR__ . '/../themes/' . $siteTheme . '/footer.php';
 ?>
