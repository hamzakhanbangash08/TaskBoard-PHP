# TaskBoard-PHP App (Plain PHP + MySQL)



## Features
- Add tasks with optional deadlines
- Mark tasks complete/incomplete
- Edit task title or deadline
- Delete tasks (with CSRF protection)
- Filter: All / Active / Completed
- Minimal styling, clean code, HTML escaping

---

## 1) Requirements
- PHP 8.0+
- MySQL/MariaDB
- A local server stack like XAMPP / WAMP / Laragon / MAMP

## 2) Setup
1. Create a database (e.g. `todo_app`) and run the SQL in `schema.sql`.
2. Copy this folder to your web root (e.g., `htdocs/todo-php` or `www/todo-php`).
3. Open `config.php` and set your DB credentials.
4. Start Apache + MySQL.
5. Visit: `http://localhost/todo-php/index.php`

## 3) Structure
```text
todo-php/
├─ config.php
├─ csrf.php
├─ functions.php
├─ index.php
├─ create.php
├─ edit.php
├─ update.php
├─ toggle.php
├─ delete.php
├─ styles.css
└─ schema.sql
```

## 4) Notes
- This is intentionally minimal to keep things easy to follow.
- For production, consider login/auth, stronger validation, and proper routing.
- Always validate and sanitize user inputs.
