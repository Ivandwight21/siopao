<?php
require_once __DIR__ . '/config.php';
require_role('admin');
$flash = consume_flash();

// Get all inventory items with their images
$query = "SELECT id, name, sku, qty, reorder_level, unit, supplier, price_per_unit, image_path, updated_at FROM inventory ORDER BY name ASC";
$result = $mysqli->query($query);
$inventory_items = [];
while ($row = $result->fetch_assoc()) {
    $inventory_items[] = $row;
}

// Calculate summary stats
$total_skus = count($inventory_items);
$low_stock = 0;
$total_value = 0;
foreach ($inventory_items as $item) {
    if ($item['qty'] <= $item['reorder_level']) {
        $low_stock++;
    }
    if ($item['price_per_unit']) {
        $total_value += $item['qty'] * $item['price_per_unit'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Inventory | SiPao Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/portal.css">
    <style>
        .image-preview { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; margin-top: 10px; }
        .inventory-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; }
        .inventory-card { background: white; border: 1px solid #e5e5e5; border-radius: 8px; padding: 16px; text-align: center; }
        .inventory-card img { width: 100%; height: 160px; object-fit: cover; border-radius: 6px; margin-bottom: 12px; }
        .inventory-card h3 { margin: 8px 0; font-size: 14px; color: #333; }
        .inventory-card .sku { font-size: 12px; color: #666; }
        .inventory-card .qty { font-size: 18px; font-weight: bold; color: #8c1c2f; margin: 10px 0; }
        .inventory-card .status { font-size: 12px; padding: 4px 8px; border-radius: 4px; }
        .status.low { background: #ffebee; color: #c1121f; }
        .status.ok { background: #e8f5e9; color: #06a77d; }
        .action-buttons { margin-top: 12px; display: flex; gap: 6px; font-size: 12px; }
        .action-buttons button { flex: 1; padding: 6px; border: 1px solid #ddd; border-radius: 4px; background: white; cursor: pointer; }
        .action-buttons button:hover { background: #f5f5f5; }
    </style>
</head>
<body>
    <div class="portal-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <img src="123.jpg" alt="Monlei SiPao logo">
                <span class="portal-subtitle">Admin Control Center</span>
            </div>
            <div class="nav-group">
                <span class="nav-label">Overview</span>
                <a class="nav-link" href="admin-dashboard.php"><i class="fa-solid fa-chart-pie"></i>Dashboard</a>
                <a class="nav-link" href="admin-sales-report.php"><i class="fa-solid fa-chart-line"></i>Sales Report</a>
                <a class="nav-link active" href="admin-inventory.php"><i class="fa-solid fa-boxes-stacked"></i>Inventory</a>
                <a class="nav-link" href="admin-pos.php"><i class="fa-solid fa-cash-register"></i>Point of Sale</a>
                <a class="nav-link" href="admin-resellers.php"><i class="fa-solid fa-users"></i>Resellers</a>
                <a class="nav-link" href="admin-account-settings.php"><i class="fa-solid fa-gear"></i>Account Settings</a>
            </div>
        </aside>
        <main class="portal-main">
            <div class="topbar">
                <div>
                    <h1>Inventory Management</h1>
                    <div class="breadcrumb">Monlei SiPao • All stocks and replenishment</div>
                </div>
                <div class="quick-actions">
                    <button class="btn-accent" onclick="document.getElementById('addStockModal').style.display='block'"><i class="fa-solid fa-plus"></i> Add new stock</button>
                    <a class="btn-logout" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </div>
            </div>
            <?php if ($flash): ?>
            <div style="margin:16px;padding:12px;border-radius:8px;color:#fff;background-color:<?php echo $flash['type']==='error' ? '#c1121f' : '#06a77d'; ?>;font-weight:700;">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
            <?php endif; ?>
            <section class="content-section">
                <div class="card-grid">
                    <div class="card">
                        <h3>Total SKUs</h3>
                        <span class="metric"><?php echo $total_skus; ?></span>
                        <span class="trend"><i class="fa-solid fa-boxes-stacked"></i> Active items</span>
                    </div>
                    <div class="card">
                        <h3>Low Stock Alert</h3>
                        <span class="metric" style="color:#c1121f;"><?php echo $low_stock; ?></span>
                        <span class="trend" style="color: #c1121f;"><i class="fa-solid fa-bell"></i> Needs replenishment</span>
                    </div>
                    <div class="card">
                        <h3>Inventory Value</h3>
                        <span class="metric">₱<?php echo number_format($total_value, 2); ?></span>
                        <span class="trend"><i class="fa-solid fa-peso-sign"></i> Total stock value</span>
                    </div>
                    <div class="card">
                        <h3>View Options</h3>
                        <button class="btn-ghost" style="width:100%;margin-top:5px;" onclick="switchView('grid')"><i class="fa-solid fa-th"></i> Grid</button>
                        <button class="btn-ghost" style="width:100%;margin-top:5px;" onclick="switchView('table')"><i class="fa-solid fa-table"></i> Table</button>
                    </div>
                </div>

                <!-- SEARCH AND FILTERS -->
                <div style="background:white;padding:20px;border-radius:8px;margin-bottom:20px;box-shadow:0 2px 4px rgba(0,0,0,0.05);">
                    <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:12px;align-items:end;">
                        <div>
                            <label style="display:block;margin-bottom:5px;font-weight:bold;color:#666;"><i class="fa-solid fa-search"></i> Search</label>
                            <input type="text" id="globalSearch" placeholder="Search by name, SKU, or supplier..." style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
                        </div>
                        <div>
                            <label style="display:block;margin-bottom:5px;font-weight:bold;color:#666;"><i class="fa-solid fa-filter"></i> Status</label>
                            <select id="filterStatus" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
                                <option value="">All Items</option>
                                <option value="low">Low Stock</option>
                                <option value="ok">In Stock</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block;margin-bottom:5px;font-weight:bold;color:#666;"><i class="fa-solid fa-box"></i> Unit</label>
                            <select id="filterUnit" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
                                <option value="">All Units</option>
                                <option value="pcs">Pieces</option>
                                <option value="kg">Kilogram</option>
                                <option value="g">Grams</option>
                                <option value="L">Liters</option>
                                <option value="mL">Milliliters</option>
                                <option value="box">Box</option>
                                <option value="pack">Pack</option>
                                <option value="bundle">Bundle</option>
                            </select>
                        </div>
                        <div>
                            <button onclick="clearFilters()" style="width:100%;padding:10px;background:#f5f5f5;border:1px solid #ddd;border-radius:6px;cursor:pointer;font-weight:bold;color:#666;">
                                <i class="fa-solid fa-refresh"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- GRID VIEW -->
                <div id="gridView" style="display:block;">
                    <h2 style="margin:20px 0 16px 0;">All Inventory Items <span id="itemCount" style="color:#999;font-size:16px;"></span></h2>
                    <div class="inventory-grid">
                        <?php foreach ($inventory_items as $item): ?>
                        <div class="inventory-card" 
                             data-search="<?php echo strtolower($item['name'] . ' ' . $item['sku'] . ' ' . ($item['supplier'] ?? '')); ?>"
                             data-status="<?php echo $item['qty'] <= $item['reorder_level'] ? 'low' : 'ok'; ?>"
                             data-unit="<?php echo strtolower($item['unit'] ?? ''); ?>">
                            <?php if ($item['image_path']): ?>
                                <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <?php else: ?>
                                <div style="width:100%;height:160px;background:#f0f0f0;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#999;"><i class="fa-solid fa-image" style="font-size:48px;"></i></div>
                            <?php endif; ?>
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p class="sku"><?php echo htmlspecialchars($item['sku']); ?></p>
                            <div class="qty"><?php echo $item['qty']; ?> <?php echo htmlspecialchars($item['unit'] ?? 'units'); ?></div>
                            <span class="status <?php echo $item['qty'] <= $item['reorder_level'] ? 'low' : 'ok'; ?>">
                                <?php echo $item['qty'] <= $item['reorder_level'] ? '⚠️ Low Stock' : '✓ In Stock'; ?>
                            </span>
                            <div class="action-buttons">
                                <button onclick="editStock(<?php echo $item['id']; ?>)"><i class="fa-solid fa-edit"></i> Edit</button>
                                <button onclick="increaseStock(<?php echo $item['id']; ?>)"><i class="fa-solid fa-plus"></i> Add</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- TABLE VIEW -->
                <div id="tableView" style="display:none;">
                    <div class="table-card">
                        <header>
                            <h2>Inventory Table</h2>
                            <input type="text" id="tableSearch" placeholder="Search items..." style="padding:8px 12px;border:1px solid #ccc;border-radius:4px;">
                        </header>
                        <table>
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>SKU</th>
                                    <th>Qty</th>
                                    <th>Reorder Level</th>
                                    <th>Unit</th>
                                    <th>Supplier</th>
                                    <th>Price/Unit</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <?php foreach ($inventory_items as $item): ?>
                                <tr class="inventory-row" 
                                    data-search="<?php echo strtolower($item['name'] . ' ' . $item['sku'] . ' ' . ($item['supplier'] ?? '')); ?>"
                                    data-status="<?php echo $item['qty'] <= $item['reorder_level'] ? 'low' : 'ok'; ?>"
                                    data-unit="<?php echo strtolower($item['unit'] ?? ''); ?>">
                                    <td style="text-align:center;">
                                        <?php if ($item['image_path']): ?>
                                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" style="width:50px;height:50px;object-fit:cover;border-radius:4px;" alt="">
                                        <?php else: ?>
                                            <i class="fa-solid fa-image" style="color:#ccc;"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($item['sku']); ?></strong></td>
                                    <td style="text-align:center;font-weight:bold;color:#8c1c2f;"><?php echo $item['qty']; ?></td>
                                    <td style="text-align:center;"><?php echo $item['reorder_level']; ?></td>
                                    <td><?php echo htmlspecialchars($item['unit'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($item['supplier'] ?? '-'); ?></td>
                                    <td>₱<?php echo number_format($item['price_per_unit'] ?? 0, 2); ?></td>
                                    <td>
                                        <?php if ($item['qty'] <= $item['reorder_level']): ?>
                                            <span class="badge danger"><i class="fa-solid fa-circle-exclamation"></i> Low</span>
                                        <?php else: ?>
                                            <span class="badge success"><i class="fa-solid fa-check-circle"></i> OK</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button onclick="editStock(<?php echo $item['id']; ?>)" class="btn-ghost" style="font-size:12px;"><i class="fa-solid fa-edit"></i></button>
                                        <button onclick="increaseStock(<?php echo $item['id']; ?>)" class="btn-ghost" style="font-size:12px;"><i class="fa-solid fa-plus"></i></button>
                                        <button onclick="deleteStock(<?php echo $item['id']; ?>)" class="btn-ghost" style="font-size:12px;color:#c1121f;"><i class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- ADD NEW STOCK MODAL -->
    <div id="addStockModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;overflow-y:auto;">
        <div style="position:relative;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:30px;border-radius:12px;width:90%;max-width:600px;">
            <button onclick="document.getElementById('addStockModal').style.display='none'" style="position:absolute;top:10px;right:10px;background:none;border:none;font-size:24px;cursor:pointer;color:#999;">&times;</button>
            <h2 style="color:#8c1c2f;margin-bottom:20px;"><i class="fa-solid fa-plus"></i> Add New Stock Item</h2>
            <form method="POST" action="auth/admin_add_stock.php" enctype="multipart/form-data">
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;">Item Name: <span style="color:red;">*</span></label>
                    <input type="text" name="name" placeholder="e.g., Classic Pork Siopao" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required>
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;">SKU (Stock Keeping Unit): <span style="color:red;">*</span></label>
                    <input type="text" name="sku" placeholder="e.g., SIO-PORK-001" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;font-weight:bold;">Initial Quantity: <span style="color:red;">*</span></label>
                        <input type="number" name="qty" min="0" placeholder="0" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;font-weight:bold;">Unit: <span style="color:red;">*</span></label>
                        <select name="unit" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required>
                            <option value="">-- Select unit --</option>
                            <option value="pcs">Pieces (pcs)</option>
                            <option value="kg">Kilogram (kg)</option>
                            <option value="g">Grams (g)</option>
                            <option value="L">Liters (L)</option>
                            <option value="mL">Milliliters (mL)</option>
                            <option value="box">Box</option>
                            <option value="pack">Pack</option>
                            <option value="bundle">Bundle</option>
                        </select>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;font-weight:bold;">Reorder Level:</label>
                        <input type="number" name="reorder_level" min="0" placeholder="10" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" value="10">
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;font-weight:bold;">Price per Unit:</label>
                        <input type="number" name="price_per_unit" step="0.01" min="0" placeholder="0.00" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;">
                    </div>
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;">Supplier:</label>
                    <input type="text" name="supplier" placeholder="e.g., Monlei Farms" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;">
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;">Product Image:</label>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;">
                    <small style="color:#666;">Supported: JPG, PNG, WebP (Max 5MB)</small>
                    <img id="imagePreview" class="image-preview" style="display:none;">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <button type="button" onclick="document.getElementById('addStockModal').style.display='none'" class="btn-ghost" style="width:100%;">Cancel</button>
                    <button type="submit" class="btn-accent" style="width:100%;"><i class="fa-solid fa-check"></i> Add Stock</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT STOCK MODAL -->
    <div id="editStockModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;overflow-y:auto;">
        <div style="position:relative;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:30px;border-radius:12px;width:90%;max-width:600px;">
            <button onclick="document.getElementById('editStockModal').style.display='none'" style="position:absolute;top:10px;right:10px;background:none;border:none;font-size:24px;cursor:pointer;color:#999;">&times;</button>
            <h2 style="color:#8c1c2f;margin-bottom:20px;"><i class="fa-solid fa-edit"></i> Edit Stock Item</h2>
            <form method="POST" action="auth/admin_edit_stock.php" enctype="multipart/form-data" id="editForm">
                <input type="hidden" name="id" id="editId">
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;">Item Name:</label>
                    <input type="text" name="name" id="editName" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required>
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;">SKU:</label>
                    <input type="text" name="sku" id="editSku" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;font-weight:bold;">Quantity:</label>
                        <input type="number" name="qty" id="editQty" min="0" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;font-weight:bold;">Unit:</label>
                        <select name="unit" id="editUnit" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;">
                            <option value="pcs">Pieces (pcs)</option>
                            <option value="kg">Kilogram (kg)</option>
                            <option value="g">Grams (g)</option>
                            <option value="L">Liters (L)</option>
                            <option value="mL">Milliliters (mL)</option>
                            <option value="box">Box</option>
                            <option value="pack">Pack</option>
                            <option value="bundle">Bundle</option>
                        </select>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;font-weight:bold;">Reorder Level:</label>
                        <input type="number" name="reorder_level" id="editReorder" min="0" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;">
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;font-weight:bold;">Price per Unit:</label>
                        <input type="number" name="price_per_unit" id="editPrice" step="0.01" min="0" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;">
                    </div>
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;">Supplier:</label>
                    <input type="text" name="supplier" id="editSupplier" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;">
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;">Update Image:</label>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;">
                    <small style="color:#666;">Leave blank to keep current image</small>
                    <img id="editImagePreview" class="image-preview" style="display:none;">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <button type="button" onclick="document.getElementById('editStockModal').style.display='none'" class="btn-ghost" style="width:100%;">Cancel</button>
                    <button type="submit" class="btn-accent" style="width:100%;"><i class="fa-solid fa-check"></i> Update Stock</button>
                </div>
            </form>
        </div>
    </div>

    <!-- INCREASE STOCK MODAL -->
    <div id="increaseStockModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;">
        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:30px;border-radius:12px;width:500px;">
            <button onclick="document.getElementById('increaseStockModal').style.display='none'" style="position:absolute;top:10px;right:10px;background:none;border:none;font-size:24px;cursor:pointer;color:#999;">&times;</button>
            <h2 style="color:#8c1c2f;margin-bottom:20px;"><i class="fa-solid fa-plus"></i> Increase Stock</h2>
            <form method="POST" action="auth/admin_increase_stock.php">
                <input type="hidden" name="id" id="increaseId">
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;">Item:</label>
                    <input type="text" id="increaseName" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;background:#f5f5f5;" disabled>
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;">Current Quantity:</label>
                    <input type="text" id="increaseCurrentQty" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;background:#f5f5f5;" disabled>
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;">Quantity to Add: <span style="color:red;">*</span></label>
                    <input type="number" name="qty_to_add" min="1" placeholder="0" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <button type="button" onclick="document.getElementById('increaseStockModal').style.display='none'" class="btn-ghost" style="width:100%;">Cancel</button>
                    <button type="submit" class="btn-accent" style="width:100%;"><i class="fa-solid fa-check"></i> Confirm</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let stockData = <?php echo json_encode($inventory_items); ?>;

        function switchView(view) {
            document.getElementById('gridView').style.display = view === 'grid' ? 'block' : 'none';
            document.getElementById('tableView').style.display = view === 'table' ? 'block' : 'none';
        }

        function editStock(id) {
            const item = stockData.find(x => x.id == id);
            if (item) {
                document.getElementById('editId').value = item.id;
                document.getElementById('editName').value = item.name;
                document.getElementById('editSku').value = item.sku;
                document.getElementById('editQty').value = item.qty;
                document.getElementById('editUnit').value = item.unit || 'pcs';
                document.getElementById('editReorder').value = item.reorder_level;
                document.getElementById('editPrice').value = item.price_per_unit || '';
                document.getElementById('editSupplier').value = item.supplier || '';
                if (item.image_path) {
                    document.getElementById('editImagePreview').src = item.image_path;
                    document.getElementById('editImagePreview').style.display = 'block';
                }
                document.getElementById('editStockModal').style.display = 'block';
            }
        }

        function increaseStock(id) {
            const item = stockData.find(x => x.id == id);
            if (item) {
                document.getElementById('increaseId').value = item.id;
                document.getElementById('increaseName').value = item.name;
                document.getElementById('increaseCurrentQty').value = item.qty + ' ' + (item.unit || 'units');
                document.getElementById('increaseStockModal').style.display = 'block';
            }
        }

        function deleteStock(id) {
            if (confirm('Are you sure you want to delete this stock item?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'auth/admin_delete_stock.php';
                form.innerHTML = '<input type="hidden" name="id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Image preview for add modal
        document.addEventListener('DOMContentLoaded', function() {
            const addForm = document.querySelector('#addStockModal form');
            if (addForm) {
                const imageInput = addForm.querySelector('input[name="image"]');
                imageInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            document.getElementById('imagePreview').src = e.target.result;
                            document.getElementById('imagePreview').style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Edit form image preview
            const editForm = document.querySelector('#editStockModal form');
            if (editForm) {
                const editImageInput = editForm.querySelector('input[name="image"]');
                editImageInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            document.getElementById('editImagePreview').src = e.target.result;
                            document.getElementById('editImagePreview').style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Global filtering function
            function applyFilters() {
                const searchTerm = document.getElementById('globalSearch').value.toLowerCase();
                const statusFilter = document.getElementById('filterStatus').value;
                const unitFilter = document.getElementById('filterUnit').value.toLowerCase();
                
                // Filter grid cards
                const cards = document.querySelectorAll('.inventory-card');
                let visibleCount = 0;
                cards.forEach(card => {
                    const searchData = card.getAttribute('data-search');
                    const status = card.getAttribute('data-status');
                    const unit = card.getAttribute('data-unit');
                    
                    const matchesSearch = searchData.includes(searchTerm);
                    const matchesStatus = !statusFilter || status === statusFilter;
                    const matchesUnit = !unitFilter || unit === unitFilter;
                    
                    if (matchesSearch && matchesStatus && matchesUnit) {
                        card.style.display = '';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Filter table rows
                const rows = document.querySelectorAll('.inventory-row');
                rows.forEach(row => {
                    const searchData = row.getAttribute('data-search');
                    const status = row.getAttribute('data-status');
                    const unit = row.getAttribute('data-unit');
                    
                    const matchesSearch = searchData.includes(searchTerm);
                    const matchesStatus = !statusFilter || status === statusFilter;
                    const matchesUnit = !unitFilter || unit === unitFilter;
                    
                    row.style.display = (matchesSearch && matchesStatus && matchesUnit) ? '' : 'none';
                });
                
                // Update count
                document.getElementById('itemCount').textContent = `(${visibleCount} items shown)`;
            }
            
            // Clear all filters
            window.clearFilters = function() {
                document.getElementById('globalSearch').value = '';
                document.getElementById('filterStatus').value = '';
                document.getElementById('filterUnit').value = '';
                applyFilters();
            };
            
            // Attach filter listeners
            document.getElementById('globalSearch').addEventListener('keyup', applyFilters);
            document.getElementById('filterStatus').addEventListener('change', applyFilters);
            document.getElementById('filterUnit').addEventListener('change', applyFilters);
            
            // Table search (legacy - kept for table view)
            const searchInput = document.getElementById('tableSearch');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const filter = this.value.toLowerCase();
                    const rows = document.querySelectorAll('.inventory-row');
                    rows.forEach(row => {
                        const searchData = row.getAttribute('data-search').toLowerCase();
                        row.style.display = searchData.includes(filter) ? '' : 'none';
                    });
                });
            }
            
            // Initialize count on page load
            applyFilters();

            // Close modals on outside click
            window.addEventListener('click', function(e) {
                if (e.target.id === 'addStockModal') {
                    e.target.style.display = 'none';
                }
                if (e.target.id === 'editStockModal') {
                    e.target.style.display = 'none';
                }
                if (e.target.id === 'increaseStockModal') {
                    e.target.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
                    <input type="text" name="supplier" placeholder="Supplier name" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="submit" style="flex:1;padding:12px;background:#8c1c2f;color:white;border:none;border-radius:6px;font-weight:bold;cursor:pointer;">Receive Stock</button>
                    <button type="button" onclick="document.getElementById('stockInModal').style.display='none'" style="flex:1;padding:12px;background:#ccc;color:#333;border:none;border-radius:6px;font-weight:bold;cursor:pointer;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Purchase Order Modal -->
    <div id="poModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;">
        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:30px;border-radius:12px;width:500px;">
            <h2 style="color:#8c1c2f;margin-bottom:20px;">Create Purchase Order</h2>
            <form method="POST" action="auth/admin_create_po.php">
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;">Supplier Name:</label>
                    <input type="text" name="supplier_name" placeholder="e.g., Monlei Farms" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required>
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;">Contact Info:</label>
                    <input type="text" name="supplier_contact" placeholder="Phone or email" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="submit" style="flex:1;padding:12px;background:#8c1c2f;color:white;border:none;border-radius:6px;font-weight:bold;cursor:pointer;">Create PO</button>
                    <button type="button" onclick="document.getElementById('poModal').style.display='none'" style="flex:1;padding:12px;background:#ccc;color:#333;border:none;border-radius:6px;font-weight:bold;cursor:pointer;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
