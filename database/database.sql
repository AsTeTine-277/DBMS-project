

CREATE DATABASE IF NOT EXISTS `hermart`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `hermart`;



DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `deliveries`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `custom_order_items`;
DROP TABLE IF EXISTS `custom_orders`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `cart_items`;
DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `product_images`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `businesses`;
DROP TABLE IF EXISTS `regions`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

-- USERS  (customers, sellers, admins)

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(30) UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('customer','seller','admin') NOT NULL DEFAULT 'customer',
  `status` ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `address` VARCHAR(255) DEFAULT NULL,
  `profile_image` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_role` (`role`),
  INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- LOCATIONS  (Division → District → Upazila)

CREATE TABLE `locations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(80) NOT NULL,
  `type` ENUM('division','district','upazila') NOT NULL,
  `parent_id` INT DEFAULT NULL,
  CONSTRAINT `fk_loc_parent` FOREIGN KEY (`parent_id`) REFERENCES `locations`(`id`) ON DELETE CASCADE,
  INDEX `idx_loc_type` (`type`),
  INDEX `idx_loc_parent` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- CATEGORIES

CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(80) NOT NULL,
  `slug` VARCHAR(80) NOT NULL UNIQUE,
  `icon` VARCHAR(60) DEFAULT 'category',
  `description` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- BUSINESSES / SELLERS (Artisans)

