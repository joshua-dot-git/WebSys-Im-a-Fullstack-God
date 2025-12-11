<?php
require 'database.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['ok'=>false,'msg'=>'Invalid request']); exit;
}

$first = trim($_POST['first_name'] ?? '');
$last  = trim($_POST['last_name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$pass  = $_POST['password'] ?? '';

if (!$first || !$email || !$pass) {
  echo json_encode(['ok'=>false,'msg'=>'Please fill required fields']); exit;
}

// check duplicate
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
  echo json_encode(['ok'=>false,'msg'=>'Email already registered']); exit;
}

$hash = password_hash($pass, PASSWORD_DEFAULT);
$ins = $pdo->prepare("INSERT INTO users (first_name,last_name,email,password_hash,role) VALUES (?,?,?,?,?)");
$ins->execute([$first,$last,$email,$hash,'customer']);
$userId = $pdo->lastInsertId();

// auto-login
session_regenerate_id(true);
$_SESSION['user_id'] = $userId;
$_SESSION['first_name'] = $first;
$_SESSION['role'] = 'customer';

echo json_encode(['ok'=>true,'msg'=>'Account created','user'=>['id'=>$userId,'first_name'=>$first,'role'=>'customer']]);
exit;