# Student Connect

## Project Overview

Student Connect is a web-based student registration system developed as part of an assignment for CodenProfit Technologies. It allows students to submit their registration details and enables administrators to view and filter submitted registrations through an admin dashboard.

## Features

- Student registration form
- College selection from predefined colleges
- Academic year selection
- Server-side form validation
- Database storage using PHP and MySQL/MariaDB
- Duplicate mobile number and email handling
- Admin dashboard displaying student registrations
- Registration count
- Filtering by college and academic year
- Mobile and email verification status tracking
- Responsive user interface

## Technology Stack

- Frontend: HTML, CSS
- Backend: PHP
- Database: MySQL/MariaDB
- Local development: XAMPP
- Database administration: phpMyAdmin

## Requirements

- XAMPP with Apache and MySQL/MariaDB
- A web browser
- PHP with PDO MySQL support


## Installation and Setup

1. Copy the project folder into `C:\xampp\htdocs\Student-Connect`.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Open phpMyAdmin at `http://localhost/phpmyadmin`.
4. Import `database.sql` using the Import tab. The script creates the `student_connect` database and required tables.
5. Check `config/db.php` and configure the database connection details for your local environment.
6. Open the registration form at `http://localhost/Student-Connect/`.
7. Open the admin dashboard at `http://localhost/Student-Connect/admin/dashboard.php`.


## Database

The project uses the following tables:

- `students` — stores student registration details and verification statuses.
- `colleges` — stores predefined college information.
- `admins` — stores administrator account information, if used by the project.

## Verification Status

The system tracks mobile and email verification status. In the current implementation, newly registered students are marked as pending until verification functionality is implemented.

## Testing

The registration form, database insertion, dashboard record display, college filter, academic year filter, and verification status display have been tested locally.

A separate CSV file containing 60 test student records is provided for testing.

## Project Status

The student registration and admin dashboard modules are implemented. Authentication integration and actual mobile/email verification may require additional development.

## Author

Developed as part of the Student Connect assignment for CodenProfit Technologies.
