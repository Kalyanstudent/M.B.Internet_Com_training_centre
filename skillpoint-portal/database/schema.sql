-- ============================================================================
-- MB Internet And Digital Studio | Mobile & Gift House
-- Complete Production Database Schema
-- Target: MySQL 8.0+ / MariaDB 10.4+
-- Charset: utf8mb4 / Collation: utf8mb4_unicode_ci
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+05:30";

-- ----------------------------------------------------------------------------
-- 1. Table: users (Unified Student Users & Administrators)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_code` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `mobile` VARCHAR(20) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `address` TEXT NULL,
  `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_users_email` (`email`),
  INDEX `idx_users_mobile` (`mobile`),
  INDEX `idx_users_role` (`role`),
  INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. Table: courses (Computer Training Catalog)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `courses`;
CREATE TABLE `courses` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_code` VARCHAR(80) NOT NULL UNIQUE,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `title` VARCHAR(180) NOT NULL,
  `category` VARCHAR(80) NOT NULL,
  `short_desc` TEXT NOT NULL,
  `overview` LONGTEXT NULL,
  `duration` VARCHAR(50) NOT NULL,
  `hours` VARCHAR(50) NOT NULL DEFAULT '45 Hours',
  `price` DECIMAL(10,2) NOT NULL,
  `original_price` DECIMAL(10,2) NULL,
  `rating` DECIMAL(3,2) NOT NULL DEFAULT 4.90,
  `reviews_count` INT UNSIGNED NOT NULL DEFAULT 100,
  `badge` VARCHAR(50) NULL,
  `icon` VARCHAR(80) NOT NULL DEFAULT 'bi-journal-bookmark-fill',
  `level` VARCHAR(80) NOT NULL DEFAULT 'Beginner to Advanced',
  `eligibility` VARCHAR(150) NOT NULL DEFAULT 'Open to all students & job seekers',
  `learnings_json` LONGTEXT NULL,
  `syllabus_json` LONGTEXT NULL,
  `benefits_json` LONGTEXT NULL,
  `batches_json` LONGTEXT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `display_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_courses_category` (`category`),
  INDEX `idx_courses_status` (`status`),
  INDEX `idx_courses_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. Table: orders (Course Purchase Orders)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_number` VARCHAR(60) NOT NULL UNIQUE,
  `enrollment_number` VARCHAR(60) NULL UNIQUE,
  `user_id` INT UNSIGNED NOT NULL,
  `course_id` INT UNSIGNED NOT NULL,
  `customer_name` VARCHAR(120) NOT NULL,
  `mobile` VARCHAR(20) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `address` TEXT NULL,
  `course_title` VARCHAR(180) NOT NULL,
  `course_price` DECIMAL(10,2) NOT NULL,
  `coupon_code` VARCHAR(50) NULL,
  `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `final_amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'INR',
  `payment_gateway` VARCHAR(50) NOT NULL DEFAULT 'Razorpay',
  `payment_status` ENUM('Pending', 'Paid', 'Failed', 'Refunded') NOT NULL DEFAULT 'Pending',
  `razorpay_order_id` VARCHAR(100) NULL,
  `razorpay_payment_id` VARCHAR(100) NULL,
  `razorpay_signature` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_orders_user_id` (`user_id`),
  INDEX `idx_orders_course_id` (`course_id`),
  INDEX `idx_orders_number` (`order_number`),
  INDEX `idx_orders_status` (`payment_status`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_orders_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. Table: payments (Verified Transactions Audit Log)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_number` VARCHAR(60) NOT NULL UNIQUE,
  `order_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'INR',
  `gateway` VARCHAR(50) NOT NULL DEFAULT 'Razorpay',
  `gateway_order_id` VARCHAR(100) NULL,
  `gateway_payment_id` VARCHAR(100) NULL,
  `gateway_signature` VARCHAR(255) NULL,
  `status` ENUM('Success', 'Failed', 'Pending', 'Refunded') NOT NULL DEFAULT 'Success',
  `notes` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_payments_order_id` (`order_id`),
  INDEX `idx_payments_user_id` (`user_id`),
  INDEX `idx_payments_status` (`status`),
  CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_payments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 5. Table: enrollments (Active Student Course Enrollments)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `enrollments`;
