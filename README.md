📦 CampusFind – Smart Lost & Found Portal
📌 Project Overview

CampusFind is a web-based Lost & Found Management System developed to help students, faculty, and staff efficiently report, search, and recover lost belongings within a campus environment.

The platform provides a centralized notice board where users can post lost item notices, register found items, submit ownership claims, and track recovery status. An administrative dashboard enables efficient management of users, items, and claims.

🎯 Objectives
Digitize the campus lost-and-found process.
Provide a centralized platform for reporting lost and found items.
Enable secure ownership verification through claim requests.
Reduce the time required to recover lost belongings.
Improve transparency and communication between owners and finders.
✨ Key Features
👤 User Authentication
User Registration
Secure Login & Logout
Session Management
Password Hashing for Security
📢 Lost Item Reporting
Create Lost Item Notices
Add Item Description
Specify Lost Location
Add Contact Information
Track Notice Status
🔍 Found Item Registration
Register Found Items
Categorize Items
View Detailed Item Information
Update Item Status
✅ Claim Verification System
Submit Ownership Claims
Verify Ownership Through Questions
Claim Approval Workflow
📋 Notice Board
Public Lost Item Notice Board
Browse Active Notices
Search and Filter Items
📊 Dashboard
View Personal Reports
Track Claims
Monitor Item Recovery Progress
🛡️ Admin Panel
Manage Users
Manage Items
Monitor System Activity
Administrative Authentication
🛠️ Technology Stack
Frontend
HTML5
CSS3
JavaScript
Backend
PHP
Database
MySQL
Server Environment
XAMPP / Apache Server
Development Tools
Visual Studio Code
Git & GitHub
📂 Project Structure
CampusFind/
│
├── admin/
│   ├── dashboard.php
│   ├── items.php
│   ├── users.php
│   └── login.php
│
├── auth/
│   ├── login.php
│   ├── signup.php
│   └── logout.php
│
├── config/
│   └── db.php
│
├── includes/
│   ├── header.php
│   └── footer.php
│
├── items/
│   ├── report.php
│   ├── report-lost.php
│   ├── claim.php
│   ├── update-status.php
│   └── update-lost-notice.php
│
├── pages/
│   ├── home.php
│   ├── dashboard.php
│   ├── notice-board.php
│   └── item-detail.php
│
├── assets/
│
├── campusfind.sql
├── index.php
└── README.md
⚙️ Installation Guide
Prerequisites
PHP 8.0+
MySQL
XAMPP/WAMP Server
Web Browser
Step 1: Clone Repository
git clone https://github.com/Nandanad3/Smart-lost-found-portal.git
Step 2: Move Project

Place the project folder inside:

xampp/htdocs/
Step 3: Create Database
Open phpMyAdmin.
Create a database named:
campusfind
Import:
campusfind.sql
Step 4: Configure Database

Update database credentials in:

config/db.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'campusfind');
Step 5: Run Project

Start:

Apache
MySQL

Open:

http://localhost/campusfind
🔄 System Workflow
User creates an account.
User logs into the portal.
User reports a lost or found item.
Item details are stored in the database.
Other users browse available notices.
Owners submit claims for matching items.
Verification is performed.
Item status is updated after successful recovery.
🔒 Security Features
Password Hashing using PHP Password API
Session-Based Authentication
Role-Based Access Control
Protected Administrative Portal
Secure Database Queries using PDO Prepared Statements
📊 Database Modules
Users

Stores registered user information.

Items

Stores found item details.

Lost Notices

Stores lost item reports.

Claims

Tracks ownership verification requests.

Admins

Manages administrative access.

🚀 Future Enhancements
AI-Based Item Matching
Image Recognition for Lost Items
Email Notifications
Mobile Application Integration
Real-Time Chat Between Users
QR Code-Based Item Verification
Advanced Search & Recommendation System
🎓 Academic Project Details

Project Title: CampusFind – Smart Lost & Found Portal
Project Type: MCA Final Year Project
Domain: Web Development
Technology: PHP, MySQL, HTML, CSS, JavaScript
Development Environment: XAMPP

👩‍💻 Author

Nandana Dinesh A
MCA Student
GitHub: Nandanad3 GitHub Profile

🙏 Acknowledgements
PHP Community
MySQL Documentation
XAMPP
Open Source Contributors
Faculty Mentors and Project Guide
🌟 CampusFind – Making Lost Items Easier to Find and Return Within the Campus Community.
