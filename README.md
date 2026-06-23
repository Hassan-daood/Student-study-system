# CS10 — Smart Study Planner with Secure Backend

This website is a dynamic database-driven application designed to help 10th-grade students organize their academic schedule and monitor milestone progress. This system automatically saves the student's study progress to the database in the background whenever they check or uncheck a topic. This ensures their study logs and completed milestones are permanently saved and can be accessed from any device securely.

## 🚀 Features
* **Secure Authentication:** Multi-user login system with structured sessions (`student01` to `student70`).
* **Temporary Password Setup:** Forces users to securely change their initial password upon first login.
* **Real-time Progress Sync:** Automatically saves and retrieves checked/unchecked topic states directly using background AJAX requests.
* **Centralized Storage:** Replaces device-specific local storage with a permanent MySQL database.
* **Activity Logs:** Tracks application actions like logins and logouts for security.

## 🛠️ Tech Stack
* **Frontend:** HTML5, CSS3 (Tajawal/Amiri typography), JavaScript (Fetch API)
* **Backend:** PHP 8.x (PDO Extension)
* **Database:** MySQL (via phpMyAdmin)
* **Local Server:** XAMPP (Apache)

## 📁 Database Schema
The system runs on the `study_planner` database containing:
1. `users` - Manages unique student identifiers and encrypted passwords.
2. `planner_records` - Dynamically stores student milestone ticks in a clean JSON string (`LONGTEXT`).
3. `activity_logs` - Logs traffic and login attempts.

## ⚙️ How to Setup Locally
1. Clone this repository or copy the project folder into your XAMPP directory: `C:/xampp/htdocs/`
2. Start **Apache** and **MySQL** modules from the XAMPP Control Panel.
3. Open your browser and navigate to `http://localhost/phpmyadmin/`.
4. Create a new database named `study_planner`.
5. Run the provided initialization script in your browser: `http://localhost/[your-folder-name]/setup_database.php` to auto-create and seed the tables.
6. Open `http://localhost/[your-folder-name]/login.html` and use a standard login (e.g., `student01` with default password) to start tracking!
