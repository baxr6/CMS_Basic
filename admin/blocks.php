<?php
// admin/blocks.php - Block Management Interface
session_start();
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /auth/login.php');
    exit;
}

global $blockManager, $settings;
require_admin();

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                createBlock();
                break;
            case 'update':
                updateBlock();
                break;
            case 'delete':
                deleteBlock();
                break;
            case 'toggle':
                toggleBlock();
                break;
            case 'reorder':
                reorderBlocks();
                break;
        }
    }
}

function createBlock() {
    global $pdo;
    
    $data = [
        'title' => $_POST['title'] ?? '',
        'content' => $_POST['content'] ?? '',
        'block_type' => $_POST['block_type'] ?? 'html',
        'position' => $_POST['position'] ?? 'right',
        'css_class' => $_POST['css_class'] ?? '',
        'sort_order' => intval($_POST['sort_order'] ?? 0),
        'is_enabled' => isset($_POST['is_enabled']) ? 1 : 0
    ];
    
    // Process block-specific settings
    $settings = [];
    if (isset($_POST['settings']) && is_array($_POST['settings'])) {
        $settings = $_POST['settings'];
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO blocks (title, content, block_type, position, css_class, sort_order, is_enabled, settings)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $data['title'],
        $data['content'],
        $data['block_type'],
        $data['position'],
        $data['css_class'],
        $data['sort_order'],
        $data['is_enabled'],
        json_encode($settings)
    ]);
    
    header('Location: /admin/blocks.php?success=created');
    exit;
}

function updateBlock() {
    global $pdo;
    
    $id = intval($_POST['id']);
    $data = [
        'title' => $_POST['title'] ?? '',
        'content' => $_POST['content'] ?? '',
        'block_type' => $_POST['block_type'] ?? 'html',
        'position' => $_POST['position'] ?? 'right',
        'css_class' => $_POST['css_class'] ?? '',
        'sort_order' => intval($_POST['sort_order'] ?? 0),
        'is_enabled' => isset($_POST['is_enabled']) ? 1 : 0
    ];
    
    $settings = [];
    if (isset($_POST['settings']) && is_array($_POST['settings'])) {
        $settings = $_POST['settings'];
    }
    
    $stmt = $pdo->prepare("
        UPDATE blocks 
        SET title = ?, content = ?, block_type = ?, position = ?, css_class = ?, sort_order = ?, is_enabled = ?, settings = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $data['title'],
        $data['content'],
        $data['block_type'],
        $data['position'],
        $data['css_class'],
        $data['sort_order'],
        $data['is_enabled'],
        json_encode($settings),
        $id
    ]);
    
    header('Location: /admin/blocks.php?success=updated');
    exit;
}

function deleteBlock() {
    global $pdo;
    $id = intval($_POST['id']);
    
    $stmt = $pdo->prepare("DELETE FROM blocks WHERE id = ?");
    $stmt->execute([$id]);
    
    header('Location: /admin/blocks.php?success=deleted');
    exit;
}

function toggleBlock() {
    global $pdo;
    $id = intval($_POST['id']);
    
    $stmt = $pdo->prepare("UPDATE blocks SET is_enabled = NOT is_enabled WHERE id = ?");
    $stmt->execute([$id]);
    
    header('Location: /admin/blocks.php');
    exit;
}

function reorderBlocks() {
    global $pdo;
    
    if (isset($_POST['block_order']) && is_array($_POST['block_order'])) {
        foreach ($_POST['block_order'] as $order => $blockId) {
            $stmt = $pdo->prepare("UPDATE blocks SET sort_order = ? WHERE id = ?");
            $stmt->execute([$order, intval($blockId)]);
        }
    }
    
    header('Location: /admin/blocks.php?success=reordered');
    exit;
}

// Get blocks for display
$stmt = $pdo->query("SELECT * FROM blocks ORDER BY position ASC, sort_order ASC, id ASC");
$blocks = $stmt->fetchAll();

// Get block types
$blockTypes = $blockManager->getRegisteredBlockTypes();

