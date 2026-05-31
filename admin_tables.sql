-- Admin-related tables
CREATE TABLE IF NOT EXISTS promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(190) NOT NULL,
    description TEXT,
    discount_percent INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    active TINYINT(1) DEFAULT 1,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_promo_admin FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS purchase_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_number VARCHAR(50) NOT NULL UNIQUE,
    supplier_name VARCHAR(190) NOT NULL,
    contact_info VARCHAR(255),
    status ENUM('pending', 'confirmed', 'shipped', 'delivered') DEFAULT 'pending',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_po_admin FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS po_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_id INT NOT NULL,
    inventory_id INT NOT NULL,
    quantity INT NOT NULL,
    CONSTRAINT fk_poitem_po FOREIGN KEY (po_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_poitem_inv FOREIGN KEY (inventory_id) REFERENCES inventory(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reseller_incentives (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reseller_id INT NOT NULL,
    type ENUM('bonus', 'discount_coupon', 'commission_boost') NOT NULL,
    value VARCHAR(100) NOT NULL,
    issued_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_incentive_reseller FOREIGN KEY (reseller_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_incentive_admin FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
