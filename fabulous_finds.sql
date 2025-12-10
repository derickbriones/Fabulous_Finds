-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 01, 2025 at 08:29 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fabulous_finds`
--

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `FeedbackID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `SellerID` int(11) NOT NULL,
  `Comment` text NOT NULL,
  `Rating` enum('1','2','3','4','5') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orderdetails`
--

CREATE TABLE `orderdetails` (
  `OrderID` int(11) NOT NULL,
  `ProductID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orderdetails`
--

INSERT INTO `orderdetails` (`OrderID`, `ProductID`, `Quantity`) VALUES
(5, 4, 1),
(6, 6, 1),
(7, 4, 1),
(8, 2, 1),
(9, 6, 1),
(10, 2, 1),
(11, 4, 1),
(12, 5, 1),
(13, 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `OrderID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `SellerID` int(11) NOT NULL,
  `OrderDate` datetime NOT NULL,
  `Status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`OrderID`, `UserID`, `SellerID`, `OrderDate`, `Status`) VALUES
(5, 4, 2, '2025-12-01 08:13:19', 'Cancelled'),
(6, 4, 4, '2025-12-01 08:13:30', 'Completed'),
(7, 5, 2, '2025-12-01 08:17:23', 'Completed'),
(8, 5, 1, '2025-12-01 08:17:43', 'Pending'),
(9, 6, 4, '2025-12-01 08:19:24', 'Completed'),
(10, 6, 1, '2025-12-01 08:19:39', 'Cancelled'),
(11, 7, 2, '2025-12-01 08:22:59', 'Pending'),
(12, 7, 3, '2025-12-01 08:23:09', 'Pending'),
(13, 8, 4, '2025-12-01 08:24:44', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `PaymentID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `PaymentMethod` varchar(50) NOT NULL,
  `PaymentDate` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`PaymentID`, `OrderID`, `Amount`, `PaymentMethod`, `PaymentDate`) VALUES
(5, 5, 699.50, 'Gcash', '2025-12-01 08:13:19'),
(6, 6, 2999.90, 'Paymaya', '2025-12-01 08:13:30'),
(7, 7, 699.50, 'COD', '2025-12-01 08:17:23'),
(8, 8, 499.90, 'COD', '2025-12-01 08:17:43'),
(9, 9, 2999.90, 'Gcash', '2025-12-01 08:19:24'),
(10, 10, 499.90, 'Gcash', '2025-12-01 08:19:39'),
(11, 11, 699.50, 'Paymaya', '2025-12-01 08:22:59'),
(12, 12, 1999.90, 'Paymaya', '2025-12-01 08:23:09'),
(13, 13, 2999.90, 'Gcash', '2025-12-01 08:24:44');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `ProductID` int(11) NOT NULL,
  `ProductName` varchar(150) NOT NULL,
  `Category` varchar(100) NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `StockQuantity` int(11) NOT NULL,
  `Status` char(1) NOT NULL DEFAULT 'A' COMMENT 'A=Active, D=Disabled',
  `SellerID` int(11) NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`ProductID`, `ProductName`, `Category`, `Price`, `StockQuantity`, `Status`, `SellerID`, `image`) VALUES
(2, 'Louis Vuitton', 'Shirt', 499.90, 9, 'A', 1, 'lvshirt.jpg'),
(4, 'Lacoste Polo Shirt', 'Shirt', 699.50, 13, 'A', 2, 'lacoste.webp'),
(5, 'Denim Pants', 'Pants', 1999.90, 9, 'A', 3, 'levis.webp'),
(6, 'Dior Perfume', 'Perfume', 2999.90, 7, 'A', 4, 'dior.avif');

-- --------------------------------------------------------

--
-- Table structure for table `seller`
--

