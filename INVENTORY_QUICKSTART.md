# Inventory System - Quick Start Guide

## 🎯 What You Got

Complete inventory management system with:
- ✅ Display all stocks (grid + table views)
- ✅ Upload new stock items with images
- ✅ Edit existing items
- ✅ Increase quantities
- ✅ Delete items
- ✅ Real-time search
- ✅ Image storage & management

---

## 🚀 Quick Start (5 Minutes)

### 1. Access Inventory Page
```
URL: http://localhost/monleisiopao/admin-inventory.php
Login: Admin account required
```

### 2. Add Your First Item
```
Button: "Add new stock" (top right)
Fill Form:
  ├─ Item Name: "Your Product Name" *
  ├─ SKU: "PROD-CODE-001" * (must be unique)
  ├─ Quantity: "100" *
  ├─ Unit: "pcs" * (select from dropdown)
  ├─ Reorder Level: "20"
  ├─ Price per Unit: "45.50"
  ├─ Supplier: "Your Supplier"
  └─ Product Image: [JPG/PNG/WebP max 5MB]
Click: "Add Stock"
Result: ✅ Item added, image saved
```

### 3. View Your Inventory
```
Default: Grid View (cards with images)
Toggle: Click "View Options" → "Table" for detailed table
```

### 4. Find Items
```
In Table View:
├─ Search field appears at top
├─ Type to filter by name/SKU/supplier
└─ Results update instantly
```

### 5. Manage Items
```
Edit:     Click "Edit" → Change any field → Click "Update"
Add Qty:  Click "Add" → Enter quantity → Click "Confirm"
Delete:   Click trash icon → Click "Yes" to confirm
```

---

## 📋 Form Fields Reference

| Field | Required | Example | Notes |
|-------|----------|---------|-------|
| Item Name | ✅ Yes | Classic Pork Siopao | Product name |
| SKU | ✅ Yes | SIO-PORK-001 | Must be unique |
| Quantity | ✅ Yes | 150 | Current stock count |
| Unit | ✅ Yes | pcs | pcs, kg, L, box, pack, bundle |
| Reorder Level | ❌ No | 50 | Alert when qty drops below |
| Price per Unit | ❌ No | 45.50 | For inventory valuation |
| Supplier | ❌ No | Monlei Farms | Where to order from |
| Image | ❌ No | image.jpg | JPG, PNG, WebP (max 5MB) |

---

## 🎨 View Modes

### Grid View (Default)
```
┌──────────────────────────────────────────┐
│  [Product Image]  [Product Image]        │
│  Item Name         Item Name              │
│  SKU: XXX         SKU: YYY               │
│  150 pcs          120 pcs                │
│  ✓ In Stock       ⚠️ Low Stock           │
│  [Edit] [Add]     [Edit] [Add]           │
└──────────────────────────────────────────┘
```

### Table View
```
| Image | Name | SKU | Qty | Unit | Status | Actions |
|-------|------|-----|-----|------|--------|---------|
| [img] | Pork | ... | 150 | pcs  | ✓ OK   | [E][A][D] |
| [img] | Chkn | ... | 120 | pcs  | ✓ OK   | [E][A][D] |
```

---

## 💡 Tips & Tricks

### Best Practices
✅ Use consistent SKU format (e.g., CATEGORY-DESCRIPTION-NUMBER)
✅ Always upload product image for better visibility
✅ Set realistic reorder levels based on sales
✅ Keep supplier names consistent for easy filtering
✅ Update prices periodically for accurate valuation

### Common Tasks

**Add bulk inventory:**
```
1. Click "Add new stock"
2. Enter large quantity (e.g., 500)
3. Add image
4. Click "Add Stock"
5. Repeat for other items
```

**Find low stock items:**
```
1. Switch to Table View
2. Look for ⚠️ "Low Stock" badges
3. Click "Add" to replenish quickly
```

**Update pricing:**
```
1. Find item in table
2. Click "Edit"
3. Update "Price per Unit"
4. Click "Update Stock"
5. Inventory value auto-recalculates
```

**Search for items:**
```
In Table View:
├─ Search "steam" → Shows all steam products
├─ Search "SIO-" → Shows all siopao items
└─ Search "Monlei" → Shows items from Monlei supplier
```

---

## 📊 Dashboard Metrics

