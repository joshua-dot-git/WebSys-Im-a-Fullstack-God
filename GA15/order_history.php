<?php
require 'database.php';
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login_required.php");
  exit;
}
$user_id = $_SESSION['user_id'];

// --- Handle "Mark as Delivered" button ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deliver_order_id'])) {
    $oid = (int)$_POST['deliver_order_id'];
    // Only update if it's user's order and not already delivered/cancelled
    $stmt = $pdo->prepare("SELECT status FROM orders WHERE id=? AND user_id=?");
    $stmt->execute([$oid, $user_id]);
    $row = $stmt->fetch();
    if ($row && !in_array($row['status'], ['delivered', 'cancelled'])) {
        $pdo->prepare("UPDATE orders SET status='delivered' WHERE id=? AND user_id=?")->execute([$oid, $user_id]);
        $pdo->prepare("INSERT INTO order_tracking (order_id,status,note) VALUES (?,?,?)")
            ->execute([$oid, 'delivered', 'Order marked delivered by customer']);
        header("Location: order_history.php");
        exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY placed_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Your Orders</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body { background: #fdf7f4; }
    .orders-header {
      text-align: center;
      margin: 36px 0 22px 0;
      font-weight: 700;
      letter-spacing: 1px;
      color: var(--dark, #685752);
    }
    .order-list-container {
      max-width: 780px;
      margin: 0 auto 60px auto;
    }
    .order-card {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 6px 28px rgba(0,0,0,0.06);
      padding: 24px 26px 16px 26px;
      margin-bottom: 22px;
      display: flex;
      align-items: center;
      gap: 32px;
      transition: box-shadow .21s;
    }
    .order-card:hover {
      box-shadow: 0 16px 32px rgba(70,130,90,0.11);
      border: 1px solid #aad4ad;
    }
    .order-link {
      font-size: 1.14em;
      font-weight: bold;
      color: var(--primary, #8EB486);
      text-decoration: none;
      transition: color .2s;
    }
    .order-link:hover { color: #6a9c5b; text-decoration: underline; }
    .order-badge {
      font-size: .92em;
      padding: 4px 14px 4px 11px;
      border-radius: 9px;
      color: #fff;
      background: var(--primary, #8EB486);
      margin-left: 9px;
      vertical-align: middle;
      display: inline-block;
    }
    .order-meta {
      font-size: .98em;
      color: var(--muted, #7a7a7a);
      margin-top: 2px;
      margin-bottom: 0;
    }
    .order-total {
      font-size: 1.14em;
      font-weight: 600;
      color: var(--accent, #997C70);
      margin-top: 4px;
    }
    .order-icon {
      font-size: 2.5rem;
      color: var(--primary, #8EB486);
      margin-right: 10px;
      margin-left: 2px;
      min-width: 38px;
    }
    .btn-delivered {
      background: #8EB486;
      color: #fff;
      border: none;
      padding: 8px 18px;
      border-radius: 10px;
      font-weight: bold;
      margin-top: 9px;
      transition: background .18s;
    }
    .btn-delivered:hover { background: #6a9c5b; }
    @media (max-width: 600px) {
      .order-card { flex-direction: column; gap: 10px; padding: 14px 8px;}
      .order-icon { margin-bottom: 6px;}
    }
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
<div class="order-list-container">
  <h2 class="orders-header"><i class="fa-solid fa-clipboard-list"></i> Your Orders</h2>
  <?php if (!$orders): ?>
    <div class="notice-card" style="margin-top:40px; text-align:center;">
      <div class="pot-shape" style="margin:0 auto 20px auto;"></div>
      <h3>No orders yet</h3>
      <p>You haven't placed any orders yet. Go shop and add items to your cart!</p>
      <a href="products.php" class="btn btn-primary"><i class="fa fa-store"></i> Go to Shop</a>
    </div>
  <?php else: ?>
    <?php foreach($orders as $o): ?>
      <div class="order-card">
        <div class="order-icon">
          <i class="fa-solid fa-box"></i>
        </div>
        <div style="flex:1">
          <a class="order-link" href='order_summary.php?order_id=<?= $o['id'] ?>'>
            Order #<?= $o['id'] ?>
          </a>
          <span class="order-badge"><?= htmlspecialchars($o['status']) ?></span>
          <div class="order-meta">
            <i class="fa fa-calendar"></i> <?= $o['placed_at'] ?>
          </div>
          <div class="order-total">
            <i class="fa fa-receipt"></i> ₱<?= number_format($o['total_amount'],2) ?>
          </div>
          <?php if (!in_array($o['status'], ['delivered','cancelled'])): ?>
            <form method="post" style="margin-top:10px;display:inline">
              <input type="hidden" name="deliver_order_id" value="<?= $o['id'] ?>">
              <button type="submit" class="btn-delivered">
                <i class="fa fa-check"></i> Order Delivered
              </button>
            </form>
          <?php elseif ($o['status'] === 'delivered'): ?>
            <div style="margin-top:10px;color:#4caf50;font-weight:bold">
              <i class="fa fa-check-circle"></i> Delivered
            </div>
          <?php endif; ?>
        </div>
        <div>
          <a href="order_summary.php?order_id=<?= $o['id'] ?>" class="btn btn-outline-success btn-sm">
            <i class="fa fa-eye"></i> View
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>