CREATE TABLE `seller` (
  `SellerID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `ContactInfo` varchar(150) NOT NULL,
  `AssignPrice` text NOT NULL,
  `logo` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seller`
--

INSERT INTO `seller` (`SellerID`, `Name`, `ContactInfo`, `AssignPrice`, `logo`) VALUES
(1, 'Louis Vuitton', '09123456789', '0', 'LvLogo.png'),
(2, 'Lacoste', '09998887777', '0', 'LacosteLogo.png'),
(3, 'Levis', '090000001', '', 'Levislogo.png'),
(4, 'Dior', '091000000', '', 'DiorLogo.png');

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `TransactionID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `PaymentID` int(11) NOT NULL,
  `Status` varchar(50) NOT NULL,
  `TrackingInfo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UserID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Address` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--
ALTER TABLE `user` ADD `ContactNo` VARCHAR(20) NOT NULL AFTER `Address`;

INSERT INTO `user` (`UserID`, `Name`, `Email`, `Password`, `Address`, `ContactNo`) VALUES
(1, 'Admin', 'admin@fabulousfinds.com', '$2y$10$wyv7Fw70kUNLCZ8JkR7OK.bBkLEXpXCg0v/IpuqFIN2OZ3yFGNXCO', 'Camalig\r\n', '09617552132'),
(4, 'John Drex Cantor', 'johndrex14@gmail.com', '$2y$10$NCAqzNFF/7g3174mDWmpOuPHzXtr.wQID8yUfPOKI7eVsAfAK737a', 'Tobog Oas Albay\r\n', '09920646179'),
(5, 'Dan Francis Etorma', 'danfrancisetorma@gmail.com', '$2y$10$JsoYRmU8sC2BybaTYdTEheUZLLPUzx68hqebz9NAqFuSftSirgv02', 'Sugcad Polangui Albay', '09928583763'),
(6, 'Derick Briones', 'derickbriones@gmail.com', '$2y$10$hrn2VDMtKcrE2nwgw4bIUuZWv52kCd2v1ikPKfRcBjiqGxxNjn36O', 'Camalig Albay', '09618587036'),
(7, 'Aljon Viñas', 'aljonvinas@gmail.com', '$2y$10$jImCpzDTu221.RDWVWEExeXmSfPKDIYV5ge13keyWZwXTGa8ihSvu', 'Centro Polangui', '09934504221'),
(8, 'Gabriel Meshach Salcedo', 'gabrielmeshach@gmail.com', '$2y$10$nRAo/LAl8pCf174XAzsKn.YhKlyiWBOdtLy.UTU41zAAHHTpWCjie', 'Libon', '09176254321');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`FeedbackID`),
  ADD KEY `fk_feedback_user` (`UserID`),
  ADD KEY `fk_feedback_seller` (`SellerID`);

--
-- Indexes for table `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD PRIMARY KEY (`OrderID`,`ProductID`),
  ADD KEY `fk_orderdetails_product` (`ProductID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `fk_order_user` (`UserID`),
  ADD KEY `fk_order_seller` (`SellerID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`PaymentID`),
  ADD KEY `fk_payment_order` (`OrderID`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`ProductID`),
  ADD KEY `fk_product_seller` (`SellerID`);

--
-- Indexes for table `seller`
--
ALTER TABLE `seller`
  ADD PRIMARY KEY (`SellerID`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`TransactionID`),
  ADD KEY `fk_transaction_order` (`OrderID`),
  ADD KEY `fk_transaction_payment` (`PaymentID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `FeedbackID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `seller`
--
ALTER TABLE `seller`
  MODIFY `SellerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transaction`
--
ALTER TABLE `transaction`
  MODIFY `TransactionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_feedback_seller` FOREIGN KEY (`SellerID`) REFERENCES `seller` (`SellerID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_feedback_user` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD CONSTRAINT `fk_orderdetails_order` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_orderdetails_product` FOREIGN KEY (`ProductID`) REFERENCES `product` (`ProductID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_seller` FOREIGN KEY (`SellerID`) REFERENCES `seller` (`SellerID`),
  ADD CONSTRAINT `fk_order_user` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `fk_payment_order` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `fk_product_seller` FOREIGN KEY (`SellerID`) REFERENCES `seller` (`SellerID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `fk_transaction_order` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaction_payment` FOREIGN KEY (`PaymentID`) REFERENCES `payment` (`PaymentID`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
