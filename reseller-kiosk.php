<?php
require_once __DIR__ . '/config.php';
require_role('reseller');
$user = current_user();

// Load available products
$stmt = $mysqli->prepare("SELECT id, name, sku, qty, price_per_unit, image_path FROM inventory WHERE qty > 0 ORDER BY name ASC");
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reseller Kiosk | SiPao Portal</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/portal.css">
  <style>
    .kiosk-layout{display:grid;grid-template-columns:1fr 380px;gap:16px;}
    .product-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;}
    .product-card{background:#fff;border:1px solid #eee;border-radius:10px;overflow:hidden;cursor:pointer;display:flex;flex-direction:column}
    .product-card img{width:100%;height:140px;object-fit:cover}
    .product-card .body{padding:10px}
    .product-card .name{font-size:14px;font-weight:700;color:#333;margin-bottom:4px}
    .product-card .sku{font-size:12px;color:#777;margin-bottom:6px}
    .product-card .price{font-size:16px;color:#8c1c2f;font-weight:800}
    .cart{background:#fff;border:1px solid #eee;border-radius:10px;padding:14px;display:flex;flex-direction:column;max-height:calc(100vh - 220px)}
    .cart-items{overflow:auto;flex:1}
    .cart-item{display:grid;grid-template-columns:1fr 70px 90px 24px;align-items:center;gap:6px;padding:6px 0;border-bottom:1px dashed #eee}
    .qty-control{display:flex;gap:6px;align-items:center}
    .qty-control button{border:1px solid #ddd;background:#fafafa;border-radius:4px;padding:2px 6px}
    .summary{margin-top:10px}
    .summary div{display:flex;justify-content:space-between;margin:4px 0}
    .pay-row{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:8px 0}
    .checkout-btn{width:100%;padding:12px;background:#8c1c2f;color:#fff;border:none;border-radius:8px;font-weight:800;cursor:pointer}
    .checkout-btn:disabled{opacity:.6;cursor:not-allowed}
    .searchbar{margin-bottom:10px}
    .searchbar input{width:100%;padding:10px;border:1px solid #ddd;border-radius:8px}
  </style>
</head>
<body>
<div class="portal-shell">
  <aside class="sidebar">
    <div class="brand-block">
      <img src="123.jpg" alt="Monlei SiPao logo">
      <span class="portal-subtitle">Reseller Kiosk</span>
    </div>
    <div class="nav-group">
      <span class="nav-label">Quick access</span>
      <a class="nav-link" href="resellerdashboard.php"><i class="fa-solid fa-chart-pie"></i>Dashboard</a>
      <a class="nav-link" href="reseller-sales-report.php"><i class="fa-solid fa-chart-line"></i>Sales Report</a>
        <a class="nav-link" href="reseller-pos.php"><i class="fa-solid fa-cash-register"></i>POS</a>
        <a class="nav-link active" href="reseller-kiosk.php"><i class="fa-solid fa-store"></i>Kiosk</a>
        <a class="nav-link" href="reseller-history.php"><i class="fa-solid fa-receipt"></i>History</a>
      <a class="nav-link" href="reseller-account-settings.php"><i class="fa-solid fa-user"></i>Account</a>
    </div>
  </aside>
  <main class="portal-main">
    <div class="topbar">
      <div>
        <h1>Kiosk - Tap to add</h1>
        <div class="breadcrumb">Browse products, add to cart, and checkout. Print receipt after payment.</div>
      </div>
      <div class="quick-actions">
        <button class="btn-accent" onclick="window.print()"><i class="fa-solid fa-print"></i> Print Page</button>
        <a class="btn-logout" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
      </div>
    </div>

    <section class="content-section kiosk-layout">
      <div>
        <div class="searchbar"><input id="search" placeholder="Search products by name or SKU..."></div>
        <div class="product-grid" id="productGrid">
          <?php foreach ($products as $p): $price = $p['price_per_unit'] !== null ? (float)$p['price_per_unit'] : 0.00; ?>
          <div class="product-card" data-search="<?php echo strtolower($p['name'] . ' ' . $p['sku']); ?>" onclick='addToCart(<?php echo json_encode(["id"=>$p["id"],"name"=>$p["name"],"sku"=>$p["sku"],"price"=>$price]); ?>)'>
            <?php if (!empty($p['image_path'])): ?>
              <img src="<?php echo htmlspecialchars($p['image_path']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
            <?php else: ?>
              <div style="height:140px;background:#f3f3f3;display:flex;align-items:center;justify-content:center;color:#aaa"><i class="fa-solid fa-image" style="font-size:40px"></i></div>
            <?php endif; ?>
            <div class="body">
              <div class="name"><?php echo htmlspecialchars($p['name']); ?></div>
              <div class="sku">SKU: <?php echo htmlspecialchars($p['sku']); ?> • Stock: <?php echo (int)$p['qty']; ?></div>
              <div class="price">₱<?php echo number_format($price,2); ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="cart">
        <h3 style="margin:0 0 10px 0;color:#8c1c2f"><i class="fa-solid fa-bag-shopping"></i> Cart</h3>
        <div id="emptyState" style="color:#777;margin:10px 0">No items yet. Tap products to add.</div>
        <div class="cart-items" id="cartItems"></div>
        <div class="summary">
          <div><span>Subtotal</span><strong id="subtotal">₱0.00</strong></div>
          <div class="pay-row">
            <div>
              <label style="font-size:12px;color:#666">Payment</label>
              <select id="payment_method" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px">
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="gcash">GCash</option>
              </select>
            </div>
            <div>
              <label style="font-size:12px;color:#666">Cash Tendered</label>
              <input id="cash" type="number" min="0" step="0.01" placeholder="0.00" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px">
            </div>
          </div>
          <div><span>Change</span><strong id="change">₱0.00</strong></div>
          <button class="checkout-btn" id="checkoutBtn" onclick="checkout()" disabled><i class="fa-solid fa-check"></i> Checkout</button>
        </div>
        <form id="checkoutForm" method="POST" action="auth/reseller_checkout.php" style="display:none">
          <input type="hidden" name="cart" id="cartInput">
          <input type="hidden" name="payment_method" id="paymentInput">
          <input type="hidden" name="cash" id="cashInput">
        </form>
      </div>
    </section>
  </main>
</div>

<script>
let cart = [];

function addToCart(prod){
  const idx = cart.findIndex(i=>i.id===prod.id);
  if(idx>=0){ cart[idx].qty += 1; }
  else { cart.push({id:prod.id, name:prod.name, sku:prod.sku, price:parseFloat(prod.price)||0, qty:1}); }
  renderCart();
}

function removeItem(id){ cart = cart.filter(i=>i.id!==id); renderCart(); }
function inc(id){ const it = cart.find(i=>i.id===id); if(it){ it.qty++; renderCart(); } }
function dec(id){ const it = cart.find(i=>i.id===id); if(it){ it.qty=Math.max(1,it.qty-1); renderCart(); } }

function renderCart(){
  const box = document.getElementById('cartItems');
  const empty = document.getElementById('emptyState');
  box.innerHTML='';
  if(cart.length===0){ empty.style.display='block'; document.getElementById('checkoutBtn').disabled=true; updateTotals(); return; }
  empty.style.display='none';
  cart.forEach(it=>{
    const lineTotal = (it.price*it.qty).toFixed(2);
    const row = document.createElement('div');
    row.className='cart-item';
    row.innerHTML = `
      <div><strong>${escapeHtml(it.name)}</strong><div style="font-size:12px;color:#777">${escapeHtml(it.sku)}</div></div>
      <div class='qty-control'>
        <button type='button' onclick='dec(${it.id})'>-</button>
        <span>${it.qty}</span>
        <button type='button' onclick='inc(${it.id})'>+</button>
      </div>
      <div style='text-align:right'>₱${lineTotal}</div>
      <button type='button' onclick='removeItem(${it.id})' style='background:none;border:none;color:#c1121f'><i class='fa-solid fa-xmark'></i></button>
    `;
    box.appendChild(row);
  });
  document.getElementById('checkoutBtn').disabled=false;
  updateTotals();
}

function updateTotals(){
  const subtotal = cart.reduce((s,i)=>s + (i.price*i.qty), 0);
  document.getElementById('subtotal').innerText = '₱' + subtotal.toFixed(2);
  const cash = parseFloat(document.getElementById('cash').value||0);
  const change = Math.max(0, cash - subtotal);
  document.getElementById('change').innerText = '₱' + change.toFixed(2);
}

document.getElementById('cash').addEventListener('input', updateTotals);

document.getElementById('search').addEventListener('keyup', function(){
  const f = this.value.toLowerCase();
  document.querySelectorAll('.product-card').forEach(card=>{
    card.style.display = card.getAttribute('data-search').includes(f) ? '' : 'none';
  });
});

function checkout(){
  if(cart.length===0) return;
  const subtotal = cart.reduce((s,i)=>s + (i.price*i.qty), 0);
  const cash = parseFloat(document.getElementById('cash').value||0);
  const method = document.getElementById('payment_method').value;
  if(method==='cash' && cash < subtotal){
    alert('Cash tendered is not enough.');
    return;
  }
  document.getElementById('cartInput').value = JSON.stringify(cart);
  document.getElementById('paymentInput').value = method;
  document.getElementById('cashInput').value = isNaN(cash)?0:cash;
  document.getElementById('checkoutForm').submit();
}

function escapeHtml(str){ return str.replace(/[&<>"]+/g, s=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;"}[s])); }
</script>
</body>
</html>