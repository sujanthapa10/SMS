-- =========================================
-- WIN STUDENT MANAGEMENT SYSTEM DATABASE
-- Clean role tables: admins, teachers, and students authenticate separately.
-- =========================================

DROP DATABASE IF EXISTS sms;
CREATE DATABASE sms;
USE sms;

-- =========================================
-- 1. ADMINS TABLE
-- =========================================

CREATE TABLE admins (
    id INT(6) UNSIGNED ZEROFILL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO admins (name, email, password)
VALUES
(
    'Admin User',
    'admin@sms.com',
    '$2y$10$hpQxni.WXfRBtMm..Q0iFeqO7V7ea9KF/kg5wPDskNDLDWGUfw7R6'
);

-- =========================================
-- 2. STUDENTS TABLE
-- =========================================

CREATE TABLE students (
    id INT(6) UNSIGNED ZEROFILL AUTO_INCREMENT PRIMARY KEY,
    student_number VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other'),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO students (
    student_number,
    first_name,
    last_name,
    email,
    password,
    phone,
    date_of_birth,
    gender,
    address
)
VALUES
(
    'STU001',
    'Alice',
    'Johnson',
    'student@sms.com',
    '$2y$10$K2ZTPwXBcO16qMXQzR0D3u7hePGh6hLtCXoPZCLm9nYKsTFabEUgy',
    '0400000001',
    '2002-05-12',
    'Female',
    'Sydney, Australia'
),
(
    'STU002',
    'Bob',
    'Smith',
    'bob@sms.com',
    '$2y$10$K2ZTPwXBcO16qMXQzR0D3u7hePGh6hLtCXoPZCLm9nYKsTFabEUgy',
    '0400000002',
    '2001-09-20',
    'Male',
    'Melbourne, Australia'
),
(
    'STU003',
    'Chloe',
    'Nguyen',
    'chloe.nguyen@sms.com',
    '$2y$10$K2ZTPwXBcO16qMXQzR0D3u7hePGh6hLtCXoPZCLm9nYKsTFabEUgy',
    '0400000003',
    '2003-01-18',
    'Female',
    'Parramatta, Australia'
),
(
    'STU004',
    'Daniel',
    'Brown',
    'daniel.brown@sms.com',
    '$2y$10$K2ZTPwXBcO16qMXQzR0D3u7hePGh6hLtCXoPZCLm9nYKsTFabEUgy',
    '0400000004',
    '2002-11-03',
    'Male',
    'Newcastle, Australia'
),
(
    'STU005',
    'Ella',
    'Martinez',
    'ella.martinez@sms.com',
    '$2y$10$K2ZTPwXBcO16qMXQzR0D3u7hePGh6hLtCXoPZCLm9nYKsTFabEUgy',
    '0400000005',
    '2001-07-27',
    'Female',
    'Wollongong, Australia'
),
(
    'STU006',
    'Finn',
    'Taylor',
    'finn.taylor@sms.com',
    '$2y$10$K2ZTPwXBcO16qMXQzR0D3u7hePGh6hLtCXoPZCLm9nYKsTFabEUgy',
    '0400000006',
    '2002-03-14',
    'Male',
    'Sydney, Australia'
),
(
    'STU007',
    'Grace',
    'Singh',
    'grace.singh@sms.com',
    '$2y$10$K2ZTPwXBcO16qMXQzR0D3u7hePGh6hLtCXoPZCLm9nYKsTFabEUgy',
    '0400000007',
    '2003-08-09',
    'Female',
    'Blacktown, Australia'
),
(
    'STU008',
    'Henry',
    'Kim',
    'henry.kim@sms.com',
    '$2y$10$K2ZTPwXBcO16qMXQzR0D3u7hePGh6hLtCXoPZCLm9nYKsTFabEUgy',
    '0400000008',
    '2001-12-21',
    'Male',
    'Chatswood, Australia'
),
(
    'STU009',
    'Ivy',
    'Patel',
    'ivy.patel@sms.com',
    '$2y$10$K2ZTPwXBcO16qMXQzR0D3u7hePGh6hLtCXoPZCLm9nYKsTFabEUgy',
    '0400000009',
    '2002-06-30',
    'Female',
    'Liverpool, Australia'
),
(
    'STU010',
    'Jack',
    'O''Connor',
    'jack.oconnor@sms.com',
    '$2y$10$K2ZTPwXBcO16qMXQzR0D3u7hePGh6hLtCXoPZCLm9nYKsTFabEUgy',
    '0400000010',
    '2003-02-02',
    'Male',
    'Manly, Australia'
),
(
    'STU011',
    'Kiara',
    'Sharma',
    'kiara.sharma@sms.com',
    '$2y$10$K2ZTPwXBcO16qMXQzR0D3u7hePGh6hLtCXoPZCLm9nYKsTFabEUgy',
    '0400000011',
    '2002-10-12',
    'Female',
    'Burwood, Australia'
),
(
    'STU012',
    'Leo',
    'Garcia',
    'leo.garcia@sms.com',
    '$2y$10$K2ZTPwXBcO16qMXQzR0D3u7hePGh6hLtCXoPZCLm9nYKsTFabEUgy',
    '0400000012',
    '2001-04-25',
    'Male',
    'Penrith, Australia'
),
(
    'STU013',
    'Mia',
    'Roberts',
    'mia.roberts@sms.com',
    '$2y$10$K2ZTPwXBcO16qMXQzR0D3u7hePGh6hLtCXoPZCLm9nYKsTFabEUgy',
    '0400000013',
    '2003-09-17',
    'Female',
    'Randwick, Australia'
),
(
    'STU014',
    'Noah',
    'Harris',
    'noah.harris@sms.com',
    '$2y$10$K2ZTPwXBcO16qMXQzR0D3u7hePGh6hLtCXoPZCLm9nYKsTFabEUgy',
    '0400000014',
    '2002-01-05',
    'Male',
    'Campbelltown, Australia'
),
(
    'STU015',
    'Olivia',
    'Clark',
    'olivia.clark@sms.com',
    '$2y$10$K2ZTPwXBcO16qMXQzR0D3u7hePGh6hLtCXoPZCLm9nYKsTFabEUgy',
    '0400000015',
    '2001-06-11',
    'Female',
    'Hurstville, Australia'
),
(
    'STU016',
    'Ryan',
    'Evans',
    'ryan.evans@sms.com',
    '$2y$10$K2ZTPwXBcO16qMXQzR0D3u7hePGh6hLtCXoPZCLm9nYKsTFabEUgy',
    '0400000016',
    '2003-03-19',
    'Male',
    'Ryde, Australia'
),
(
    'STU017',
    'Sofia',
    'Lopez',
    'sofia.lopez@sms.com',
    '$2y$10$K2ZTPwXBcO16qMXQzR0D3u7hePGh6hLtCXoPZCLm9nYKsTFabEUgy',
    '0400000017',
    '2002-05-29',
    'Female',
    'Auburn, Australia'
),
(
    'STU018',
    'Thomas',
    'White',
    'thomas.white@sms.com',
    '$2y$10$K2ZTPwXBcO16qMXQzR0D3u7hePGh6hLtCXoPZCLm9nYKsTFabEUgy',
    '0400000018',
    '2001-08-22',
    'Male',
    'Bondi, Australia'
),
(
    'STU019',
    'Uma',
    'Reddy',
    'uma.reddy@sms.com',
    '$2y$10$K2ZTPwXBcO16qMXQzR0D3u7hePGh6hLtCXoPZCLm9nYKsTFabEUgy',
    '0400000019',
    '2003-12-04',
    'Female',
    'Strathfield, Australia'
),
(
    'STU020',
    'William',
    'King',
    'william.king@sms.com',
    '$2y$10$K2ZTPwXBcO16qMXQzR0D3u7hePGh6hLtCXoPZCLm9nYKsTFabEUgy',
    '0400000020',
    '2002-04-16',
    'Male',
    'Surry Hills, Australia'
);

-- =========================================
-- 3. TEACHERS TABLE
-- =========================================

CREATE TABLE teachers (
    id INT(6) UNSIGNED ZEROFILL AUTO_INCREMENT PRIMARY KEY,
    employee_number VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    department VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO teachers (
    employee_number,
    first_name,
    last_name,
    email,
    password,
    phone,
    department
)
VALUES
(
    'EMP001',
    'John',
    'Doe',
    'teacher@sms.com',
    '$2y$10$oHoGe6.x1rQZThw1nJ4R4OGrthKJ36NGad1dS7SlzVzjz5sslk8e.',
    '0411111111',
    'Information Technology'
),
(
    'EMP002',
    'Sarah',
    'Lee',
    'sarah@sms.com',
    '$2y$10$oHoGe6.x1rQZThw1nJ4R4OGrthKJ36NGad1dS7SlzVzjz5sslk8e.',
    '0422222222',
    'Business'
),
(
    'EMP003',
    'Michael',
    'Chen',
    'michael.chen@sms.com',
    '$2y$10$oHoGe6.x1rQZThw1nJ4R4OGrthKJ36NGad1dS7SlzVzjz5sslk8e.',
    '0433333333',
    'Information Technology'
),
(
    'EMP004',
    'Priya',
    'Nair',
    'priya.nair@sms.com',
    '$2y$10$oHoGe6.x1rQZThw1nJ4R4OGrthKJ36NGad1dS7SlzVzjz5sslk8e.',
    '0444444444',
    'Accounting'
),
(
    'EMP005',
    'Emma',
    'Wilson',
    'emma.wilson@sms.com',
    '$2y$10$oHoGe6.x1rQZThw1nJ4R4OGrthKJ36NGad1dS7SlzVzjz5sslk8e.',
    '0455555555',
    'English'
);

-- =========================================
-- 4. COURSES / UNITS TABLE
-- =========================================

CREATE TABLE courses (
    id INT(6) UNSIGNED ZEROFILL AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    description TEXT,
    teacher_id INT(6) UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (teacher_id) REFERENCES teachers(id)
);

INSERT INTO courses (
    course_code,
    course_name,
    description,
    teacher_id
)
VALUES
(
    'IT101',
    'Introduction to IT',
    'Basic concepts of Information Technology',
    000001
),
(
    'BUS201',
    'Business Management',
    'Fundamentals of business management',
    000002
),
(
    'ACC110',
    'Accounting Principles',
    'Core accounting concepts, ledgers, and reporting',
    000004
),
(
    'ENG120',
    'Academic English',
    'Reading, writing, presentation, and study skills',
    000005
),
(
    'IT205',
    'Web Development',
    'Responsive websites, PHP, MySQL, and JavaScript foundations',
    000003
);

-- =========================================
-- 5. SEMESTERS TABLE
-- =========================================

CREATE TABLE semesters (
    id INT(6) UNSIGNED ZEROFILL AUTO_INCREMENT PRIMARY KEY,
    semester_name VARCHAR(50) NOT NULL,
    academic_year YEAR NOT NULL,
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_semester_year (semester_name, academic_year)
);

INSERT INTO semesters (semester_name, academic_year, start_date, end_date)
VALUES
(
    'Semester 1',
    2026,
    '2026-02-16',
    '2026-06-05'
),
(
    'Semester 2',
    2026,
    '2026-07-20',
    '2026-11-06'
);

-- =========================================
-- 6. CLASSES TABLE
-- =========================================

CREATE TABLE classes (
    id INT(6) UNSIGNED ZEROFILL AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(80) NOT NULL,
    course_id INT(6) UNSIGNED NOT NULL,
    teacher_id INT(6) UNSIGNED NOT NULL,
    semester_id INT(6) UNSIGNED NOT NULL,
    room VARCHAR(50),
    capacity INT DEFAULT 30,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id),
    FOREIGN KEY (semester_id) REFERENCES semesters(id)
);

INSERT INTO classes (class_name, course_id, teacher_id, semester_id, room, capacity)
VALUES
(
    'IT101-A',
    000001,
    000001,
    000001,
    'Room 201',
    30
),
(
    'BUS201-A',
    000002,
    000002,
    000001,
    'Room 105',
    28
),
(
    'ACC110-A',
    000003,
    000004,
    000001,
    'Room 203',
    25
),
(
    'ENG120-A',
    000004,
    000005,
    000001,
    'Room 301',
    32
),
(
    'IT205-A',
    000005,
    000003,
    000002,
    'Lab 2',
    24
);

-- =========================================
-- 7. ENROLLMENTS TABLE
-- =========================================

CREATE TABLE enrollments (
    id INT(6) UNSIGNED ZEROFILL AUTO_INCREMENT PRIMARY KEY,
    student_id INT(6) UNSIGNED,
    course_id INT(6) UNSIGNED,
    enrollment_date DATE DEFAULT (CURRENT_DATE),
    status ENUM('active', 'completed', 'dropped') DEFAULT 'active',

    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

INSERT INTO enrollments (
    student_id,
    course_id,
    status
)
VALUES
(
    000001,
    000001,
    'active'
),
(
    000002,
    000002,
    'active'
),
(
    000003,
    000001,
    'active'
),
(
    000004,
    000001,
    'active'
),
(
    000005,
    000001,
    'active'
),
(
    000006,
    000002,
    'active'
),
(
    000007,
    000002,
    'active'
),
(
    000008,
    000002,
    'active'
),
(
    000009,
    000003,
    'active'
),
(
    000010,
    000003,
    'active'
),
(
    000011,
    000003,
    'active'
),
(
    000012,
    000004,
    'active'
),
(
    000013,
    000004,
    'active'
),
(
    000014,
    000004,
    'active'
),
(
    000015,
    000005,
    'active'
),
(
    000016,
    000005,
    'active'
),
(
    000017,
    000005,
    'active'
),
(
    000018,
    000001,
    'active'
),
(
    000019,
    000002,
    'active'
),
(
    000020,
    000005,
    'active'
);

-- =========================================
-- 8. ATTENDANCE TABLE
-- =========================================

CREATE TABLE attendance (
    id INT(6) UNSIGNED ZEROFILL AUTO_INCREMENT PRIMARY KEY,
    class_id INT(6) UNSIGNED NOT NULL,
    student_id INT(6) UNSIGNED NOT NULL,
    week_number TINYINT UNSIGNED NOT NULL,
    status ENUM('present', 'absent', 'late', 'excused') DEFAULT NULL,
    marked_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_class_student_week (class_id, student_id, week_number),
    CHECK (week_number BETWEEN 1 AND 12)
);

INSERT INTO attendance (class_id, student_id, week_number, status, marked_at)
VALUES
(
    000001,
    000001,
    1,
    'present',
    CURRENT_TIMESTAMP
),
(
    000002,
    000002,
    1,
    'late',
    CURRENT_TIMESTAMP
),
(
    000001,
    000003,
    1,
    'present',
    CURRENT_TIMESTAMP
),
(
    000001,
    000004,
    1,
    'absent',
    CURRENT_TIMESTAMP
),
(
    000001,
    000005,
    1,
    'present',
    CURRENT_TIMESTAMP
),
(
    000001,
    000018,
    1,
    'late',
    CURRENT_TIMESTAMP
),
(
    000002,
    000006,
    1,
    'present',
    CURRENT_TIMESTAMP
),
(
    000002,
    000007,
    1,
    'present',
    CURRENT_TIMESTAMP
),
(
    000002,
    000008,
    1,
    'absent',
    CURRENT_TIMESTAMP
),
(
    000002,
    000019,
    1,
    'present',
    CURRENT_TIMESTAMP
),
(
    000003,
    000009,
    1,
    'present',
    CURRENT_TIMESTAMP
),
(
    000003,
    000010,
    1,
    'late',
    CURRENT_TIMESTAMP
),
(
    000003,
    000011,
    1,
    'present',
    CURRENT_TIMESTAMP
),
(
    000004,
    000012,
    1,
    'present',
    CURRENT_TIMESTAMP
),
(
    000004,
    000013,
    1,
    'excused',
    CURRENT_TIMESTAMP
),
(
    000004,
    000014,
    1,
    'present',
    CURRENT_TIMESTAMP
),
(
    000005,
    000015,
    1,
    'present',
    CURRENT_TIMESTAMP
),
(
    000005,
    000016,
    1,
    'present',
    CURRENT_TIMESTAMP
),
(
    000005,
    000017,
    1,
    'late',
    CURRENT_TIMESTAMP
),
(
    000005,
    000020,
    1,
    'present',
    CURRENT_TIMESTAMP
),
(
    000001,
    000001,
    2,
    'present',
    CURRENT_TIMESTAMP
),
(
    000001,
    000003,
    2,
    'present',
    CURRENT_TIMESTAMP
),
(
    000001,
    000004,
    2,
    'late',
    CURRENT_TIMESTAMP
),
(
    000001,
    000005,
    2,
    'present',
    CURRENT_TIMESTAMP
),
(
    000001,
    000018,
    2,
    'present',
    CURRENT_TIMESTAMP
);

-- =========================================
-- 9. GRADES TABLE
-- =========================================

CREATE TABLE assessments (
    id INT(6) UNSIGNED ZEROFILL AUTO_INCREMENT PRIMARY KEY,
    course_id INT(6) UNSIGNED NOT NULL,
    assessment_name VARCHAR(100) NOT NULL,
    total_marks DECIMAL(5,2) NOT NULL DEFAULT 100,
    weight_percent DECIMAL(5,2) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_course_assessment (course_id, assessment_name)
);

INSERT INTO assessments (course_id, assessment_name, total_marks, weight_percent)
VALUES
(000001, 'Assignment 1', 100, 20),
(000001, 'Assignment 2', 100, 20),
(000001, 'Assignment 3', 100, 30),
(000001, 'Assignment 4', 100, 30),
(000002, 'Assignment 1', 100, 20),
(000002, 'Assignment 2', 100, 20),
(000002, 'Assignment 3', 100, 30),
(000002, 'Assignment 4', 100, 30),
(000003, 'Assignment 1', 100, 25),
(000003, 'Assignment 2', 100, 25),
(000003, 'Assignment 3', 100, 25),
(000003, 'Assignment 4', 100, 25),
(000004, 'Assignment 1', 100, 20),
(000004, 'Assignment 2', 100, 20),
(000004, 'Assignment 3', 100, 30),
(000004, 'Assignment 4', 100, 30),
(000005, 'Assignment 1', 100, 20),
(000005, 'Assignment 2', 100, 20),
(000005, 'Assignment 3', 100, 30),
(000005, 'Assignment 4', 100, 30);

CREATE TABLE grades (
    id INT(6) UNSIGNED ZEROFILL AUTO_INCREMENT PRIMARY KEY,
    student_id INT(6) UNSIGNED NOT NULL,
    assessment_id INT(6) UNSIGNED NOT NULL,
    marks DECIMAL(5,2),
    grade VARCHAR(5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (assessment_id) REFERENCES assessments(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_assessment (student_id, assessment_id)
);

INSERT INTO grades (
    student_id,
    assessment_id,
    marks,
    grade
)
VALUES
(
    000001,
    000001,
    85,
    'A'
),
(
    000002,
    000005,
    72,
    'B'
),
(
    000003,
    000001,
    91,
    'A'
),
(
    000004,
    000001,
    64,
    'C'
),
(
    000006,
    000005,
    78,
    'B'
),
(
    000009,
    000009,
    88,
    'A'
),
(
    000012,
    000013,
    81,
    'A'
),
(
    000015,
    000017,
    76,
    'B'
);

-- =========================================
-- LOGIN PASSWORDS
-- =========================================
-- admin@sms.com / admin123
-- teacher@sms.com / teacher123
-- student@sms.com / student123
-- =========================================
