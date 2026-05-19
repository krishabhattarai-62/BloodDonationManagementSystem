# Blood Donation Management System

## Introduction

The Blood Donation Management System is a web-based project developed to simplify the process of blood donation and blood request management. The system helps connect donors, patients, and administrators in a more organized and efficient way.

This project includes two types of users:

- Donors
- Admins

The system also provides additional smart features such as AI chatbot support, OCR-based blood group detection, email verification, donation scheduling, and request tracking.

---

## Features

### Donor Features

Donors can create an account and securely log in using email OTP verification. After logging in, users can manage their profile, update personal details, and check their donation eligibility.

The system also allows donors to upload medical reports, where OCR technology is used to automatically detect the blood group from the uploaded image.

Users can:

- Schedule blood donation appointments
- Submit blood requests with hospital details and supporting documents
- View their request history and request status
- Browse active blood requests from other users
- Receive notifications about updates and approvals
- Reset forgotten passwords using email OTP verification

An AI-powered chatbot is also included to help users with blood donation guidance and nearby donor search.

---

### Admin Features

Admins have access to a dashboard that displays important information such as:

- Total registered users
- Total blood requests
- Pending requests
- Donation records
- Blood reserve information

Admins can manage donors, approve or reject blood requests, monitor donation activities, and receive system notifications for new requests and updates.

---

## Technologies Used

The project is developed using the following technologies:

- PHP 8+ for backend development
- MySQL for database management
- HTML, CSS, and JavaScript for frontend design
- PHPMailer for sending emails and OTP verification
- OpenRouter API for AI chatbot and OCR functionality
- Tesseract OCR for blood group detection from uploaded medical reports
- Font Awesome for icons and interface improvements

---

## Project Structure

The project is organized into several folders:

- assets → contains CSS files and images
- config → stores database configuration files
- includes → contains reusable PHP functions and components
- public → contains all main application pages
- uploads → stores uploaded files and documents
- PHPMailer → contains the PHPMailer library
- sqlcommands.sql → database setup file
- index.php → main entry point of the application

---

## Database Overview

### Users Table

This table stores all user-related information such as:

- Name
- Contact details
- Address
- Email
- Gender
- Age
- Blood group
- Password
- User role
- Donation eligibility

### Blood Requests Table

This table stores blood request details including:

- Patient name
- Required blood group
- Units required
- Hospital information
- Contact details
- Uploaded documents
- Request status

### Donations Table

This table stores donation appointment records such as:

- Donor information
- Donation date
- Donation status

---

## System Requirements

Before running the project, the following software and tools are required:

- PHP 8.0 or higher
- MySQL 5.7 or higher
- XAMPP, WAMP, or LAMP server
- SMTP email account for email verification
- OpenRouter API key for AI and OCR features

---

## Installation Process

### Step 1: Copy the Project Folder

Move the project folder into your web server directory.

For XAMPP:
C:/xampp/htdocs/BloodDonationManagementSystem/

For LAMP:
/var/www/html/BloodDonationManagementSystem/

---

### Step 2: Create the Database

Import the SQL file using phpMyAdmin or MySQL CLI.

```sql
SOURCE /path/to/BloodDonationManagementSystem/sqlcommands.sql;
```

This will automatically create the database, tables, and default admin account.

---

### Step 3: Configure Database Connection

Open the file:

config/db.php

Update the database credentials:

```php
$server   = 'localhost';
$username = 'root';
$password = '';
$database = 'blood_donation';
```

---

### Step 4: Add OpenRouter API Key

Open the .env file and add your API key:

```env
OPENROUTER_API_KEY=your_api_key_here
```

---

### Step 5: Configure Email Settings

Open:

includes/functions.php

Update the SMTP settings:

```php
$mail->Host     = 'smtp.gmail.com';
$mail->Username = 'your_email@gmail.com';
$mail->Password = 'your_app_password';
$mail->Port     = 587;
```

---

### Step 6: Set Upload Folder Permission

```bash
chmod 755 uploads/
```

---

### Step 7: Run the Application

Open the project in your browser:

http://localhost/BloodDonationManagementSystem/

---

## Configuration Files

| File Name              | Purpose                      |
| ---------------------- | ---------------------------- |
| config/db.php          | Database configuration       |
| .env                   | OpenRouter API configuration |
| includes/functions.php | SMTP email settings          |

---

## Default Admin Login

The system includes a default admin account for testing and demonstration purposes.

Email: admin@test.com  
Password: password

Note: It is recommended to change the default password after the first login for security purposes.

---

## Security Features

Several security measures have been implemented in the system:

- Passwords are encrypted using bcrypt
- CSRF protection is implemented in forms
- User inputs are sanitized before processing
- PDO prepared statements are used to prevent SQL injection
- Session timeout protection is enabled
- File uploads are validated before saving
- Email verification is required before account activation

---

## Conclusion

The Blood Donation Management System is designed to make blood donation and request management easier, faster, and more secure. By combining modern technologies such as AI support, OCR detection, and secure authentication, the system provides a reliable and user-friendly platform for both donors and administrators.
