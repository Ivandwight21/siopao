-- Add reseller profile table for storing account information
CREATE TABLE IF NOT EXISTS reseller_profile (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    store_name VARCHAR(190),
    address VARCHAR(255),
    phone_number VARCHAR(20),
    bank_name VARCHAR(100),
    account_number VARCHAR(20),
    account_holder VARCHAR(190),
    notification_stock_delivery TINYINT(1) DEFAULT 1,
    notification_payout TINYINT(1) DEFAULT 1,
    notification_promo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reseller_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Add sales records table
CREATE TABLE IF NOT EXISTS sales_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT,
    quantity INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    sale_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sales_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Add stock orders table
CREATE TABLE IF NOT EXISTS stock_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    status ENUM('pending', 'approved', 'shipped', 'delivered') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_stock_order_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
