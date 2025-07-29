-- Create database
CREATE DATABASE IF NOT EXISTS dfw2;
USE dfw2;

-- Users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','moderator','user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Threads
CREATE TABLE threads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Posts (Replies)
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thread_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (thread_id) REFERENCES threads(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Files
CREATE TABLE files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    thread_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (thread_id) REFERENCES threads(id)
);
CREATE TABLE settings (
    `key` VARCHAR(100) PRIMARY KEY,
    `value` TEXT NOT NULL
);

-- Default settings
INSERT INTO settings (`key`, `value`) VALUES
('site_name', 'My PHP CMS'),
('site_description', 'A simple CMS with forum and admin panel'),
('theme', 'default');

-- Enhanced blocks table
CREATE TABLE blocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    content TEXT,
    block_type VARCHAR(50) DEFAULT 'html',
    position ENUM('left', 'right', 'footer', 'top', 'bottom', 'custom') DEFAULT 'right',
    is_enabled BOOLEAN DEFAULT 1,
    sort_order INT DEFAULT 0,
    css_class VARCHAR(255),
    settings JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Block cache table for performance
CREATE TABLE block_cache (
    cache_key VARCHAR(255) PRIMARY KEY,
    content LONGTEXT,
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Block positions table for custom positions
CREATE TABLE block_positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE,
    display_name VARCHAR(100),
    description TEXT,
    is_active BOOLEAN DEFAULT 1
);

-- Insert default block positions
INSERT INTO block_positions (name, display_name, description) VALUES
('left', 'Left Sidebar', 'Left sidebar blocks'),
('right', 'Right Sidebar', 'Right sidebar blocks'),
('top', 'Top Banner', 'Top of page blocks'),
('bottom', 'Bottom Banner', 'Bottom of page blocks'),
('footer', 'Footer', 'Footer blocks'),
('custom', 'Custom Position', 'Custom positioned blocks');

-- Sample blocks data
INSERT INTO blocks (title, content, block_type, position, settings) VALUES
('Welcome', '<p>Welcome to our forum! Join the discussion.</p>', 'html', 'right', '{}'),
('Recent Posts', '', 'recent_posts', 'right', '{"limit": 5}'),
('Navigation', '', 'menu', 'left', '{"menu_type": "sidebar", "show_icons": false}'),
('Site Statistics', '', 'user_stats', 'right', '{}'),
('Pages', '', 'pages_list', 'left', '{"show_description": true}');
CREATE TABLE pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    is_published TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
