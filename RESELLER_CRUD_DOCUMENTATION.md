# Admin Reseller Management - CRUD Operations

## Overview
Complete CRUD (Create, Read, Update, Delete) system for managing all reseller accounts from the admin control panel.

## Pages & Features

### 1. Admin Reseller Management Page
**File:** `admin-reseller-management.php`

#### Main Features:
- **List all resellers** - Table with email, store name, phone, sales count, revenue, and join date
- **Search functionality** - Real-time filtering by email, store name, or phone
- **Action buttons** for each reseller:
  - **View** - Modal showing complete reseller details
  - **Edit** - Modal for updating reseller information
  - **Delete** - Confirmation prompt, then permanent deletion

#### Sections:
1. **Reseller list table** with sortable data
2. **Search bar** for quick filtering
3. **Add reseller button** opens creation modal
4. **Three modals:**
   - View details modal (read-only)
   - Edit modal (all profile fields editable)
   - Add new reseller modal (create new accounts)

---

## CRUD Operations

### CREATE - Add New Reseller
**Modal:** "Create new reseller account"

**Form fields:**
- Email (required, must be unique)
- Username (required, 3+ characters, must be unique)
- Store name (optional)
- Phone number (optional)

**Process:**
1. Admin clicks "Add reseller" button
2. Modal opens with form
3. Admin fills in details
4. System generates temporary password
5. New user account created with role='reseller'
6. Reseller profile record created
7. Flash message shows temporary password (admin must share securely)

**Backend:** `auth/admin_add_reseller.php`
- Validates email and username uniqueness
- Hashes password with `password_hash()`
- Creates user in `users` table
- Creates profile in `reseller_profile` table
- Returns success with temporary password

---

### READ - View Reseller Details
**Modal:** "Reseller details"

**Displays:**
- Account information (email, username, join date)
- Store information (name, address, phone)
- Payout information (bank, account number, holder)
- Sales performance (total transactions, revenue)

**Functionality:**
- Click "View" button on any reseller row
- Modal loads via AJAX (`admin_get_reseller.php`)
- Shows all stored information
- Read-only display
- Easy close button

**Backend:** `auth/admin_get_reseller.php`
- Fetches reseller data from `users` and `reseller_profile` tables
- Joins with `sales_records` for performance metrics
- Returns formatted HTML display
- Secure, read-only output

---

### UPDATE - Edit Reseller Account
**Modal:** "Edit reseller account"

**Editable fields:**
- Email
- Store name
- Address
- Phone number
- Bank name
- Account number
- Account holder name

**Process:**
1. Click "Edit" button on reseller row
2. Modal loads current data via AJAX (`admin_get_reseller_data.php`)
3. Admin modifies any fields
4. Click "Save changes"
5. System validates and updates database
6. Success message shown

**Validations:**
- Email must be valid email format
- Email must be unique (if changed)
- All text fields sanitized
- Updates both `users` and `reseller_profile` tables

**Backend:** `auth/admin_edit_reseller.php`
- Validates email uniqueness
- Updates user email in `users` table
- Creates or updates `reseller_profile` record
- Uses prepared statements to prevent SQL injection
- Returns success/error message

---

### DELETE - Remove Reseller Account
**Trigger:** Click "Delete" button on reseller row

**Process:**
1. Confirmation dialog appears: "Delete this reseller?"
2. If confirmed, POST request to `admin_delete_reseller.php`
3. System verifies it's a reseller (not admin)
4. Cascading deletes remove:
   - User account from `users` table
   - Reseller profile from `reseller_profile` table
   - Related stock orders from `stock_orders` table
   - Related sales records from `sales_records` table
   - All foreign key dependencies
5. Success message confirms deletion

**Validations:**
- Only reseller accounts can be deleted (prevents accidental admin deletion)
- Must exist in database
- Confirmation required from admin

**Backend:** `auth/admin_delete_reseller.php`
- Verifies target is a reseller account
- Uses CASCADE foreign keys to clean up related data
- Prevents deletion of non-existent users
- Returns success/error message

---

## Data Flow

