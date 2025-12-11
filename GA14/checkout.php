<?php
session_start();
require 'database.php';
if (!isset($_SESSION['user_id'])) {
  header("Location: login_required.php");
  exit;
}
$cart = $_SESSION['cart'] ?? [];
$cartCount = array_sum($cart);

# ------- Only use selected items from the cart -------
$selectedIds = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout_ids']) && is_array($_POST['checkout_ids'])) {
    foreach ($_POST['checkout_ids'] as $pid) {
        $pid = (int)$pid;
        if (isset($cart[$pid])) $selectedIds[$pid] = $cart[$pid];
    }
} else {
    // If not POST, or none selected, fallback to all items
    $selectedIds = $cart;
}

if (!$selectedIds) {
  header("Location: cart.php");
  exit;
}

// Get product details for selected items
$ids = array_keys($selectedIds);
$products = [];
$total = 0.0;
if ($ids) {
  $in = implode(',', array_fill(0, count($ids), '?'));
  $stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id IN ($in)");
  $stmt->execute($ids);
  foreach ($stmt->fetchAll() as $row) {
    $row['qty'] = $selectedIds[$row['id']];
    $row['subtotal'] = $row['price'] * $row['qty'];
    $total += $row['subtotal'];
    $products[] = $row;
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Checkout — Baytisan</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body { background: #fdf7f4; }
    .checkout-container {
      max-width: 540px;
      margin: 48px auto;
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 6px 32px rgba(100,100,100,0.08);
      padding: 40px 30px 30px 30px;
      text-align: center;
    }
    table {
      width: 100%;
      margin-bottom: 18px;
    }
    th, td {
      padding: 7px 3px;
      text-align: left;
    }
    th {
      color: #685752;
      font-size: 1em;
    }
    .checkout-form label {
      display: block;
      margin-bottom: 5px;
      font-weight: 600;
      color: #685752;
      text-align:left;
    }
    .checkout-form input[type="text"],
    .checkout-form textarea {
      width: 100%;
      padding: 9px 10px;
      border-radius: 8px;
      border: 1px solid #ddd;
      margin-bottom: 15px;
      font-size: 1em;
    }
    .checkout-total {
      font-size: 1.2em;
      font-weight: bold;
      margin: 15px 0 24px 0;
      color: var(--accent, #997C70);
      text-align: right;
    }
    .checkout-actions {
      margin-top: 18px;
      display: flex;
      gap: 14px;
      justify-content: center;
      flex-wrap: wrap;
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
      <a href="order_history.php">Orders</a>
      <a href="admin_dashboard.php">Admin</a>
    </nav>
    <div class="nav-actions" id="navActions">
      <span class="welcome">Welcome, <?= htmlspecialchars($_SESSION['first_name']) ?></span>
      <a href="cart.php" class="btn btn-outline">Cart <span id="cartCount">(<?= $cartCount ?>)</span></a>
      <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
  </div>
</header>

<main>
<div class="checkout-container">
  <h2><i class="fa fa-credit-card"></i> Checkout</h2>
  <p>Review your order and enter your shipping address.</p>
  <table>
    <thead>
      <tr>
        <th>Product</th>
        <th>Qty</th>
        <th style="text-align:right">Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['name']) ?></td>
          <td><?= (int)$p['qty'] ?></td>
          <td style="text-align:right">₱<?= number_format($p['subtotal'],2) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class="checkout-total">
    Total: ₱<?= number_format($total,2) ?>
  </div>
  <form class="checkout-form" method="post" action="place_order.php">
    <?php foreach ($products as $p): ?>
      <input type="hidden" name="checkout_ids[]" value="<?= (int)$p['id'] ?>">
    <?php endforeach; ?>
    <label for="shipping_address">Shipping Address</label>
    <textarea id="shipping_address" name="shipping_address" required rows="3" placeholder="Enter your full address"></textarea>
    <div class="checkout-actions">
      <a href="cart.php" class="btn btn-outline"><i class="fa fa-arrow-left"></i> Back to Cart</a>
      <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Place Order</button>
    </div>
  </form>
</div>
</main>
<script src="script.js"></script>
</body>
</html>