// Get current block for editing
$editBlock = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM blocks WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $editBlock = $stmt->fetch();
}

include __DIR__ . '/../themes/' . $siteTheme . '/header.php';
?>

<div class="admin-content">
    <h2>Block Management</h2>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            Block <?= htmlspecialchars($_GET['success']) ?> successfully!
        </div>
    <?php endif; ?>
    
    <div class="admin-tabs">
        <div class="tab-content">
            <!-- Block List -->
            <div class="tab-pane active">
                <div class="block-actions">
                    <a href="/admin/blocks.php?create=1" class="btn btn-primary">Add New Block</a>
                    <button onclick="showReorderMode()" class="btn btn-secondary">Reorder Blocks</button>
                </div>
                
                <div class="blocks-grid">
                    <?php
                    $positions = ['left' => [], 'right' => [], 'top' => [], 'bottom' => [], 'footer' => [], 'custom' => []];
                    foreach ($blocks as $block) {
                        $positions[$block['position']][] = $block;
                    }
                    ?>
                    
                    <?php foreach ($positions as $position => $positionBlocks): ?>
                        <div class="position-column">
                            <h3><?= ucfirst($position) ?> Sidebar</h3>
                            <div class="blocks-list" data-position="<?= $position ?>">
                                <?php foreach ($positionBlocks as $block): ?>
                                    <div class="block-item" data-id="<?= $block['id'] ?>">
                                        <div class="block-header">
                                            <h4><?= htmlspecialchars($block['title']) ?></h4>
                                            <span class="block-type"><?= htmlspecialchars($block['block_type']) ?></span>
                                        </div>
                                        
                                        <div class="block-meta">
                                            Status: <?= $block['is_enabled'] ? 'Enabled' : 'Disabled' ?>
                                            | Order: <?= $block['sort_order'] ?>
                                        </div>
                                        
                                        <div class="block-actions">
                                            <a href="/admin/blocks.php?edit=<?= $block['id'] ?>" class="btn btn-sm">Edit</a>
                                            
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle">
                                                <input type="hidden" name="id" value="<?= $block['id'] ?>">
                                                <button type="submit" class="btn btn-sm">
                                                    <?= $block['is_enabled'] ? 'Disable' : 'Enable' ?>
                                                </button>
                                            </form>
                                            
                                            <form method="post" style="display: inline;" onsubmit="return confirm('Delete this block?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $block['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Block Editor Modal -->
