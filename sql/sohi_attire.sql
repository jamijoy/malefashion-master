
CREATE DATABASE IF NOT EXISTS sohi_attire CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sohi_attire;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS product_reviews,contact_messages,coupons,website_settings,payments,payment_methods,order_items,orders,cart_items,carts,product_images,product_variants,products,brands,categories,merchants,users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 role ENUM('admin','customer','merchant','staff') DEFAULT 'customer',
 first_name VARCHAR(100), last_name VARCHAR(100),
 email VARCHAR(150) UNIQUE, phone VARCHAR(20) UNIQUE,
 password VARCHAR(255) NOT NULL,
 avatar VARCHAR(255), status TINYINT DEFAULT 1,
 created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL
);

CREATE TABLE merchants(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(200), email VARCHAR(150), phone VARCHAR(20),
 address TEXT, commission DECIMAL(5,2), status TINYINT DEFAULT 1,
 created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL
);

CREATE TABLE categories(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 parent_id BIGINT UNSIGNED NULL,
 name VARCHAR(150) NOT NULL,
 slug VARCHAR(180) UNIQUE,
 image VARCHAR(255), description TEXT,
 status TINYINT DEFAULT 1,
 created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
 FOREIGN KEY(parent_id) REFERENCES categories(id)
);

CREATE TABLE brands(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120), slug VARCHAR(150), logo VARCHAR(255),
 status TINYINT DEFAULT 1,
 created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL
);

CREATE TABLE products(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 category_id BIGINT UNSIGNED,
 brand_id BIGINT UNSIGNED,
 merchant_id BIGINT UNSIGNED,
 name VARCHAR(255), slug VARCHAR(255) UNIQUE, sku VARCHAR(100) UNIQUE,
 short_description TEXT, description LONGTEXT,
 price DECIMAL(12,2), sale_price DECIMAL(12,2), cost_price DECIMAL(12,2),
 weight DECIMAL(10,2),
 featured TINYINT DEFAULT 0, status TINYINT DEFAULT 1,
 created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
 FOREIGN KEY(category_id) REFERENCES categories(id),
 FOREIGN KEY(brand_id) REFERENCES brands(id),
 FOREIGN KEY(merchant_id) REFERENCES merchants(id)
);

CREATE TABLE product_images(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 product_id BIGINT UNSIGNED,
 image VARCHAR(255), sort_order INT DEFAULT 0,
 FOREIGN KEY(product_id) REFERENCES products(id)
);

CREATE TABLE product_variants(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 product_id BIGINT UNSIGNED,
 color VARCHAR(50), size VARCHAR(50),
 barcode VARCHAR(100), price DECIMAL(12,2), stock INT DEFAULT 0,
 FOREIGN KEY(product_id) REFERENCES products(id)
);

CREATE TABLE carts(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED,
 FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE cart_items(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 cart_id BIGINT UNSIGNED,
 product_variant_id BIGINT UNSIGNED,
 qty INT, price DECIMAL(12,2),
 FOREIGN KEY(cart_id) REFERENCES carts(id),
 FOREIGN KEY(product_variant_id) REFERENCES product_variants(id)
);

CREATE TABLE orders(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_no VARCHAR(30) UNIQUE,
 user_id BIGINT UNSIGNED,
 subtotal DECIMAL(12,2), discount DECIMAL(12,2),
 shipping_cost DECIMAL(12,2), tax DECIMAL(12,2), total DECIMAL(12,2),
 payment_status ENUM('pending','paid','failed','refunded'),
 order_status ENUM('pending','confirmed','processing','shipped','delivered','cancelled'),
 shipping_address TEXT,billing_address TEXT,notes TEXT,
 created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,
 FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE order_items(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_id BIGINT UNSIGNED,
 product_variant_id BIGINT UNSIGNED,
 price DECIMAL(12,2), qty INT, subtotal DECIMAL(12,2),
 FOREIGN KEY(order_id) REFERENCES orders(id),
 FOREIGN KEY(product_variant_id) REFERENCES product_variants(id)
);

CREATE TABLE payment_methods(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100), code VARCHAR(50), status TINYINT DEFAULT 1
);

INSERT INTO payment_methods(name,code) VALUES
('Cash on Delivery','cod'),
('bKash','bkash'),
('Nagad','nagad'),
('SSLCommerz','sslcommerz'),
('Stripe','stripe'),
('PayPal','paypal');

CREATE TABLE payments(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_id BIGINT UNSIGNED,
 payment_method_id BIGINT UNSIGNED,
 transaction_id VARCHAR(200),
 amount DECIMAL(12,2),
 status ENUM('pending','paid','failed'),
 created_at TIMESTAMP NULL,
 FOREIGN KEY(order_id) REFERENCES orders(id),
 FOREIGN KEY(payment_method_id) REFERENCES payment_methods(id)
);

CREATE TABLE website_settings(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 site_name VARCHAR(200), tagline VARCHAR(255),
 logo VARCHAR(255), favicon VARCHAR(255),
 phone VARCHAR(20), email VARCHAR(150), address TEXT,
 facebook VARCHAR(255), instagram VARCHAR(255),
 youtube VARCHAR(255), linkedin VARCHAR(255),
 currency VARCHAR(20), timezone VARCHAR(50),
 maintenance_mode TINYINT DEFAULT 0
);

INSERT INTO website_settings(site_name,currency,timezone)
VALUES('SOHI Attire','BDT','Asia/Dhaka');

CREATE TABLE coupons(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(50) UNIQUE,
 type ENUM('fixed','percentage'),
 value DECIMAL(10,2),
 minimum_amount DECIMAL(10,2),
 maximum_discount DECIMAL(10,2),
 usage_limit INT, used INT DEFAULT 0,
 start_date DATE, end_date DATE,
 status TINYINT DEFAULT 1
);

CREATE TABLE product_reviews(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 product_id BIGINT UNSIGNED,
 user_id BIGINT UNSIGNED,
 rating INT, review TEXT,
 created_at TIMESTAMP NULL,
 FOREIGN KEY(product_id) REFERENCES products(id),
 FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE contact_messages(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(150), email VARCHAR(150), phone VARCHAR(30),
 subject VARCHAR(200), message TEXT,
 created_at TIMESTAMP NULL
);
