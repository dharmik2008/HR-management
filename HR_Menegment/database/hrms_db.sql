-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 29, 2025 at 06:29 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hrms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `Attendance_id` int(11) NOT NULL,
  `Emp_id` int(11) NOT NULL,
  `Date` date NOT NULL,
  `Status` varchar(20) NOT NULL,
  `Checkin_time` time DEFAULT NULL,
  `Checkout_time` time DEFAULT NULL,
  `Marked_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`Attendance_id`, `Emp_id`, `Date`, `Status`, `Checkin_time`, `Checkout_time`, `Marked_by`) VALUES
(12, 12, '2025-12-22', 'Present', '09:00:00', '16:30:00', 2),
(13, 12, '2025-12-23', 'Present', '09:30:00', '16:20:00', 2),
(14, 12, '2025-12-24', 'Absent', NULL, NULL, 2),
(15, 12, '2025-12-25', 'Present', '09:00:00', '17:00:00', 2);

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `Doc_id` int(11) NOT NULL,
  `Emp_id` int(11) DEFAULT NULL,
  `File_name` varchar(255) NOT NULL,
  `File_type` varchar(100) DEFAULT NULL,
  `File_path` varchar(255) NOT NULL,
  `Uploaded_by` int(11) DEFAULT NULL,
  `Uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`Doc_id`, `Emp_id`, `File_name`, `File_type`, `File_path`, `Uploaded_by`, `Uploaded_at`) VALUES
(8, 12, 'misurina-sunset.jpg', 'jpg', 'D:\\xampp\\htdocs\\dharmik\\HR_Menegment\\backend\\config/../../uploads/documents/file_695203d86b70b_1766982616.jpg', 12, '2025-12-29 10:00:16');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `Emp_id` int(11) NOT NULL,
  `Emp_code` varchar(50) NOT NULL,
  `Emp_firstName` varchar(100) NOT NULL,
  `Emp_lastName` varchar(100) NOT NULL,
  `Emp_email` varchar(255) NOT NULL,
  `Emp_password` varchar(255) NOT NULL,
  `Emp_phone` varchar(50) DEFAULT NULL,
  `Emp_dob` date DEFAULT NULL,
  `Emp_gender` varchar(10) DEFAULT NULL,
  `Joining_date` date DEFAULT NULL,
  `ProjectCategory_id` int(11) DEFAULT NULL,
  `Salary` decimal(12,2) DEFAULT NULL,
  `Address` text DEFAULT NULL,
  `Profile_pic` varchar(255) DEFAULT NULL,
  `Status` varchar(20) DEFAULT 'Active',
  `Created_at` datetime DEFAULT current_timestamp(),
  `reset_code` int(6) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`Emp_id`, `Emp_code`, `Emp_firstName`, `Emp_lastName`, `Emp_email`, `Emp_password`, `Emp_phone`, `Emp_dob`, `Emp_gender`, `Joining_date`, `ProjectCategory_id`, `Salary`, `Address`, `Profile_pic`, `Status`, `Created_at`, `reset_code`, `reset_expiry`) VALUES
(9, 'nir15', 'Nirav', 'Nimavat', 'niravnimavat01@gmail.com', '$2y$12$D8R54ldLRRCOCXRNp3tNU.Qmf2En4kLpUPfuBhuuQ2VHzwMkqcUlC', '7777777777', '2006-06-20', 'Male', '2025-12-16', 2, 45699.00, 'darshan university,hadala patiya,rajkot morbi highway', 'D:\\xampp\\htdocs\\dharmik\\HR_Menegment\\backend\\config/../../uploads/profiles/file_694230d67702b_1765945558.jpg', 'Active', '2025-12-17 09:55:58', 978959, '2025-12-22 06:33:57'),
(12, 'dhar1', 'Dharmik', 'Vaishnani', 'dharmikpatel20062008@gmail.com', '$2y$12$fb3vAGwEglNKFXgtP0gw1O7qDTWzme4OVwwsBv/Cvk02De2zVW6mW', '8849484307', '2007-06-20', 'Male', '2025-12-20', 6, 56300.00, 'Kesiya', 'D:\\xampp\\htdocs\\dharmik\\HR_Menegment\\backend\\config/../../uploads/profiles/file_6952116d5b906_1766986093.jpg', 'Active', '2025-12-26 10:24:06', NULL, NULL),
(13, 'shi1', 'shivam', 'Kansagara', 'kansagarashivam40@gmail.com', '$2y$12$4F2nwgOHfxF0zkFfWIYo6Ozkc97bf5e9LdPUqSXcoWpxn8nwIuj/q', '7383854005', '2007-09-25', 'Male', '2025-12-27', 4, 45600.00, 'anjar', 'D:\\xampp\\htdocs\\dharmik\\HR_Menegment\\backend\\config/../../uploads/profiles/file_694e37f6547df_1766733814.png', 'Active', '2025-12-26 12:53:34', 824097, '2025-12-26 08:39:40');

