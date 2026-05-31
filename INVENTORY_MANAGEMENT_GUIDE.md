# Inventory Management System - Complete Documentation

## Overview
The inventory management system in `admin-inventory.php` provides a comprehensive solution for managing all stock items with image uploads, real-time stock tracking, and low-stock alerts.

---

## Features Implemented ✅

### 1. **Display All Stocks**
- **Grid View**: Visual card layout showing all inventory items with images
- **Table View**: Detailed table format with all stock information
- **Real-time Search**: Instantly filter items by name, SKU, or supplier
- **Stock Status**: Visual indicators (Low/OK) based on reorder levels
- **Image Support**: Display product images (JPG, PNG, WebP)
- **Summary Statistics**: Total SKUs, low stock alerts, inventory value

### 2. **Upload New Stock with Image**
- **Add Stock Modal**: User-friendly form to add new inventory items
- **Image Upload**: Support for JPG, PNG, WebP (max 5MB)
- **Live Preview**: Image preview while uploading
- **Required Fields**: Item name, SKU, initial quantity, unit
- **Optional Fields**: Supplier, price per unit, reorder level
- **Automatic Image Storage**: Saves to `assets/inventory/` directory

### 3. **Edit Existing Stock**
- **Edit Modal**: Update all stock information
- **Image Replace**: Upload new image or keep existing
- **Validation**: Ensures unique SKUs
- **Confirmation**: User-friendly updates with success messages

### 4. **Increase Stock Quantity**
- **Quick Add**: Add stock without full edit form
- **Shows Current**: Displays current quantity before adding
- **Calculation**: Automatically calculates new total

### 5. **Delete Stock Items**
- **Safe Deletion**: Requires confirmation
- **Image Cleanup**: Automatically removes uploaded images
- **Cascade Delete**: Maintains database integrity

---

## Database Schema

### Updated `inventory` Table
```sql
CREATE TABLE inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(190) NOT NULL,
    sku VARCHAR(64) NOT NULL UNIQUE,
    qty INT NOT NULL DEFAULT 0,
    reorder_level INT NOT NULL DEFAULT 10,
    unit VARCHAR(50),                    -- NEW: pcs, kg, L, etc
    supplier VARCHAR(190),                -- NEW: Supplier name
    price_per_unit DECIMAL(10,2),        -- NEW: Cost per unit
    image_path VARCHAR(255),              -- NEW: Path to product image
    created_by INT,                       -- NEW: Admin who added item
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
```

### New Columns Added:
| Column | Type | Purpose |
|--------|------|---------|
| `unit` | VARCHAR(50) | Measurement unit (pcs, kg, L, box, etc.) |
| `supplier` | VARCHAR(190) | Supplier name for ordering |
| `price_per_unit` | DECIMAL(10,2) | Cost per unit for inventory valuation |
| `image_path` | VARCHAR(255) | Path to product image in assets/inventory/ |
| `created_by` | INT | Admin ID who created the item |

---

## File Structure

### Frontend
- **admin-inventory.php** - Main inventory dashboard
  - Grid and table view modes
  - 4 modals: Add, Edit, Increase Stock, (Delete via confirm)
  - Real-time search functionality
  - Image preview functionality
  - Live data from database

### Backend Handlers
- **auth/admin_add_stock.php** - Create new inventory item
  - Image upload handling
  - SKU uniqueness validation
  - Database insertion with prepared statements

- **auth/admin_edit_stock.php** - Update existing item
  - Image replacement (delete old, upload new)
  - All field updates
  - Maintains database integrity

- **auth/admin_increase_stock.php** - Add quantity to existing stock
  - Simple quantity increment
  - Audit trail in flash message
  - No data loss

- **auth/admin_delete_stock.php** - Remove stock item
  - Image file cleanup
  - Database deletion
  - Safety check for existing item