<?php if (isset($_GET['create']) || $editBlock): ?>
<div class="modal-overlay" onclick="closeModal()">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3><?= $editBlock ? 'Edit Block' : 'Create New Block' ?></h3>
            <button onclick="closeModal()" class="close-btn">&times;</button>
        </div>
        
        <form method="post" class="block-form">
            <input type="hidden" name="action" value="<?= $editBlock ? 'update' : 'create' ?>">
            <?php if ($editBlock): ?>
                <input type="hidden" name="id" value="<?= $editBlock['id'] ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label>Block Title</label>
                <input type="text" name="title" value="<?= htmlspecialchars($editBlock['title'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Block Type</label>
                <select name="block_type" onchange="updateBlockFields(this.value)">
                    <?php foreach ($blockTypes as $type => $config): ?>
                        <option value="<?= $type ?>" <?= ($editBlock['block_type'] ?? 'html') === $type ? 'selected' : '' ?>>
                            <?= htmlspecialchars($config['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="help-text">Choose the type of block to create</small>
            </div>
            
            <div class="form-group">
                <label>Position</label>
                <select name="position">
                    <option value="left" <?= ($editBlock['position'] ?? 'right') === 'left' ? 'selected' : '' ?>>Left Sidebar</option>
                    <option value="right" <?= ($editBlock['position'] ?? 'right') === 'right' ? 'selected' : '' ?>>Right Sidebar</option>
                    <option value="top" <?= ($editBlock['position'] ?? 'right') === 'top' ? 'selected' : '' ?>>Top Banner</option>
                    <option value="bottom" <?= ($editBlock['position'] ?? 'right') === 'bottom' ? 'selected' : '' ?>>Bottom Banner</option>
                    <option value="footer" <?= ($editBlock['position'] ?? 'right') === 'footer' ? 'selected' : '' ?>>Footer</option>
                    <option value="custom" <?= ($editBlock['position'] ?? 'right') === 'custom' ? 'selected' : '' ?>>Custom</option>
                </select>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" value="<?= $editBlock['sort_order'] ?? 0 ?>" min="0">
                </div>
                
                <div class="form-group">
                    <label>CSS Class</label>
                    <input type="text" name="css_class" value="<?= htmlspecialchars($editBlock['css_class'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_enabled" <?= ($editBlock['is_enabled'] ?? 1) ? 'checked' : '' ?>>
                    Block Enabled
                </label>
            </div>
            
            <!-- Dynamic block-specific fields -->
            <div id="block-specific-fields">
                <?php if ($editBlock): ?>
                    <?php
                    $settings = json_decode($editBlock['settings'] ?? '{}', true) ?: [];
                    $blockConfig = $blockTypes[$editBlock['block_type']] ?? [];
                    ?>
                    <?php if (!empty($blockConfig['fields'])): ?>
                        <h4>Block Settings</h4>
                        <?php foreach ($blockConfig['fields'] as $fieldName => $fieldConfig): ?>
                            <div class="form-group">
                                <label><?= htmlspecialchars($fieldConfig['label']) ?></label>
                                <?php
                                $fieldValue = $settings[$fieldName] ?? ($fieldConfig['default'] ?? '');
                                renderFormField($fieldName, $fieldConfig, $fieldValue);
                                ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <!-- HTML Content field (for HTML blocks) -->
            <div class="form-group" id="content-field">
                <label>Content</label>
                <textarea name="content" rows="10"><?= htmlspecialchars($editBlock['content'] ?? '') ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $editBlock ? 'Update Block' : 'Create Block' ?>
                </button>
                <button type="button" onclick="closeModal()" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function closeModal() {
    window.location.href = '/admin/blocks.php';
}

function updateBlockFields(blockType) {
    // This would be enhanced with AJAX to load block-specific fields
    console.log('Block type changed to:', blockType);
}

function showReorderMode() {
    // Enable drag-and-drop reordering
    alert('Drag and drop functionality would be implemented here');
}
</script>

<?php
function renderFormField($fieldName, $fieldConfig, $value = '') {
    $type = $fieldConfig['type'] ?? 'text';
    $label = $fieldConfig['label'] ?? ucfirst($fieldName);
    
    switch ($type) {
        case 'textarea':
            echo '<textarea name="settings[' . htmlspecialchars($fieldName) . ']" rows="4">' . htmlspecialchars($value) . '</textarea>';
            break;
            
        case 'select':
            echo '<select name="settings[' . htmlspecialchars($fieldName) . ']">';
            if (isset($fieldConfig['options'])) {
                if (is_array($fieldConfig['options'])) {
                    foreach ($fieldConfig['options'] as $optValue => $optLabel) {
                        $selected = ($value == $optValue) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($optValue) . '" ' . $selected . '>' . htmlspecialchars($optLabel) . '</option>';
                    }
                }
            }
            echo '</select>';
            break;
            
        case 'checkbox':
            $checked = $value ? 'checked' : '';
            echo '<input type="checkbox" name="settings[' . htmlspecialchars($fieldName) . ']" value="1" ' . $checked . '>';
            break;
            
        case 'number':
            echo '<input type="number" name="settings[' . htmlspecialchars($fieldName) . ']" value="' . htmlspecialchars($value) . '">';
            break;
            
        default:
            echo '<input type="text" name="settings[' . htmlspecialchars($fieldName) . ']" value="' . htmlspecialchars($value) . '">';
            break;
    }
}

include __DIR__ . '/../themes/' . $siteTheme . '/footer.php';
?>