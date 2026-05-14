# WIN Student Management System

WIN Student Management System is a PHP and MySQL web app for managing students, teachers, units, classes, attendance, and grades. It runs on XAMPP and uses role-based dashboards for Admin, Teacher, and Student users.

## Quick Start

1. Copy the project to:

```text
C:\xampp\htdocs\SMS
```

2. Start XAMPP:

```text
Apache
MySQL
```

3. Import the database file in phpMyAdmin:

```text
database/database.sql
```

4. Open the app:

```text
http://localhost/SMS/
```

## Test Accounts

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@sms.com` | `admin123` |
| Teacher | `teacher@sms.com` | `teacher123` |
| Student | `student@sms.com` | `student123` |

## Folder Structure

```text
SMS/
|-- index.php
|-- login.php
|-- logout.php
|-- Read.md
|
|-- admin/
|   |-- admin_dashboard.php
|   |-- attendance_sheet.php
|   |-- manage_admins.php
|   |-- manage_attendance.php
|   |-- manage_classes.php
|   |-- manage_courses.php
|   |-- manage_grades.php
|   |-- manage_semesters.php
|   |-- manage_students.php
|   `-- manage_teachers.php
|
|-- teachers/
|   |-- teacher_dashboard.php
|   |-- teacher_classes.php
|   |-- teacher_attendance.php
|   `-- teacher_grades.php
|
|-- students/
|   |-- student_dashboard.php
|   |-- student_units.php
|   |-- student_attendance.php
|   `-- student_grades.php
|
|-- includes/
|   |-- auth.php
|   |-- header.php
|   |-- navbar.php
|   |-- sidebar.php
|   `-- footer.php
|
|-- database/
|   |-- connection.php
|   `-- database.sql
|
|-- css/
|   |-- style.css
|   |-- auth.css
|   `-- dashboard.css
|
|-- js/
|   `-- script.js
|
`-- images/
    |-- logo.png
    |-- teacher1.jpg
    |-- teacher2.jpg
    |-- teacher3.jpg
    `-- teacher4.jpg
```

## Main Pages

### Public

| File | Purpose |
| --- | --- |
| `index.php` | Landing page |
| `login.php` | Role-based login |
| `logout.php` | Session logout |

### Admin

| File | Purpose |
| --- | --- |
| `admin/admin_dashboard.php` | Admin overview |
| `admin/manage_admins.php` | Create, update, delete admin accounts |
| `admin/manage_students.php` | Create, update, delete students |
| `admin/manage_teachers.php` | Create, update, delete teachers |
| `admin/manage_courses.php` | Manage units |
| `admin/manage_semesters.php` | Manage academic semesters |
| `admin/manage_classes.php` | Assign units to teachers and classes |
| `admin/attendance_sheet.php` | Week 1-12 attendance sheet |
| `admin/manage_grades.php` | Manage assessments and student grades |
| `admin/manage_attendance.php` | Redirects to `attendance_sheet.php` |

### Teacher

| File | Purpose |
| --- | --- |
| `teachers/teacher_dashboard.php` | Teacher overview |
| `teachers/teacher_classes.php` | View assigned classes and students |
| `teachers/teacher_attendance.php` | Mark attendance for own classes |
| `teachers/teacher_grades.php` | Enter grades for own class students |

### Student

| File | Purpose |
| --- | --- |
| `students/student_dashboard.php` | Student overview |
| `students/student_units.php` | View enrolled units and classes |
| `students/student_attendance.php` | View week 1-12 attendance |
| `students/student_grades.php` | View grades and pending assessments |

## Database

Connection file:

```text
database/connection.php
```

Default XAMPP connection:

```php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'sms';
```

Main tables:

| Table | Purpose |
| --- | --- |
| `admins` | Admin login accounts |
| `teachers` | Teacher accounts and profile data |
| `students` | Student accounts and profile data |
| `courses` | Units |
| `semesters` | Academic semesters |
| `classes` | Unit, teacher, semester, and room allocation |
| `enrollments` | Student enrolment in units |
| `attendance` | Week 1-12 attendance records |
| `assessments` | Unit assessments, such as Assignment 1-4 |
| `grades` | Student marks and grade letters |

## Role Summary

### Admin

- Manage admin, teacher, and student accounts
- Manage units, semesters, and classes
- Assign teachers to classes
- Mark or update week 1-12 attendance
- Create unit assessments
- Grade only students enrolled in the selected unit/class

### Teacher

- View assigned classes and units
- View students enrolled in their classes
- Mark week 1-12 attendance for their own classes
- Enter grades for their own class students

### Student

- View enrolled units and classes
- View teacher, room, and semester details
- View attendance by class and week
- View assessment marks, grades, pending assessments, and averages

## Security

- Role pages require login.
- `includes/auth.php` protects dashboards and management pages.
- Users are redirected away from pages outside their role.
- Passwords use PHP password hashes.
- Database writes use prepared statements.

## Notes

- `courses` means units in the interface.
- `attendance` is the week 1-12 attendance sheet. There is no separate duplicate attendance table.
- `assessments` controls which assignments exist for each unit.
- `grades` only stores marks for valid student-assessment pairs.
