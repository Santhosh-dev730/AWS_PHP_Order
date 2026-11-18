-- ========================================================
-- E-commerce Database Setup Script
-- Database: ecommerce_db
-- ========================================================

-- Step 1: Create Database if it does not already exist
CREATE DATABASE IF NOT EXISTS ecommerce_db;
USE ecommerce_db;

-- Step 2: Create Products Table
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(500) NOT NULL
);

-- Step 3: Clear any existing sample data to prevent duplicates (Optional)
TRUNCATE TABLE products;

-- Step 4: Insert 6 Sample Products
INSERT INTO products (product_name, description, price, image) VALUES
(
    'Wireless Noise-Canceling Headphones',
    'Premium over-ear Bluetooth headphones with active noise cancellation, high-fidelity sound, and 30-hour battery life.',
    79.99,
    'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=80'
),
(
    'Smart Fitness Watch',
    'Sleek waterproof smartwatch with all-day heart rate tracking, sleep monitor, and bright AMOLED touch display.',
    49.99,
    'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=80'
),
(
    'Ergonomic Wireless Mouse',
    'Comfortable 2.4GHz wireless optical mouse designed to reduce wrist strain with silent clicks and high precision.',
    24.99,
    'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=600&auto=format&fit=crop&q=80'
),
(
    'Mechanical RGB Gaming Keyboard',
    'Tactile mechanical switches with customizable per-key RGB backlighting and durable aircraft-grade aluminum chassis.',
    89.99,
    'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=600&auto=format&fit=crop&q=80'
),
(
    'Portable Waterproof Bluetooth Speaker',
    'Compact rugged wireless speaker with 360-degree punchy bass, IPX7 waterproof rating, and 12-hour playtime.',
    39.99,
    'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=600&auto=format&fit=crop&q=80'
),
(
    'Minimalist Laptop Backpack',
    'Modern water-resistant commuter backpack with padded 15.6-inch laptop sleeve and built-in USB charging port.',
    34.99,
    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&auto=format&fit=crop&q=80'
);

-- Verify the inserted records
SELECT * FROM products;
