# 📦 CampusFind – Smart Lost & Found Portal

## 📌 Project Overview

CampusFind is a web-based Lost & Found Management System designed to help students, faculty, and staff report, search, and recover lost belongings within a campus environment.

The platform provides a centralized notice board where users can post lost item notices, register found items, submit ownership claims, and track recovery status. An administrative dashboard enables efficient management of users, items, and claims.

---

## 🎯 Objectives

* Digitize the campus lost-and-found process.
* Provide a centralized platform for reporting lost and found items.
* Enable secure ownership verification through claim requests.
* Reduce the time required to recover lost belongings.
* Improve transparency and communication between owners and finders.

---

## ✨ Features

### 👤 User Authentication

* User Registration
* Secure Login & Logout
* Session Management
* Password Hashing

### 📢 Lost Item Reporting

* Create Lost Item Notices
* Add Item Description
* Specify Lost Location
* Add Contact Information
* Track Notice Status

### 🔍 Found Item Registration

* Register Found Items
* Categorize Items
* View Detailed Item Information
* Update Item Status

### ✅ Claim Verification System

* Submit Ownership Claims
* Verify Ownership Through Questions
* Claim Approval Workflow

### 📋 Notice Board

* Public Lost Item Notice Board
* Browse Active Notices
* Search and Filter Items

### 📊 Dashboard

* View Personal Reports
* Track Claims
* Monitor Item Recovery Progress

### 🛡️ Admin Panel

* Manage Users
* Manage Items
* Monitor System Activity
* Administrative Authentication

---

## 🛠️ Technology Stack

### Frontend

* HTML5
* CSS3
* JavaScript
* Bootstrap

### Backend

* PHP

### Database

* MySQL

### Development Tools

* XAMPP
* Visual Studio Code
* Git & GitHub

---

## 📂 Project Structure

```text
CampusFind/
│
├── admin/
├── auth/
├── config/
├── includes/
├── items/
├── pages/
├── assets/
├── campusfind.sql
├── index.php
└── README.md
```

---

## ⚙️ Installation

### Prerequisites

* PHP 8.0+
* MySQL
* XAMPP/WAMP

### Clone Repository

```bash
git clone https://github.com/Nandanad3/Smart-lost-found-portal.git
```

### Move Project

Place the project folder inside:

```text
xampp/htdocs/
```

### Create Database

1. Open phpMyAdmin
2. Create a database named `campusfind`
3. Import the `campusfind.sql` file

### Configure Database

Edit the database credentials in:

```php
config/db.php
```

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'campusfind');
```

### Run the Project

Start Apache and MySQL from XAMPP.

Open:

```text
http://localhost/campusfind
```

---

## 🔄 Workflow

1. User registers and logs in.
2. User reports a lost or found item.
3. Item details are stored in the database.
4. Users browse available notices.
5. Owners submit claims for matching items.
6. Verification is performed.
7. Item status is updated after successful recovery.

---

## 🔒 Security Features

* Password Hashing
* Session-Based Authentication
* Role-Based Access Control
* Protected Administrative Portal
* Secure Database Queries

---

## 🚀 Future Enhancements

* AI-Based Item Matching
* Image Recognition for Lost Items
* Email Notifications
* Mobile Application Support
* Real-Time Chat System
* QR Code Verification
* Advanced Search and Recommendation System

---

## 🎓 Academic Project

**Project Title:** CampusFind – Smart Lost & Found Portal

**Project Type:** MCA Final Year Project

**Domain:** Web Development

**Technology Stack:** PHP, MySQL, HTML, CSS, JavaScript

---

## 👩‍💻 Author

**Nandana Dinesh A**

MCA Student

GitHub: https://github.com/Nandanad3

---

## 📜 License

This project is developed for educational and academic purposes only.

---

## 🙏 Acknowledgements

* PHP Community
* MySQL Documentation
* XAMPP
* Open Source Contributors
* Faculty Mentors and Project Guide

---

⭐ CampusFind – Making Lost Items Easier to Find and Return Within the Campus Community.

