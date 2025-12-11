<?php
session_start();
require 'database.php';
$loggedIn = isset($_SESSION['user_id']);
$cart = $_SESSION['cart'] ?? [];
$cartCount = array_sum($cart);

// --- Handle Add to Cart POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $pid = (int)$_POST['product_id'];
    $qty = max(1, (int)($_POST['qty'] ?? 1));
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    if (isset($_SESSION['cart'][$pid])) {
        $_SESSION['cart'][$pid] += $qty;
    } else {
        $_SESSION['cart'][$pid] = $qty;
    }
    // Save to DB cart if logged in
    if ($loggedIn) save_cart_to_db($_SESSION['user_id'], $_SESSION['cart']);
    header("Location: cart.php");
    exit;
}

// --- Handle Remove from Cart POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
    $rid = (int)$_POST['remove_id'];
    if (isset($_SESSION['cart'][$rid])) {
        unset($_SESSION['cart'][$rid]);
        // Save to DB cart if logged in
        if ($loggedIn) save_cart_to_db($_SESSION['user_id'], $_SESSION['cart']);
    }
    header("Location: cart.php");
    exit;
}

// Helper for image path (tries png and jpg, falls back to logo)
function product_image_path($filename) {
    $base = pathinfo($filename, PATHINFO_FILENAME);
    $png = "images/products/{$base}.png";
    $jpg = "images/products/{$base}.jpg";
    if ($filename && file_exists(__DIR__ . '/' . $png)) return $png;
    if ($filename && file_exists(__DIR__ . '/' . $jpg)) return $jpg;
    if ($filename && file_exists(__DIR__ . '/' . $filename)) return $filename;
    return "images/logo.png";
}

// --- Save cart to DB ---
function save_cart_to_db($user_id, $cart_arr) {
    global $pdo;
    // Find or create cart
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id=?");
    $stmt->execute([$user_id]);
    $cartRow = $stmt->fetch();
    if ($cartRow) {
        $cartId = $cartRow['id'];
        $pdo->prepare("DELETE FROM cart_items WHERE cart_id=?")->execute([$cartId]);
    } else {
        $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)")->execute([$user_id]);
        $cartId = $pdo->lastInsertId();
    }
    foreach ($cart_arr as $pid => $qty) {
        $pdo->prepare("INSERT INTO cart_items (cart_id,product_id,quantity) VALUES (?,?,?)")->execute([$cartId, $pid, $qty]);
    }
}

