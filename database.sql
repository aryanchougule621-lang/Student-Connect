
CREATE DATABASE IF NOT EXISTS student_connect
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE student_connect;

CREATE TABLE IF NOT EXISTS colleges (
    college_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    college_name VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS students (
    student_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(100) NOT NULL,
    mobile VARCHAR(15) NOT NULL UNIQUE,
    email VARCHAR(254) NOT NULL UNIQUE,
    college_id INT UNSIGNED NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    program_details TEXT DEFAULT NULL,
    mobile_verified TINYINT(1) NOT NULL DEFAULT 0,
    email_verified TINYINT(1) NOT NULL DEFAULT 0,
    registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_students_college
        FOREIGN KEY (college_id)
        REFERENCES colleges(college_id)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO colleges (college_name)
SELECT 'College 1'
WHERE NOT EXISTS (
    SELECT 1 FROM colleges WHERE college_name = 'College 1'
);

INSERT INTO colleges (college_name)
SELECT 'College 2'
WHERE NOT EXISTS (
    SELECT 1 FROM colleges WHERE college_name = 'College 2'
);

INSERT INTO colleges (college_name)
SELECT 'College 3'
WHERE NOT EXISTS (
    SELECT 1 FROM colleges WHERE college_name = 'College 3'
);

INSERT INTO colleges (college_name)
SELECT 'College 4'
WHERE NOT EXISTS (
    SELECT 1 FROM colleges WHERE college_name = 'College 4'
);

INSERT INTO colleges (college_name)
SELECT 'College 5'
WHERE NOT EXISTS (
    SELECT 1 FROM colleges WHERE college_name = 'College 5'
);