CREATE TABLE `businesses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `owner_id` INT DEFAULT NULL,
  `shop_name` VARCHAR(150) NOT NULL,
  `description` TEXT,
  `category_id` INT DEFAULT NULL,
  `location_id` INT DEFAULT NULL,
  `division` VARCHAR(60) DEFAULT NULL,
  `district` VARCHAR(80) DEFAULT NULL,
  `upazila` VARCHAR(80) DEFAULT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `facebook` VARCHAR(255) DEFAULT NULL,
  `online_shop` VARCHAR(255) DEFAULT NULL,
  `profile_image` VARCHAR(255) DEFAULT NULL,
  `cover_image` VARCHAR(255) DEFAULT NULL,
  `rating` DECIMAL(3,2) NOT NULL DEFAULT 0,
  `verified` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_biz_owner` FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_biz_cat`   FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_biz_loc`   FOREIGN KEY (`location_id`) REFERENCES `locations`(`id`) ON DELETE SET NULL,
  INDEX `idx_biz_verified` (`verified`),
  INDEX `idx_biz_district` (`district`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PRODUCTS

CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `seller_id` INT DEFAULT NULL,
  `category_id` INT DEFAULT NULL,
  `name` VARCHAR(180) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `discount_price` DECIMAL(10,2) DEFAULT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `sku` VARCHAR(80) DEFAULT NULL,
  `material` VARCHAR(120) DEFAULT NULL,
  `color` VARCHAR(60) DEFAULT NULL,
  `size` VARCHAR(60) DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active','draft','out_of_stock','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_product_seller` FOREIGN KEY (`seller_id`) REFERENCES `businesses`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_product_cat`    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  INDEX `idx_product_status` (`status`),
  INDEX `idx_product_price` (`price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PRODUCT IMAGES  (gallery / additional pictures)

CREATE TABLE `product_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  CONSTRAINT `fk_pi_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  INDEX `idx_pi_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- CART  (per-customer, persistent)

CREATE TABLE `cart` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_cart_user` FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uk_cart_user` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cart_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `cart_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_ci_cart`    FOREIGN KEY (`cart_id`)    REFERENCES `cart`(`id`)       ON DELETE CASCADE,
  CONSTRAINT `fk_ci_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)   ON DELETE CASCADE,
  UNIQUE KEY `uk_cart_product` (`cart_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ORDERS + ORDER ITEMS

CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(40) NOT NULL UNIQUE,
  `customer_id` INT DEFAULT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `shipping_address` VARCHAR(500) DEFAULT NULL,
  `district` VARCHAR(80) DEFAULT NULL,
  `upazila` VARCHAR(80) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `notes` TEXT,
  `payment_method` ENUM('cod','bkash','nagad','card','bank') NOT NULL DEFAULT 'cod',
  `payment_status` ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `order_status` ENUM('pending','confirmed','processing','shipped','out_for_delivery','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_orders_status` (`order_status`),
  INDEX `idx_orders_customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT DEFAULT NULL,
  `seller_id` INT DEFAULT NULL,
  `product_name` VARCHAR(180) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0,
  CONSTRAINT `fk_oi_order`   FOREIGN KEY (`order_id`)   REFERENCES `orders`(`id`)      ON DELETE CASCADE,
  CONSTRAINT `fk_oi_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)    ON DELETE SET NULL,
  CONSTRAINT `fk_oi_seller`  FOREIGN KEY (`seller_id`)  REFERENCES `businesses`(`id`)  ON DELETE SET NULL,
  INDEX `idx_oi_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- PAYMENTS

CREATE TABLE `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `payment_method` ENUM('cod','bkash','nagad','card','bank') NOT NULL DEFAULT 'cod',
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `transaction_reference` VARCHAR(120) DEFAULT NULL,
  `payment_status` ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `paid_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_pay_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  INDEX `idx_pay_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- DELIVERIES

CREATE TABLE `deliveries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `customer_id` INT DEFAULT NULL,
  `address` VARCHAR(500) DEFAULT NULL,
  `district` VARCHAR(80) DEFAULT NULL,
  `upazila` VARCHAR(80) DEFAULT NULL,
  `tracking_number` VARCHAR(60) DEFAULT NULL,
  `carrier` VARCHAR(80) DEFAULT 'HerMart Logistics',
  `status` ENUM('pending','confirmed','processing','shipped','out_for_delivery','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `estimated_delivery` DATE DEFAULT NULL,
  `delivered_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_del_order` FOREIGN KEY (`order_id`)    REFERENCES `orders`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_del_user`  FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`)  ON DELETE SET NULL,
  INDEX `idx_del_order` (`order_id`),
  INDEX `idx_del_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- REVIEWS

CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT DEFAULT NULL,
  `product_id` INT DEFAULT NULL,
  `seller_id` INT DEFAULT NULL,
  `order_id` INT DEFAULT NULL,
  `rating` TINYINT NOT NULL DEFAULT 5,
  `review_text` TEXT,
  `status` ENUM('active','hidden') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_rev_user`    FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`)      ON DELETE SET NULL,
  CONSTRAINT `fk_rev_product` FOREIGN KEY (`product_id`)  REFERENCES `products`(`id`)  ON DELETE SET NULL,
  CONSTRAINT `fk_rev_seller`  FOREIGN KEY (`seller_id`)   REFERENCES `businesses`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_rev_order`   FOREIGN KEY (`order_id`)    REFERENCES `orders`(`id`)    ON DELETE SET NULL,
  INDEX `idx_rev_product` (`product_id`),
  INDEX `idx_rev_seller` (`seller_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- SERVICES + BOOKINGS

CREATE TABLE `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `category` VARCHAR(80) DEFAULT NULL,
  `base_price` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `description` TEXT,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT DEFAULT NULL,
  `vendor_id` INT DEFAULT NULL,
  `service_id` INT DEFAULT NULL,
  `service_name` VARCHAR(150) DEFAULT NULL,
  `event_type` VARCHAR(120) DEFAULT NULL,
  `booking_date` DATE DEFAULT NULL,
  `booking_time` TIME DEFAULT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `notes` TEXT,
  `status` ENUM('pending','accepted','rejected','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_bk_user`    FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`)      ON DELETE SET NULL,
  CONSTRAINT `fk_bk_vendor`  FOREIGN KEY (`vendor_id`)   REFERENCES `businesses`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_bk_service` FOREIGN KEY (`service_id`)  REFERENCES `services`(`id`)   ON DELETE SET NULL,
  INDEX `idx_bk_status` (`status`),
  INDEX `idx_bk_vendor` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- CUSTOM ORDERS + ITEMS

CREATE TABLE `custom_orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT DEFAULT NULL,
  `title` VARCHAR(180) NOT NULL,
  `description` TEXT NOT NULL,
  `budget` DECIMAL(10,2) DEFAULT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `preferred_date` DATE DEFAULT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `reference_image` VARCHAR(255) DEFAULT NULL,
  `seller_id` INT DEFAULT NULL,
  `quoted_price` DECIMAL(10,2) DEFAULT NULL,
  `estimated_completion` DATE DEFAULT NULL,
  `status` ENUM('pending','reviewed','quoted','accepted','in_progress','completed','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_co_user`   FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`)      ON DELETE SET NULL,
  CONSTRAINT `fk_co_seller` FOREIGN KEY (`seller_id`)   REFERENCES `businesses`(`id`) ON DELETE SET NULL,
  INDEX `idx_co_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `custom_order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `custom_order_id` INT NOT NULL,
  `name` VARCHAR(180) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(10,2) DEFAULT NULL,
  CONSTRAINT `fk_coi_co` FOREIGN KEY (`custom_order_id`) REFERENCES `custom_orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- NOTIFICATIONS

CREATE TABLE `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `type` VARCHAR(40) NOT NULL DEFAULT 'info',
  `title` VARCHAR(180) NOT NULL,
  `body` TEXT,
  `link` VARCHAR(255) DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_notif_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- SEED DATA


-- Categories
INSERT INTO `categories` (`name`, `slug`, `icon`, `description`) VALUES
('Handmade & Crafts',     'handmade',         'palette',          'Handcrafted artisan items'),
('Fashion & Clothing',    'fashion',          'checkroom',        'Clothing and fashion'),
('Food & Homemade',       'food',             'restaurant',       'Homemade foods and pickles'),
('Home & Decoration',     'home-decoration',  'chair',            'Home decor and furniture'),
('Nakshi Kantha',         'nakshi-kantha',    'bed',              'Traditional embroidered Kantha'),
('Rural & Traditional',   'rural-traditional','agriculture',      'Traditional rural crafts'),
('Pahari & Indigenous',   'pahari',           'landscape',        'Indigenous Pahari crafts'),
('Jewelry & Accessories', 'jewelry',          'diamond',          'Jewelry and accessories'),
('Customized Products',   'customized',       'auto_awesome',     'Custom-made products');

-- Locations (Divisions)
INSERT INTO `locations` (`name`, `type`, `parent_id`) VALUES
('Dhaka','division',NULL),('Chattogram','division',NULL),('Rajshahi','division',NULL),
('Khulna','division',NULL),('Barishal','division',NULL),('Sylhet','division',NULL),
('Rangpur','division',NULL),('Mymensingh','division',NULL);

-- Locations (Districts) — children by parent_id of divisions above (1..8)
INSERT INTO `locations` (`name`, `type`, `parent_id`) VALUES
('Dhaka','district',1),('Gazipur','district',1),('Narayanganj','district',1),
('Chattogram','district',2),("Cox's Bazar",'district',2),
('Rajshahi','district',3),('Pabna','district',3),('Natore','district',3),
('Khulna','district',4),('Jessore','district',4),
('Barishal','district',5),('Patuakhali','district',5),
('Sylhet','district',6),('Moulvibazar','district',6),('Habiganj','district',6),
('Rangpur','district',7),('Dinajpur','district',7),('Kurigram','district',7),
('Mymensingh','district',8),('Jamalpur','district',8);

-- Services
INSERT INTO `services` (`name`, `category`, `base_price`, `description`) VALUES
('Tailoring & Dress Making',    'Fashion',      1500.00, 'Custom tailoring for women and children.'),
('Event Catering',              'Food',        12000.00, 'Home-style catering for small events and parties.'),
('Mehendi & Bridal Art',        'Beauty',       3000.00, 'Traditional mehendi, bridal and party makeup.'),
('Home Tutoring',               'Education',    1500.00, 'Female home tutors for primary and secondary students.'),
('Home Cleaning',               'Household',    1000.00, 'Trained women home cleaning staff.'),
('Baby Sitting',                'Care',         1200.00, 'Experienced baby sitters and elderly care.'),
('Handmade Jewelry Workshop',   'Artisan',      2000.00, 'Bespoke jewelry creation workshops.'),
('Pottery & Clay Art',          'Artisan',      1800.00, 'Traditional pottery classes and products.');
