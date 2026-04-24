-- ============================================
-- Departmental Stock Management System
-- Database Setup Script
-- Run this in phpMyAdmin or MySQL CLI
-- ============================================

CREATE DATABASE IF NOT EXISTS stock_db;
USE stock_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    role ENUM('admin', 'staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Departments Table
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Items Table
CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    category_id INT,
    quantity INT DEFAULT 0,
    unit VARCHAR(50) DEFAULT 'pcs',
    supplier VARCHAR(200),
    description TEXT,
    date_added DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Stock Issues Table
CREATE TABLE IF NOT EXISTS stock_issues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    issued_to VARCHAR(200) NOT NULL,
    department_id INT,
    quantity_issued INT NOT NULL,
    issue_date DATE NOT NULL,
    purpose TEXT,
    issued_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================
-- Default Data
-- ============================================

-- Default Admin User (password: admin123)
INSERT INTO users (username, password, full_name, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin'),
('staff1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff Member', 'staff');

-- Default Departments
INSERT INTO departments (name, description) VALUES
('Computer Science', 'CS Department'),
('Library', 'Library Department'),
('Administration', 'Admin Office'),
('Laboratory', 'Science Lab');

-- Default Categories
INSERT INTO categories (name) VALUES
('Stationery'),
('Electronics'),
('Furniture'),
('Cleaning Supplies'),
('Books & Manuals'),
('Lab Equipment');

-- Sample Items
INSERT INTO items (name, category_id, quantity, unit, supplier, date_added) VALUES
('A4 Paper Ream', 1, 50, 'reams', 'OfficeSupplies Co.', CURDATE()),
('Ballpoint Pens', 1, 200, 'pcs', 'Stationery World', CURDATE()),
('Whiteboard Marker', 1, 30, 'pcs', 'Stationery World', CURDATE()),
('Laptop - Dell', 2, 10, 'pcs', 'TechStore', CURDATE()),
('Printer Ink Cartridge', 2, 15, 'pcs', 'TechStore', CURDATE()),
('Office Chair', 3, 20, 'pcs', 'FurnitureMart', CURDATE()),
('Stapler', 1, 25, 'pcs', 'OfficeSupplies Co.', CURDATE()),
('Scientific Calculator', 2, 40, 'pcs', 'TechStore', CURDATE());