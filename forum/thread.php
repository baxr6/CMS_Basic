<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

global $settings;
$thread_id = (int)($_GET['id'] ?? 0);

// Get thread
$stmt = $pdo->prepare("SELECT threads.*, users.username FROM threads
                       JOIN users ON threads.user_id = users.id
                       WHERE threads.id = ?");
$stmt->execute([$thread_id]);
$thread = $stmt->fetch();

if (!$thread) die('Thread not found');

// Handle reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_logged_in()) {
    $content = trim($_POST['content']);
    $stmt = $pdo->prepare("INSERT INTO posts (thread_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$thread_id, $_SESSION['user_id'], $content]);
    // Notify thread author if not the replier
    $threadOwnerId = $thread['user_id'];
    if ($threadOwnerId != $_SESSION['user_id']) {
    $msg = $_SESSION['username'] . " replied to your thread: " . $thread['title'];
    $notify = $pdo->prepare("INSERT INTO notifications (user_id, thread_id, message) VALUES (?, ?, ?)");
    $notify->execute([$threadOwnerId, $thread_id, $msg]);
   }

    header("Location: thread.php?id=$thread_id");
    exit;
}

// Get replies
$replies = $pdo->prepare("SELECT posts.*, users.username FROM posts
                          JOIN users ON posts.user_id = users.id
                          WHERE thread_id = ?
                          ORDER BY created_at ASC");
$replies->execute([$thread_id]);
?>

<?php include __DIR__ . '/../themes/' . $siteTheme . '/header.php';
 ?>
<h3><?= htmlspecialchars($thread['title']) ?></h3>
<p><?= nl2br(htmlspecialchars($thread['content'])) ?></p>
<p><em>Posted by <?= htmlspecialchars($thread['username']) ?></em></p>

<hr>
<h4>Replies</h4>
<?php foreach ($replies as $reply): ?>
    <p>
        <?= nl2br(htmlspecialchars($reply['content'])) ?><br>
        <small><em>— <?= htmlspecialchars($reply['username']) ?></em></small>
    </p>
    <hr>
<?php endforeach; ?>

<?php if (is_logged_in()): ?>
<h3>Post a Reply</h3>
<form method="post">
    <textarea name="content" required rows="5" placeholder="Your reply..."></textarea><br>
    <button type="submit">Submit</button>
</form>
<?php else: ?>
<p><a href="/auth/login.php">Login</a> to reply.</p>
<?php endif; ?>

<?php include __DIR__ . '/../themes/' . $siteTheme . '/footer.php';
 ?>
