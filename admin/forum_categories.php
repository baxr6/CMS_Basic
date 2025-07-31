<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$action = $_POST['action'] ?? $_GET['action'] ?? null;

// Handle reordering
if ($action === 'reorder' && !empty($_POST['order'])) {
    $ids = explode(',', $_POST['order']);
    foreach ($ids as $i => $id) {
        $stmt = $pdo->prepare("UPDATE categories SET sort_order = ? WHERE id = ?");
        $stmt->execute([$i, (int)$id]);
    }
    header('Location: forum_categories.php');
    exit;
}

// Handle create/update/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action !== 'reorder') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');

    if ($action === 'create' && $name) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $desc]);
    } elseif ($action === 'update' && $id > 0) {
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $desc, $id]);
    } elseif ($action === 'delete' && $id > 0) {
        $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
    }

    header('Location: forum_categories.php');
    exit;
}

// Fetch all categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC, id ASC");
$categories = $stmt->fetchAll();

include __DIR__ . '/../themes/' . $siteTheme . '/header.php';
?>

<div class="admin-content">
    <h2>Manage Forum Categories</h2>

    <!-- Create Category -->
    <h3>Create New Category</h3>
    <form method="post" class="admin-form">
        <input type="hidden" name="action" value="create">
        <label>Name: <input type="text" name="name" required></label><br>
        <label>Description: <textarea name="description"></textarea></label><br>
        <button type="submit">Add Category</button>
    </form>

    <hr>

    <!-- Existing Categories -->
    <h3>Edit or Delete Existing Categories</h3>
    <?php foreach ($categories as $cat): ?>
        <form method="post" class="admin-form" style="margin-bottom: 1em;">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
            <label>Name: <input type="text" name="name" value="<?= htmlspecialchars($cat['name'] ?? '') ?>" required></label><br>
            <label>Description: <textarea name="description"><?= htmlspecialchars($cat['description'] ?? '') ?></textarea></label><br>
            <button type="submit">Update</button>
        </form>
        <form method="post" onsubmit="return confirm('Are you sure you want to delete this category?')" style="margin-bottom: 2em;">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
    <?php endforeach; ?>

    <hr>

    <!-- Reorder Categories -->
    <h3>Reorder Categories</h3>
    <form method="post" id="reorderForm">
        <input type="hidden" name="action" value="reorder">
        <input type="hidden" name="order" id="orderInput">
        <ul id="sortable" class="sortable-list">
            <?php foreach ($categories as $cat): ?>
                <li class="sortable-item" data-id="<?= $cat['id'] ?>">
                    <span class="handle">☰</span>
                    <?= htmlspecialchars($cat['name'] ?? 'Untitled') ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <button type="submit">Save Order</button>
    </form>

    <style>
        .sortable-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sortable-item {
            padding: 10px;
            border: 1px solid #ccc;
            background: #f9f9f9;
            margin-bottom: 5px;
            cursor: move;
        }
        .sortable-item .handle {
            margin-right: 8px;
            cursor: grab;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script>
        $(function () {
            $("#sortable").sortable({
                placeholder: "ui-state-highlight",
                update: function () {
                    let ids = $("#sortable").sortable("toArray", { attribute: "data-id" });
                    $("#orderInput").val(ids.join(","));
                }
            });
            $("#reorderForm").on("submit", function (e) {
                if (!$("#orderInput").val()) {
                    let ids = $("#sortable").sortable("toArray", { attribute: "data-id" });
                    $("#orderInput").val(ids.join(","));
                }
            });
        });
    </script>
</div>

<?php include __DIR__ . '/../themes/' . $siteTheme . '/footer.php'; ?>
