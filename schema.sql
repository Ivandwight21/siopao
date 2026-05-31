-- Schema for Monlei SiPao portal
CREATE DATABASE IF NOT EXISTS monleisiopao;
USE monleisiopao;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    username VARCHAR(64) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','reseller') NOT NULL DEFAULT 'reseller',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    otp_hash CHAR(60) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id),
    CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(190) NOT NULL,
    sku VARCHAR(64) NOT NULL UNIQUE,
    qty INT NOT NULL DEFAULT 0,
    reorder_level INT NOT NULL DEFAULT 10,
    unit VARCHAR(50),
    supplier VARCHAR(190),
    price_per_unit DECIMAL(10,2),
    image_path VARCHAR(255),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (created_by),
    CONSTRAINT fk_inventory_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS low_stock_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inventory_id INT NOT NULL,
    qty_at_alert INT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_low_stock_item FOREIGN KEY (inventory_id) REFERENCES inventory(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Sample Data
-- Users (passwords: Admin123! for admin, Reseller123! for resellers)
INSERT INTO users (email, username, password_hash, role) VALUES
('admin@monleisiopao.com', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('jade.supremo@gmail.com', 'jadeadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('reseller1@example.com', 'reseller1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'reseller'),
('reseller2@example.com', 'reseller2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'reseller'),
('reseller3@example.com', 'reseller3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'reseller'),
('maria.santos@example.com', 'mariasantos', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'reseller'),
('juan.delacruz@example.com', 'juandc', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'reseller')
ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash);

-- Inventory Items
INSERT INTO inventory (name, sku, qty, reorder_level) VALUES
('Classic Pork Siopao', 'SIO-PORK-001', 150, 50),
('Chicken Asado Siopao', 'SIO-CHICK-002', 120, 40),
('Beef Bola-Bola Siopao', 'SIO-BEEF-003', 85, 30),
('Vegetable Siopao', 'SIO-VEG-004', 45, 25),
('Special Barbeque Siopao', 'SIO-BBQ-005', 60, 20),
('Cheese Siopao', 'SIO-CHEESE-006', 35, 20),
('Spicy Pork Siopao', 'SIO-SPICY-007', 55, 25),
('Jumbo Pork Siopao', 'SIO-JUMBO-008', 40, 15),
('Mini Siopao Pack (6pcs)', 'SIO-MINI-009', 90, 30),
('Premium Combo Pack', 'SIO-COMBO-010', 25, 10),
('Steamed Buns (Plain)', 'BUN-PLAIN-011', 200, 80),
('Pork Filling Mix (kg)', 'ING-PORK-012', 12, 15),
('Flour Premium (25kg)', 'ING-FLOUR-013', 8, 10),
('Cooking Oil (5L)', 'ING-OIL-014', 15, 12),
('Soy Sauce (1L)', 'ING-SOY-015', 22, 15),
('Oyster Sauce (1L)', 'ING-OYSTER-016', 18, 12),
('Sesame Oil (500ml)', 'ING-SESAME-017', 9, 8),
('Green Onions (bundle)', 'ING-ONION-018', 6, 10),
('Bamboo Steamer Large', 'EQUIP-STEAM-019', 12, 5),
('Disposable Boxes (100pcs)', 'PKG-BOX-020', 45, 20),
('Paper Bags (100pcs)', 'PKG-BAG-021', 65, 25),
('Plastic Forks (200pcs)', 'PKG-FORK-022', 38, 20),
('Tissue Paper (pack)', 'PKG-TISSUE-023', 55, 30),
('Chili Garlic Sauce (bottle)', 'COND-CHILI-024', 42, 20),
('Sweet Soy Dip (bottle)', 'COND-SWEET-025', 38, 18)
ON DUPLICATE KEY UPDATE qty=VALUES(qty), reorder_level=VALUES(reorder_level);
