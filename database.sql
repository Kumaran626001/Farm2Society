-- Database: farm2society_db
CREATE DATABASE IF NOT EXISTS farm2society_db;
USE farm2society_db;

-- Table: Users
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('farmer', 'consumer', 'admin') NOT NULL,
    location VARCHAR(255),
    is_verified BOOLEAN DEFAULT FALSE, -- Mainly for farmers
    average_rating DECIMAL(3, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: Products
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL, -- Price per kg
    quantity DECIMAL(10, 2) NOT NULL, -- Available quantity in kg
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Table: Orders
CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    consumer_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10, 2) NOT NULL,
    order_status ENUM('Pending', 'Packed', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    FOREIGN KEY (consumer_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Table: Order_Items
CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(10, 2) NOT NULL,
    price DECIMAL(10, 2) NOT NULL, -- Price at time of order
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- Table: Delivery_Status (Optional per requirements, but good for tracking)
CREATE TABLE IF NOT EXISTS delivery_status (
    delivery_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    updated_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);

-- Table: Ratings
CREATE TABLE IF NOT EXISTS ratings (
    rating_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    farmer_id INT NOT NULL,
    rating TINYINT(1) NOT NULL, -- 1 to 5
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (farmer_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Insert Default Admin (Password: admin123)
-- Ideally password should be hashed. Using raw for SQL insert example, but PHP will handle hashing.
-- For initial access, we might need a way to create first admin or insert one manually with a known hash.
-- Hashed 'admin123' using PASSWORD_DEFAULT (bcrypt) -> '$2y$10$...' (Example hash)
INSERT INTO users (name, email, password, role, is_verified) 
VALUES ('Super Admin', 'admin@farm2society.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', TRUE);
