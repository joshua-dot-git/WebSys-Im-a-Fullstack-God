<?php
ini_set('display_errors',1); error_reporting(E_ALL);
session_start();
require 'database.php';
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: admin_required.php');
  exit;
}

// Add/Edit/Delete logic
$addMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Add Product
  if (isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $origin_id = (int)$_POST['origin_location_id'];
    $desc = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $image = $_FILES['image'] ?? null;
    $imgFilename = '';

    // Handle image upload
    if ($image && $image['tmp_name']) {
      $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
      $allowed = ['jpg','jpeg','png','gif'];
      if (in_array($ext, $allowed)) {
        $imgDir = __DIR__."/images/products/";
        if (!is_dir($imgDir)) mkdir($imgDir, 0777, true);
        $imgFilename = uniqid('prod_').'.'.$ext;
        if (!move_uploaded_file($image['tmp_name'], $imgDir.$imgFilename)) {
          $addMsg = '<div class="alert alert-danger mb-2">Image upload failed!</div>';
        }
      } else {
        $addMsg = '<div class="alert alert-danger mb-2">Invalid image format!</div>';
      }
    }

    if (!$addMsg) {
      $stmt = $pdo->prepare("INSERT INTO products (name, category_id, origin_location_id, description, price, stock, image_filename) VALUES (?, ?, ?, ?, ?, ?, ?)");
      if ($stmt->execute([$name, $category_id, $origin_id, $desc, $price, $stock, $imgFilename])) {
        $addMsg = '<div class="alert alert-success mb-2">Product added!</div>';
      } else {
        $addMsg = '<div class="alert alert-danger mb-2">Error adding product.</div>';
      }
    }
  }
  // Edit Product
  if (isset($_POST['edit_product'])) {
    $id = (int)$_POST['product_id'];
    $name = trim($_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $origin_id = (int)$_POST['origin_location_id'];
    $desc = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $imgFilename = $_POST['current_image'] ?? '';
    // Handle new image upload
    $image = $_FILES['image'] ?? null;
    if ($image && $image['tmp_name']) {
      $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
      $allowed = ['jpg','jpeg','png','gif'];
      if (in_array($ext, $allowed)) {
        $imgDir = __DIR__."/images/products/";
        if (!is_dir($imgDir)) mkdir($imgDir, 0777, true);
        $imgFilename = uniqid('prod_').'.'.$ext;
        if (!move_uploaded_file($image['tmp_name'], $imgDir.$imgFilename)) {
          $addMsg = '<div class="alert alert-danger mb-2">Image upload failed!</div>';
        }
      }
    }
    $stmt = $pdo->prepare("UPDATE products SET name=?, category_id=?, origin_location_id=?, description=?, price=?, stock=?, image_filename=? WHERE id=?");
    if ($stmt->execute([$name, $category_id, $origin_id, $desc, $price, $stock, $imgFilename, $id])) {
      $addMsg = '<div class="alert alert-success mb-2">Product updated!</div>';
    } else {
      $addMsg = '<div class="alert alert-danger mb-2">Error updating product.</div>';
    }
  }
  // Delete Product FIXED
  if (isset($_POST['delete_product'])) {
    $id = (int)$_POST['product_id'];
    try {
      $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
      $stmt->execute([$id]);
      $addMsg = '<div class="alert alert-success mb-2">Product deleted!</div>';
    } catch (PDOException $e) {
      // Check for foreign key constraint error
      if ($e->getCode() == '23000' && strpos($e->getMessage(), 'Cannot delete or update a parent row') !== false) {
        $addMsg = '<div class="alert alert-danger mb-2">Cannot delete this product because it has related orders.<br>If you want to remove it, please delete related orders first.</div>';
      } else {
        $addMsg = '<div class="alert alert-danger mb-2">Error deleting product: '.$e->getMessage().'</div>';
      }
    }
  }
}
// Handle admin "Mark as Delivered" button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deliver_order_id_admin'])) {
    $oid = (int)$_POST['deliver_order_id_admin'];
    // Only update if not already delivered/cancelled
    $stmt = $pdo->prepare("SELECT status FROM orders WHERE id=?");
    $stmt->execute([$oid]);
    $row = $stmt->fetch();
    if ($row && !in_array($row['status'], ['delivered', 'cancelled'])) {
        $pdo->prepare("UPDATE orders SET status='delivered' WHERE id=?")->execute([$oid]);
        $pdo->prepare("INSERT INTO order_tracking (order_id,status,note) VALUES (?,?,?)")
            ->execute([$oid, 'delivered', 'Order marked delivered by admin']);
    }
    // Prevent resubmit
    header("Location: admin_dashboard.php");
    exit;
}

