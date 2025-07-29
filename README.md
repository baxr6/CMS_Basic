# PHP CMS with Forum, Admin Panel, User Roles, Theming, and File Downloads

A lightweight content management system built with PHP and MySQL featuring:
- User roles (`admin`, `moderator`, `user`)
- Forum with categories, threads, and replies
- Admin area for user and content management
- File upload and download system
- Theme support (with default Bootstrap theme)
- Flash messaging and CSRF protection

---

## 🛠 Installation Instructions

### 1. Clone the Repository

```bash
git clone https://github.com/YOUR_USERNAME/php-cms-forum-system.git
cd php-cms-forum-system
```

### 2. Set Up the Database

- Import the `database.sql` file using phpMyAdmin or MySQL CLI:

```bash
mysql -u root -p cms_db < database.sql
```

- Create a MySQL user with access or update `includes/config.php` to use root.

### 3. Configure the Project

- Edit `includes/config.php` with your database credentials.
- Ensure file write access to `/uploads/` and `/downloads/` directories.

### 4. Start the Server

Using PHP’s built-in server:

```bash
php -S localhost:8000
```

Then visit: [http://localhost:8000](http://localhost:8000)

---

## 🔐 Default Admin Account

| Username | Password |
|----------|----------|
| admin    | admin123 |

Make sure to change this after first login.

---

## 📂 Folder Structure

```
/admin       → Admin dashboard
/auth        → Login, register, logout
/forum       → Forum (categories, threads, replies)
/includes    → Config, DB, auth helpers
/themes      → Theming support
/uploads     → File upload storage
/downloads   → Secure download script
```

---

## 📝 License

MIT

