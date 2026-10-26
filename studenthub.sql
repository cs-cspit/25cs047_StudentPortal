-- ========================================================
-- StudentHub Master Unified Database Schema (studenthub_db)
-- Charotar University of Science and Technology (CHARUSAT)
-- Faculty of Technology and Engineering (FTE / CSPIT)
-- Subject: Web Development Frameworks (ITUE203)
-- Developer: Dhruvi Mokani (25CS047)
-- ========================================================

CREATE DATABASE IF NOT EXISTS `studenthub_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `studenthub_db`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `feedback`;
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `assignments`;
DROP TABLE IF EXISTS `attendance`;
DROP TABLE IF EXISTS `timetable`;
DROP TABLE IF EXISTS `results`;
DROP TABLE IF EXISTS `fees`;
DROP TABLE IF EXISTS `announcements`;
DROP TABLE IF EXISTS `menu_items`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- Table: users (Practical 9, 10, 14, 15)
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('student', 'editor', 'admin', 'user') DEFAULT 'student',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `remember_token` VARCHAR(255) NULL,
  `last_login` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: students (Practical 8, 11, 13, 14, 15)
-- Virtual student_id guarantees 100% compatibility with Practical 8
-- --------------------------------------------------------
CREATE TABLE `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT AS (id) VIRTUAL,
  `user_id` INT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `mobile` VARCHAR(20) DEFAULT NULL,
  `course` VARCHAR(100) NOT NULL DEFAULT 'B.Tech CSE',
  `year` VARCHAR(50) DEFAULT 'Third Year',
  `gender` VARCHAR(20) DEFAULT 'Female',
  `pincode` VARCHAR(10) DEFAULT '387002',
  `address` VARCHAR(255) DEFAULT 'CHARUSAT Campus, Changa',
  `message` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive', 'archived') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`email`),
  INDEX (`course`),
  INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: events (Practical 6, 8, 12, 14, 15)
-- Virtual event_id, event_name, venue guarantees compatibility
-- with Practical 8 & 12
-- --------------------------------------------------------
CREATE TABLE `events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `event_id` INT AS (id) VIRTUAL,
  `title` VARCHAR(150) NOT NULL,
  `event_name` VARCHAR(150) AS (title) VIRTUAL,
  `description` TEXT DEFAULT NULL,
  `event_date` DATE NOT NULL,
  `location` VARCHAR(150) NOT NULL,
  `venue` VARCHAR(150) AS (location) VIRTUAL,
  `category` VARCHAR(50) NOT NULL DEFAULT 'Technical',
  `status` ENUM('Open', 'Closed') NOT NULL DEFAULT 'Open',
  `poster` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`event_date`),
  INDEX (`category`),
  INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: registrations (Practical 8, 14, 15)
-- --------------------------------------------------------
CREATE TABLE `registrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `registration_id` INT AS (id) VIRTUAL,
  `user_id` INT NOT NULL,
  `student_id` INT NULL,
  `event_id` INT NOT NULL,
  `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('Confirmed', 'Pending', 'Cancelled') DEFAULT 'Confirmed',
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: menu_items (Practical 14 Dynamic Menu)
-- --------------------------------------------------------
CREATE TABLE `menu_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL,
  `link` VARCHAR(150) NOT NULL,
  `min_role` ENUM('student', 'user', 'editor', 'admin') DEFAULT 'student',
  `icon` VARCHAR(50) DEFAULT 'fa-link',
  `sort_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: audit_logs (Practical 14 Audit Logging)
-- --------------------------------------------------------
CREATE TABLE `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `action` VARCHAR(255) NOT NULL,
  `details` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: contact_messages (Practical 7 & Contact Page)