### Storage
- **assets/inventory/** - Stores uploaded product images
  - Secure filename generation (random hex)
  - File size validation (max 5MB)
  - Type validation (JPG, PNG, WebP only)

---

## Usage Guide

### Adding New Stock

1. **Click "Add new stock" button**
   ```
   Button location: Top right of admin-inventory.php
   ```

2. **Fill in the form:**
   - Item Name (required): "Classic Pork Siopao"
   - SKU (required, unique): "SIO-PORK-001"
   - Initial Quantity (required): "150"
   - Unit (required): Select from dropdown (pcs, kg, L, etc.)
   - Reorder Level: "50" (alerts when qty drops below this)
   - Price per Unit (optional): "45.50"
   - Supplier (optional): "Monlei Farms"
   - Product Image (optional): Upload JPG/PNG/WebP

3. **Image Preview**
   - Upload image to see live preview
   - Images are automatically resized to fit 100x100px cards

4. **Click "Add Stock"**
   - Validates all inputs
   - Stores image to assets/inventory/
   - Saves to database
   - Shows success message

### Viewing Inventory

#### Grid View (Default)
```
[Grid of cards showing:]
- Product image (or placeholder)
- Item name
- SKU
- Current quantity (bold, large)
- Status badge (Low/OK)
- Action buttons (Edit, Add)
```

#### Table View
```
[Click "View Options" → "Table" to see:]
- Image thumbnail
- Name, SKU, Quantity
- Reorder level, Unit, Supplier
- Price per unit
- Status badge
- Action buttons (Edit, Add, Delete)
```

### Searching Inventory

**In Table View:**
1. Click "Table" view mode
2. Use search field: "Search items..."
3. Type to instantly filter by:
   - Item name
   - SKU
   - Supplier name

**Example searches:**
- "steam" → Shows "Steam Masters" related items
- "SIO-PORK" → Shows all pork siopao items
- "Monlei" → Shows items from Monlei supplier

### Editing Stock

1. **Click "Edit" button** on any card or table row
2. **Update fields** as needed
3. **Change image** (optional) - leave blank to keep current
4. **Click "Update Stock"**
   - Old image automatically deleted
   - New image stored if provided

### Increasing Quantity

1. **Click "Add" button** on any card
2. **Enter quantity to add** (e.g., "50")
3. **Click "Confirm"**
   - New quantity = current + added
   - Message shows: "Added 50 units (Total: 200)"

### Deleting Stock

1. **Click trash icon** in table view (only visible in table mode)
2. **Confirm deletion** in popup
3. **Item is removed** along with its image

---

## Summary Statistics

### Dashboard Cards Show:

| Metric | Calculation |
|--------|-------------|
| **Total SKUs** | COUNT of all inventory items |
| **Low Stock Alert** | Count where qty ≤ reorder_level |
| **Inventory Value** | SUM(qty × price_per_unit) |
| **View Options** | Toggle between Grid and Table |

**Example:**
```
Total SKUs: 25
Low Stock Alert: 3 (Items that need ordering)
Inventory Value: ₱145,230.50 (Total value of all stock)
```

---

## Image Upload Details

### Validation
- **Allowed formats**: JPG, PNG, WebP
- **Max size**: 5MB per image
- **Stored location**: `assets/inventory/` directory
- **Filename**: Random hex (prevents collisions)
  - Example: `item_a1b2c3d4e5f6g7h8.jpg`

### Upload Process
1. User selects image
2. Client-side validation (format check)
3. Server-side validation (size + type)
4. File saved with unique name
5. Database stores relative path: `assets/inventory/item_xxxx.jpg`
6. Image displayed in grid/table views

### Image Deletion
- When replacing image: Old file deleted from server
- When deleting item: Image file cleaned up
- Prevents orphaned files and disk bloat

---

## Security Features ✅

| Feature | Implementation |
|---------|-----------------|
| **SQL Injection** | Prepared statements for all queries |
| **XSS Prevention** | htmlspecialchars() on all output |
| **Admin Only** | require_role('admin') on all handlers |
| **Input Validation** | sanitize_text() on all string inputs |
| **File Validation** | MIME type and size checks |
| **SKU Uniqueness** | Database constraint + application check |
| **Safe Deletion** | Confirmation dialog + file cleanup |

---

## Data Flow Diagram

```
Admin Dashboard (admin-inventory.php)
         ↓
    [User Actions]
         ↓
  ┌─────┴─────────────────┬─────────────┬────────────┐
  ↓                       ↓             ↓            ↓
Add Stock          Edit Stock     Increase Stock   Delete Stock
  ↓                       ↓             ↓            ↓
admin_add_stock   admin_edit_stock  admin_increase admin_delete
  ↓                       ↓             ↓            ↓
[Insert]          [Update]        [Update Qty]    [Delete]
  ↓                       ↓             ↓            ↓
Database          Database        Database        Database
  ↓                       ↓             ↓            ↓
  └─────┬───────────────┬─────────────┬────────────┘
        ↓
  Redirect to admin-inventory.php
        ↓
  Display updated inventory with flash message
```

---

## Error Handling

### User Errors (With Flash Messages)
- ❌ Duplicate SKU: "SKU already exists. Please use a unique SKU."
- ❌ Invalid image: "Only JPG, PNG, and WebP images are allowed"
- ❌ Image too large: "Image size exceeds 5MB limit"
- ❌ Missing required field: "Item name and SKU are required"
- ❌ Item not found: "Item not found" (Edit/Delete operations)

### Success Messages
- ✅ Item added: "Stock item added successfully: [Name]"
- ✅ Item updated: "Stock item updated successfully: [Name]"
- ✅ Qty increased: "[Name]: Added 50 units (Total: 200)"
- ✅ Item deleted: "Stock item deleted: [Name]"

---

## Testing Checklist

✅ Can add new inventory item with image
✅ Image uploads and displays in grid/table
✅ Can view inventory in both grid and table modes
✅ Search filters work in table view
✅ Can edit item details and image
✅ Can increase quantity without editing
✅ Can delete items with confirmation
✅ Low stock status displays correctly (qty ≤ reorder_level)
✅ Summary statistics calculate correctly
✅ Flash messages display on all operations
✅ Images are cleaned up when replaced/deleted
✅ SKU uniqueness is enforced
✅ Unauthorized users cannot access (admin-only)

---

## Quick Reference

### Add New Item:
1. Click "Add new stock"
2. Fill form with Item Name, SKU, Qty, Unit
3. Optionally add image, supplier, price
4. Click "Add Stock"

### View Items:
- **Grid**: Cards with images (default)
- **Table**: Detailed rows with all fields

### Edit Item:
- Click "Edit" on any item
- Update any fields
- Optionally replace image
- Click "Update Stock"

### Increase Stock:
- Click "Add" on any item
- Enter quantity to add
- Click "Confirm"

### Delete Item:
- In table view, click trash icon
- Confirm deletion
- Item and image removed

---

## Database Queries

### Get All Inventory
```sql
SELECT id, name, sku, qty, reorder_level, unit, supplier, 
       price_per_unit, image_path, created_at 
FROM inventory 
ORDER BY name ASC
```

### Get Low Stock Items
```sql
SELECT name, sku, qty, reorder_level 
FROM inventory 
WHERE qty <= reorder_level
```

### Calculate Inventory Value
```sql
SELECT SUM(qty * price_per_unit) as total_value 
FROM inventory 
WHERE price_per_unit IS NOT NULL
```

---

## Future Enhancements

🔄 **Batch Import**: Upload CSV of inventory items
📊 **Stock History**: Track qty changes over time
🚨 **Email Alerts**: Notify admin when stock drops below reorder level
📁 **Bulk Operations**: Export to CSV, print barcode labels
🔄 **Stock Movements**: Track stock in/out with timestamps
🎯 **Forecasting**: Predict reorder dates based on sales
🏪 **Multi-warehouse**: Manage stock across locations

---

## Support & Troubleshooting

### Images Not Showing
- Check `assets/inventory/` directory exists
- Verify file permissions (755)
- Check image path in database

### Upload Fails
- Ensure `assets/inventory/` is writable
- Check file size (max 5MB)
- Verify file format (JPG, PNG, WebP)

### Slow Performance
- Add database index on `sku` and `name`
- Limit grid view to recent items
- Consider pagination for large inventories