CREATE TABLE `enrollments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `enrollment_number` VARCHAR(60) NOT NULL UNIQUE,
  `order_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `course_id` INT UNSIGNED NOT NULL,
  `status` ENUM('Active', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Active',
  `enrolled_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_enrollments_user_id` (`user_id`),
  INDEX `idx_enrollments_course_id` (`course_id`),
  INDEX `idx_enrollments_number` (`enrollment_number`),
  CONSTRAINT `fk_enrollments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_enrollments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_enrollments_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 6. Table: tax_services (Tax & ITR Compliance Services)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tax_services`;
CREATE TABLE `tax_services` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `title` VARCHAR(150) NOT NULL,
  `category` VARCHAR(80) NOT NULL DEFAULT 'Taxation',
  `short_desc` TEXT NOT NULL,
  `full_desc` LONGTEXT NULL,
  `icon` VARCHAR(80) NOT NULL DEFAULT 'bi-file-earmark-medical-fill',
  `accent_class` VARCHAR(50) NOT NULL DEFAULT 'accent-tax',
  `price` DECIMAL(10,2) NULL,
  `display_order` INT NOT NULL DEFAULT 0,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tax_services_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 7. Table: service_requests (Tax & Digital Service Submissions)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `service_requests`;
CREATE TABLE `service_requests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_number` VARCHAR(60) NOT NULL UNIQUE,
  `user_id` INT UNSIGNED NOT NULL,
  `service_id` INT UNSIGNED NULL,
  `service_type` VARCHAR(150) NOT NULL,
  `customer_name` VARCHAR(120) NOT NULL,
  `mobile` VARCHAR(20) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `address` TEXT NULL,
  `pan_number` VARCHAR(30) NULL,
  `financial_year` VARCHAR(20) NOT NULL DEFAULT '2025-26',
  `assessment_year` VARCHAR(20) NOT NULL DEFAULT '2026-27',
  `status` ENUM('Pending', 'Processing', 'Need More Information', 'Completed', 'Rejected') NOT NULL DEFAULT 'Pending',
  `internal_notes` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_requests_user_id` (`user_id`),
  INDEX `idx_requests_status` (`status`),
  INDEX `idx_requests_number` (`request_number`),
  CONSTRAINT `fk_requests_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_requests_service` FOREIGN KEY (`service_id`) REFERENCES `tax_services` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 8. Table: documents (Protected Vault)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` INT UNSIGNED NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `stored_name` VARCHAR(255) NOT NULL UNIQUE,
  `file_type` VARCHAR(100) NOT NULL,
  `file_size` INT UNSIGNED NOT NULL,
  `file_size_formatted` VARCHAR(30) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `upload_ip` VARCHAR(45) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_documents_request_id` (`request_id`),
  INDEX `idx_documents_user_id` (`user_id`),
  CONSTRAINT `fk_documents_request` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_documents_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 9. Table: important_links (Official Government & Digital Portals)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `important_links`;
CREATE TABLE `important_links` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `link_code` VARCHAR(50) NOT NULL UNIQUE,
  `title` VARCHAR(150) NOT NULL,
  `category` VARCHAR(80) NOT NULL DEFAULT 'Taxation',
  `description` TEXT NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `button_text` VARCHAR(80) NOT NULL DEFAULT 'Visit Portal',
  `icon` VARCHAR(80) NOT NULL DEFAULT 'bi-link-45deg',
  `accent_class` VARCHAR(50) NOT NULL DEFAULT 'accent-links',
  `display_order` INT NOT NULL DEFAULT 1,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_links_status` (`status`),
  INDEX `idx_links_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 10. Table: digital_services (Form Filling, Printing, Scanning)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `digital_services`;
CREATE TABLE `digital_services` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `title` VARCHAR(150) NOT NULL,
  `category` VARCHAR(80) NOT NULL DEFAULT 'Online Form & Citizen Services',
  `description` TEXT NOT NULL,
  `icon` VARCHAR(80) NOT NULL DEFAULT 'bi-printer-fill',
  `accent_class` VARCHAR(50) NOT NULL DEFAULT 'accent-digital',
  `price_text` VARCHAR(100) NOT NULL DEFAULT 'Nominal Service Charge',
  `display_order` INT NOT NULL DEFAULT 1,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_digital_services_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 11. Table: products (Mobile & Gift House Catalog)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(150) NOT NULL,
  `category` VARCHAR(80) NOT NULL,
  `description` TEXT NOT NULL,
  `price` DECIMAL(10,2) NULL,
  `badge` VARCHAR(50) NULL,
  `image` VARCHAR(255) NULL,
  `icon` VARCHAR(80) NOT NULL DEFAULT 'bi-phone-fill',
  `accent_class` VARCHAR(50) NOT NULL DEFAULT 'accent-mobile',
  `display_order` INT NOT NULL DEFAULT 1,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_products_category` (`category`),
  INDEX `idx_products_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 12. Table: settings (Institute Configuration & Razorpay Credentials)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(80) NOT NULL UNIQUE,
  `setting_value` LONGTEXT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- SEED DATA
-- ============================================================================

-- 1. Initial Users (Admin & Seed Users)
-- Admin: admin@mbinternet.com / admin123
-- User: user@mbinternet.com / student123
INSERT INTO `users` (`id`, `user_code`, `name`, `email`, `mobile`, `password_hash`, `address`, `role`, `status`, `created_at`) VALUES
(1, 'adm-001', 'Mukesh Bhattacharya (Admin)', 'admin@mbinternet.com', '9876500001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'MB Internet And Digital Studio, Main Road', 'admin', 'active', NOW()),
(2, 'usr-101', 'Rahul Sharma', 'user@mbinternet.com', '9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Station Road, City Centre', 'user', 'active', NOW()),
(3, 'usr-102', 'Priya Sen', 'priya.sen@gmail.com', '9811223344', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'College Para, Ward 4', 'user', 'active', NOW()),
(4, 'usr-103', 'Amitava Das', 'amitava.das@outlook.com', '9823012345', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Market Square, Main Town', 'user', 'active', NOW());

-- 2. Computer Training Courses (8 Core Courses)
INSERT INTO `courses` (
  `id`, `course_code`, `slug`, `title`, `category`, `short_desc`, `overview`, 
  `duration`, `hours`, `price`, `original_price`, `rating`, `reviews_count`, 
  `badge`, `icon`, `level`, `eligibility`, `learnings_json`, `syllabus_json`, 
  `benefits_json`, `batches_json`, `status`, `display_order`
) VALUES 
(
  1,
  'course-basic-computer',
  'basic-computer-digital-literacy',
  'Basic Computer & Digital Literacy',
  'Computer Basics',
  'Fundamental PC operations, Windows 11, Typing tutor, Internet navigation, email, and digital safety.',
  'An essential course designed for beginners, students, and seniors to gain solid confidence in operating computers, creating basic documents, browsing the internet safely, and accessing online citizen services.',
  '2 Months',
  '40 Hours',
  1999.00,
  3000.00,
  4.90,
  165,
  'Foundation',
  'bi-pc-display',
  'Beginner (Zero Knowledge)',
  'Open to all students, job seekers, and seniors',
  '["Computer architecture, hardware parts, and Windows 11 navigation","Touch typing speed enhancement in English & Regional fonts","Internet searching, Gmail, cloud storage & Google Drive","Digital payments safety (UPI, Net Banking)","Government portal form fill-up and online application techniques"]',
  '[{"module":"Module 1","title":"Computer Fundamentals & OS Navigation","topics":["Input/Output devices, CPU, RAM and storage","Desktop, file system, folders and shortcuts","Control Panel, settings and driver awareness"]},{"module":"Module 2","title":"Typing & Internet Applications","topics":["Touch Typing Mastery (25+ WPM target)","Web Browsing, search strategies and bookmarks","Creating, composing and managing Gmail"]},{"module":"Module 3","title":"Digital Citizen Portals & Cyber Safety","topics":["Accessing govt portals & downloading certificates","Online payments security & avoiding phishing","Safe file downloads and antivirus basics"]}]',
  '["Government recognized standard course completion certificate","1-on-1 practical computer lab allocation","Free reference study notes and speed typing kit"]',
  '[{"type":"Morning Batch","time":"08:30 AM - 10:00 AM (Mon - Fri)","seats":"4 Seats Available"},{"type":"Evening Batch","time":"05:00 PM - 06:30 PM (Mon - Fri)","seats":"3 Seats Available"}]',
  'active',
  1
),
(
  2,
  'course-ms-word',
  'ms-word-official-documentation',
  'MS Word & Official Documentation',
  'Office Productivity',
  'Professional document creation, official letterhead formatting, table layout, and automated Mail Merge.',
  'Master Microsoft Word for office administration, legal typing, commercial agreements, reports, and bulk automated mailing workflows.',
  '1 Month',
  '25 Hours',
  1499.00,
  2200.00,
  4.85,
  98,
  'Essential',
  'bi-file-earmark-word-fill',
  'Beginner to Intermediate',
  'Students, Office Clerks, Data Entry Aspirants',
  '["Complete typography, margins, orientation, and layout control","Multi-column articles, brochures, and invoice tables in Word","Official letterheads, watermarks, headers & footers","Automated Mail Merge for bulk dispatch of letters/certificates","Converting documents to secure PDFs and password protection"]',
  '[{"module":"Module 1","title":"Document Design & Typography","topics":["Font styling, paragraph spacing, and line height","Bullets, numbered lists, and multi-level outlines","Page borders, shading, and background watermarks"]},{"module":"Module 2","title":"Tables, Graphics & SmartArt","topics":["Advanced Table formatting, cell splitting & merging","Inserting images, alignment, wrap text and captions","SmartArt hierarchy charts and process flow diagrams"]},{"module":"Module 3","title":"Automation & Mail Merge","topics":["Creating recipient lists in Excel/Word","Setting up Mail Merge letters and envelopes","Generating mass identity cards and certificates"]}]',
  '["Certificate in Office Word Documentation","Official document templates library","Speed typing practice included"]',
  '[{"type":"Morning Fast-Track","time":"10:00 AM - 11:30 AM (Mon - Fri)","seats":"6 Seats Available"}]',
  'active',
  2
),
(
  3,
  'course-ms-excel',
  'ms-excel-data-management',
  'MS Excel Essentials & Data Management',
  'Office Productivity',
  'Data entry, formulas (SUM, AVERAGE, IF, VLOOKUP), charts, sorting, filtering, and conditional formatting.',
  'Gain complete command over Microsoft Excel spreadsheets for commercial billing, payroll management, stock registers, and data analysis.',
  '1.5 Months',
  '35 Hours',
  2499.00,
  3500.00,
  4.90,
  210,
  'High Demand',
  'bi-file-earmark-excel-fill',
  'Beginner to Intermediate',
  'Commerce students, shop owners, accountants',
  '["Cell referencing: Relative, Absolute ($), and Mixed references","Essential business formulas (SUM, COUNTIF, SUMIFS, IF/ELSE)","VLOOKUP and HLOOKUP database lookups","Data sorting, advanced multi-level filtering, and cell validation rules","Building informative bar, line, and pie charts with dynamic labels"]',
  '[{"module":"Module 1","title":"Workbook Mechanics & Calculations","topics":["Data entry shortcuts, flash fill, and autofill","Mathematical calculations and basic functions","Cell formatting (Currency, Dates, Percentages)"]},{"module":"Module 2","title":"Logical Functions & Lookups","topics":["IF, AND, OR conditional statements","VLOOKUP and HLOOKUP formula architecture","Error handling with IFERROR and ISBLANK"]},{"module":"Module 3","title":"Data Visualization & Printing","topics":["Conditional formatting rules and data bars","Dynamic 2D/3D charts and trendlines","Print area setup, header/footer, fit-to-page"]}]',
  '["Certified MS Excel Specialist Certificate","50+ Ready commercial Excel templates","Practical business billing assignments"]',
  '[{"type":"Evening Batch","time":"06:30 PM - 08:00 PM (Mon - Fri)","seats":"5 Seats Available"}]',
  'active',
  3
),
(
  4,
  'course-adv-excel',
  'advanced-ms-excel-mis-analytics',
  'Advanced MS Excel & MIS Analytics',
  'Office Productivity',
  'XLOOKUP, INDEX/MATCH, Pivot Tables, Slicers, Power Query data cleanup, and dynamic KPI dashboards.',
  'Elevate your corporate reporting and MIS analytics skills with automated KPI executive dashboards, Power Query data transformation, and dynamic array lookups.',
  '2 Months',
  '45 Hours',
  3499.00,
  5000.00,
  5.00,
  320,
  'Top Rated',
  'bi-file-earmark-spreadsheet-fill',
  'Intermediate to Advanced',
  'MIS Executives, Accountants, Data Analysts',
  '["Modern dynamic formulas: XLOOKUP, INDEX-MATCH, FILTER, UNIQUE","Complex multi-condition aggregations and nested logical arrays","Interactive Pivot Tables, Slicers, Timelines, and calculated fields","Automating repetitive data sanitization using Power Query","Building dynamic KPI executive dashboards with conditional formatting","Workbook security, cell locking, and introductory Macro automation"]',
  '[{"module":"Module 1","title":"Advanced Formula Engineering","topics":["XLOOKUP, 2-Way Lookups with INDEX & MATCH","Array formulas, dynamic spilling, LET and LAMBDA basics","Text manipulation (TEXTSPLIT, TEXTJOIN, REGEXEXTRACT)"]},{"module":"Module 2","title":"Pivot Tables & Data Modeling","topics":["Grouping dates/numbers, custom calculated items","Slicers & interactive visual dashboard filters","Connecting multiple tables with Relationships"]},{"module":"Module 3","title":"Power Query & Executive Dashboards","topics":["ETL: Extract, Transform & Load external data","Automating monthly report compilation","Building executive MIS KPI dashboard cards"]}]',
  '["Corporate MIS Analyst Certificate","Comprehensive interview question kit","5 ready-to-deploy corporate dashboard templates"]',
  '[{"type":"Morning Pro","time":"07:00 AM - 08:30 AM (Mon - Fri)","seats":"2 Seats Available"},{"type":"Sunday Intensive","time":"09:00 AM - 01:00 PM (Every Sunday)","seats":"3 Seats Available"}]',
  'active',
  4
),
(
  5,
  'course-powerpoint',
  'ms-powerpoint-presentation-mastery',
  'MS PowerPoint Presentation Mastery',
  'Office Productivity',
  'Corporate slide design, custom animations, infographic charts, and video presentation export.',
  'Learn to create visually stunning business pitch decks, academic seminars, and marketing presentation slides that captivate audiences.',
  '1 Month',
  '20 Hours',
  1499.00,
  2200.00,
  4.80,
  75,
  'Skill Builder',
  'bi-file-earmark-ppt-fill',
  'Beginner to Intermediate',
  'Students, Teachers, Sales Executives',
  '["Slide Master customization for uniform company branding","Infographic design, vector icons, and layout symmetry","Custom object animations, entrance/exit effects, and smooth morph transitions","Embedding audio, background music, and video narrations","Exporting presentations to HD MP4 video and interactive PDF slides"]',
  '[{"module":"Module 1","title":"Slide Architecture & Branding","topics":["Slide Master, themes, and custom color palettes","Typography hierarchy, alignment, and whitespace balance","SmartArt transformation into editable custom shapes"]},{"module":"Module 2","title":"Animations & Dynamic Transitions","topics":["Triggered animations, motion paths, and timings","The Power of Morph transition for fluid movement","Embedding multimedia, screen recordings, and voiceover"]}]',
  '["Master Presenter Certificate","100+ Premium PowerPoint Slide Templates"]',
  '[{"type":"Afternoon Batch","time":"02:00 PM - 03:30 PM (Mon - Fri)","seats":"7 Seats Available"}]',
  'active',
  5
),
(
  6,
  'course-tally-prime',
  'tally-prime-accounting-professional',
  'Tally Prime Accounting Professional',
  'Accounting',
  'Complete financial accounting, company creation, inventory control, voucher entries, and BRS.',
  'Hands-on practical accounting training on the official Tally Prime software with real trade invoices, stock godowns, bank reconciliations, and balance sheet finalization.',
  '2 Months',
  '50 Hours',
  3499.00,
  5500.00,
  4.95,
  280,
  'Job Oriented',
  'bi-calculator-fill',
  'Beginner to Intermediate',
  '12th Commerce / B.Com Students / Accountants',
  '["Company creation, security control, and chart of accounts hierarchy","Standard vouchers: Sales, Purchase, Payment, Receipt, Journal, Contra","Inventory management, stock categories, units of measure, and godowns","Bank Reconciliation Statement (BRS) with live bank statements","Trial Balance, Profit & Loss Account, and Balance Sheet finalization"]',
  '[{"module":"Module 1","title":"Accounting Principles & Tally Basics","topics":["Golden rules of accounting and debit/credit logic","Company creation, alteration, and security passwords","Master configuration (Ledger groups, Cost centres)"]},{"module":"Module 2","title":"Voucher Processing & Inventory","topics":["Purchase & sales order processing with delivery challans","Standard voucher entries with discounts and rounding off","Stock items, batches, expiry dates, and multiple godowns"]},{"module":"Module 3","title":"Financial Statements & Analysis","topics":["Automated Bank Reconciliation (BRS)","Trial Balance, P&L, Balance sheet inspection","Exporting accounting books to Excel and PDF"]}]',
  '["Professional Tally Accountant Certificate","100% Practical lab assignments on real trade data","Placement assistance with local retail & business accounts"]',
  '[{"type":"Morning Batch","time":"10:00 AM - 11:30 AM (Mon - Fri)","seats":"4 Seats Available"},{"type":"Evening Batch","time":"06:00 PM - 07:30 PM (Mon - Fri)","seats":"2 Seats Available"}]',
  'active',
  6
),
(
  7,
  'course-tally-gst',
  'tally-prime-gst-taxation-mastery',
  'Tally Prime + GST Taxation Mastery',
  'Accounting',
  'Advanced Tally Prime integrated with practical GST e-filing, e-invoicing, E-Way bills, and TDS.',
  'An all-in-one comprehensive accounting and taxation course covering live GST portal workflow, GSTR-1, GSTR-3B preparation, ITC 2B reconciliation, and TDS deduction.',
  '3 Months',
  '70 Hours',
  4999.00,
  7500.00,
  5.00,
  410,
  'Career Master',
  'bi-file-earmark-bar-graph-fill',
  'Intermediate to Advanced',
  'Commerce Graduates / Tax Consultants / Accountants',
  '["Complete Tally Prime accounting and inventory management engine","GST structure: CGST, SGST, IGST, Reverse Charge (RCM), and Composition","GSTR-1, GSTR-3B, and GSTR-9 annual return computation from Tally","E-way bill and QR code e-invoice generation direct from Tally","TDS deduction on rent, contracts, professional fees, and Form 26Q preparation"]',
  '[{"module":"Module 1","title":"Advanced Accounting & Multi-Currency","topics":["Cost Centres, Cost Categories, and Budgets","Multi-Currency transactions and exchange rates","Interest calculations and overdue bill tracking"]},{"module":"Module 2","title":"GST Implementation in Tally","topics":["Tax ledgers, HSN/SAC codes, and GST rates hierarchy","Intra-State vs Inter-State commercial invoicing","Credit Notes, Debit Notes, and Advance Receipts"]},{"module":"Module 3","title":"GST Returns & E-Way Bills","topics":["GSTR-1 (Outward Supplies) JSON generation","GSTR-3B monthly summary return computation","E-Way Bill generation and ITC 2B matching"]},{"module":"Module 4","title":"TDS & Payroll Essentials","topics":["TDS statutory ledgers and deduction vouchers","Employee masters, attendance, and payslip generation"]}]',
  '["Advanced Tax & Accounting Master Certificate","Live GST Portal Filing Simulation Experience","Lifetime guidance for local tax consultancy practice"]',
  '[{"type":"Morning Pro","time":"08:30 AM - 10:00 AM (Mon - Fri)","seats":"3 Seats Available"},{"type":"Weekend Special","time":"10:00 AM - 01:30 PM (Sat - Sun)","seats":"5 Seats Available"}]',
  'active',
  7
),
(
  8,
  'course-web-dev',
  'full-stack-web-development-bootcamp',
  'Full-Stack Web Development Bootcamp',
  'Web Development',
  'HTML5, CSS3, Bootstrap 5, JavaScript, PHP 8, and MySQL to build responsive business portals.',
  'Build real-world dynamic web applications, landing pages, database-driven business portals, and secure user management systems from scratch.',
  '3 Months',
  '80 Hours',
  5999.00,
  9000.00,
  4.90,
  145,
  'Career Launch',
  'bi-code-slash',
  'Beginner to Intermediate',
  'Students interested in software and web careers',
  '["Semantic HTML5 and modern CSS3 Flexbox & Grid layouts","Bootstrap 5 responsive UI components and theme creation","Vanilla JavaScript DOM manipulation, events, and API fetch calls","PHP 8 backend architecture, PDO prepared statements, and session security","MySQL database design, foreign keys, and CRUD operations","Deploying real websites live on cPanel/Apache web hosting"]',
  '[{"module":"Module 1","title":"HTML5 & Modern CSS3","topics":["Semantic HTML elements and accessibility","Flexbox & CSS Grid responsive design","CSS variables and light/dark theme systems"]},{"module":"Module 2","title":"Bootstrap 5 & JavaScript UI","topics":["Bootstrap grid, navigation, modals, and forms","JavaScript ES6+ fundamentals, DOM manipulation","AJAX fetch requests and JSON handling"]},{"module":"Module 3","title":"PHP 8 & MySQL Backend","topics":["PHP syntax, functions, and session security","MySQL database schema, PDO prepared statements","Building complete CRUD APIs and auth systems"]},{"module":"Module 4","title":"Deployment & Live Projects","topics":["cPanel hosting setup, FTP, and MySQL import","Domain configuration and SSL security"]}]',
  '["Full-Stack Web Developer Certificate","4 Real Live Portfolio Projects hosted online","Interview and freelance client acquisition training"]',
  '[{"type":"Evening Pro","time":"07:00 PM - 08:30 PM (Mon - Fri)","seats":"4 Seats Available"}]',
  'active',
  8
);

-- 3. Tax & ITR Services Catalog
INSERT INTO `tax_services` (`id`, `slug`, `title`, `category`, `short_desc`, `full_desc`, `icon`, `accent_class`, `price`, `display_order`, `status`) VALUES
(1, 'itr-filing', 'Income Tax (ITR) Filing', 'Taxation', 'Salaried (Form 16), Capital Gains, Business 44AD & Freelance Income returns with 26AS cross-check.', 'Comprehensive income tax preparation covering ITR-1 (Sahaj), ITR-2, and ITR-4 (Sugam) with tax saving optimization, 26AS/AIS verification, and instant e-verification.', 'bi-file-earmark-medical-fill', 'accent-tax', 499.00, 1, 'active'),
(2, 'gst-services', 'GST Registration & Monthly Filing', 'Taxation', 'New GSTIN application, monthly GSTR-1 & 3B return preparation, E-way bills, and ITC reconciliation.', 'Complete end-to-end GST support for small businesses, traders, manufacturers, and online e-commerce sellers with timely return submissions.', 'bi-building-fill-check', 'accent-tax', 799.00, 2, 'active'),
(3, 'pan-services', 'PAN & Aadhaar Services', 'Citizen Services', 'Instant digital e-PAN, physical PAN card home delivery, name/DOB corrections, and PAN-Aadhaar linking.', 'Official NSDL/UTIITSL PAN application facilitation with door-step card delivery, minor-to-major conversions, and correction assistance.', 'bi-person-badge-fill', 'accent-tax', 200.00, 3, 'active'),
(4, 'tds-returns', 'TDS Returns & Refund Assistance', 'Taxation', 'Quarterly TDS returns (Form 24Q, 26Q), Form 16 generation, and tracking delayed income tax refunds.', 'Corporate and deductor TDS quarterly filings, Form 16/16A generation, correction statements, and income tax refund status tracking.', 'bi-file-earmark-bar-graph-fill', 'accent-tax', 999.00, 4, 'active'),
(5, 'dsc-token', 'Class 3 Digital Signature (DSC)', 'Digital Services', 'Class 3 crypto USB tokens for e-Tendering, MCA company filing, trademark registration, and GST signing.', 'Legally compliant Class 3 Signing & Encryption combo tokens delivered with full crypto driver installation support on client computers.', 'bi-key-fill', 'accent-tax', 1499.00, 5, 'active'),
(6, 'udyam-msme', 'Udyam MSME Registration', 'Business Compliance', 'Zero-fee government registration for small businesses to unlock subsidies, bank collateral benefits, and tenders.', 'Instant government MSME certificate processing under the Ministry of Micro, Small and Medium Enterprises.', 'bi-briefcase-fill', 'accent-tax', 299.00, 6, 'active');

-- 4. Digital & Online Services (Form Filling, Scanning, Printing)
INSERT INTO `digital_services` (`id`, `slug`, `title`, `category`, `description`, `icon`, `accent_class`, `price_text`, `display_order`, `status`) VALUES
(1, 'online-form-filling', 'Government & Exam Online Form Fill-up', 'Application Services', 'Assistance with WBCS, SSC, Railway, UPSC, Police, Nursing, College Admissions, and Scholarship applications with photo & signature resizing.', 'bi-pencil-square', 'accent-digital', 'Affordable Form Fee', 1, 'active'),
(2, 'high-speed-printing-xerox', 'High-Speed Color Printing & Xerox', 'Print & Copy', 'High-resolution digital laser color printing, black & white Xerox copies, project report binding, and document lamination.', 'bi-printer-fill', 'accent-digital', 'From ₹2 / page', 2, 'active'),
(3, 'document-scanning-ocr', 'Document Scanning & PDF Conversion', 'Digital Services', 'Multi-page high-DPI scanning, document digitization, OCR text extraction, and PDF file merging & optimization.', 'bi-file-earmark-pdf-fill', 'accent-digital', 'From ₹5 / doc', 3, 'active'),
(4, 'passport-photo-studio', 'Instant Digital Passport Photos', 'Photography', 'Studio quality instant passport size photos, biometric visa photos, and background change with photo sheet printing in 5 minutes.', 'bi-camera-fill', 'accent-digital', '₹50 / sheet', 4, 'active'),
(5, 'e-challan-bill-payments', 'Electricity & Online Bill Payments', 'Utility Payments', 'Instant online payment of WBSEDCL / State electricity bills, municipal property taxes, vehicle e-challans, and broadband bills.', 'bi-lightning-charge-fill', 'accent-digital', 'Free Receipt Given', 5, 'active'),
(6, 'aadhaar-pvc-card-print', 'Aadhaar & Voter PVC Smart Card Print', 'Card Services', 'High-durability waterproof plastic PVC smart card printing for Aadhaar, Voter Card, Health Scheme card, and Driving License.', 'bi-credit-card-2-front-fill', 'accent-digital', '₹60 / card', 6, 'active');

-- 5. Mobile & Gift House Catalog
INSERT INTO `products` (`id`, `code`, `name`, `category`, `description`, `price`, `badge`, `icon`, `accent_class`, `display_order`, `status`) VALUES
(1, 'PROD-MOB-01', 'Fast Charging USB-C Cables & Braided Wires', 'Mobile Accessories', 'Durable 65W fast-charging Type-C, Micro-USB, and Lightning braided cables with heavy-duty metal joints.', 199.00, 'Best Seller', 'bi-usb-c-fill', 'accent-mobile', 1, 'active'),
(2, 'PROD-MOB-02', 'High-Speed QC 3.0 / PD Wall Chargers', 'Mobile Accessories', 'Dual-port fast chargers with over-voltage surge protection compatible with all Android and iOS smartphones.', 399.00, 'Top Rated', 'bi-plug-fill', 'accent-mobile', 2, 'active'),
(3, 'PROD-MOB-03', 'HD Stereo Bass Earphones & Neckbands', 'Audio & Music', 'Noise-isolating in-ear earphones with high-clarity microphone, magnetic earbuds, and deep dynamic bass.', 299.00, 'Popular', 'bi-headphones', 'accent-mobile', 3, 'active'),
(4, 'PROD-MOB-04', 'Tempered Glass & Protective Back Covers', 'Mobile Accessories', '9H hardness curved edge tempered screen guards and shockproof designer silicone mobile phone covers for all models.', 149.00, 'All Models', 'bi-phone', 'accent-mobile', 4, 'active'),
(5, 'PROD-GIFT-01', 'Customized Photo Mugs & Magic Cups', 'Gift House', 'High-grade ceramic coffee mugs customized with your personal photo, name, or birthday wish. Heat-sensitive magic mugs available.', 249.00, 'Custom Gift', 'bi-cup-hot-fill', 'accent-mobile', 5, 'active'),
(6, 'PROD-GIFT-02', 'Personalized Photo Frames & Wooden Plaques', 'Gift House', 'Elegant acrylic and wooden designer wall & table photo frames for birthdays, anniversaries, and festive celebrations.', 399.00, 'Memories', 'bi-image-fill', 'accent-mobile', 6, 'active'),
(7, 'PROD-GIFT-03', 'Engraved Metal Keychains & Crystal Pens', 'Gift House', 'Premium laser engraved personalized metal keychains and luxury crystal gift pens in attractive presentation boxes.', 149.00, 'Gift Pack', 'bi-key-fill', 'accent-mobile', 7, 'active'),
(8, 'PROD-GIFT-04', 'Soft Toys, Teddy Bears & Birthday Items', 'Gift House', 'Plush soft teddy bears, decorative LED birthday greeting cards, musical gifts, and festival presentation items.', 299.00, 'Kids & Festive', 'bi-gift-fill', 'accent-mobile', 8, 'active');

-- 6. Important Links (Official Government Portals)
INSERT INTO `important_links` (`id`, `link_code`, `title`, `category`, `description`, `url`, `button_text`, `icon`, `accent_class`, `display_order`, `status`) VALUES
(1, 'link-itr', 'Income Tax e-Filing Portal', 'Taxation', 'Official online portal for ITR e-filing, AIS/TIS inspection, e-Verification, and 26AS tax credit download.', 'https://www.incometax.gov.in', 'Visit Portal', 'bi-file-earmark-medical-fill', 'accent-links', 1, 'active'),
(2, 'link-gst', 'GST Services Portal', 'Taxation', 'Official GST portal to file GSTR-1, GSTR-3B, generate E-way bills, and check input tax credit (ITC) ledgers.', 'https://www.gst.gov.in', 'Visit Portal', 'bi-building-fill-check', 'accent-links', 2, 'active'),
(3, 'link-pan', 'PAN Services Portal (NSDL / Protean)', 'Citizen Services', 'Apply for New PAN Card (Form 49A), PAN reprint, and correction in existing PAN data.', 'https://www.onlineservices.nsdl.com', 'Visit Portal', 'bi-person-badge-fill', 'accent-links', 3, 'active'),
(4, 'link-aadhaar', 'UIDAI MyAadhaar Portal', 'Citizen Services', 'Download e-Aadhaar, check Aadhaar-bank link status, verify Aadhaar, and book appointment.', 'https://myaadhaar.uidai.gov.in', 'Visit Portal', 'bi-fingerprint', 'accent-links', 4, 'active'),
(5, 'link-epfo', 'EPFO Unified Member Portal', 'Employment', 'Check PF account balance, view UAN passbook, submit online PF claims, and transfer member accounts.', 'https://unifiedportal-mem.epfindia.gov.in', 'Visit Portal', 'bi-shield-check', 'accent-links', 5, 'active'),
(6, 'link-passport', 'Passport Seva Portal', 'Citizen Services', 'Official Ministry of External Affairs portal for fresh passport applications, re-issue, and appointment booking.', 'https://www.passportindia.gov.in', 'Visit Portal', 'bi-globe-americas', 'accent-links', 6, 'active'),
(7, 'link-digilocker', 'DigiLocker Digital Wallet', 'Digital India', 'Access authentic digital copies of Driving License, Aadhaar, Academic Certificates, and Vehicle RC.', 'https://www.digilocker.gov.in', 'Visit Portal', 'bi-shield-lock-fill', 'accent-links', 7, 'active'),
(8, 'link-udyam', 'Udyam Registration Portal (MSME)', 'Business', 'Free zero-fee official government registration portal for micro, small, and medium enterprises.', 'https://udyamregistration.gov.in', 'Visit Portal', 'bi-briefcase-fill', 'accent-links', 8, 'active');

-- 7. Default Business Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('business_name', 'MB Internet And Digital Studio'),
('sub_brand', 'Mobile & Gift House'),
('owner_name', 'Mukesh Bhattacharya'),
('tagline', 'Computer Training, Tax & ITR Services, Digital Services, Mobile & Gift Solutions — All Under One Roof.'),
('phone', '+91 98765 43210'),
('whatsapp', '+91 98765 43210'),
('email', 'contact@mbinternet.com'),
('address', 'MB Internet And Digital Studio, Main Market Road, Opp. Central Park, West Bengal / India'),
('facebook_url', 'https://www.facebook.com/MBIDS/photos_by'),
('gstin', '19AAACS1234F1Z5'),
('working_hours', 'Monday – Saturday: 08:30 AM – 08:30 PM (Sunday Closed / By Appointment)'),
('razorpay_key_id', 'rzp_test_demo12345678'),
('razorpay_key_secret', 'test_secret_demo12345678');

-- 8. Seed Initial Orders for User #2 (Rahul) & User #3 (Priya)
INSERT INTO `orders` (`id`, `order_number`, `enrollment_number`, `user_id`, `course_id`, `customer_name`, `mobile`, `email`, `address`, `course_title`, `course_price`, `discount_amount`, `final_amount`, `payment_gateway`, `payment_status`, `razorpay_order_id`, `razorpay_payment_id`, `created_at`) VALUES
(1, 'ORD-2026-00101', 'ENR-2026-00101', 2, 7, 'Rahul Sharma', '9876543210', 'user@mbinternet.com', 'Station Road, City Centre', 'Tally Prime + GST Taxation Mastery', 4999.00, 0.00, 4999.00, 'Razorpay / UPI', 'Paid', 'order_demo_101', 'PAY-DEMO-99101', '2026-08-28 10:30:00'),
(2, 'ORD-2026-00102', 'ENR-2026-00102', 3, 4, 'Priya Sen', '9811223344', 'priya.sen@gmail.com', 'College Para, Ward 4', 'Advanced MS Excel & MIS Analytics', 3499.00, 0.00, 3499.00, 'Card (Visa)', 'Paid', 'order_demo_102', 'PAY-DEMO-99102', '2026-08-27 14:15:00'),
(3, 'ORD-2026-00103', 'ENR-2026-00103', 2, 1, 'Rahul Sharma', '9876543210', 'user@mbinternet.com', 'Station Road, City Centre', 'Basic Computer & Digital Literacy', 1999.00, 0.00, 1999.00, 'NetBanking', 'Paid', 'order_demo_103', 'PAY-DEMO-99103', '2026-08-26 11:00:00');

INSERT INTO `enrollments` (`id`, `enrollment_number`, `order_id`, `user_id`, `course_id`, `status`, `enrolled_at`) VALUES
(1, 'ENR-2026-00101', 1, 2, 7, 'Active', '2026-08-28 10:30:00'),
(2, 'ENR-2026-00102', 2, 3, 4, 'Active', '2026-08-27 14:15:00'),
(3, 'ENR-2026-00103', 3, 2, 1, 'Active', '2026-08-26 11:00:00');

INSERT INTO `payments` (`id`, `payment_number`, `order_id`, `user_id`, `amount`, `currency`, `gateway`, `gateway_payment_id`, `gateway_order_id`, `status`, `created_at`) VALUES
(1, 'PAY-DEMO-99101', 1, 2, 4999.00, 'INR', 'Razorpay', 'PAY-DEMO-99101', 'order_demo_101', 'Success', '2026-08-28 10:30:00'),
(2, 'PAY-DEMO-99102', 2, 3, 3499.00, 'INR', 'Razorpay', 'PAY-DEMO-99102', 'order_demo_102', 'Success', '2026-08-27 14:15:00'),
(3, 'PAY-DEMO-99103', 3, 2, 1999.00, 'INR', 'Razorpay', 'PAY-DEMO-99103', 'order_demo_103', 'Success', '2026-08-26 11:00:00');

-- 9. Seed Service Requests
INSERT INTO `service_requests` (`id`, `request_number`, `user_id`, `service_id`, `service_type`, `customer_name`, `mobile`, `email`, `address`, `pan_number`, `financial_year`, `assessment_year`, `status`, `internal_notes`, `created_at`) VALUES
(1, 'REQ-2026-00101', 4, 1, 'Income Tax (ITR) Filing', 'Amitava Das', '9823012345', 'amitava.das@outlook.com', 'Market Square, Main Town', 'ABCDE1234F', '2025-26', '2026-27', 'Processing', 'Form 16 verified with 26AS. Preparing computation.', '2026-08-29 09:45:00'),
(2, 'REQ-2026-00102', 2, 3, 'PAN & Aadhaar Services', 'Rahul Sharma', '9876543210', 'user@mbinternet.com', 'Station Road, City Centre', 'N/A (New Application)', '2025-26', '2026-27', 'Completed', 'Physical PAN application submitted on NSDL. Acknowledgement slip generated.', '2026-08-26 16:20:00'),
(3, 'REQ-2026-00103', 2, 1, 'Income Tax (ITR) Filing', 'Rahul Sharma', '9876543210', 'user@mbinternet.com', 'Station Road, City Centre', 'ABCDE1234F', '2025-26', '2026-27', 'Pending', 'Awaiting salary slip confirmation for Q4.', '2026-08-28 11:15:00');

INSERT INTO `documents` (`id`, `request_id`, `user_id`, `original_name`, `stored_name`, `file_type`, `file_size`, `file_size_formatted`, `file_path`, `created_at`) VALUES
(1, 1, 4, 'Form16_Part_A_B.pdf', 'demo_doc_form16_amitava.pdf', 'application/pdf', 1468006, '1.4 MB', 'storage/documents/demo_doc_form16_amitava.pdf', '2026-08-29 09:45:00'),
(2, 1, 4, 'PAN_Card_Copy.jpg', 'demo_doc_pan_amitava.jpg', 'image/jpeg', 430080, '420 KB', 'storage/documents/demo_doc_pan_amitava.jpg', '2026-08-29 09:45:00'),
(3, 2, 2, 'Aadhaar_Card_Front_Back.pdf', 'demo_doc_aadhaar_rahul.pdf', 'application/pdf', 1153433, '1.1 MB', 'storage/documents/demo_doc_aadhaar_rahul.pdf', '2026-08-26 16:20:00'),
(4, 3, 2, 'Form_16_Salary.pdf', 'demo_doc_form16_rahul.pdf', 'application/pdf', 1003520, '980 KB', 'storage/documents/demo_doc_form16_rahul.pdf', '2026-08-28 11:15:00');
