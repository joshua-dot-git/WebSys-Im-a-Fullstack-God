<?php
session_start();
require_once 'database.php';

// Always get cart count from session for badge
$cart = $_SESSION['cart'] ?? [];
$cartCount = array_sum($cart);

$loggedIn = isset($_SESSION['user_id']);
$firstName = $_SESSION['first_name'] ?? '';
$role = $_SESSION['role'] ?? '';

// Get selected category filter (if any)
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

$sql = "SELECT p.*, c.name AS category_name, l.name AS location_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        LEFT JOIN locations l ON p.origin_location_id = l.id";
if ($categoryFilter > 0) {
    $sql .= " WHERE p.category_id = ?";
}
$sql .= " ORDER BY p.id ASC";

$stmt = $pdo->prepare($sql);
$categoryFilter > 0 ? $stmt->execute([$categoryFilter]) : $stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// --- FIXED IMAGE FUNCTION ---
// This will now show the actual product image uploaded to images/products/ (same as seller_dashboard.php)
function product_image_path($filename) {
    // Prefer product image in images/products/
    if (!$filename) return "images/logo.png";
    $candidates = [];
    // if filename already contains folder part
    if (strpos($filename, 'images/') === 0 || strpos($filename, '/images/') === 0) {
        $candidates[] = ltrim($filename, '/');
    } else {
        $candidates[] = "images/products/{$filename}";
        $candidates[] = "images/{$filename}";
        // try adding common extensions if not present
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $candidates[] = "images/products/{$base}.png";
        $candidates[] = "images/products/{$base}.jpg";
        $candidates[] = "images/products/{$base}.jpeg";
        $candidates[] = "images/{$base}.png";
        $candidates[] = "images/{$base}.jpg";
        $candidates[] = "images/{$base}.jpeg";
    }
    foreach ($candidates as $c) {
        if (file_exists(__DIR__ . '/' . $c)) return $c;
    }
    // fallback
    return "images/logo.png";
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Shop — Baytisan</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="style.css">
  <script defer src="script.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .category-filter .btn,
    .nav-actions .btn {
      background: #8EB486;
      color: #fff !important;
      border: none;
      border-radius: 10px;
      padding: 10px 18px;
      font-weight: 600;
      font-size: 1em;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      box-shadow: 0 6px 18px rgba(142,180,134,0.07);
      cursor: pointer;
      transition: background .18s, color .18s, box-shadow .18s, transform .16s;
    }
    .category-filter .btn:hover,
    .nav-actions .btn:hover,
    .category-filter .btn:focus,
    .nav-actions .btn:focus {
      background: #6a9c5b;
      color: #fff !important;
      box-shadow: 0 12px 26px rgba(104,180,130,0.13);
      transform: translateY(-2px) scale(1.04);
      text-decoration: none;
    }
    .category-filter .btn i,
    .nav-actions .btn i {
      font-size: 1.07em;
      margin-right: 5px;
      transition: transform .16s;
    }
    .category-filter .btn:hover i,
    .nav-actions .btn:hover i {
      transform: scale(1.15) rotate(-8deg);
    }
    .category-filter .btn.active,
    .category-filter .btn.btn-primary {
      background: #8EB486;
      color: #fff !important;
      font-weight: bold;
      border: none;
      box-shadow: 0 10px 22px rgba(142,180,134,0.11);
    }
    .category-filter .btn { margin: 0 4px 9px 4px; }
    .btn-add {
      background: #8EB486;
      color: #fff !important;
      border: none;
      border-radius: 10px;
      padding: 8px 18px;
      font-weight: 600;
      font-size: 1em;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      box-shadow: 0 6px 18px rgba(142,180,134,0.07);
      cursor: pointer;
      transition: background .18s, color .18s, box-shadow .18s, transform .16s;
    }
    .btn-add:hover, .btn-add:focus {
      background: #6a9c5b;
      color: #fff !important;
      box-shadow: 0 12px 26px rgba(104,180,130,0.13);
      transform: translateY(-2px) scale(1.04);
      text-decoration: none;
    }
    .btn-add i { font-size: 1.07em; margin-right: 5px; transition: transform .16s; }
    .btn-add:hover i { transform: scale(1.15) rotate(-8deg); }

    /* product modal */
    .product-modal {
      display:none;
      position:fixed;
      inset:0;
      z-index:1200;
      background:rgba(0,0,0,0.5);
      align-items:center;
      justify-content:center;
    }
    .product-modal.show { display:flex; }
    .product-modal .modal-inner { background:#fff;border-radius:12px;max-width:900px;width:94%;padding:18px;display:flex;gap:16px;align-items:flex-start; }
    .product-modal img { width:48%;border-radius:10px;object-fit:cover; }
    .product-modal .info { width:52%; }
    .product-modal .close { position:absolute; top:20px; right:24px; background:transparent;border:none; font-size:24px; color:#fff; cursor:pointer;}
    @media(max-width:900px){ .product-modal .modal-inner{flex-direction:column} .product-modal img{width:100%} .product-modal .info{width:100%} }
  </style>
</head>
<body>
<header class="site-header">
  <div class="container nav">
    <a href="index.php" class="brand">
      <img src="images/logo.png" alt="Baytisan logo" class="logo">
      <span class="brand-text">Baytisan</span>
    </a>
    <nav class="main-nav">
      <a href="index.php">Home</a>
      <a href="products.php" class="active">Shop</a>
      <a href="order_history.php">Orders</a>
      <a href="admin_dashboard.php">Admin</a>
    </nav>
    <div class="nav-actions" id="navActions">
      <?php if ($loggedIn): ?>
        <span class="welcome"><i class="fa fa-user"></i> Welcome, <?= htmlspecialchars($firstName) ?></span>
        <?php if ($role === 'customer'): ?>
          <a href="profile.php" class="btn btn-primary" style="margin-left:8px;">
            <i class="fa fa-user"></i> Profile
          </a>
        <?php elseif ($role === 'admin'): ?>
          <a href="admin_profile.php" class="btn btn-primary" style="margin-left:8px;">
            <i class="fa fa-user-shield"></i> Admin Profile
          </a>
        <?php elseif ($role === 'seller'): ?>
          <a href="seller_dashboard.php" class="btn btn-primary" style="margin-left:8px;">
            <i class="fa fa-store"></i> Seller Dashboard
          </a>
        <?php endif; ?>
        <a href="cart.php" class="btn"><i class="fa fa-shopping-cart"></i> Cart (<?= $cartCount ?>)</a>
        <a href="logout.php" class="btn btn-danger"><i class="fa fa-sign-out-alt"></i> Logout</a>
      <?php endif; ?>
      <!-- No buttons for guests -->
    </div>
  </div>
</header>

<main class="container" style="padding:20px 0">
  <h2><i class="fa fa-store"></i> Our Products</h2>

  <!-- Category Filter Buttons -->
  <div class="category-filter">
    <a href="products.php" class="btn <?php echo ($categoryFilter === 0) ? 'active' : ''; ?>"><i class="fa fa-th-large"></i> All</a>
    <?php foreach ($categories as $cat): ?>
      <a href="products.php?category=<?= $cat['id'] ?>"
         class="btn <?php echo ($categoryFilter === (int)$cat['id']) ? 'active' : ''; ?>">
         <?= htmlspecialchars($cat['name']) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="product-grid">
    <?php if (empty($products)): ?>
      <p>No products found.</p>
    <?php else: ?>
      <?php foreach ($products as $p): ?>
        <div class="card">
          <div class="img-wrap" style="position:relative;">
            <img
                 src="<?= htmlspecialchars(product_image_path($p['image_filename'])) ?>"
                 alt="<?= htmlspecialchars($p['name']) ?>"
                 class="product-click-img"
                 data-id="<?= (int)$p['id'] ?>"
                 data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>"
                 data-price="<?= number_format($p['price'],2,'.','') ?>"
                 data-desc="<?= htmlspecialchars($p['description'] ?? '', ENT_QUOTES) ?>"
                 data-img="<?= htmlspecialchars(product_image_path($p['image_filename'])) ?>"
                 data-category="<?= htmlspecialchars($p['category_name']) ?>"
                 data-location="<?= htmlspecialchars($p['location_name']) ?>"
                 style="cursor:pointer"
                 >
            <div class="overlay"><button class="view product-view-btn" type="button"
              data-id="<?= (int)$p['id'] ?>"
              data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>"
              data-price="<?= number_format($p['price'],2,'.','') ?>"
              data-desc="<?= htmlspecialchars($p['description'] ?? '', ENT_QUOTES) ?>"
              data-img="<?= htmlspecialchars(product_image_path($p['image_filename'])) ?>"
              data-category="<?= htmlspecialchars($p['category_name']) ?>"
              data-location="<?= htmlspecialchars($p['location_name']) ?>"
              >View</button></div>
          </div>
          <h3 style="margin:10px 12px 0 12px;">
            <a href="javascript:void(0)" class="product-click" 
              data-id="<?= (int)$p['id'] ?>"
              data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>"
              data-price="<?= number_format($p['price'],2,'.','') ?>"
              data-desc="<?= htmlspecialchars($p['description'] ?? '', ENT_QUOTES) ?>"
              data-img="<?= htmlspecialchars(product_image_path($p['image_filename'])) ?>"
              data-category="<?= htmlspecialchars($p['category_name']) ?>"
              data-location="<?= htmlspecialchars($p['location_name']) ?>"
              style="text-decoration:none;color:inherit;cursor:pointer;">
              <?= htmlspecialchars($p['name']) ?>
            </a>
          </h3>
          <p class="text-muted" style="margin:6px 12px 0 12px;"><?= htmlspecialchars($p['category_name']) ?><?= $p['location_name'] ? ' • ' . htmlspecialchars($p['location_name']) : '' ?></p>
          <p class="price" style="margin:10px 12px 8px 12px;">₱<?= number_format($p['price'],2) ?></p>
          <form method="POST" action="cart.php" style="display:flex;gap:8px;align-items:center;margin:8px 12px 12px 12px">
            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
            <input type="number" name="qty" value="1" min="1" style="width:72px;padding:8px;border-radius:8px;border:1px solid #ddd">
            <button type="submit" class="btn-add"><i class="fa fa-cart-plus"></i> Add</button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>

<!-- Product detail modal -->
<div id="productDetailModal" class="product-modal" aria-hidden="true">
  <button class="close" id="closeProductModal">×</button>
  <div class="modal-inner" role="dialog" aria-modal="true">
    <img id="productDetailImg" src="" alt="">
    <div class="info">
      <h3 id="productDetailName"></h3>
      <p id="productDetailCategory" class="text-muted"></p>
      <p id="productDetailLocation" class="text-muted"></p>
      <p id="productDetailDesc" style="margin-top:12px;"></p>
      <p style="font-weight:700;margin-top:14px;font-size:1.15rem;">₱<span id="productDetailPrice"></span></p>
      <div style="margin-top:16px;">
        <a href="cart.php" class="btn btn-outline">Go to Cart</a>
        <a href="products.php" class="btn btn-primary">Close</a>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  function openProdModal(data) {
    document.getElementById('productDetailImg').src = data.img || 'images/logo.png';
    document.getElementById('productDetailName').textContent = data.name || '';
    document.getElementById('productDetailPrice').textContent = data.price || '';
    document.getElementById('productDetailDesc').textContent = data.desc || '';
    document.getElementById('productDetailCategory').textContent = data.category || '';
    document.getElementById('productDetailLocation').textContent = data.location || '';
    document.getElementById('productDetailModal').classList.add('show');
    document.getElementById('productDetailModal').setAttribute('aria-hidden', 'false');
  }
  function closeProdModal(){
    document.getElementById('productDetailModal').classList.remove('show');
    document.getElementById('productDetailModal').setAttribute('aria-hidden', 'true');
  }

  // Click on the View button
  document.querySelectorAll('.product-view-btn').forEach(function(btn){
    btn.addEventListener('click', function(e){
      e.stopPropagation();
      openProdModal({
        id: btn.dataset.id,
        name: btn.dataset.name,
        price: btn.dataset.price,
        desc: btn.dataset.desc,
        img: btn.dataset.img,
        category: btn.dataset.category,
        location: btn.dataset.location
      });
    });
  });
  // Click on image itself
  document.querySelectorAll('.product-click-img').forEach(function(img) {
    img.addEventListener('click', function(e) {
      openProdModal({
        id: img.dataset.id,
        name: img.dataset.name,
        price: img.dataset.price,
        desc: img.dataset.desc,
        img: img.dataset.img,
        category: img.dataset.category,
        location: img.dataset.location
      });
    });
  });

  // Optional: Also allow the card title to open the modal
  document.querySelectorAll('.product-click').forEach(function(el) {
    el.addEventListener('click', function() {
      openProdModal({
        id: el.dataset.id,
        name: el.dataset.name,
        price: el.dataset.price,
        desc: el.dataset.desc,
        img: el.dataset.img,
        category: el.dataset.category,
        location: el.dataset.location
      });
    });
  });

  document.getElementById('closeProductModal').addEventListener('click', closeProdModal);
  document.getElementById('productDetailModal').addEventListener('click', function(e){
    if (e.target === this) closeProdModal();
  });
});
</script>

</body>
</html>