**At the top of inventory page:**
```
┌──────────────────────────────────────────────────┐
│ Total SKUs  │ Low Stock Alerts │ Inventory Value │
│     25      │        3         │   ₱145,230.50   │
│Active items │ Need ordering    │ Total value     │
└──────────────────────────────────────────────────┘
```

- **Total SKUs**: Number of unique products in stock
- **Low Stock Alerts**: Count of items below reorder level
- **Inventory Value**: Total cost of all inventory

---

## 🖼️ Image Upload Rules

✅ **Allowed formats**: JPG, PNG, WebP
✅ **Maximum size**: 5 MB per image
✅ **Preview**: See image before confirming
✅ **Storage**: Automatically saved to assets/inventory/
✅ **On edit**: Old image auto-deleted when replaced
✅ **On delete**: Image file cleaned up

**Example:**
```
Upload → Preview appears → Click "Add Stock" → Image saved ✅
```

---

## ❌ Error Messages & Solutions

| Error | Cause | Solution |
|-------|-------|----------|
| "SKU already exists" | SKU not unique | Use different SKU code |
| "Only JPG, PNG, WebP..." | Wrong image format | Upload JPG, PNG, or WebP |
| "Image size exceeds 5MB" | File too large | Compress image or use smaller |
| "Item name and SKU required" | Missing field | Fill in all required fields (*) |
| "Item not found" | Item deleted | Refresh page, check ID |

---

## 📱 Mobile Friendly

✅ Grid view works great on mobile
✅ Table view scrolls horizontally on small screens
✅ Touch-friendly buttons and modals
✅ Search works on mobile devices

---

## 🔐 Security

✅ **Admin-only access**: Non-admins cannot access
✅ **Validated uploads**: Images scanned for safety
✅ **Database protection**: All queries use prepared statements
✅ **File cleanup**: Orphaned files removed automatically
✅ **Confirmation dialogs**: Prevents accidental deletion

---

## ⚡ Keyboard Shortcuts

| Key | Action |
|-----|--------|
| Tab | Navigate between form fields |
| Enter | Submit form |
| Escape | Close modal without saving |
| Ctrl+F | Search page (browser) |

---

## 📈 Inventory Value Formula

```
Total Inventory Value = SUM(Quantity × Price per Unit)

Example:
┌────────────────┬──────┬───────────┬────────────┐
│ Item           │ Qty  │ Price/pc  │ Subtotal   │
├────────────────┼──────┼───────────┼────────────┤
│ Pork Siopao    │ 150  │ 45.50     │ 6,825.00   │
│ Chicken Sio    │ 120  │ 48.00     │ 5,760.00   │
│ Beef Siopao    │ 85   │ 50.00     │ 4,250.00   │
├────────────────┼──────┼───────────┼────────────┤
│ TOTAL          │ 355  │ avg 47.70 │ 16,835.00  │
└────────────────┴──────┴───────────┴────────────┘
```

---

## 🧪 Test Items

Try adding these to test:
```
1. Classic Pork Siopao
   SKU: SIO-PORK-001
   Qty: 100
   Unit: pcs
   Price: 45.50

2. Spicy Beef Siopao
   SKU: SIO-BEEF-002
   Qty: 75
   Unit: pcs
   Price: 48.00

3. Pork Filling (bulk)
   SKU: ING-PORK-FILL-001
   Qty: 12
   Unit: kg
   Price: 250.00
```

---

## 📞 Support

**Issues?**
1. Check INVENTORY_MANAGEMENT_GUIDE.md (detailed docs)
2. Check INVENTORY_IMPLEMENTATION_SUMMARY.md (technical details)
3. Verify database connection in config.php
4. Check assets/inventory/ folder permissions (755)

**File structure:**
```
admin-inventory.php          Main dashboard
auth/admin_add_stock.php     Create items
auth/admin_edit_stock.php    Update items
auth/admin_increase_stock.php Add quantity
auth/admin_delete_stock.php  Delete items
assets/inventory/            Image storage
```

---

## 🎉 You're All Set!

Your inventory system is ready to use.

**Start now:**
1. Log in as admin
2. Go to Inventory page
3. Click "Add new stock"
4. Upload your first item
5. View in grid or table mode
6. Try search and edit functions

**Happy managing!** 📦✨