$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalSales = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status IN ('processing','shipped','delivered')")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$products = $pdo->query("SELECT p.*, c.name as category, l.name as location FROM products p JOIN categories c ON p.category_id=c.id LEFT JOIN locations l ON p.origin_location_id=l.id ORDER BY p.id DESC")->fetchAll();
$categoriesList = $pdo->query("SELECT id, name FROM categories")->fetchAll();
$locationsList = $pdo->query("SELECT id, name FROM locations")->fetchAll();

function product_image_path($filename) {
    $base = pathinfo($filename, PATHINFO_FILENAME);
    $png = "images/products/{$base}.png";
    $jpg = "images/products/{$base}.jpg";
    if ($filename && file_exists(__DIR__ . '/' . $png)) return $png;
    if ($filename && file_exists(__DIR__ . '/' . $jpg)) return $jpg;
    if ($filename && file_exists(__DIR__ . '/' . $filename)) return $filename;
    return "images/logo.png";
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Dashboard — Baytisan</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* ... all your CSS unchanged ... */
    :root {
      --bg: #FDF7F4;
      --primary: #8EB486;
      --accent: #997C70;
      --dark: #685752;
      --muted: #7a7a7a;
      --danger: #e05d5d;
      --table-alt: #F7FAF7;
    }
    body { background: var(--bg); }
    .dashboard-header { display: flex; align-items: center; gap: 16px; margin-bottom: 16px; margin-top: 40px; }
    .dashboard-header i { color: var(--primary); font-size: 2.2em; }
    .stat-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 18px rgba(0,0,0,0.05); padding: 22px 22px 16px 22px; text-align: center; margin-bottom: 18px; min-height: 120px;}
    .stat-card .icon { font-size: 2.2em; color: var(--primary); margin-bottom:8px;}
    .stat-label { color:var(--muted); font-size:1em; }
    .stat-value { font-size:1.6em; font-weight:700; }
    .tab-pane { padding: 20px 0; }
    .table thead th { background: var(--table-alt); color: var(--dark); font-weight: 700; }
    .table-striped > tbody > tr:nth-of-type(odd) { background: #f9f6f3; }
    .btn-sm { font-size: .98em; }
    .nav-tabs .nav-link.active { background: var(--primary)!important; color: #fff !important; border: none; }
    .nav-tabs .nav-link { color: var(--primary); font-weight:600; border: none; }
    .nav-tabs .nav-link:hover { background: #e6f1e6; }
    .admin-logout { margin-left: 16px;}
    .product-img-thumb { width: 48px; height: 48px; object-fit: cover; border-radius: 9px; border:1px solid #eee; }
    .bg-light2 { background: #faf9f7 !important; }
    .modal.show { display:block; }
    .modal-backdrop.show { opacity:.5; }
    .modal.fade .modal-dialog { transition: transform .3s ease-out; }
    .modal { z-index: 1050; }
    .modal-backdrop { z-index: 1040; }
    /* Custom Buttons */
    .btn-main {
      background: var(--primary);
      color: #fff;
      border: none;
      border-radius: 13px;
      padding: 9px 24px;
      font-size: 1.05em;
      font-weight: 600;
      letter-spacing: 0.02em;
      box-shadow: 0 2px 13px rgba(120,160,120,0.07);
      transition: background .18s, color .18s, transform .18s;
    }
    .btn-main:hover, .btn-main:focus { background: #6a9c5b; color: #fff; transform: translateY(-2px); }
    .btn-danger {
      background: var(--danger)!important;
      color: #fff!important;
      border: none!important;
      border-radius: 13px!important;
      font-weight: 600;
    }
    .btn-danger:hover, .btn-danger:focus { background: #b43636!important; color:#fff!important; }
    .btn-outline-main {
      background: #fff;
      color: var(--primary);
      border: 2px solid var(--primary);
      border-radius: 13px;
      font-weight: 600;
      transition: background .18s, color .18s;
    }
    .btn-outline-main:hover, .btn-outline-main:focus {
      background: var(--primary);
      color: #fff;
    }
    .badge.bg-success {
      background: var(--primary)!important;
      color: #fff;
      font-weight: 600;
      font-size: .98em;
    }
    .badge.bg-danger {
      background: var(--danger)!important;
      color: #fff;
      font-weight: 600;
      font-size: .98em;
    }
    .actions-cell { display: flex; gap: 8px; }
    .btn-action {
      display: flex; align-items: center; gap: 6px; 
      padding: 8px 18px; font-size: 1.01em; font-weight: 600;
      border-radius: 11px; border:none;
      transition: background .16s;
    }
    .btn-action-edit {
      background: var(--primary); color:#fff;
    }
    .btn-action-edit:hover { background: #5b8f4b; }
    .btn-action-delete {
      background: var(--danger); color:#fff;
    }
    .btn-action-delete:hover { background: #b43636; }
    /* Table hover effect */
    .table-hover tbody tr:hover {
      background: #eaf3e6;
      transition: background .18s;
    }
    /* Disabled button look */
    .btn[disabled], .btn.disabled, .btn:disabled {
      background: #e4e4e4 !important;
      color: #b1b1b1 !important;
      border-color: #e4e4e4 !important;
      cursor: not-allowed !important;
      box-shadow: none !important;
    }
    /* Modal header color */
    .modal-header {
      background: var(--primary);
      color: #fff;
      border-bottom: none;
    }
    .modal-title { color: #fff; font-weight: 600; }
    /* Highlight table row on action */
    .table .highlight-row { background: #e7f3e7 !important; transition: background .25s; }
    /* Responsive adjust */
    @media (max-width: 900px) {
      .modal-dialog { max-width: 97vw; margin: 1.5rem auto; }
      .modal-content, .modal-body, .modal-footer { padding: 1rem; }
    }
    @media (max-width: 600px) {
      .modal-body .row > [class^="col-"] { flex: 0 0 100%; max-width: 100%; }
      .table th, .table td { font-size: 0.97em; }
      .btn-action { padding: 7px 10px; font-size: .97em; }
    }
    h4, .section-h4 {
      color: var(--dark);
      font-size: 1.45em;
      margin-bottom: 20px;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 12px;
    }
  </style>
</head>
<body>
    <header class="site-header">
    <div class="container nav">
      <a href="index.php" class="brand">
        <img src="images/logo.png" alt="Baytisan logo" class="logo">
        <span class="brand-text">Baytisan Admin</span>
      </a>
      <nav class="main-nav">
        <a href="index.php">Home</a>
        <a href="admin_dashboard.php" class="active">Admin Dashboard</a>
      </nav>
      <div class="nav-actions">
        <span class="welcome">Welcome, <?= htmlspecialchars($_SESSION['first_name']) ?> <i class="fa fa-user-shield"></i></span>
        <a href="admin_profile.php" class="btn btn-primary" style="margin-left:8px;">
          <i class="fa fa-user-shield"></i> Admin Profile
        </a>
        <a href="logout.php" class="btn btn-danger admin-logout">Logout</a>
      </div>
    </div>
  </header>
  <main class="container">
    <div class="dashboard-header">
      <i class="fa fa-chart-line"></i>
      <h2 style="margin:0;">Admin Dashboard</h2>
    </div>
    <div class="row g-3 mb-3">
      <div class="col-md-3"><div class="stat-card"><div class="icon"><i class="fa fa-shopping-basket"></i></div><div class="stat-value"><?= $totalProducts ?></div><div class="stat-label">Items for Sale</div></div></div>
      <div class="col-md-3"><div class="stat-card"><div class="icon"><i class="fa fa-clipboard-list"></i></div><div class="stat-value"><?= $totalOrders ?></div><div class="stat-label">Total Orders</div></div></div>
      <div class="col-md-3"><div class="stat-card"><div class="icon"><i class="fa fa-coins"></i></div><div class="stat-value">₱<?= number_format($totalSales,2) ?></div><div class="stat-label">Total Sales</div></div></div>
      <div class="col-md-3"><div class="stat-card"><div class="icon"><i class="fa fa-users"></i></div><div class="stat-value"><?= $totalUsers ?></div><div class="stat-label">Registered Users</div></div></div>
    </div>
    <ul class="nav nav-tabs" id="adminTab" role="tablist" style="margin-bottom:12px;">
      <li class="nav-item" role="presentation"><button class="nav-link active" id="items-tab" data-bs-toggle="tab" data-bs-target="#items" type="button" role="tab"><i class="fa fa-boxes-stacked"></i> Items to Sell</button></li>
      <li class="nav-item" role="presentation"><button class="nav-link" id="ordersum-tab" data-bs-toggle="tab" data-bs-target="#ordersum" type="button" role="tab"><i class="fa fa-list"></i> Order Summary</button></li>
      <li class="nav-item" role="presentation"><button class="nav-link" id="orderhist-tab" data-bs-toggle="tab" data-bs-target="#orderhist" type="button" role="tab"><i class="fa fa-history"></i> Order History</button></li>
      <li class="nav-item" role="presentation"><button class="nav-link" id="invoice-tab" data-bs-toggle="tab" data-bs-target="#invoice" type="button" role="tab"><i class="fa fa-file-invoice"></i> Invoices/Receipts</button></li>
      <li class="nav-item" role="presentation"><button class="nav-link" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales" type="button" role="tab"><i class="fa fa-chart-bar"></i> Sales & Inventory Report</button></li>
    </ul>
    <div class="tab-content bg-light2 rounded-bottom shadow-sm" style="padding:24px;">
  <!-- Items to Sell Tab (functional with modals) -->
  <div class="tab-pane fade show active" id="items" role="tabpanel">
    <h4 class="section-h4"><i class="fa fa-box"></i> Items to Sell</h4>
    <?= $addMsg ?>
    <!-- Add Product Button -->
    <button class="btn btn-main" id="addProductBtn" style="margin-bottom:16px;">
      <i class="fa fa-plus"></i> Add Product
    </button>
    <!-- Add Product Modal -->
    <div class="modal fade" id="addItemModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="post" enctype="multipart/form-data">
            <div class="modal-header">
              <h5 class="modal-title"><i class="fa fa-plus"></i> Add Product</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="mb-2">
                <label>Name</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="mb-2">
                <label>Category</label>
                <select name="category_id" class="form-control" required>
                  <option value="">Select Category</option>
                  <?php foreach($categoriesList as $cat): ?>
                  <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-2">
                <label>Origin Location</label>
                <select name="origin_location_id" class="form-control" required>
                  <option value="">Select Location</option>
                  <?php foreach($locationsList as $loc): ?>
                  <option value="<?= $loc['id'] ?>"><?= htmlspecialchars($loc['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-2">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="2"></textarea>
              </div>
              <div class="mb-2">
                <label>Price (₱)</label>
                <input type="number" name="price" class="form-control" min="0" step="0.01" required>
              </div>
              <div class="mb-2">
                <label>Stock</label>
                <input type="number" name="stock" class="form-control" min="0" required>
              </div>
              <div class="mb-2">
                <label>Image</label>
                <input type="file" name="image" accept="image/*" class="form-control">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-main" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" name="add_product" class="btn btn-main"><i class="fa fa-plus"></i> Add</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- Edit Product Modal -->
    <div class="modal fade" id="editItemModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="post" enctype="multipart/form-data">
            <div class="modal-header">
              <h5 class="modal-title"><i class="fa fa-edit"></i> Edit Product</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="product_id" id="edit_product_id">
              <input type="hidden" name="current_image" id="edit_current_image">
              <div class="mb-2">
                <label>Name</label>
                <input type="text" name="name" id="edit_name" class="form-control" required>
              </div>
              <div class="mb-2">
                <label>Category</label>
                <select name="category_id" id="edit_category_id" class="form-control" required>
                  <?php foreach($categoriesList as $cat): ?>
                  <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-2">
                <label>Origin Location</label>
                <select name="origin_location_id" id="edit_origin_location_id" class="form-control" required>
                  <?php foreach($locationsList as $loc): ?>
                  <option value="<?= $loc['id'] ?>"><?= htmlspecialchars($loc['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-2">
                <label>Description</label>
                <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
              </div>
              <div class="mb-2">
                <label>Price (₱)</label>
                <input type="number" name="price" id="edit_price" class="form-control" min="0" step="0.01" required>
              </div>
              <div class="mb-2">
                <label>Stock</label>
                <input type="number" name="stock" id="edit_stock" class="form-control" min="0" required>
              </div>
              <div class="mb-2">
                <label>Current Image</label>
                <img id="edit_img_prev" src="" alt="Product Image" style="width:48px;height:48px;border-radius:9px;border:1px solid #eee;display:block;margin-bottom:7px;">
                <input type="file" name="image" accept="image/*" class="form-control">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-main" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" name="edit_product" class="btn btn-main"><i class="fa fa-save"></i> Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- Delete Product Modal -->
    <div class="modal fade" id="deleteItemModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="post">
            <div class="modal-header">
              <h5 class="modal-title"><i class="fa fa-trash"></i> Delete Product</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="product_id" id="delete_product_id">
              <p>Are you sure you want to delete this product?</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-main" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" name="delete_product" class="btn btn-danger"><i class="fa fa-trash"></i> Delete</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="table-responsive mt-3">
      <table class="table table-bordered table-hover table-striped align-middle">
        <thead>
          <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Category</th>
            <th>Stock</th>
            <th>Price</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($products as $p): ?>
          <tr>
            <td><img src="<?= htmlspecialchars(product_image_path($p['image_filename'])) ?>" class="product-img-thumb" alt=""></td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= htmlspecialchars($p['category']) ?></td>
            <td><?= $p['stock'] ?></td>
            <td>₱<?= number_format($p['price'],2) ?></td>
            <td><?= $p['stock'] > 0 ? "<span class='badge bg-success'>In Stock</span>" : "<span class='badge bg-danger'>Out of Stock</span>" ?></td>
            <td class="actions-cell">
              <button class="btn btn-action btn-action-edit edit-btn"
                data-id="<?= $p['id'] ?>"
                data-name="<?= htmlspecialchars($p['name'],ENT_QUOTES) ?>"
                data-category_id="<?= $p['category_id'] ?>"
                data-origin_location_id="<?= $p['origin_location_id'] ?>"
                data-description="<?= htmlspecialchars($p['description'] ?? '',ENT_QUOTES) ?>"
                data-price="<?= $p['price'] ?>"
                data-stock="<?= $p['stock'] ?>"
                data-image="<?= htmlspecialchars($p['image_filename']) ?>"
                data-img-preview="<?= htmlspecialchars(product_image_path($p['image_filename'])) ?>"
              ><i class="fa fa-edit"></i> Edit</button>
              <button class="btn btn-action btn-action-delete delete-btn" data-id="<?= $p['id'] ?>"><i class="fa fa-trash"></i> Delete</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<!-- Other tabs remain unchanged below -->
<!-- ... (keep all other tab contents as in your original file) ... -->
  <!-- Order Summary Tab (unchanged, working) -->
  <div class="tab-pane fade" id="ordersum" role="tabpanel">
    <div class="order-section-card" style="background:#fff;border-radius:16px;box-shadow:0 6px 28px rgba(0,0,0,0.06);padding:28px 26px 20px 26px;margin-bottom:22px;max-width:1100px;margin-left:auto;margin-right:auto;">
      <h4 class="section-h4" style="margin-bottom:24px;display:flex;align-items:center;gap:12px;">
        <i class="fa fa-list"></i> <span>Order Summary</span>
      </h4>
      <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped align-middle" style="border-radius:12px;overflow:hidden;">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>User</th>
              <th>Total</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $orders = $pdo->query("SELECT o.*, u.first_name, u.last_name FROM orders o JOIN users u ON o.user_id=u.id ORDER BY o.placed_at DESC")->fetchAll();
            foreach($orders as $order): ?>
            <tr>
              <td><?= $order['id'] ?></td>
              <td><?= htmlspecialchars(trim($order['first_name'].' '.$order['last_name'])) ?></td>
              <td>₱<?= number_format($order['total_amount'],2) ?></td>
              <td>
  <?php
  $status = ucfirst($order['status']);
  $badgeColor = 'bg-success';
  if ($order['status'] === 'pending') $badgeColor = 'bg-warning';
  if ($order['status'] === 'cancelled') $badgeColor = 'bg-danger';
  if ($order['status'] === 'processing') $badgeColor = 'bg-info';
  if ($order['status'] === 'shipped') $badgeColor = 'bg-primary';
  ?>
  <span class="badge <?= $badgeColor ?>" style="font-size:1em;min-width:84px;">
    <?= $status ?>
  </span>
  <?php if (!in_array($order['status'], ['delivered', 'cancelled'])): ?>
    <form method="post" style="display:inline">
      <input type="hidden" name="deliver_order_id_admin" value="<?= $order['id'] ?>">
      <button type="submit" class="btn btn-outline-main btn-sm" style="margin-left:7px;">
        <i class="fa fa-check"></i> Mark Delivered
      </button>
    </form>
  <?php elseif ($order['status'] === 'delivered'): ?>
    <span style="color:#4caf50;font-weight:600;margin-left:8px;"><i class="fa fa-check-circle"></i> Delivered</span>
  <?php endif; ?>
</td>
              <td><?= $order['placed_at'] ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <!-- Order History Tab -->
  <div class="tab-pane fade" id="orderhist" role="tabpanel">
    <!-- ... unchanged ... -->
    <div class="order-section-card" style="background:#fff;border-radius:16px;box-shadow:0 6px 28px rgba(0,0,0,0.06);padding:28px 26px 20px 26px;margin-bottom:22px;max-width:1100px;margin-left:auto;margin-right:auto;">
      <h4 class="section-h4" style="margin-bottom:24px;display:flex;align-items:center;gap:12px;">
        <i class="fa fa-history"></i> Order History
      </h4>
      <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped align-middle" style="border-radius:12px;overflow:hidden;">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>User</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $orders = $pdo->query("SELECT o.*, u.first_name, u.last_name FROM orders o JOIN users u ON o.user_id=u.id ORDER BY o.placed_at DESC")->fetchAll();
            foreach($orders as $order): ?>
            <tr>
              <td><?= $order['id'] ?></td>
              <td><?= htmlspecialchars(trim($order['first_name'].' '.$order['last_name'])) ?></td>
              <td>
                <?php
                  $status = ucfirst($order['status']);
                  $badgeColor = 'bg-success';
                  if ($order['status'] === 'pending') $badgeColor = 'bg-warning';
                  if ($order['status'] === 'cancelled') $badgeColor = 'bg-danger';
                  if ($order['status'] === 'processing') $badgeColor = 'bg-info';
                  if ($order['status'] === 'shipped') $badgeColor = 'bg-primary';
                ?>
                <span class="badge <?= $badgeColor ?>" style="font-size:1em;min-width:84px;">
                  <?= $status ?>
                </span>
              </td>
              <td><?= $order['placed_at'] ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <!-- Invoices/Receipts Tab -->
  <div class="tab-pane fade" id="invoice" role="tabpanel">
    <!-- ... unchanged ... -->
    <div class="order-section-card" style="background:#fff;border-radius:16px;box-shadow:0 6px 28px rgba(0,0,0,0.06);padding:28px 26px 20px 26px;margin-bottom:22px;max-width:1100px;margin-left:auto;margin-right:auto;">
      <h4 class="section-h4" style="margin-bottom:24px;display:flex;align-items:center;gap:12px;">
        <i class="fa fa-file-invoice"></i> Invoices / Receipts
      </h4>
      <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped align-middle" style="border-radius:12px;overflow:hidden;">
          <thead>
            <tr>
              <th>Invoice #</th>
              <th>Order ID</th>
              <th>User</th>
              <th>Total</th>
              <th>Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $orders = $pdo->query("SELECT o.*, u.first_name, u.last_name FROM orders o JOIN users u ON o.user_id=u.id ORDER BY o.placed_at DESC")->fetchAll();
            foreach($orders as $order): ?>
            <tr>
              <td>INV-<?= $order['id'] ?></td>
              <td><?= $order['id'] ?></td>
              <td><?= htmlspecialchars(trim($order['first_name'].' '.$order['last_name'])) ?></td>
              <td>₱<?= number_format($order['total_amount'],2) ?></td>
              <td><?= $order['placed_at'] ?></td>
              <td>
                <a href="order_summary.php?order_id=<?= $order['id'] ?>" target="_blank" class="btn btn-sm btn-outline-success">
                  <i class="fa fa-eye"></i> View
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <!-- Sales & Inventory Report Tab -->
  <div class="tab-pane fade" id="sales" role="tabpanel">
    <div class="order-section-card" style="background:#fff;border-radius:16px;box-shadow:0 6px 28px rgba(0,0,0,0.06);padding:28px 26px 20px 26px;margin-bottom:22px;max-width:1100px;margin-left:auto;margin-right:auto;">
      <h4 class="section-h4" style="margin-bottom:24px;display:flex;align-items:center;gap:12px;">
        <i class="fa fa-chart-bar"></i> Sales & Inventory Report
      </h4>
      <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped align-middle" style="border-radius:12px;overflow:hidden;">
          <thead>
            <tr>
              <th>Product</th>
              <th>Category</th>
              <th>Sold</th>
              <th>Stock</th>
              <th>Total Sales</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $reportSQL = "
              SELECT p.name, c.name AS category, p.stock,
                COALESCE(SUM(oi.quantity),0) AS sold,
                COALESCE(SUM(oi.subtotal),0) AS total_sales
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.id
              LEFT JOIN order_items oi ON oi.product_id = p.id
              GROUP BY p.id
              ORDER BY p.id ASC";
            $report = $pdo->query($reportSQL)->fetchAll();
            foreach($report as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= htmlspecialchars($row['category']) ?></td>
              <td><?= $row['sold'] ?></td>
              <td><?= $row['stock'] ?></td>
              <td>₱<?= number_format($row['total_sales'],2) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
  </main>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  // Add Product button handler
  document.getElementById('addProductBtn').addEventListener('click', function() {
    var addModal = new bootstrap.Modal(document.getElementById('addItemModal'));
    addModal.show();
  });
  // Edit button handler
  document.querySelectorAll('.edit-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.getElementById('edit_product_id').value = btn.dataset.id;
      document.getElementById('edit_name').value = btn.dataset.name;
      document.getElementById('edit_category_id').value = btn.dataset.category_id;
      document.getElementById('edit_origin_location_id').value = btn.dataset.origin_location_id;
      document.getElementById('edit_stock').value = btn.dataset.stock;
      document.getElementById('edit_price').value = btn.dataset.price;
      document.getElementById('edit_description').value = btn.dataset.description;
      document.getElementById('edit_current_image').value = btn.dataset.image;
      document.getElementById('edit_img_prev').src = btn.dataset.imgPreview;
      var editModal = new bootstrap.Modal(document.getElementById('editItemModal'));
      editModal.show();
    });
  });
  // Delete button handler
  document.querySelectorAll('.delete-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.getElementById('delete_product_id').value = btn.dataset.id;
      var deleteModal = new bootstrap.Modal(document.getElementById('deleteItemModal'));
      deleteModal.show();
    });
  });
  </script>
</body>
</html>