# 🩸 REDSTREAM: Real-Time Blood Availability System
## Setup Guide

A comprehensive guide to setting up and running the REDSTREAM system.

---

## 📌 Prerequisites

Before you begin, ensure you have the following installed:

- 🔹 [XAMPP](https://www.apachefriends.org/index.html) - A local web server to run Apache, MySQL, and PHP
- 🔹 [Composer](https://getcomposer.org/) - A PHP dependency manager for installing required packages
- 🔹 [Twilio](https://www.twilio.com/) (optional) - To enable SMS notifications

> **Note:** All components should be installed before proceeding with the setup.

---

## ⚙️ Setting Up XAMPP and Database

### 🚀 Steps:

1️⃣ Open **XAMPP Control Panel** and start **Apache** & **MySQL**.

2️⃣ Open your browser and go to: 
```
http://localhost/phpmyadmin/
```

3️⃣ Click **New**, enter `bloodbank` as the database name, and click **Create**.

4️⃣ Import the database file (`db.sql`) into phpMyAdmin:
   - Select the `bloodbank` database
   - Navigate to the **Import** tab
   - Choose `db.sql` from your project folder and click **Go**

---

## 🔧 Installing Dependencies

Navigate to your project directory and run the following commands based on your system:

### ✅ Command Prompt (CMD)
```cmd
cd path\to\your\project
composer install
composer require twilio/sdk
```

### ✅ Windows PowerShell
```powershell
cd "C:\path\to\your\project"
composer install
composer require twilio/sdk
```

### ✅ Git Bash
```sh
cd /c/path/to/your/project
composer install
composer require twilio/sdk
```

> **Important:** These commands install necessary dependencies, including the Twilio SDK for sending SMS notifications.

---

## 📲 Setting Up Twilio for SMS Notifications

Enable SMS notifications for donors and recipients using Twilio.

### 🚀 Setup Steps:

1️⃣ Sign up for an account at [Twilio](https://www.twilio.com/).

2️⃣ Retrieve your **Account SID**, **Auth Token**, and **Twilio Phone Number** from the Twilio dashboard.

3️⃣ Open `hospital_panel/send_sms.php` and configure the credentials:
```php
require_once 'vendor/autoload.php';
use Twilio\Rest\Client;
$sid = 'YOUR_TWILIO_SID';
$token = 'YOUR_TWILIO_AUTH_TOKEN';
$twilio_number = '+1234567890'; // Your Twilio number
```

4️⃣ **Trial Account Limitation:** Twilio trial accounts can only send messages to **verified phone numbers**.

5️⃣ **Upgrade to a paid account** to send SMS to any number without restrictions.

---

## 🚀 Running the Project

### 🔹 Steps to Launch:

1️⃣ Move the project folder to the `htdocs` directory inside your XAMPP installation.

2️⃣ Start **Apache** and **MySQL** from the XAMPP Control Panel.

3️⃣ Open your browser and go to:
```
http://localhost/redstream/
```

4️⃣ Register **users, hospitals, and donors** to begin using the system.

---

## 🤝 Contribution

🔹 Feel free to modify, improve, or expand the REDSTREAM system.

🔹 If you have suggestions or feature requests, report them in the project repository.

---

### 🎉 Congratulations! Your REDSTREAM System is Now Ready to Use! 🚀
### Real-Time Blood Availability System
