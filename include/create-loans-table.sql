-- Create loans table
CREATE TABLE IF NOT EXISTS `loans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `subcategory` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `date_index` (`date`),
  KEY `category_index` (`category`),
  KEY `name_index` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create loan_categories table for managing loan categories
CREATE TABLE IF NOT EXISTS `loan_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create loan_subcategories table for managing loan subcategories
CREATE TABLE IF NOT EXISTS `loan_subcategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `loan_subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `loan_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert some default loan categories
INSERT INTO `loan_categories` (`name`) VALUES 
('Personal Loan'),
('Business Loan'),
('Home Loan'),
('Vehicle Loan'),
('Education Loan'),
('Gold Loan');

-- Insert some default loan subcategories
INSERT INTO `loan_subcategories` (`category_id`, `name`) 
SELECT id, 'Short Term' FROM loan_categories WHERE name = 'Personal Loan'
UNION ALL
SELECT id, 'Long Term' FROM loan_categories WHERE name = 'Personal Loan'
UNION ALL
SELECT id, 'Working Capital' FROM loan_categories WHERE name = 'Business Loan'
UNION ALL
SELECT id, 'Equipment Finance' FROM loan_categories WHERE name = 'Business Loan'
UNION ALL
SELECT id, 'Construction' FROM loan_categories WHERE name = 'Home Loan'
UNION ALL
SELECT id, 'Purchase' FROM loan_categories WHERE name = 'Home Loan'
UNION ALL
SELECT id, 'Car Loan' FROM loan_categories WHERE name = 'Vehicle Loan'
UNION ALL
SELECT id, 'Two Wheeler' FROM loan_categories WHERE name = 'Vehicle Loan'
UNION ALL
SELECT id, 'Undergraduate' FROM loan_categories WHERE name = 'Education Loan'
UNION ALL
SELECT id, 'Postgraduate' FROM loan_categories WHERE name = 'Education Loan'
UNION ALL
SELECT id, 'Jewellery' FROM loan_categories WHERE name = 'Gold Loan'
UNION ALL
SELECT id, 'Coins' FROM loan_categories WHERE name = 'Gold Loan'; 