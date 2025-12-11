<?php
require 'database.php';
session_start();
$user_id = $_SESSION['user_id'] ?? null;
$order_id = (int)($_GET['order_id'] ?? 0);
if (!$user_id || !$order_id) {
  header("Location: login_required.php");
  exit;
}

// Fetch this order
if ($_SESSION['role'] === 'admin') {
  // Admin can view any order
  $stmt = $pdo->prepare("SELECT o.*, u.first_name FROM orders o JOIN users u ON o.user_id=u.id WHERE o.id=?");
  $stmt->execute([$order_id]);
} else {
  // Customers can view only their own orders
  $stmt = $pdo->prepare("SELECT o.*, u.first_name FROM orders o JOIN users u ON o.user_id=u.id WHERE o.id=? AND o.user_id=?");
  $stmt->execute([$order_id,$user_id]);
}
$order = $stmt->fetch();
if (!$order) die('Order not found');

// Find user's order count and this order's position for user
$userOrders = $pdo->prepare("SELECT id FROM orders WHERE user_id=? ORDER BY placed_at ASC, id ASC");
$userOrders->execute([$user_id]);
$orderIds = array_column($userOrders->fetchAll(), 'id');
$userOrderNum = array_search($order_id, $orderIds) !== false ? (array_search($order_id, $orderIds) + 1) : false;

// Get product image, name, etc
$items = $pdo->prepare("SELECT oi.*, p.name, p.image_filename FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE oi.order_id=?");
$items->execute([$order_id]);
$items = $items->fetchAll();

$tracking = $pdo->prepare("SELECT * FROM order_tracking WHERE order_id=? ORDER BY created_at ASC");
$tracking->execute([$order_id]);
$tracking = $tracking->fetchAll();

// Helper for product image path
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
<html>
<head>
  <meta charset="utf-8">
  <title>Order Summary</title>
  <link rel="stylesheet" href="style.css">
  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- FontAwesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body { background: #fdf7f4; }
    .order-summary-card {
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.10);
      padding: 36px 36px 26px 36px;
      max-width: 620px;
      margin: 48px auto 48px auto;
    }
    .order-summary-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 7px;
    }
    .order-summary-header .fa-clipboard-list {
      color: var(--primary, #8EB486);
      font-size: 2rem;
      margin-right: 4px;
    }
    .order-status {
      display: inline-block;
      padding: 4px 13px 4px 10px;
      border-radius: 8px;
      font-size: .98em;
      color: #fff;
      background: var(--primary, #8EB486);
      margin-left: 10px;
      vertical-align: middle;
    }
    .summary-table {
      width: 100%;
      margin: 22px 0 10px 0;
      border-radius: 12px;
      overflow: hidden;
      background: #f7faf7;
    }
    .summary-table th, .summary-table td { vertical-align: middle !important; }
    .summary-table th {
      background: #f7faf7;
      font-weight: 600;
    }
    .summary-table td {
      background: #fff;
      font-size: 1.05em;
    }
    .summary-table img {
      width: 52px; height: 52px; object-fit: cover; border-radius: 10px; border:1px solid #eee;
      margin-right: 11px;
      background: #fafafa;
    }
    .order-summary-footer {
      margin-top: 18px;
      text-align: left;
      display: flex;
      gap: 12px;
    }
    .btn-primary, .btn-success, .btn-outline-success {
      background: var(--primary, #8EB486) !important;
      border: none !important;
      color: #fff !important;
      border-radius: 10px !important;
      font-weight: bold;
    }
    .btn-primary:hover, .btn-success:hover, .btn-outline-success:hover {
      background: #6a9c5b !important;
      color: #fff !important;
    }
    .tracking-list {
      margin: 8px 0 0 0; padding: 0; list-style: none;
    }
    .tracking-list li {
      margin-bottom: 7px;
      font-size: 1em;
      color: var(--muted, #7a7a7a);
      display: flex; align-items: center; gap: 7px;
    }
    .tracking-list .fa-check-circle { color: var(--primary, #8EB486); font-size: 1.2rem; }
    .tracking-label { font-weight: 600; margin-bottom: 5px; }
  </style>
</head>
<body>
<header class="site-header">
  <div class="container nav">
    <a href="index.php" class="brand"><img src="images/logo.png" class="logo">Baytisan</a>
    <nav class="main-nav">
      <a href="index.php">Home</a>
      <a href="products.php">Shop</a>
      <a href="order_history.php" class="active">Orders</a>
      <a href="admin_dashboard.php">Admin</a>
    </nav>
  </div>
</header>
<div class="order-summary-card">
  <div class="order-summary-header">
    <i class="fa-solid fa-clipboard-list"></i>
    <h3 style="margin:0;">
      <!-- Show both for clarity, or just user's order number if you prefer -->
      Order #<?= $userOrderNum ? $userOrderNum : $order['id'] ?> 
      <span style="font-size:0.9em;color:#bbb;">(System Order #<?= $order['id'] ?>)</span>
    </h3>
    <span class="order-status"><?= htmlspecialchars($order['status']) ?></span>
  </div>
  <div style="margin-bottom:12px;">
    Placed at <?= $order['placed_at'] ?> —
    <i class="fa fa-user"></i> <?= htmlspecialchars($order['first_name']) ?>
  </div>
  <table class="table summary-table align-middle">
    <thead>
      <tr>
        <th style="width:70px">Item</th>
        <th>Unit</th>
        <th>Qty</th>
        <th>Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($items as $it):
        $img = product_image_path($it['image_filename'] ?? '');
      ?>
      <tr>
        <td>
          <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($it['name']) ?>">
          <?= htmlspecialchars($it['name']) ?>
        </td>
        <td>₱<?= number_format($it['unit_price'],2) ?></td>
        <td><?= $it['quantity'] ?></td>
        <td>₱<?= number_format($it['subtotal'],2) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class="order-summary-footer">
    <strong style="margin-right:auto">Total: ₱<?= number_format($order['total_amount'],2) ?></strong>
    <a href="order_history.php" class="btn btn-success"><i class="fa fa-arrow-left"></i> Back to Orders</a>
    <a href="products.php" class="btn btn-success"><i class="fa fa-shopping-basket"></i> Shop More</a>
  </div>
  <div class="tracking-label mt-3"><i class="fa fa-truck"></i> Tracking</div>
  <ul class="tracking-list">
    <?php foreach($tracking as $t): ?>
      <li><i class="fa fa-check-circle"></i> <?= $t['created_at'] ?> — <?= htmlspecialchars($t['status']) ?> - <?= htmlspecialchars($t['note']) ?></li>
    <?php endforeach; ?>
  </ul>
</div>
<!-- Bootstrap JS (optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>