-- --------------------------------------------------------

--
-- Table structure for table `hr_admins`
--

CREATE TABLE `hr_admins` (
  `Hr_id` int(11) NOT NULL,
  `Hr_code` varchar(150) NOT NULL,
  `Hr_firstName` varchar(100) DEFAULT NULL,
  `Hr_lastName` varchar(100) DEFAULT NULL,
  `Hr_email` varchar(255) NOT NULL,
  `Hr_password` varchar(255) NOT NULL,
  `Hr_phone` varchar(20) DEFAULT NULL,
  `Created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hr_admins`
--

INSERT INTO `hr_admins` (`Hr_id`, `Hr_code`, `Hr_firstName`, `Hr_lastName`, `Hr_email`, `Hr_password`, `Hr_phone`, `Created_at`) VALUES
(1, 'HR001', 'Rahul', 'Kumar', 'admin@hrms.com', '5f4dcc3b5aa765d61d8327deb882cf99', '9876543210', '2025-12-10 11:45:51'),
(2, 'HR002', 'dharmik', '', 'vaishnanidharmik@gmail.com', '$2y$12$EG6u4nlfCDWrVxcrpUrtX.ofavb1c99iFhayTJx/frIcfU17Qhpo6', '0000000000', '2025-12-10 18:30:10');

-- --------------------------------------------------------

--
-- Table structure for table `leaves`
--

CREATE TABLE `leaves` (
  `Leave_id` int(11) NOT NULL,
  `Emp_id` int(11) NOT NULL,
  `Leave_type` varchar(100) DEFAULT NULL,
  `Start_date` date DEFAULT NULL,
  `End_date` date DEFAULT NULL,
  `Days` int(11) DEFAULT NULL,
  `Status` varchar(20) DEFAULT 'Pending',
  `Comment` text DEFAULT NULL,
  `Attachment` varchar(255) DEFAULT NULL,
  `Requested_at` datetime DEFAULT current_timestamp(),
  `Action_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leaves`
--

INSERT INTO `leaves` (`Leave_id`, `Emp_id`, `Leave_type`, `Start_date`, `End_date`, `Days`, `Status`, `Comment`, `Attachment`, `Requested_at`, `Action_by`) VALUES
(9, 12, 'Casual', '2025-12-27', '2025-12-27', 1, 'Pending', NULL, NULL, '2025-12-26 12:50:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `Notification_id` int(11) NOT NULL,
  `Target_type` varchar(20) NOT NULL,
  `Target_id` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Message` text DEFAULT NULL,
  `Status` varchar(20) DEFAULT 'Unread',
  `Created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`Notification_id`, `Target_type`, `Target_id`, `Title`, `Message`, `Status`, `Created_at`) VALUES
(1, '1', 1, 'Task Assigned', 'New task assigned: Setup Database', 'Unread', '2025-12-10 11:45:51'),
(2, '1', 2, 'Leave Approved', 'Your sick leave has been approved', 'Read', '2025-12-10 11:45:51'),
(3, '1', 1, 'Attendance Reminder', 'Please mark your attendance', 'Unread', '2025-12-10 11:45:51'),
(4, '1', 3, 'Leave Status', 'Your annual leave has been approved', 'Read', '2025-12-10 11:45:51'),
(26, 'employee', 9, 'New Project Assignment', 'You have been added to project \"laravel website\".', 'Read', '2025-12-19 10:13:50'),
(27, 'employee', 12, 'Attendance Updated', 'Your attendance for Dec 22, 2025 has been marked as Present.', 'Read', '2025-12-26 12:25:02'),
(28, 'employee', 12, 'Attendance Updated', 'Your attendance for Dec 23, 2025 has been marked as Present.', 'Read', '2025-12-26 12:31:21'),
(29, 'employee', 12, 'Attendance Updated', 'Your attendance for Dec 24, 2025 has been marked as Absent.', 'Read', '2025-12-26 12:35:30'),
(30, 'employee', 12, 'Attendance Updated', 'Your attendance for Dec 25, 2025 has been marked as Present.', 'Read', '2025-12-26 12:39:18'),
(31, 'employee', 12, 'Attendance Updated', 'Your attendance for Dec 25, 2025 has been marked as Present.', 'Read', '2025-12-26 12:43:06'),
(32, 'employee', 12, 'New Project Assigned', 'You have been assigned to project \"coffee shop\".', 'Read', '2025-12-26 12:46:54'),
(33, 'employee', 12, 'New Task Assigned', 'A new task \"add coffee photo and price\" has been assigned to you', 'Read', '2025-12-26 12:48:31');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `Project_id` int(11) NOT NULL,
  `Project_name` varchar(200) NOT NULL,
  `ProjectCategory_id` int(11) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Start_date` date DEFAULT NULL,
  `End_date` date DEFAULT NULL,
  `Status` varchar(50) DEFAULT 'Active',
  `Created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`Project_id`, `Project_name`, `ProjectCategory_id`, `Description`, `Start_date`, `End_date`, `Status`, `Created_at`) VALUES
(1, 'Mobile App Development', 1, 'Building iOS and Android apps', '2023-06-01', '2024-06-01', 'Active', '2025-12-10 11:45:51'),
(2, 'Website Redesign', 2, 'Redesigning company website', '2023-07-15', '2024-01-31', 'Active', '2025-12-10 11:45:51'),
(3, 'Cloud Migration', 1, 'Migrating to cloud infrastructure', '2023-08-01', '2024-08-01', 'Active', '2025-12-10 11:45:51'),
(4, 'sem-6', 4, 'sem-6 project', '2025-12-09', '2025-12-30', 'Active', '2025-12-10 19:49:57'),
(5, 'laravel website', 1, 'one type of ecommerce but not selling from portal and just forword to my whatsapp app', '2025-12-16', '2026-02-01', 'Active', '2025-12-16 12:35:00'),
(6, 'coffee shop', 2, 'make the coffee shop website \r\nin website login,view item,buy coffee,feedback,book table,....', '2025-12-26', '2026-01-01', 'Active', '2025-12-26 12:46:54');

-- --------------------------------------------------------

--
-- Table structure for table `project_assign`
--

CREATE TABLE `project_assign` (
  `Assign_id` int(11) NOT NULL,
  `Project_id` int(11) NOT NULL,
  `Emp_id` int(11) NOT NULL,
  `Assigned_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_assign`
--

INSERT INTO `project_assign` (`Assign_id`, `Project_id`, `Emp_id`, `Assigned_at`) VALUES
(11, 5, 9, '2025-12-19 10:13:50'),
(12, 6, 12, '2025-12-26 12:46:54');

-- --------------------------------------------------------

--
-- Table structure for table `project_category`
--

CREATE TABLE `project_category` (
  `ProjectCategory_id` int(11) NOT NULL,
  `Category_name` varchar(150) NOT NULL,
  `Description` text DEFAULT NULL,
  `Created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_category`
--

INSERT INTO `project_category` (`ProjectCategory_id`, `Category_name`, `Description`, `Created_at`) VALUES
(1, 'Engineering', 'Software Development Team', '2025-12-10 11:45:51'),
(2, 'Design', 'UI/UX Design Team', '2025-12-10 11:45:51'),
(4, 'Marketing', 'Marketing and Sales', '2025-12-10 11:45:51'),
(6, 'Management', 'Management of project team', '2025-12-10 19:56:07'),
(7, 'SEO Team', 'must focus on dubai', '2025-12-16 12:37:16');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `Task_id` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Description` text DEFAULT NULL,
  `Assigned_by` int(11) DEFAULT NULL,
  `Assigned_to` int(11) DEFAULT NULL,
  `Project_id` int(11) DEFAULT NULL,
  `Priority` varchar(50) DEFAULT NULL,
  `Status` varchar(50) DEFAULT 'Pending',
  `Due_date` date DEFAULT NULL,
  `Created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`Task_id`, `Title`, `Description`, `Assigned_by`, `Assigned_to`, `Project_id`, `Priority`, `Status`, `Due_date`, `Created_at`) VALUES
(9, 'add coffee photo and price', 'add coffee photo and price', 2, 12, 6, 'Medium', 'In Progress', '2025-12-27', '2025-12-26 12:48:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`Attendance_id`),
  ADD KEY `fk_attendance_employee` (`Emp_id`),
  ADD KEY `fk_attendance_marked_by` (`Marked_by`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`Doc_id`),
  ADD KEY `fk_documents_employee` (`Emp_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`Emp_id`),
  ADD UNIQUE KEY `Emp_code` (`Emp_code`),
  ADD UNIQUE KEY `Emp_email` (`Emp_email`),
  ADD KEY `fk_employees_project_category` (`ProjectCategory_id`);

--
-- Indexes for table `hr_admins`
--
ALTER TABLE `hr_admins`
  ADD PRIMARY KEY (`Hr_id`),
  ADD UNIQUE KEY `Hr_email` (`Hr_email`);

--
-- Indexes for table `leaves`
--
ALTER TABLE `leaves`
  ADD PRIMARY KEY (`Leave_id`),
  ADD KEY `fk_leaves_employee` (`Emp_id`),
  ADD KEY `fk_leaves_action_by` (`Action_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`Notification_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`Project_id`),
  ADD KEY `fk_projects_project_category` (`ProjectCategory_id`);

--
-- Indexes for table `project_assign`
--
ALTER TABLE `project_assign`
  ADD PRIMARY KEY (`Assign_id`),
  ADD KEY `fk_assign_project` (`Project_id`),
  ADD KEY `fk_assign_employee` (`Emp_id`);

--
-- Indexes for table `project_category`
--
ALTER TABLE `project_category`
  ADD PRIMARY KEY (`ProjectCategory_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`Task_id`),
  ADD KEY `fk_tasks_assigned_by` (`Assigned_by`),
  ADD KEY `fk_tasks_assigned_to` (`Assigned_to`),
  ADD KEY `fk_tasks_project` (`Project_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `Attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `Doc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `Emp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `hr_admins`
--
ALTER TABLE `hr_admins`
  MODIFY `Hr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `leaves`
--
ALTER TABLE `leaves`
  MODIFY `Leave_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `Notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `Project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `project_assign`
--
ALTER TABLE `project_assign`
  MODIFY `Assign_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `project_category`
--
ALTER TABLE `project_category`
  MODIFY `ProjectCategory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `Task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_attendance_employee` FOREIGN KEY (`Emp_id`) REFERENCES `employees` (`Emp_id`),
  ADD CONSTRAINT `fk_attendance_marked_by` FOREIGN KEY (`Marked_by`) REFERENCES `hr_admins` (`Hr_id`);

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `fk_documents_employee` FOREIGN KEY (`Emp_id`) REFERENCES `employees` (`Emp_id`);

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `fk_employees_project_category` FOREIGN KEY (`ProjectCategory_id`) REFERENCES `project_category` (`ProjectCategory_id`);

--
-- Constraints for table `leaves`
--
ALTER TABLE `leaves`
  ADD CONSTRAINT `fk_leaves_action_by` FOREIGN KEY (`Action_by`) REFERENCES `hr_admins` (`Hr_id`),
  ADD CONSTRAINT `fk_leaves_employee` FOREIGN KEY (`Emp_id`) REFERENCES `employees` (`Emp_id`);

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `fk_projects_project_category` FOREIGN KEY (`ProjectCategory_id`) REFERENCES `project_category` (`ProjectCategory_id`);

--
-- Constraints for table `project_assign`
--
ALTER TABLE `project_assign`
  ADD CONSTRAINT `fk_assign_employee` FOREIGN KEY (`Emp_id`) REFERENCES `employees` (`Emp_id`),
  ADD CONSTRAINT `fk_assign_project` FOREIGN KEY (`Project_id`) REFERENCES `projects` (`Project_id`);

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `fk_tasks_assigned_by` FOREIGN KEY (`Assigned_by`) REFERENCES `hr_admins` (`Hr_id`),
  ADD CONSTRAINT `fk_tasks_assigned_to` FOREIGN KEY (`Assigned_to`) REFERENCES `employees` (`Emp_id`),
  ADD CONSTRAINT `fk_tasks_project` FOREIGN KEY (`Project_id`) REFERENCES `projects` (`Project_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
