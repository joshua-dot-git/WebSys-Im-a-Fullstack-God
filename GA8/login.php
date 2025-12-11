<?php
require 'database.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['ok'=>false,'msg'=>'Invalid']); exit;
}

$email = strtolower(trim($_POST['email'] ?? ''));
$pass = $_POST['password'] ?? '';

if (!$email || !$pass) {
  echo json_encode(['ok'=>false,'msg'=>'Fill required fields']); exit;
}

// Use LOWER(email) for case-insensitive match
$stmt = $pdo->prepare("SELECT id, first_name, password_hash, role FROM users WHERE LOWER(email) = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($pass, $user['password_hash'])) {
  echo json_encode(['ok'=>false,'msg'=>'Invalid credentials']); exit;
}

// success
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['first_name'] = $user['first_name'];
$_SESSION['role'] = $user['role'];

// ---- RESTORE CART FROM DB ----
$stmtCart = $pdo->prepare("SELECT id FROM carts WHERE user_id=?");
$stmtCart->execute([$user['id']]);
$cartRow = $stmtCart->fetch();
$cartArr = [];
if ($cartRow) {
    $cartId = $cartRow['id'];
    $stmtItems = $pdo->prepare("SELECT product_id, quantity FROM cart_items WHERE cart_id=?");
    $stmtItems->execute([$cartId]);
    foreach ($stmtItems->fetchAll() as $item) {
        $cartArr[(int)$item['product_id']] = (int)$item['quantity'];
    }
}
$_SESSION['cart'] = $cartArr;

echo json_encode([
  'ok' => true,
  'msg' => 'Welcome '.$user['first_name'],
  'user' => [
    'id' => $user['id'],
    'first_name' => $user['first_name'],
    'role' => $user['role']
  ]
]);
exit;