// --- Clear cart after checkout or logout ---
if (isset($_GET['clear']) && $_GET['clear'] == '1') {
    unset($_SESSION['cart']);
    $cart = [];
    $cartCount = 0;
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Cart — Baytisan</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body { background: #fdf7f4; }
    .cart-container {
      max-width: 540px;
      margin: 48px auto;
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 6px 32px rgba(100,100,100,0.08);
      padding: 40px 30px 30px 30px;
      text-align: center;
    }
    .cart-icon {
      font-size: 3.5rem;
      color: var(--primary, #8EB486);
      margin-bottom: 18px;
    }
    .cart-empty-text {
      font-size: 1.36rem;
      color: var(--dark, #685752);
      margin-bottom: 8px;
      font-weight: bold;
    }
    .cart-message {
      color: var(--muted, #7a7a7a);
      margin-bottom: 26px;
      font-size: 1.1rem;
    }
    .cart-actions {
      margin-top: 20px;
      display: flex;
      gap: 16px;
      justify-content: center;
      flex-wrap: wrap;
    }
    .btn-primary, .btn-outline {
      font-size: 1.07em;
      font-weight: bold;
      border-radius: 10px;
      padding: 10px 22px;
      cursor: pointer;
      text-decoration: none;
      border: none;
      display: inline-block;
      transition: background .18s, color .18s;
    }
    .btn-primary {
      background: var(--primary, #8EB486);
      color: #fff;
    }
    .btn-primary:hover { background: #6a9c5b;}
    .btn-outline {
      background: #fff;
      color: var(--primary, #8EB486);
      border: 2px solid var(--primary, #8EB486);
    }
    .btn-outline:hover { background: #eef6ef;}

    /* Cart Table Styles */
    .cart-table {
      width: 100%;
      margin-bottom: 20px;
      border-collapse: separate;
      border-spacing: 0 12px;
    }
    .cart-table th, .cart-table td {
      font-size: 1.08em;
      text-align: left;
      border: none;
      background: transparent;
      vertical-align: middle;
      padding: 0 8px;
    }
    .cart-table th {
      color: #685752;
      font-size: 1.13em;
      font-weight: 700;
      padding-bottom: 6px;
    }
    .cart-table td.qty-col {
      text-align: center;
      font-size: 1.14em;
      font-weight: 600;
      color: #685752;
      width: 60px;
      padding-right: 0;
    }
    .cart-prod-cell {
      display: flex;
      align-items: center;
      gap: 14px;
    }
    .cart-img-thumb {
      width: 48px; height: 48px; object-fit: cover;
      border-radius: 10px;
      border: 1px solid #eee;
      background: #fafafa;
      box-shadow: 0 2px 7px rgba(80,80,80,0.06);
    }
    .cart-prod-name {
      font-size: 1.08em;
      color: #685752;
      font-weight: 500;
    }
    .cart-table td.select-col {
      text-align: center;
      width: 32px;
    }
    .cart-check {
      accent-color: var(--primary, #8EB486);
      width: 18px;
      height: 18px;
      cursor: pointer;
    }
    .cart-table td.remove-col {
      text-align: center;
      width: 40px;
    }
    .btn-remove {
      background: #e05d5d;
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 5px 10px;
      cursor: pointer;
      font-size: 1em;
      font-weight: 600;
      transition: background 0.15s;
    }
    .btn-remove:hover { background: #b43636; }
    @media (max-width: 600px) {
      .cart-table th, .cart-table td { font-size: .97em; }
      .cart-img-thumb { width: 34px; height: 34px; }
      .cart-prod-cell { gap: 7px; }
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
      <?php if ($loggedIn): ?>
        <span class="welcome">Welcome, <?= htmlspecialchars($_SESSION['first_name']) ?></span>
        <a href="cart.php" class="btn btn-outline">Cart <span id="cartCount">(<?= $cartCount ?>)</span></a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
      <?php else: ?>
        
        <a href="cart.php" class="btn btn-outline">Cart <span id="cartCount">(0)</span></a>
      <?php endif; ?>
    </div>
  </div>
</header>

<main>
<div class="cart-container">
<?php if (!$loggedIn): ?>
    <div class="cart-icon"><i class="fa fa-shopping-cart"></i></div>
    <div class="cart-empty-text">Please login to view your cart.</div>
    <div class="cart-message">Sign in to add and view items in your cart.</div>
<?php elseif (empty($cart)): ?>
    <div class="cart-icon"><i class="fa fa-shopping-cart"></i></div>
    <div class="cart-empty-text">Your cart is empty!</div>
    <div class="cart-message">You don't have anything in your cart yet.</div>
    <div class="cart-actions">
      <a href="products.php" class="btn btn-outline"><i class="fa fa-store"></i> Continue Shopping</a>
    </div>
<?php else: ?>
    <div class="cart-icon"><i class="fa fa-shopping-cart"></i></div>
    <div class="cart-empty-text">Your Cart</div>
    <div class="cart-message">
        <strong>Items in your cart:</strong>
    </div>
    <form method="post" action="checkout.php" id="cartCheckoutForm">
    <table class="cart-table">
      <thead>
        <tr>
          <th class="select-col"></th>
          <th>Product</th>
          <th class="qty-col">Qty</th>
          <th class="remove-col"></th>
        </tr>
      </thead>
      <tbody>
        <?php
        $ids = array_keys($cart);
        $products = [];
        if ($ids) {
          $in = implode(',', array_fill(0, count($ids), '?'));
          $stmt = $pdo->prepare("SELECT id, name, image_filename FROM products WHERE id IN ($in)");
          $stmt->execute($ids);
          foreach ($stmt->fetchAll() as $row) {
            $products[$row['id']] = [
              'name' => $row['name'],
              'image' => product_image_path($row['image_filename'])
            ];
          }
        }
        foreach ($cart as $pid => $qty): ?>
          <tr>
            <td class="select-col">
              <input type="checkbox" name="checkout_ids[]" class="cart-check" value="<?= (int)$pid ?>" checked>
            </td>
            <td>
              <div class="cart-prod-cell">
                <img src="<?= htmlspecialchars($products[$pid]['image']) ?>" class="cart-img-thumb" alt="">
                <span class="cart-prod-name"><?= htmlspecialchars($products[$pid]['name'] ?? "Product #$pid") ?></span>
              </div>
            </td>
            <td class="qty-col"><?= (int)$qty ?></td>
            <td class="remove-col">
              <!-- Remove button is now outside the checkout form by using a plain button and JS -->
              <button type="button" class="btn-remove" title="Remove" onclick="removeFromCart(<?= (int)$pid ?>)"><i class="fa fa-trash"></i></button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="cart-actions">
      <a href="products.php" class="btn btn-outline"><i class="fa fa-store"></i> Continue Shopping</a>
      <button type="submit" class="btn btn-primary"><i class="fa fa-credit-card"></i> Checkout</button>
    </div>
    </form>
    <!-- Hidden form for item removal -->
    <form id="removeCartForm" method="post" style="display:none">
      <input type="hidden" name="remove_id" id="remove_id_input">
    </form>
<?php endif; ?>
</div>
</main>
<script src="script.js"></script>
<script>
  // Prevent empty checkout
  document.getElementById('cartCheckoutForm')?.addEventListener('submit', function(e){
    if (!document.querySelector('input.cart-check:checked')) {
      e.preventDefault();
      alert('Select at least one item to check out.');
    }
  });
  // Remove item from cart with hidden form
  function removeFromCart(pid) {
    document.getElementById('remove_id_input').value = pid;
    document.getElementById('removeCartForm').submit();
  }
</script>
</body>
</html>