### GET Reseller Data (for Edit modal)
```
Click Edit → JavaScript fetch() → admin_get_reseller_data.php 
→ Query users + reseller_profile → JSON response → Fill form
```

### View Reseller Details
```
Click View → JavaScript fetch() → admin_get_reseller.php 
→ Query with JOINs → Formatted HTML → Display in modal
```

### Create Reseller
```
Fill form → POST admin_add_reseller.php 
→ Validate email/username → Hash password 
→ Insert users + reseller_profile → Redirect with flash message
```

### Update Reseller
```
Edit form → POST admin_edit_reseller.php 
→ Validate email → Update users/reseller_profile → Redirect with flash message
```

### Delete Reseller
```
Click Delete → Confirm → POST admin_delete_reseller.php 
→ Verify reseller → DELETE users (CASCADE) → Redirect with flash message
```

---

## Database Operations

### Tables Involved:
1. **users** - User accounts (id, email, username, password_hash, role, created_at)
2. **reseller_profile** - Reseller-specific data
3. **stock_orders** - Reseller orders (auto-deleted on user delete)
4. **sales_records** - Sales transactions (auto-deleted on user delete)

### Foreign Key Relationships:
- `reseller_profile.user_id` → `users.id` (CASCADE DELETE)
- `stock_orders.user_id` → `users.id` (CASCADE DELETE)
- `sales_records.user_id` → `users.id` (CASCADE DELETE)

This ensures data integrity when deleting a reseller - all related records are automatically removed.

---

## Security Features

✅ **Role-based access control** - Only admins can access this page
✅ **Prepared statements** - All database queries use parameterized statements
✅ **Input validation** - Email format, text sanitization
✅ **Input sanitization** - `sanitize_email()`, `sanitize_text()` functions
✅ **Password hashing** - `password_hash()` with PASSWORD_DEFAULT algorithm
✅ **Email uniqueness checking** - Prevents duplicate accounts
✅ **Verification of user role** - Delete only works on resellers, not admins
✅ **Confirmation dialogs** - Destructive actions require confirmation
✅ **Flash messaging** - Secure session-based feedback
✅ **AJAX security** - GET requests for read-only data, no sensitive info in URL

---

## UI/UX Features

- **Search functionality** - Real-time table filtering
- **Modal dialogs** - Non-intrusive data entry
- **Action buttons** - Inline View/Edit/Delete
- **Visual feedback** - Flash messages for all operations
- **Responsive layout** - Works on different screen sizes
- **Color-coded buttons** - Green for positive, red for destructive
- **Consistent styling** - Matches existing admin portal design
- **AJAX loading states** - "Loading..." text during data fetch

---

## Integration with Existing Admin Pages

The new reseller management page integrates with:
- **admin-resellers.php** - Now has "Manage accounts" button linking here
- **admin-dashboard.php** - Reseller count metrics
- **admin-inventory.php** - Stock orders from resellers
- **admin-pos.php** - Sales from resellers
- **admin-account-settings.php** - Admin account security

---

## Backend Files Created

1. `auth/admin_get_reseller.php` - Fetch and display reseller details
2. `auth/admin_get_reseller_data.php` - Get reseller data for edit modal (JSON)
3. `auth/admin_add_reseller.php` - Create new reseller account
4. `auth/admin_edit_reseller.php` - Update reseller information
5. `auth/admin_delete_reseller.php` - Delete reseller account

---

## Error Handling

Each operation includes:
- Input validation with clear error messages
- Database error catching
- User-friendly error displays via flash messages
- Proper HTTP status codes
- Graceful degradation (no partial updates)

---

## Temporary Passwords

When creating a new reseller:
1. System generates random 12-character hex string
2. Displayed in flash message after creation
3. Admin must copy and share securely with reseller
4. Reseller can reset password on first login (via reset password page)
5. Password never sent via email (admin must share manually)

---

## Future Enhancements

Possible additions:
- Bulk export of reseller data (CSV)
- Reseller account status (Active/Inactive/Suspended)
- Email notification when new reseller account created
- Reseller activity logs
- Bulk delete with confirmation
- Filter by sales range or date joined
- Reseller tier/classification system
