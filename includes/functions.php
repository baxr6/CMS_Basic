<?php
// functions.php

function flash($key, $message = null) {
    if ($message) {
        $_SESSION['flash'][$key] = $message;
    } elseif (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check($token) {
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function load_settings() {
    global $pdo;
    global $settings;

    $stmt = $pdo->query("SELECT `key`, `value` FROM settings");
    $settings = [];
    foreach ($stmt as $row) {
        $settings[$row['key']] = $row['value'];
    }
}

function get_setting($key, $default = null) {
    global $settings;
    return $settings[$key] ?? $default;
}

// Enhanced breadcrumb functions for functions.php

class BreadcrumbManager {
    private $pdo;
    private $breadcrumbs = [];
    private $separator = '&raquo;';
    private $homeLabel = 'Home';
    private $homeUrl = '/';
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->addHome();
    }
    
    /**
     * Set breadcrumb options
     */
    public function setOptions($options = []) {
        if (isset($options['separator'])) $this->separator = $options['separator'];
        if (isset($options['home_label'])) $this->homeLabel = $options['home_label'];
        if (isset($options['home_url'])) $this->homeUrl = $options['home_url'];
        
        // Update home breadcrumb if options changed
        $this->breadcrumbs[0] = ['label' => $this->homeLabel, 'url' => $this->homeUrl, 'type' => 'home'];
    }
    
    /**
     * Add home breadcrumb
     */
    private function addHome() {
        $this->breadcrumbs[] = [
            'label' => $this->homeLabel, 
            'url' => $this->homeUrl, 
            'type' => 'home'
        ];
    }
    
    /**
     * Add a breadcrumb item
     */
    public function add($label, $url = null, $type = 'page') {
        $this->breadcrumbs[] = [
            'label' => $label,
            'url' => $url,
            'type' => $type
        ];
        return $this;
    }
    
    /**
     * Generate breadcrumbs automatically based on current path
     */
    public function generateDynamic() {
        $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $segments = array_filter(explode('/', $path));
        
        if (empty($segments)) {
            return $this; // Just home
        }
        
        // Route-specific breadcrumb generation
        switch ($segments[0]) {
            case 'forum':
                $this->generateForumBreadcrumbs($segments);
                break;
                
            case 'admin':
                $this->generateAdminBreadcrumbs($segments);
                break;
                
            case 'auth':
                $this->generateAuthBreadcrumbs($segments);
                break;
                
            case 'page.php':
                $this->generatePageBreadcrumbs();
                break;
                
            default:
                $this->generateGenericBreadcrumbs($segments);
                break;
        }
        
        return $this;
    }
    
    /**
     * Generate forum-specific breadcrumbs
     */
    private function generateForumBreadcrumbs($segments) {
        $this->add('Forum', '/forum/', 'section');
        
        if (!isset($segments[1])) return;
        
        $page = $segments[1];
        
        switch ($page) {
            case 'category.php':
                if (isset($_GET['id'])) {
                    $category = $this->getCategoryById($_GET['id']);
                    if ($category) {
                        $this->add($category['name'], null, 'category');
                    }
                }
                break;
                
            case 'thread.php':
                if (isset($_GET['id'])) {
                    $thread = $this->getThreadById($_GET['id']);
                    if ($thread) {
                        $this->add(
                            $thread['cat_name'], 
                            '/forum/category.php?id=' . $thread['cat_id'], 
                            'category'
                        );
                        $this->add($thread['title'], null, 'thread');
                    }
                }
                break;
                
            case 'search.php':
                $this->add('Search', null, 'page');
                break;
                
            case 'new_thread.php':
                if (isset($_GET['category_id'])) {
                    $category = $this->getCategoryById($_GET['category_id']);
                    if ($category) {
                        $this->add(
                            $category['name'], 
                            '/forum/category.php?id=' . $category['id'], 
                            'category'
                        );
                    }
                }
                $this->add('New Thread', null, 'action');
                break;
        }
    }
    
    /**
     * Generate admin breadcrumbs
     */
    private function generateAdminBreadcrumbs($segments) {
        $this->add('Admin', '/admin/dashboard.php', 'section');
        
        if (empty($segments[1]) || $segments[1] === 'dashboard.php') {
            $this->add('Dashboard', null, 'page');
            return;
        }
        
        $page = str_replace('.php', '', $segments[1]);
        $pageLabels = [
            'users' => 'Users',
            'settings' => 'Settings',
            'blocks' => 'Blocks',
            'categories' => 'Categories',
            'pages' => 'Pages',
            'themes' => 'Themes'
        ];
        
        $label = $pageLabels[$page] ?? ucfirst($page);
        $this->add($label, null, 'page');
        
        // Handle sub-actions
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            $actionLabels = [
                'edit' => 'Edit',
                'create' => 'Create',
                'delete' => 'Delete',
                'view' => 'View'
            ];
            
            $actionLabel = $actionLabels[$action] ?? ucfirst($action);
            $this->add($actionLabel, null, 'action');
        }
    }
    
    /**
     * Generate auth breadcrumbs
     */
    private function generateAuthBreadcrumbs($segments) {
        if (!isset($segments[1])) return;
        
        $page = str_replace('.php', '', $segments[1]);
        $labels = [
            'login' => 'Login',
            'register' => 'Register',
            'forgot' => 'Forgot Password',
            'reset' => 'Reset Password'
        ];
        
        $label = $labels[$page] ?? ucfirst($page);
        $this->add($label, null, 'auth');
    }
    
    /**
     * Generate page breadcrumbs
     */
    private function generatePageBreadcrumbs() {
        if (isset($_GET['slug'])) {
            $page = $this->getPageBySlug($_GET['slug']);
            if ($page) {
                $this->add($page['title'], null, 'page');
            }
        }
    }
    
    /**
     * Generate generic breadcrumbs for unknown routes
     */
    private function generateGenericBreadcrumbs($segments) {
        foreach ($segments as $segment) {
            $label = ucfirst(str_replace(['.php', '_', '-'], [' ', ' ', ' '], $segment));
            $this->add($label, null, 'page');
        }
    }
    
    /**
     * Get category by ID
     */
    private function getCategoryById($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, name, slug FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Get thread with category info by ID
     */
    private function getThreadById($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT t.title, t.slug, c.id AS cat_id, c.name AS cat_name, c.slug AS cat_slug
                FROM threads t 
                JOIN categories c ON t.category_id = c.id
                WHERE t.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Get page by slug
     */
    private function getPageBySlug($slug) {
        try {
            $stmt = $this->pdo->prepare("SELECT title, slug FROM pages WHERE slug = ? AND is_published = 1");
            $stmt->execute([$slug]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Render breadcrumbs as HTML
     */
    public function render($template = 'default') {
        switch ($template) {
            case 'bootstrap':
                return $this->renderBootstrap();
            case 'json-ld':
                return $this->renderJsonLd();
            case 'minimal':
                return $this->renderMinimal();
            default:
                return $this->renderDefault();
        }
    }
    
    /**
     * Default breadcrumb rendering
     */
    private function renderDefault() {
        if (count($this->breadcrumbs) <= 1) {
            return ''; // Don't show breadcrumbs if only home
        }
        
        $html = '<nav class="breadcrumb" aria-label="Breadcrumb navigation">';
        $html .= '<ol class="breadcrumb-list">';
        
        $count = count($this->breadcrumbs);
        
        foreach ($this->breadcrumbs as $i => $item) {
            $isLast = ($i === $count - 1);
            $itemClass = 'breadcrumb-item breadcrumb-' . $item['type'];
            
            if ($isLast) {
                $itemClass .= ' breadcrumb-current';
            }
            
            $html .= '<li class="' . $itemClass . '">';
            
            if (!empty($item['url']) && !$isLast) {
                $html .= '<a href="' . htmlspecialchars($item['url']) . '" class="breadcrumb-link">';
                $html .= htmlspecialchars($item['label']);
                $html .= '</a>';
            } else {
                $html .= '<span class="breadcrumb-text">' . htmlspecialchars($item['label']) . '</span>';
            }
            
            if (!$isLast) {
                $html .= '<span class="breadcrumb-separator"> ' . $this->separator . ' </span>';
            }
            
            $html .= '</li>';
        }
        
        $html .= '</ol>';
        $html .= '</nav>';
        
        return $html;
    }
    
    /**
     * Bootstrap-style breadcrumb rendering
     */
    private function renderBootstrap() {
        if (count($this->breadcrumbs) <= 1) {
            return '';
        }
        
        $html = '<nav aria-label="breadcrumb">';
        $html .= '<ol class="breadcrumb">';
        
        $count = count($this->breadcrumbs);
        
        foreach ($this->breadcrumbs as $i => $item) {
            $isLast = ($i === $count - 1);
            $itemClass = 'breadcrumb-item';
            
            if ($isLast) {
                $itemClass .= ' active';
            }
            
            $html .= '<li class="' . $itemClass . '"';
            
            if ($isLast) {
                $html .= ' aria-current="page"';
            }
            
            $html .= '>';
            
            if (!empty($item['url']) && !$isLast) {
                $html .= '<a href="' . htmlspecialchars($item['url']) . '">';
                $html .= htmlspecialchars($item['label']);
                $html .= '</a>';
            } else {
                $html .= htmlspecialchars($item['label']);
            }
            
            $html .= '</li>';
        }
        
        $html .= '</ol>';
        $html .= '</nav>';
        
        return $html;
    }
    
    /**
     * Minimal breadcrumb rendering
     */
    private function renderMinimal() {
        if (count($this->breadcrumbs) <= 1) {
            return '';
        }
        
        $html = '<div class="breadcrumb-minimal">';
        $parts = [];
        
        foreach ($this->breadcrumbs as $i => $item) {
            $isLast = ($i === count($this->breadcrumbs) - 1);
            
            if (!empty($item['url']) && !$isLast) {
                $parts[] = '<a href="' . htmlspecialchars($item['url']) . '">' . 
                          htmlspecialchars($item['label']) . '</a>';
            } else {
                $parts[] = '<span>' . htmlspecialchars($item['label']) . '</span>';
            }
        }
        
        $html .= implode(' ' . $this->separator . ' ', $parts);
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * JSON-LD structured data for SEO
     */
    private function renderJsonLd() {
        if (count($this->breadcrumbs) <= 1) {
            return '';
        }
        
        $listItems = [];
        
        foreach ($this->breadcrumbs as $i => $item) {
            $position = $i + 1;
            $listItem = [
                "@type" => "ListItem",
                "position" => $position,
                "name" => $item['label']
            ];
            
            if (!empty($item['url'])) {
                $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
                $listItem["item"] = $baseUrl . $item['url'];
            }
            
            $listItems[] = $listItem;
        }
        
        $breadcrumbList = [
            "@context" => "https://schema.org",
            "@type" => "BreadcrumbList",
            "itemListElement" => $listItems
        ];
        
        return '<script type="application/ld+json">' . 
               json_encode($breadcrumbList, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . 
               '</script>';
    }
    
    /**
     * Get breadcrumbs as array (for custom rendering)
     */
    public function toArray() {
        return $this->breadcrumbs;
    }
    
    /**
     * Clear all breadcrumbs except home
     */
    public function clear() {
        $this->breadcrumbs = [];
        $this->addHome();
        return $this;
    }
    
    /**
     * Remove last breadcrumb
     */
    public function pop() {
        if (count($this->breadcrumbs) > 1) {
            array_pop($this->breadcrumbs);
        }
        return $this;
    }
}

// Updated functions for backward compatibility
function generate_dynamic_breadcrumb($template = 'default', $options = []): string {
    global $pdo;
    
    $breadcrumb = new BreadcrumbManager($pdo);
    
    if (!empty($options)) {
        $breadcrumb->setOptions($options);
    }
    
    return $breadcrumb->generateDynamic()->render($template);
}

function render_breadcrumb(array $items, $template = 'default'): string {
    global $pdo;
    
    $breadcrumb = new BreadcrumbManager($pdo);
    $breadcrumb->clear(); // Remove default home
    
    foreach ($items as $item) {
        $breadcrumb->add(
            $item['label'], 
            $item['url'] ?? null, 
            $item['type'] ?? 'page'
        );
    }
    
    return $breadcrumb->render($template);
}

// Example usage in your pages:
/*
// Manual breadcrumb creation
$breadcrumb = new BreadcrumbManager($pdo);
$breadcrumb->add('Products', '/products/')
          ->add('Electronics', '/products/electronics/')
          ->add('Smartphones', null);
echo $breadcrumb->render();

// With options
echo generate_dynamic_breadcrumb('bootstrap', [
    'separator' => '/',
    'home_label' => 'Start'
]);

// JSON-LD for SEO
echo generate_dynamic_breadcrumb('json-ld');
*/

// Enhanced functions.php - Modular Blocks System

class BlockManager {
    private $pdo;
    private $registeredBlocks = [];
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->loadBlockTypes();
    }
    
    /**
     * Register a custom block type
     */
    public function registerBlockType($type, $config) {
        $this->registeredBlocks[$type] = array_merge([
            'title' => ucfirst($type),
            'description' => '',
            'fields' => [],
            'template' => null,
            'callback' => null,
            'cache' => false,
            'permissions' => []
        ], $config);
    }
    
    /**
     * Load default block types
     */
    private function loadBlockTypes() {
        // HTML Block
        $this->registerBlockType('html', [
            'title' => 'HTML Block',
            'description' => 'Custom HTML content',
            'fields' => [
                'content' => ['type' => 'textarea', 'label' => 'HTML Content']
            ]
        ]);
        
        // Menu Block
        $this->registerBlockType('menu', [
            'title' => 'Navigation Menu',
            'description' => 'Dynamic navigation menu',
            'fields' => [
                'menu_type' => ['type' => 'select', 'label' => 'Menu Type', 'options' => ['main', 'footer', 'sidebar']],
                'show_icons' => ['type' => 'checkbox', 'label' => 'Show Icons']
            ],
            'callback' => [$this, 'renderMenuBlock']
        ]);
        
        // Recent Posts Block
        $this->registerBlockType('recent_posts', [
            'title' => 'Recent Posts',
            'description' => 'Display recent forum posts',
            'fields' => [
                'limit' => ['type' => 'number', 'label' => 'Number of Posts', 'default' => 5],
                'category_id' => ['type' => 'select', 'label' => 'Category Filter', 'options' => 'categories']
            ],
            'callback' => [$this, 'renderRecentPostsBlock'],
            'cache' => true
        ]);
        
        // User Stats Block
        $this->registerBlockType('user_stats', [
            'title' => 'User Statistics',
            'description' => 'Display user registration stats',
            'callback' => [$this, 'renderUserStatsBlock'],
            'cache' => true
        ]);
        
        // Pages List Block
        $this->registerBlockType('pages_list', [
            'title' => 'Pages List',
            'description' => 'List of published pages',
            'fields' => [
                'show_description' => ['type' => 'checkbox', 'label' => 'Show Descriptions']
            ],
            'callback' => [$this, 'renderPagesListBlock']
        ]);
    }
    
    /**
     * Render blocks for a specific position
     */
    public function renderBlocks($position = 'right', $context = []) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM blocks 
                WHERE is_enabled = 1 AND position = ? 
                ORDER BY sort_order ASC, id ASC
            ");
            $stmt->execute([$position]);
            $blocks = $stmt->fetchAll();
            
            foreach ($blocks as $block) {
                $this->renderSingleBlock($block, $context);
            }
        } catch (PDOException $e) {
            echo '<div class="block"><div class="block-content">Error loading blocks: ' . htmlspecialchars($e->getMessage()) . '</div></div>';
        }
    }
    
    /**
     * Render a single block
     */
    public function renderSingleBlock($block, $context = []) {
        try {
            $blockType = $block['block_type'] ?? 'html';
            $blockConfig = $this->registeredBlocks[$blockType] ?? $this->registeredBlocks['html'];
            
            // Check permissions
            if (!empty($blockConfig['permissions']) && !$this->checkPermissions($blockConfig['permissions'])) {
                return;
            }
            
            // Try cache first
            $cacheKey = "block_{$block['id']}_" . md5(serialize($context));
            $cachedContent = null;
            
            if ($blockConfig['cache']) {
                $cachedContent = $this->getCache($cacheKey);
            }
            
            if ($cachedContent === null) {
                // Generate content
                $content = $this->generateBlockContent($block, $blockConfig, $context);
                
                if ($blockConfig['cache']) {
                    $this->setCache($cacheKey, $content, 300); // 5 minutes
                }
            } else {
                $content = $cachedContent;
            }
            
            // Render with wrapper
            $this->renderBlockWrapper($block, $content, $blockConfig);
        } catch (Exception $e) {
            echo '<div class="block"><div class="block-content">Error rendering block: ' . htmlspecialchars($e->getMessage()) . '</div></div>';
        }
    }
    
    /**
     * Generate block content
     */
    private function generateBlockContent($block, $config, $context) {
        // Decode block settings
        $settings = json_decode($block['settings'] ?? '{}', true) ?: [];
        
        // Use custom callback if available
        if (!empty($config['callback']) && is_callable($config['callback'])) {
            return call_user_func($config['callback'], $block, $settings, $context);
        }
        
        // Use custom template if available
        if (!empty($config['template']) && file_exists($config['template'])) {
            ob_start();
            extract(['block' => $block, 'settings' => $settings, 'context' => $context]);
            include $config['template'];
            return ob_get_clean();
        }
        
        // Default: return content field
        return $block['content'] ?? '';
    }
    
    /**
     * Render block wrapper
     */
    private function renderBlockWrapper($block, $content, $config) {
        $wrapperClass = 'block block-' . ($block['block_type'] ?? 'html');
        if (!empty($block['css_class'])) {
            $wrapperClass .= ' ' . htmlspecialchars($block['css_class']);
        }
        
        echo '<div class="' . $wrapperClass . '" data-block-id="' . $block['id'] . '">';
        
        if (!empty($block['title'])) {
            echo '<h3 class="block-title">' . htmlspecialchars($block['title']) . '</h3>';
        }
        
        echo '<div class="block-content">' . $content . '</div>';
        echo '</div>';
    }
    
    /**
     * Block callbacks
     */
    public function renderMenuBlock($block, $settings, $context) {
        $menuType = $settings['menu_type'] ?? 'main';
        $showIcons = $settings['show_icons'] ?? false;
        
        $html = '<ul class="block-menu menu-' . htmlspecialchars($menuType) . '">';
        
        switch ($menuType) {
            case 'main':
                $html .= '<li><a href="/index.php">Home</a></li>';
                $html .= '<li><a href="/forum/index.php">Forum</a></li>';
                if (!empty($_SESSION['user_id'])) {
                    $html .= '<li><a href="/notifications.php">Notifications</a></li>';
                    if ($_SESSION['role'] === 'admin') {
                        $html .= '<li><a href="/admin/dashboard.php">Admin</a></li>';
                    }
                    $html .= '<li><a href="/auth/logout.php">Logout</a></li>';
                } else {
                    $html .= '<li><a href="/auth/login.php">Login</a></li>';
                    $html .= '<li><a href="/auth/register.php">Register</a></li>';
                }
                break;
                
            case 'sidebar':
                try {
                    $stmt = $this->pdo->query("SELECT title, slug FROM pages WHERE is_published = 1 ORDER BY title ASC");
                    while ($page = $stmt->fetch()) {
                        $html .= '<li><a href="/page.php?slug=' . urlencode($page['slug']) . '">' . 
                               htmlspecialchars($page['title']) . '</a></li>';
                    }
                } catch (PDOException $e) {
                    $html .= '<li>Error loading pages</li>';
                }
                break;
        }
        
        $html .= '</ul>';
        return $html;
    }
    
    public function renderRecentPostsBlock($block, $settings, $context) {
        $limit = intval($settings['limit'] ?? 5);
        $categoryId = $settings['category_id'] ?? null;
        
        try {
            if ($categoryId) {
                $sql = "SELECT p.*, t.title as thread_title, u.username, c.name as category_name
                        FROM posts p 
                        JOIN threads t ON p.thread_id = t.id 
                        JOIN users u ON p.user_id = u.id 
                        JOIN categories c ON t.category_id = c.id 
                        WHERE t.category_id = ?
                        ORDER BY p.created_at DESC 
                        LIMIT " . $limit;
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$categoryId]);
            } else {
                $sql = "SELECT p.*, t.title as thread_title, u.username, c.name as category_name
                        FROM posts p 
                        JOIN threads t ON p.thread_id = t.id 
                        JOIN users u ON p.user_id = u.id 
                        JOIN categories c ON t.category_id = c.id 
                        ORDER BY p.created_at DESC 
                        LIMIT " . $limit;
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();
            }
        
            $html = '<ul class="recent-posts">';
            while ($post = $stmt->fetch()) {
                $html .= '<li>';
                $html .= '<a href="/forum/thread.php?id=' . $post['thread_id'] . '#post-' . $post['id'] . '">';
                $html .= htmlspecialchars($post['thread_title']);
                $html .= '</a>';
                $html .= '<small> by ' . htmlspecialchars($post['username']) . '</small>';
                $html .= '</li>';
            }
            $html .= '</ul>';
            
            return $html;
        } catch (PDOException $e) {
            return '<p>Error loading recent posts: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }
    
    public function renderUserStatsBlock($block, $settings, $context) {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) as total_users FROM users");
            $totalUsers = $stmt->fetchColumn();
            
            $stmt = $this->pdo->query("SELECT COUNT(*) as total_posts FROM posts");
            $totalPosts = $stmt->fetchColumn();
            
            $stmt = $this->pdo->query("SELECT COUNT(*) as total_threads FROM threads");
            $totalThreads = $stmt->fetchColumn();
            
            $html = '<div class="user-stats">';
            $html .= '<div class="stat-item">Users: <strong>' . number_format($totalUsers) . '</strong></div>';
            $html .= '<div class="stat-item">Posts: <strong>' . number_format($totalPosts) . '</strong></div>';
            $html .= '<div class="stat-item">Threads: <strong>' . number_format($totalThreads) . '</strong></div>';
            $html .= '</div>';
            
            return $html;
        } catch (PDOException $e) {
            return '<p>Error loading statistics: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }
    
    public function renderPagesListBlock($block, $settings, $context) {
        $showDescription = $settings['show_description'] ?? false;
        
        try {
            // Check if description column exists
            $hasDescription = $this->columnExists('pages', 'description');
            
            if ($hasDescription && $showDescription) {
                $sql = "SELECT title, slug, description FROM pages WHERE is_published = 1 ORDER BY title ASC";
            } else {
                $sql = "SELECT title, slug FROM pages WHERE is_published = 1 ORDER BY title ASC";
            }
            
            $stmt = $this->pdo->query($sql);
            
            $html = '<ul class="pages-list">';
            while ($page = $stmt->fetch()) {
                $html .= '<li>';
                $html .= '<a href="/page.php?slug=' . urlencode($page['slug']) . '">';
                $html .= htmlspecialchars($page['title']);
                $html .= '</a>';
                
                if ($hasDescription && $showDescription && !empty($page['description'])) {
                    $html .= '<div class="page-description">' . htmlspecialchars($page['description']) . '</div>';
                }
                
                $html .= '</li>';
            }
            $html .= '</ul>';
            
            return $html;
        } catch (PDOException $e) {
            return '<p>Error loading pages: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }
    
    /**
     * Check if a column exists in a table
     */
    private function columnExists($table, $column) {
        try {
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
            $stmt->execute([$column]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get registered block types for admin interface
     */
    public function getRegisteredBlockTypes() {
        return $this->registeredBlocks;
    }
    
    /**
     * Simple cache methods (you can replace with Redis/Memcached)
     */
    private function getCache($key) {
        try {
            $stmt = $this->pdo->prepare("SELECT content, expires_at FROM block_cache WHERE cache_key = ?");
            $stmt->execute([$key]);
            $cache = $stmt->fetch();
            
            if ($cache && strtotime($cache['expires_at']) > time()) {
                return $cache['content'];
            }
        } catch (PDOException $e) {
            // Cache table might not exist, silently fail
        }
        
        return null;
    }
    
    private function setCache($key, $content, $ttl = 300) {
        try {
            $expiresAt = date('Y-m-d H:i:s', time() + $ttl);
            $stmt = $this->pdo->prepare("
                INSERT INTO block_cache (cache_key, content, expires_at) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE content = VALUES(content), expires_at = VALUES(expires_at)
            ");
            $stmt->execute([$key, $content, $expiresAt]);
        } catch (PDOException $e) {
            // Cache table might not exist, silently fail
        }
    }
    
    /**
     * Check user permissions
     */
    private function checkPermissions($permissions) {
        if (empty($permissions)) return true;
        
        foreach ($permissions as $permission) {
            switch ($permission) {
                case 'admin':
                    if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') return false;
                    break;
                case 'logged_in':
                    if (empty($_SESSION['user_id'])) return false;
                    break;
                case 'guest':
                    if (!empty($_SESSION['user_id'])) return false;
                    break;
            }
        }
        
        return true;
    }
}

// Initialize the block manager
global $blockManager;
$blockManager = new BlockManager($pdo);

// Updated render_blocks function for backward compatibility
function render_blocks($position = 'right', $context = []) {
    global $blockManager;
    $blockManager->renderBlocks($position, $context);
}

// Register custom block types (example)
$blockManager->registerBlockType('custom_widget', [
    'title' => 'Custom Widget',
    'description' => 'A custom widget block',
    'fields' => [
        'widget_title' => ['type' => 'text', 'label' => 'Widget Title'],
        'widget_content' => ['type' => 'textarea', 'label' => 'Widget Content']
    ],
    'template' => __DIR__ . '/templates/blocks/custom_widget.php'
]);