-- --------------------------------------------------------
CREATE TABLE `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `mobile` VARCHAR(20) DEFAULT NULL,
  `pincode` VARCHAR(10) DEFAULT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: feedback (Practical 2, 15)
-- --------------------------------------------------------
CREATE TABLE `feedback` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `category` VARCHAR(50) NOT NULL DEFAULT 'Academic',
  `rating` INT NOT NULL DEFAULT 5,
  `comments` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Academic Modules Tables (Portal Complete Integration)
-- --------------------------------------------------------
CREATE TABLE `announcements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `category` VARCHAR(50) DEFAULT 'General',
  `content` TEXT NOT NULL,
  `publish_date` DATE NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `timetable` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `day` ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') NOT NULL,
  `time_slot` VARCHAR(50) NOT NULL,
  `subject` VARCHAR(100) NOT NULL,
  `room` VARCHAR(50) NOT NULL,
  `faculty` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `attendance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `subject` VARCHAR(100) NOT NULL,
  `total_lectures` INT NOT NULL DEFAULT 40,
  `attended_lectures` INT NOT NULL DEFAULT 35,
  `percentage` DECIMAL(5,2) AS (attended_lectures / total_lectures * 100) VIRTUAL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `assignments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `subject` VARCHAR(100) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `due_date` DATE NOT NULL,
  `max_marks` INT DEFAULT 100,
  `status` ENUM('Pending', 'Submitted', 'Evaluated') DEFAULT 'Pending',
  `score` INT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `results` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `semester` VARCHAR(20) NOT NULL,
  `subject_code` VARCHAR(20) NOT NULL,
  `subject_name` VARCHAR(100) NOT NULL,
  `credits` INT NOT NULL,
  `grade` VARCHAR(5) NOT NULL,
  `grade_point` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `fees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `semester` VARCHAR(20) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `due_date` DATE NOT NULL,
  `status` ENUM('Paid', 'Pending', 'Overdue') DEFAULT 'Paid',
  `transaction_id` VARCHAR(50) DEFAULT NULL,
  `paid_at` DATE DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ========================================================
-- SEED DATA INSERTION
-- ========================================================

-- 1. Seed Users (Default admin, student, editor)
-- Passwords:
-- 'Admin@123'   -> $2y$10$q1vf85nkkzCX.dkDQ.k9iO.ewt0pzE3TtIe/I4njz6GY0VrQmXCmW
-- 'Student@123' -> $2y$10$kqPwxUmsrOa.MT3GfjDf2uaTuxTI16K98FFTxyuUOHz3J3ddxPWxa
-- '123456'      -> $2y$10$KaQufyQiRLmLd23yWjMx4.nRhXs74Q7IvRdALjSWYBD6mP3ZhcrC6
INSERT INTO `users` (`id`, `username`, `name`, `email`, `password`, `role`, `status`) VALUES
(1, 'admin', 'Dhruvi (Admin)', 'admin@studenthub.com', '$2y$10$q1vf85nkkzCX.dkDQ.k9iO.ewt0pzE3TtIe/I4njz6GY0VrQmXCmW', 'admin', 'active'),
(2, 'dhruvi', 'Dhruvi Mokani', 'dhruvimokani08@gmail.com', '$2y$10$kqPwxUmsrOa.MT3GfjDf2uaTuxTI16K98FFTxyuUOHz3J3ddxPWxa', 'student', 'active'),
(3, 'student01', 'Kalp Mokani', 'student01@gmail.com', '$2y$10$KaQufyQiRLmLd23yWjMx4.nRhXs74Q7IvRdALjSWYBD6mP3ZhcrC6', 'student', 'active'),
(4, 'editor01', 'Faculty Editor', 'editor@studenthub.com', '$2y$10$q1vf85nkkzCX.dkDQ.k9iO.ewt0pzE3TtIe/I4njz6GY0VrQmXCmW', 'editor', 'active'),
(5, 'riya_shah', 'Riya Shah', 'riya@example.com', '$2y$10$kqPwxUmsrOa.MT3GfjDf2uaTuxTI16K98FFTxyuUOHz3J3ddxPWxa', 'student', 'active'),
(6, 'admin01', 'System Administrator', 'admin01@gmail.com', '$2y$10$q1vf85nkkzCX.dkDQ.k9iO.ewt0pzE3TtIe/I4njz6GY0VrQmXCmW', 'admin', 'active');

-- 2. Seed 25 Students (Exceeding the 20 minimum records for Practical 11)
INSERT INTO `students` (`id`, `user_id`, `name`, `email`, `mobile`, `course`, `year`, `gender`, `pincode`, `address`, `message`, `status`) VALUES
(1, 2, 'Dhruvi Mokani', 'dhruvimokani08@gmail.com', '9023501363', 'B.Tech CSE', 'Third Year', 'Female', '387002', 'Dabhan Rd, Nadiad', 'Excited for semester project development!', 'active'),
(2, 3, 'Kalp Mokani', 'student01@gmail.com', '9876543210', 'B.Tech CSE', 'Second Year', 'Male', '387001', 'Anand City', 'Looking forward to web frameworks', 'active'),
(3, 5, 'Riya Shah', 'riya@example.com', '9898012345', 'B.Tech IT', 'Third Year', 'Female', '388001', 'Vallabh Vidyanagar', 'Interested in AI workshops', 'active'),
(4, NULL, 'Diya Patel', 'diya@example.com', '9712345678', 'B.Tech CSE', 'Third Year', 'Female', '380015', 'Ahmedabad', 'Enthusiastic web learner', 'active'),
(5, NULL, 'Khushi Sharma', 'khushi@example.com', '9823456789', 'B.Tech CSE', 'Third Year', 'Female', '390001', 'Vadodara', 'Web application design', 'active'),
(6, NULL, 'Dhanushi Joshi', 'dhanushi@example.com', '9734567890', 'B.Tech CSE', 'Third Year', 'Female', '387002', 'Nadiad', 'Frontend design focus', 'active'),
(7, NULL, 'Aarav Mehta', 'aarav.mehta@example.com', '9811223344', 'B.Tech CE', 'Second Year', 'Male', '380009', 'Ahmedabad', 'Software engineering passion', 'active'),
(8, NULL, 'Ananya Desai', 'ananya.d@example.com', '9822334455', 'BCA', 'First Year', 'Female', '388120', 'Changa', 'Campus ambassador', 'active'),
(9, NULL, 'Rohan Verma', 'rohan.v@example.com', '9833445566', 'MCA', 'Second Year', 'Male', '390007', 'Vadodara', 'Full stack development', 'active'),
(10, NULL, 'Pooja Trivedi', 'pooja.t@example.com', '9844556677', 'B.Tech IT', 'Fourth Year', 'Female', '380054', 'Ahmedabad', 'Cloud computing project', 'active'),
(11, NULL, 'Devansh Shah', 'devansh.s@example.com', '9855667788', 'B.Tech CSE', 'Third Year', 'Male', '387001', 'Nadiad', 'Cyber security researcher', 'active'),
(12, NULL, 'Tanvi Dave', 'tanvi.dave@example.com', '9866778899', 'B.Tech CE', 'Third Year', 'Female', '388001', 'Anand', 'Mobile app development', 'active'),
(13, NULL, 'Siddharth Bhatt', 'sid.bhatt@example.com', '9877889900', 'B.Tech IT', 'Second Year', 'Male', '390019', 'Vadodara', 'Data structures enthusiast', 'active'),
(14, NULL, 'Isha Parikh', 'isha.p@example.com', '9888990011', 'BCA', 'Third Year', 'Female', '380058', 'Ahmedabad', 'UI/UX Designer', 'active'),
(15, NULL, 'Harshil Rana', 'harshil.r@example.com', '9899001122', 'B.Tech CSE', 'Fourth Year', 'Male', '387002', 'Nadiad', 'Machine learning intern', 'active'),
(16, NULL, 'Meera Chokshi', 'meera.c@example.com', '9711223344', 'B.Tech CE', 'First Year', 'Female', '388121', 'Changa', 'Programming club member', 'active'),
(17, NULL, 'Kunal Pandya', 'kunal.p@example.com', '9722334455', 'MCA', 'First Year', 'Male', '390020', 'Vadodara', 'Database management system', 'active'),
(18, NULL, 'Nidhi Solanki', 'nidhi.s@example.com', '9733445566', 'B.Tech IT', 'Second Year', 'Female', '380013', 'Ahmedabad', 'Python and Django developer', 'active'),
(19, NULL, 'Yash Rajput', 'yash.r@example.com', '9744556677', 'B.Tech CSE', 'Third Year', 'Male', '387001', 'Nadiad', 'DevOps explorer', 'active'),
(20, NULL, 'Prachi Shah', 'prachi.s@example.com', '9755667788', 'BCA', 'Second Year', 'Female', '388001', 'Anand', 'Web design and animation', 'active'),
(21, NULL, 'Manan Soni', 'manan.soni@example.com', '9766778899', 'B.Tech CE', 'Fourth Year', 'Male', '390005', 'Vadodara', 'Competitive programmer', 'active'),
(22, NULL, 'Kinjal Vaghela', 'kinjal.v@example.com', '9777889900', 'B.Tech CSE', 'First Year', 'Female', '380006', 'Ahmedabad', 'AI & Data Science aspirant', 'active'),
(23, NULL, 'Aditya Joshi', 'aditya.j@example.com', '9788990011', 'B.Tech IT', 'Third Year', 'Male', '387002', 'Nadiad', 'Hackathon enthusiast', 'active'),
(24, NULL, 'Drashti Patel', 'drashti.p@example.com', '9799001122', 'B.Tech CE', 'Second Year', 'Female', '388120', 'Changa', 'IoT and Embedded Systems', 'active'),
(25, NULL, 'Parth Amin', 'parth.amin@example.com', '9700112233', 'MCA', 'Second Year', 'Male', '390012', 'Vadodara', 'Cloud architecture project', 'active');

-- 3. Seed 15 Events (Practical 6, 8, 12, 14, 15)
INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `location`, `category`, `status`, `poster`) VALUES
(1, 'Hackathon 2026', 'Join Hackathon 2026 and showcase your innovative coding skills and teamwork in 24 hours.', '2026-11-15', 'CSPIT Auditorium', 'Technical', 'Open', 'c1.png'),
(2, 'Web Development Workshop', 'Learn HTML5, CSS3, modern JavaScript ES6+, PHP, and building responsive dynamic portals.', '2026-11-20', 'CSE Lab 301', 'Workshop', 'Open', 'c2.png'),
(3, 'AI & Machine Learning Seminar', 'Explore the bleeding edge of Generative AI, Large Language Models, and deep learning architectures.', '2026-11-25', 'Seminar Hall A', 'Technical', 'Open', 'c3.png'),
(4, 'Inter-College Cricket Tournament', 'Compete with the top engineering college teams across Gujarat in this annual cricket championship.', '2026-12-02', 'University Sports Ground', 'Sports', 'Open', 'c4.png'),
(5, 'Annual Cultural Fest - Spandan', 'Celebrate music, dance, theatre, and arts in the biggest cultural fiesta of CHARUSAT.', '2026-12-10', 'Main Open Air Theatre', 'Cultural', 'Open', 'c5.png'),
(6, 'Robotics & IoT Bootcamp', 'Hands-on hardware lab building autonomous robots, Arduino and ESP32 microcontroller systems.', '2026-12-18', 'Robotics Lab 102', 'Workshop', 'Open', 'c6.png'),
(7, 'Cyber Security & Ethical Hacking', 'Learn network defense, penetration testing, SQL injection prevention, and secure web development.', '2026-11-05', 'IT Lab 204', 'Workshop', 'Closed', 'c1.png'),
(8, 'Inter-College Football Cup', 'Participate in the fast-paced 7-a-side inter-college football knockout tournament.', '2026-12-05', 'Main Stadium Turf', 'Sports', 'Open', 'c2.png'),
(9, 'Photography & Short Film Contest', 'Capture the vibrant campus life and cinematic stories of university students.', '2026-11-28', 'Campus Central Square', 'Cultural', 'Open', 'c3.png'),
(10, 'Coding Marathon & Bug Hunt', 'Speed programming contest solving algorithm puzzles and optimizing high performance queries.', '2026-10-30', 'Programming Lab 305', 'Technical', 'Closed', 'c4.png'),
(11, 'Unplugged Musical Night', 'A soothing acoustic evening featuring student bands, classical fusion, and soulful vocals.', '2026-12-22', 'Auditorium Hall B', 'Cultural', 'Open', 'c5.png'),
(12, 'Cloud Computing on AWS/GCP', 'Deploy microservices, configure containerized docker environments, and build serverless apps.', '2026-12-14', 'Lab 302', 'Workshop', 'Open', 'c6.png'),
(13, 'National Tech Quiz Competition', 'Challenge your IT, computer architecture, and tech industry knowledge against national champions.', '2026-11-12', 'Seminar Hall B', 'Technical', 'Open', 'c1.png'),
(14, 'Table Tennis & Badminton Open', 'Annual indoor racquet championship open to all engineering and pharmacy students.', '2026-12-15', 'Indoor Sports Complex', 'Sports', 'Open', 'c2.png'),
(15, 'Career Conclave & Placement Talk', 'Interact with top alumni and HR leaders from TCS, Infosys, Google, and Amazon.', '2026-10-25', 'Auditorium', 'Conference', 'Closed', 'c3.png');

-- 4. Seed Registrations (Practical 8, 14, 15)
INSERT INTO `registrations` (`id`, `user_id`, `student_id`, `event_id`, `status`) VALUES
(1, 2, 1, 1, 'Confirmed'),
(2, 2, 1, 2, 'Confirmed'),
(3, 3, 2, 1, 'Confirmed'),
(4, 5, 3, 3, 'Confirmed'),
(5, 2, 1, 4, 'Confirmed'),
(6, 3, 2, 5, 'Confirmed');

-- 5. Seed Menu Items (Practical 14 Dynamic Menu)
INSERT INTO `menu_items` (`id`, `title`, `link`, `min_role`, `icon`, `sort_order`) VALUES
(1, 'Dashboard', 'dashboard.php', 'student', 'fa-tachometer-alt', 1),
(2, 'My Profile', 'profile.php', 'student', 'fa-user', 2),
(3, 'Attendance', 'attendance.php', 'student', 'fa-calendar-check', 3),
(4, 'Timetable', 'timetable.php', 'student', 'fa-clock', 4),
(5, 'Assignments', 'assignment.php', 'student', 'fa-tasks', 5),
(6, 'Results', 'result.php', 'student', 'fa-poll', 6),
(7, 'Announcements', 'announcement.php', 'student', 'fa-bullhorn', 7),
(8, 'Campus Events', 'events.php', 'student', 'fa-calendar-alt', 8),
(9, 'Fees & Dues', 'fees.php', 'student', 'fa-receipt', 9),
(10, 'Feedback', 'feedback.php', 'student', 'fa-comment-alt', 10),
(11, 'Manage Students', 'Practical11/index.php', 'editor', 'fa-users-cog', 11),
(12, 'Manage Events', 'Practical12/index.php', 'editor', 'fa-calendar-plus', 12),
(13, 'AJAX Manager', 'Practical13/index.php', 'editor', 'fa-bolt', 13),
(14, 'Admin Overview', 'Practical14/dashboard.php', 'admin', 'fa-shield-alt', 14),
(15, 'Audit Logs', 'Practical14/audit_logs.php', 'admin', 'fa-history', 15);

-- 6. Seed Audit Logs (Practical 14)
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'Admin logged in', 'Admin initialized system dashboard', '127.0.0.1', NOW() - INTERVAL 2 DAY),
(2, 2, 'Student registration', 'Dhruvi Mokani created account', '127.0.0.1', NOW() - INTERVAL 2 DAY),
(3, 1, 'Event created', 'Added Hackathon 2026', '127.0.0.1', NOW() - INTERVAL 1 DAY),
(4, 2, 'Event registration', 'Registered for Hackathon 2026', '127.0.0.1', NOW() - INTERVAL 1 DAY),
(5, 1, 'Student updated', 'Updated student record #2', '127.0.0.1', NOW() - INTERVAL 5 HOUR),
(6, 1, 'Poster uploaded', 'Uploaded poster for Web Development Workshop', '127.0.0.1', NOW() - INTERVAL 2 HOUR);

-- 7. Seed Announcements (Portal Module)
INSERT INTO `announcements` (`id`, `title`, `category`, `content`, `publish_date`, `is_active`) VALUES
(1, 'Mid-Semester Examination Schedule Announced', 'Academic', 'The 3rd Semester Mid-Semester examinations will commence from 15th November 2026. Hall tickets can be downloaded from the portal.', '2026-10-20', 1),
(2, 'Submission of Web Development Lab Practical Files', 'Academic', 'All students must submit their complete Practical 1 to Practical 15 reports and GitHub repositories before 30th October 2026.', '2026-10-22', 1),
(3, 'Registration Open for Annual Hackathon 2026', 'Event', 'Team registrations for the 24-Hour National Hackathon are now live. Grand prizes worth Rs. 50,000 to be won!', '2026-10-24', 1),
(4, 'Diwali Vacation Notice', 'General', 'The University campus will remain closed for Diwali festivities from 1st November to 8th November 2026. Academic sessions resume on 9th November.', '2026-10-25', 1);

-- 8. Seed Timetable (Portal Module)
INSERT INTO `timetable` (`id`, `day`, `time_slot`, `subject`, `room`, `faculty`) VALUES
(1, 'Monday', '09:00 - 10:00 AM', 'Web Development Frameworks (ITUE203)', 'Lab 301', 'Dr. Martin Parmar'),
(2, 'Monday', '10:00 - 11:00 AM', 'Data Structures & Algorithms', 'Class 204', 'Prof. Vaibhavi Patel'),
(3, 'Monday', '11:15 - 01:15 PM', 'Web Development Lab', 'Lab 302', 'Prof. Ravi Patel'),
(4, 'Tuesday', '09:00 - 10:00 AM', 'Database Management Systems', 'Class 204', 'Prof. Dhaval Patel'),
(5, 'Tuesday', '10:00 - 11:00 AM', 'Computer Networks', 'Class 204', 'Dr. Mrugendra Rahevar'),
(6, 'Wednesday', '09:00 - 11:00 AM', 'DBMS Practical Lab', 'Lab 201', 'Prof. Pooja Singh'),
(7, 'Thursday', '10:00 - 11:00 AM', 'Operating Systems', 'Class 204', 'Prof. Vaibhav Matalia'),
(8, 'Friday', '02:00 - 04:00 PM', 'Project / Practical Session', 'Lab 301', 'Faculty Mentors');

-- 9. Seed Attendance for Student #1 (Dhruvi Mokani)
INSERT INTO `attendance` (`id`, `student_id`, `subject`, `total_lectures`, `attended_lectures`) VALUES
(1, 1, 'Web Development Frameworks (ITUE203)', 42, 40),
(2, 1, 'Database Management Systems', 40, 37),
(3, 1, 'Data Structures & Algorithms', 45, 42),
(4, 1, 'Computer Networks', 38, 35),
(5, 1, 'Operating Systems', 36, 33);

-- 10. Seed Assignments for Student
INSERT INTO `assignments` (`id`, `subject`, `title`, `due_date`, `max_marks`, `status`, `score`) VALUES
(1, 'Web Development', 'Practical 1 to 15 Integrated Portal Submission', '2026-10-30', 100, 'Submitted', 98),
(2, 'Database Management', 'Normalized ER Diagram & Complex SQL Queries', '2026-11-05', 50, 'Submitted', 48),
(3, 'Computer Networks', 'Packet Tracing using Wireshark & Cisco Simulation', '2026-11-12', 50, 'Pending', NULL),
(4, 'Operating Systems', 'Process Synchronization & Semaphore Implementation', '2026-11-18', 50, 'Pending', NULL);

-- 11. Seed Results for Student #1
INSERT INTO `results` (`id`, `student_id`, `semester`, `subject_code`, `subject_name`, `credits`, `grade`, `grade_point`) VALUES
(1, 1, 'Semester 2', 'ITUE101', 'Object Oriented Programming with C++', 5, 'AA', 10),
(2, 1, 'Semester 2', 'MA102', 'Engineering Mathematics II', 4, 'AB', 9),
(3, 1, 'Semester 2', 'EC101', 'Digital Electronics', 4, 'AA', 10),
(4, 1, 'Semester 2', 'HS101', 'Communication & Professional Ethics', 3, 'AA', 10),
(5, 1, 'Semester 2', 'ITUE102', 'Data Structures Foundations', 5, 'AB', 9);

-- 12. Seed Fees for Student #1
INSERT INTO `fees` (`id`, `student_id`, `semester`, `amount`, `due_date`, `status`, `transaction_id`, `paid_at`) VALUES
(1, 1, 'Semester 1', 65000.00, '2025-08-15', 'Paid', 'TXN_CHARUSAT_88291', '2025-08-10'),
(2, 1, 'Semester 2', 65000.00, '2026-01-15', 'Paid', 'TXN_CHARUSAT_93021', '2026-01-12'),
(3, 1, 'Semester 3', 68000.00, '2026-08-15', 'Paid', 'TXN_CHARUSAT_99182', '2026-08-08');

-- 13. Seed Sample Feedback
INSERT INTO `feedback` (`id`, `name`, `email`, `category`, `rating`, `comments`) VALUES
(1, 'Dhruvi Mokani', 'dhruvimokani08@gmail.com', 'Academic', 5, 'The Web Development Frameworks course is extremely practical and hands-on.'),
(2, 'Kalp Mokani', 'student01@gmail.com', 'Infrastructure', 5, 'Lab facilities and high speed Internet in CSPIT are excellent.');

-- 14. Seed Sample Contact Messages
INSERT INTO `contact_messages` (`id`, `name`, `email`, `mobile`, `pincode`, `address`, `message`) VALUES
(1, 'Dhruvi Mokani', 'dhruvimokani08@gmail.com', '9023501363', '387002', 'Nadiad', 'Inquiry regarding final year placement drive schedule.');
