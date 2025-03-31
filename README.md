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

1️⃣ Download redstream.zip and paste the redstream folder in `htdocs` directory inside your XAMPP installation.
   
2️⃣ Open **XAMPP Control Panel** and start **Apache** & **MySQL**.

3️⃣ Open your browser and go to: 
    ```
    http://localhost/phpmyadmin/
    ```

4️⃣  Click **New**, enter `bloodbank` as the database name, and click **Create**.

5️⃣  Import the database file (`db.sql`) into phpMyAdmin:
   - Select the `bloodbank` database
   - Navigate to the **Import** tab
   - Choose `db.sql` from your project folder and click **Go**
      ---

## 🔧 Installing Dependencies

Navigate to your project directory and run the following commands based on your system:

### ✅ Command Prompt (CMD)
```cmd
cd xampp\htdocs\redstream
composer install
composer require twilio/sdk
```

### ✅ Windows PowerShell
```powershell
cd " C:\xampp\htdocs\redstream>"
composer install
composer require twilio/sdk
```

### ✅ Git Bash
```sh
cd /c/xampp\htdocs\redstream
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
$account_sid = 'YOUR_TWILIO_SID';
$auth_token = 'YOUR_TWILIO_AUTH_TOKEN';
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
http://localhost/redstream/main_index.php
```

4️⃣ Register **users, hospitals, and donors** to begin using the system.

---

## Website screenshots

# Homepage

![Screenshot 2025-03-23 231242](https://github.com/user-attachments/assets/67d9ad2b-be4a-43fe-b51e-ac80c5f3f5f0)

# Hospital Panel

![Screenshot 2025-03-23 231309](https://github.com/user-attachments/assets/4e68e83a-d9e9-4bb1-9843-96c7c109bca8)

![Screenshot 2025-03-23 231326](https://github.com/user-attachments/assets/4e3fa44e-ff97-43bc-ad76-146990e0c2b6)

![Screenshot 2025-03-23 231828](https://github.com/user-attachments/assets/82beff61-ecad-4240-8aa1-8622b6b50957)

![Screenshot 2025-03-23 231844](https://github.com/user-attachments/assets/7eda9a8c-df26-45fb-9f31-f639cf57cd0c)

![Screenshot 2025-03-23 231854](https://github.com/user-attachments/assets/3b76a65a-f5b1-4adb-b496-795d38db80cf)

![Screenshot 2025-03-31 093150](https://github.com/user-attachments/assets/9a83f4b1-8388-414e-8a34-642a99bbb85c)

# Recipient Panel

![Screenshot 2025-03-23 231340](https://github.com/user-attachments/assets/9610d0a7-b33e-4772-8a86-a702d8efcaa8)

![Screenshot 2025-03-23 231349](https://github.com/user-attachments/assets/6f5bdbb0-6118-4201-802d-f5f346be67dc)

![Screenshot 2025-03-23 232002](https://github.com/user-attachments/assets/9801ae2e-04d9-40b7-9b75-4cc93392ad06)

![Screenshot 2025-03-23 232013](https://github.com/user-attachments/assets/322f60c2-741b-47ad-869d-2aa7ee6419e1)

![Screenshot 2025-03-23 232047](https://github.com/user-attachments/assets/f664d3c0-1325-4fda-bcd3-ea1be3430d34)

![Screenshot 2025-03-23 232115](https://github.com/user-attachments/assets/4a1ce896-f1e1-4a0b-99da-78d47c4f735e)

# To access Admin Panel

Open your browser and go to:
```
http://localhost/redstream/admin_panel/admin_login.php
```

![Screenshot 2025-03-23 232207](https://github.com/user-attachments/assets/379915e0-bcaa-4783-87a9-c37de11d7906)

![Screenshot 2025-03-23 232222](https://github.com/user-attachments/assets/e08e30d3-0586-4cfd-8f85-b6add10373a6)

![Screenshot 2025-03-23 232229](https://github.com/user-attachments/assets/f923c992-062b-4fac-801b-0e25ddbc0ba0)

![Screenshot 2025-03-23 232237](https://github.com/user-attachments/assets/f10330ae-c56d-4e6f-9b71-5449a13377c2)

![Screenshot 2025-03-23 232244](https://github.com/user-attachments/assets/8eddb270-57c9-4521-af8e-eb08e405f797)



## 🤝 Contribution

🔹 Feel free to modify, improve, or expand the REDSTREAM system.

🔹 If you have suggestions or feature requests, report them in the project repository.

---

### 🎉 Congratulations! Your REDSTREAM System is Now Ready to Use! 🚀
### Real-Time Blood Availability System
