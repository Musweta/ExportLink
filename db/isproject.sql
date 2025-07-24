-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 24, 2025 at 03:19 PM
-- Server version: 8.0.41
-- PHP Version: 8.3.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `banksystem`
--
CREATE DATABASE IF NOT EXISTS `banksystem` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `banksystem`;

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `account_number` int NOT NULL,
  `customer_id` int DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`account_number`, `customer_id`, `balance`) VALUES
(1, 1, 1854748.00);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `id_number` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `full_name`, `nationality`, `phone`, `email`, `id_number`) VALUES
(1, 'John Doe', 'Kenyan', '0712345678', 'johndoe@example.com', 'K1234567'),
(2, 'Kelvin M', 'Kenyan', '079898989870', 'k@gmail.com', '33123312'),
(3, 'Kelvin Musweta', 'Kenyan', '0791445548', 'kelvin@gmail.com', '40404040'),
(4, '', '', '', '', ''),
(5, 'Musweta Champe', 'Kenyan', '0712345678', 'kelvin@gmail.com', '44136777'),
(6, 'Kelvin', 'Kenyan', '0721345678', 'Kevin@gmail.com', '44507866'),
(7, 'Kelvin', 'Kenyan', '07993746464', 'hdhd@gmail.com', '6575858');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int NOT NULL,
  `transaction_type` enum('Deposit','Withdrawal') NOT NULL,
  `amount` int DEFAULT NULL,
  `transaction_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `account_number` int DEFAULT NULL,
  `id_number` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `transaction_type`, `amount`, `transaction_date`, `account_number`, `id_number`) VALUES
(1, 'Deposit', 1000, '2025-04-06 18:30:19', 1, NULL),
(2, 'Deposit', 1, '2025-04-06 20:38:36', 1, NULL),
(3, 'Deposit', 132, '2025-04-06 23:58:01', 1, NULL),
(4, 'Deposit', 234, '2025-04-06 23:58:22', 1, NULL),
(5, 'Deposit', 748494, '2025-04-06 23:58:41', 1, NULL),
(6, 'Deposit', 900000, '2025-04-06 23:59:08', 1, NULL),
(7, 'Deposit', 90000, '2025-04-07 01:48:10', 1, NULL),
(8, 'Deposit', 10887, '2025-04-07 12:53:52', 1, NULL),
(9, 'Deposit', 100000, '2025-04-07 18:11:35', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Customer') DEFAULT 'Customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'Kelvin', 'Kelvin', 'Admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`account_number`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_number` (`id_number`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `account_number` (`account_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `account_number` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`account_number`) REFERENCES `accounts` (`account_number`);
--
-- Database: `exportlink_db`
--
CREATE DATABASE IF NOT EXISTS `exportlink_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `exportlink_db`;

-- --------------------------------------------------------

--
-- Table structure for table `exportdocuments`
--

CREATE TABLE `exportdocuments` (
  `document_id` int NOT NULL,
  `order_id` int NOT NULL,
  `doc_type` varchar(100) NOT NULL,
  `content` text,
  `issue_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int NOT NULL,
  `importer_id` int NOT NULL,
  `product_id` int NOT NULL,
  `order_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `quantity_ordered` int NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','shipped','completed','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int NOT NULL,
  `farmer_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('farmer','importer','admin') NOT NULL,
  `address` text,
  `phone_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `address`, `phone_number`, `created_at`, `updated_at`) VALUES
(1, 'kelvin', 'kelvin.musweta@strathmore.edu', 'student', 'admin', '1234', '0712345678', '2025-07-12 19:39:35', '2025-07-12 19:39:35');

-- --------------------------------------------------------

--
-- Table structure for table `verifications`
--

CREATE TABLE `verifications` (
  `verification_id` int NOT NULL,
  `importer_id` int NOT NULL,
  `admin_id` int DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `notes` text,
  `submitted_on` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_on` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `exportdocuments`
--
ALTER TABLE `exportdocuments`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `idx_exportdocuments_order_id` (`order_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_orders_importer_id` (`importer_id`),
  ADD KEY `idx_orders_product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `idx_products_farmer_id` (`farmer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`);

--
-- Indexes for table `verifications`
--
ALTER TABLE `verifications`
  ADD PRIMARY KEY (`verification_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `idx_verifications_importer_id` (`importer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `exportdocuments`
--
ALTER TABLE `exportdocuments`
  MODIFY `document_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `verifications`
--
ALTER TABLE `verifications`
  MODIFY `verification_id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `exportdocuments`
--
ALTER TABLE `exportdocuments`
  ADD CONSTRAINT `exportdocuments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`importer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `verifications`
--
ALTER TABLE `verifications`
  ADD CONSTRAINT `verifications_ibfk_1` FOREIGN KEY (`importer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `verifications_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;
--
-- Database: `gymdatabase`
--
CREATE DATABASE IF NOT EXISTS `gymdatabase` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `gymdatabase`;

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `age` int DEFAULT NULL,
  `weight` int DEFAULT NULL,
  `height` int DEFAULT NULL,
  `goal` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `profiles`
--

INSERT INTO `profiles` (`id`, `user_id`, `name`, `age`, `weight`, `height`, `goal`) VALUES
(13, 1, 'Admin', 30, 70, 175, 'Maintain current fitness level'),
(14, 1, 'Ali', 21, 50, 156, 'Gain weight'),
(15, 1, 'Ali ', 21, 60, 176, 'Maintain weight');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'Admin', '1234');

-- --------------------------------------------------------

--
-- Table structure for table `workout_plans`
--

CREATE TABLE `workout_plans` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `goal` varchar(100) DEFAULT NULL,
  `plan` varchar(1000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `workout_plans`
--

INSERT INTO `workout_plans` (`id`, `user_id`, `goal`, `plan`) VALUES
(10, 1, 'Weight Loss', 'Cardio for 30 minutes, 5 times a week'),
(12, 1, 'Gain weight', 'workout 5 times'),
(13, 1, '', ''),
(14, 1, 'gain weight', ' workout 5 times a week');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `workout_plans`
--
ALTER TABLE `workout_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `workout_plans`
--
ALTER TABLE `workout_plans`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `profiles`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `workout_plans`
--
ALTER TABLE `workout_plans`
  ADD CONSTRAINT `workout_plans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
--
-- Database: `isproject`
--
CREATE DATABASE IF NOT EXISTS `isproject` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `isproject`;

-- --------------------------------------------------------

--
-- Table structure for table `export_documents`
--

CREATE TABLE `export_documents` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `document_type` varchar(100) NOT NULL,
  `document_content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `export_documents`
--

INSERT INTO `export_documents` (`id`, `order_id`, `document_type`, `document_content`, `created_at`) VALUES
(1, 1, 'invoice', 'Commercial Invoice\nInvoice Number: INV-1\nDate: 2025-07-20\nSeller: farmer\nImporter: importer (importer@importer.com)\nProduct: Ovacado\nType: Fruit\nQuantity: 20\nUnit Price: 5.00 USD\nTotal: 100 USD\nPayment Terms: Bank Cheque\nHS Code: 0800021\nCountry of Origin: Kenya\nGrade: Grade A', '2025-07-20 11:47:58'),
(2, 2, 'invoice', 'Commercial Invoice\nInvoice Number: INV-2\nDate: 2025-07-20\nSeller: farmer\nImporter: importer (importer@importer.com)\nProduct: Kales\nType: Vegetable\nQuantity: 10\nUnit Price: 2.50 USD\nTotal: 25 USD\nPayment Terms: M-Pesa\nHS Code: 0002027474\nCountry of Origin: Kenya\nGrade: Grade A', '2025-07-20 11:50:51'),
(3, 1, 'bill_of_lading', 'Tracking Number: TRK-6520797161', '2025-07-20 11:51:45'),
(4, 2, 'bill_of_lading', 'Tracking Number: TRK-B2367B573E', '2025-07-20 11:51:45'),
(6, 4, 'invoice', 'Commercial Invoice\nInvoice Number: INV-4\nDate: 2025-07-22 15:51:05\nSeller: farmer\nImporter: importer (importer@importer.com)\nProduct: Ovacado\nType: Fruit\nQuantity: 10\nUnit Price: 5.00 KES\nTotal: 50 KES\nPayment Terms: Bank Cheque\nHS Code: 0800021\nCountry of Origin: Kenya\nGrade: Grade A', '2025-07-22 15:51:05'),
(7, 4, 'receipt', 'Receipt\nOrder ID: 4\nDate: 2025-07-22 15:51:05\nProduct: Ovacado\nQuantity: 10\nTotal: 50 KES\nPayment Terms: Bank Cheque', '2025-07-22 15:51:05'),
(8, 4, 'bill_of_lading', 'Tracking Number: TRK-8C28210673', '2025-07-22 15:54:44'),
(13, 7, 'invoice', 'Commercial Invoice\nInvoice Number: INV-7\nDate: 2025-07-22 16:22:56\nSeller: farmer\nImporter: importer (importer@importer.com)\nProduct: Cabbage\nType: Vegetable\nQuantity: 25\nUnit Price: 1.29 USD\nTotal: 32.25 USD\nPayment Terms: Bank Cheque\nHS Code: 089965\nCountry of Origin: Kenya\nGrade: Grade A', '2025-07-22 16:22:56'),
(14, 7, 'receipt', 'Receipt\nOrder ID: 7\nDate: 2025-07-22 16:22:56\nProduct: Cabbage\nQuantity: 25\nTotal: 32.25 USD\nPayment Terms: Bank Cheque', '2025-07-22 16:22:56'),
(15, 8, 'invoice', 'Commercial Invoice\nInvoice Number: INV-8\nDate: 2025-07-22 16:23:56\nSeller: farmer\nImporter: importer (importer@importer.com)\nProduct: Cabbage\nType: Vegetable\nQuantity: 25\nUnit Price: 1.29 USD\nTotal: 32.25 USD\nPayment Terms: Bank Cheque\nHS Code: 089965\nCountry of Origin: Kenya\nGrade: Grade A', '2025-07-22 16:23:56'),
(16, 8, 'receipt', 'Receipt\nOrder ID: 8\nDate: 2025-07-22 16:23:56\nProduct: Cabbage\nQuantity: 25\nTotal: 32.25 USD\nPayment Terms: Bank Cheque', '2025-07-22 16:23:56'),
(17, 7, 'bill_of_lading', 'Tracking Number: TRK-080ECDCE54', '2025-07-22 16:24:21');

-- --------------------------------------------------------

--
-- Table structure for table `importer_documents`
--

CREATE TABLE `importer_documents` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `document_type` enum('import_license','business_registration','tax_id','customs_registration','import_declaration','bill_of_lading','past_import_records','business_premises') NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `importer_documents`
--

INSERT INTO `importer_documents` (`id`, `user_id`, `document_type`, `file_path`, `uploaded_at`) VALUES
(1, 8, 'import_license', '../Uploads/8_import_license_1753009378_Test Document.pdf', '2025-07-20 11:02:58'),
(2, 8, 'business_registration', '../Uploads/8_business_registration_1753009378_Test Document.pdf', '2025-07-20 11:02:58'),
(3, 8, 'tax_id', '../Uploads/8_tax_id_1753009378_Test Document.pdf', '2025-07-20 11:02:58'),
(4, 8, 'customs_registration', '../Uploads/8_customs_registration_1753009378_Test Document.pdf', '2025-07-20 11:02:58'),
(5, 8, 'import_declaration', '../Uploads/8_import_declaration_1753009378_Test Document.pdf', '2025-07-20 11:02:58'),
(6, 8, 'bill_of_lading', '../Uploads/8_bill_of_lading_1753009378_Test Document.pdf', '2025-07-20 11:02:58'),
(7, 8, 'past_import_records', '../Uploads/8_past_import_records_1753009378_Test Document.pdf', '2025-07-20 11:02:58'),
(8, 8, 'business_premises', '../Uploads/8_business_premises_1753009378_Test Document.pdf', '2025-07-20 11:02:58');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `importer_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `payment_terms` varchar(100) NOT NULL,
  `currency` enum('USD','KES','EUR') DEFAULT 'USD',
  `status` enum('pending','confirmed','shipped','delivered','cleared') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `importer_id`, `product_id`, `quantity`, `payment_terms`, `currency`, `status`, `created_at`, `updated_at`) VALUES
(1, 8, 1, 20, 'Bank Cheque', 'USD', 'delivered', '2025-07-20 11:47:58', '2025-07-22 16:27:24'),
(2, 8, 2, 10, 'M-Pesa', 'USD', 'shipped', '2025-07-20 11:50:51', '2025-07-20 11:51:45'),
(4, 8, 1, 10, 'Bank Cheque', 'KES', 'delivered', '2025-07-22 15:51:04', '2025-07-22 15:55:19'),
(7, 8, 3, 25, 'Bank Cheque', 'USD', 'shipped', '2025-07-22 16:22:56', '2025-07-22 16:24:21'),
(8, 8, 3, 25, 'Bank Cheque', 'USD', 'delivered', '2025-07-22 16:23:56', '2025-07-22 16:25:55');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `farmer_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `quantity` int NOT NULL,
  `origin` varchar(100) NOT NULL,
  `grade` varchar(50) NOT NULL,
  `hs_code` varchar(20) NOT NULL,
  `certification` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `farmer_id`, `name`, `type`, `description`, `price`, `quantity`, `origin`, `grade`, `hs_code`, `certification`, `created_at`, `image_path`) VALUES
(1, 7, 'Ovacado', 'Fruit', 'Grafted Ovacado', 5.00, 70, 'Kenya', 'Grade A', '0800021', '../Uploads/7_cert_1753011874_Test Document.pdf', '2025-07-20 11:44:34', NULL),
(2, 7, 'Kales', 'Vegetable', 'Green vegitable', 2.50, 40, 'Kenya', 'Grade A', '0002027474', '', '2025-07-20 11:47:02', NULL),
(3, 7, 'Cabbage', 'Vegetable', 'Cabbage', 1.29, 0, 'Kenya', 'Grade A', '089965', 'N/A', '2025-07-22 16:17:31', '../Uploads/7_1753201051_cabbage.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('farmer','importer','admin') NOT NULL,
  `email` varchar(100) NOT NULL,
  `is_approved` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `email`, `is_approved`, `created_at`) VALUES
(7, 'farmer', '$2y$10$heRoUHictKeF02fg6ZPj7e9mTZvzWzCx/eZD3k3PxSTl4LzqqJAru', 'farmer', 'farmer@farmer.com', 1, '2025-07-20 11:01:27'),
(8, 'importer', '$2y$10$gaLI6UCWC8vpEUN.z7PAP.aSH1gEFopLpXWgheXGWqGDoxdjXc8gO', 'importer', 'importer@importer.com', 1, '2025-07-20 11:02:58'),
(9, 'admin2', '$2y$10$pBFb4qOVUBsesBP6CutMsONOq8uDFBNSle7OTO5W8dO6RVPuSz5nq', 'admin', 'admin2@admin.com', 1, '2025-07-20 11:05:05'),
(11, 'admin', '$2y$10$jC9./Uqnff3pXKl8yzFJYeyno1M1RzRz3TyflI0ykL4lDHzgglZya', 'admin', 'admin@admin.com', 1, '2025-07-20 19:48:01'),
(14, 'test3', '$2y$10$jekwC8k68Rc0DCUO82LzoeT95YZJyQ/.vqw6Qhxp47jT6RKcLVcU2', 'farmer', 'test3@gmail.com', 1, '2025-07-22 16:00:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `export_documents`
--
ALTER TABLE `export_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `importer_documents`
--
ALTER TABLE `importer_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `importer_id` (`importer_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farmer_id` (`farmer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `export_documents`
--
ALTER TABLE `export_documents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `importer_documents`
--
ALTER TABLE `importer_documents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `export_documents`
--
ALTER TABLE `export_documents`
  ADD CONSTRAINT `export_documents_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `importer_documents`
--
ALTER TABLE `importer_documents`
  ADD CONSTRAINT `importer_documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`importer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
--
-- Database: `mydatabase`
--
CREATE DATABASE IF NOT EXISTS `mydatabase` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `mydatabase`;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `ClientID` int NOT NULL,
  `ClientName` varchar(255) DEFAULT NULL,
  `ContactPerson` varchar(255) DEFAULT NULL,
  `ContactEmail` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`ClientID`, `ClientName`, `ContactPerson`, `ContactEmail`) VALUES
(1, 'ABC Company', 'John Doe', 'john.doe@example.com'),
(2, 'XYZ Corporation', 'Jane Smith', 'jane.smith@example.com'),
(3, 'LMN Enterprises', 'Bob Johnson', 'bob.johnson@example.com'),
(4, 'Juma and Sons Limited', 'Juma Mosi', 'juma.mosi@example.com'),
(5, 'Sky Venture', 'Meryl Ivy', 'meryl.ivy@example.com');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `RegistrationNumber` varchar(20) NOT NULL,
  `Model` varchar(50) DEFAULT NULL,
  `Make` varchar(50) DEFAULT NULL,
  `year` int DEFAULT NULL,
  `CurrentMilage` int DEFAULT NULL,
  `FuelType` varchar(20) DEFAULT NULL,
  `MaintainanceStatus` varchar(20) DEFAULT NULL,
  `ClientID` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`RegistrationNumber`, `Model`, `Make`, `year`, `CurrentMilage`, `FuelType`, `MaintainanceStatus`, `ClientID`) VALUES
('KBC123', 'Sedan', 'Toyota', 2020, 25000, 'Gasoline', 'Good', 1),
('KEF789', 'Truck', 'Chevrolet', 2021, 15000, 'Gasoline', 'Excellent', 2),
('KHI234', 'Coupe', 'Honda', 2018, 40000, 'Hybrid', 'Fair', 3),
('KKL567', 'Convertible', 'BMW', 2022, 10000, 'Electric', 'Good', 4),
('KYZ456', 'SUV', 'Ford', 2019, 35000, 'Diesel', 'Needs Maintenance', 5);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`ClientID`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`RegistrationNumber`),
  ADD KEY `fk_ClientID` (`ClientID`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `fk_ClientID` FOREIGN KEY (`ClientID`) REFERENCES `clients` (`ClientID`);
--
-- Database: `oopproject`
--
CREATE DATABASE IF NOT EXISTS `oopproject` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `oopproject`;

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `service` varchar(50) NOT NULL,
  `appointment_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `name`, `email`, `phone`, `service`, `appointment_date`, `created_at`) VALUES
(1, 'Hassan Omar 1', 'Omar2@gmail.com', '0712341234', 'Dental', '2025-04-04', '2025-04-05 21:42:16'),
(2, 'Titus', 'Njoroge', '0745679036', 'Optical', '2025-06-01', '2025-04-05 23:41:34'),
(3, 'assd', 'ghsadgs@gmail.com', '664646464', 'njhdsjh', '2025-09-02', '2025-04-06 00:58:39');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `course_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `user_id`, `course_name`) VALUES
(1, 2, 'BBIT'),
(2, 2, 'BSc CS'),
(3, 2, 'BComm'),
(4, 2, 'BBIT');

-- --------------------------------------------------------

--
-- Table structure for table `course_registrations`
--

CREATE TABLE `course_registrations` (
  `id` int NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email_address` varchar(100) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `referral_source` varchar(100) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `course_registrations`
--

INSERT INTO `course_registrations` (`id`, `first_name`, `last_name`, `phone_number`, `email_address`, `course`, `referral_source`, `country`) VALUES
(1, 'Hassan', 'Omar', '0712312312', 'Hassan1@gmail.com', 'Law', 'Blog', 'India'),
(2, 'Ali', 'Abdul', '0712341234', 'Ali@gmail.com', 'Law', 'Twitter', 'Somalia'),
(3, 'Ali ', 'Roba', '0712341234', 'ali7@gmail.com', 'Business', 'Radio', 'Tanzania'),
(4, 'Ali ', 'Roba', '0712341234', 'ali7@gmail.com', 'Business', 'Radio', 'Tanzania'),
(5, 'Ali ', 'Roba', '0712341234', 'ali7@gmail.com', 'Business', 'Radio', 'Tanzania'),
(6, 'Ali ', 'Roba', '0712341234', 'ali7@gmail.com', 'Business', 'Radio', 'Tanzania'),
(7, 'Ali ', 'Roba', '0712341234', 'ali7@gmail.com', 'Business', 'Radio', 'Tanzania'),
(8, 'Ali', 'Mpishi', '0709090909', 'Ali5@gmail.com', 'Communication', 'Instagram', 'Cayman'),
(9, '', '', '', '', '-- Please select --', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `finance_records`
--

CREATE TABLE `finance_records` (
  `id` int NOT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `payment_status` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `finance_records`
--

INSERT INTO `finance_records` (`id`, `student_id`, `course`, `amount_paid`, `payment_status`) VALUES
(1, '202398', 'DBIT', 90000.00, NULL),
(2, '1234', 'dbit', 67000.00, NULL),
(3, '746', 'dbm', 56000.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `library_records`
--

CREATE TABLE `library_records` (
  `id` int NOT NULL,
  `admission_no` varchar(50) DEFAULT NULL,
  `book_title` varchar(100) DEFAULT NULL,
  `date_borrowed` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `student_id` int DEFAULT NULL,
  `borrow_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `library_records`
--

INSERT INTO `library_records` (`id`, `admission_no`, `book_title`, `date_borrowed`, `return_date`, `student_id`, `borrow_date`) VALUES
(1, '123123', 'History', '2025-01-01', '2025-02-02', NULL, NULL),
(2, '123124', 'Chemistry', '2025-01-02', '2025-02-02', NULL, NULL),
(3, '987', 'wako', '2025-02-02', '2025-02-03', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `reg_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `faculty` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `full_name`, `reg_number`, `email`, `phone`, `faculty`) VALUES
(1, 2, 'Hassan', '202320', 'hassan@strathmore.edu', '0743843897', 'DBIT'),
(2, 2, 'Hassan', '202320', 'hassan@strathmore.edu', '0743843897', 'DBIT'),
(3, 2, '', '', '', '', ''),
(4, 2, 'Hassan Omar', '202398', 'Hassan@gmail.com', '07070707', 'DBIT');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'student1', 'pass123'),
(2, 'Hassan', 'Hassan');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `course_registrations`
--
ALTER TABLE `course_registrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `finance_records`
--
ALTER TABLE `finance_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `library_records`
--
ALTER TABLE `library_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `course_registrations`
--
ALTER TABLE `course_registrations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `finance_records`
--
ALTER TABLE `finance_records`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `library_records`
--
ALTER TABLE `library_records`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
--
-- Database: `triply_travel_manager`
--
CREATE DATABASE IF NOT EXISTS `triply_travel_manager` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `triply_travel_manager`;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int NOT NULL,
  `customer_id` int NOT NULL,
  `destination` varchar(100) DEFAULT NULL,
  `check_in_date` date DEFAULT NULL,
  `check_out_date` date DEFAULT NULL,
  `guests` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);
--
-- Database: `webdev`
--
CREATE DATABASE IF NOT EXISTS `webdev` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `webdev`;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int NOT NULL,
  `content` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `sender` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
