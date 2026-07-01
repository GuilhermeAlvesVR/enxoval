<?php
require_once 'includes/config.php';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'] ?? '';
  $senha = $_POST['senha'] ?? '';

  $stmt = $pdo->prepare("SELECT * FROM casal WHERE email = ?");
  $stmt->execute([$email]);
  $casal = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($casal && password_verify($senha, $casal['senha'])) {
    $_SESSION['admin'] = $casal['id'];
    $_SESSION['admin_nome'] = $casal['nome1'] . ' & ' . $casal['nome2'];
    header('Location: admin/index.php');
    exit;
  } else {
    $erro = 'E-mail ou senha inválidos.';
  }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Enxoval</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .login-box { max-width: 400px; margin: 80px auto; padding: 40px; background: #fff; border-radius: 12px; box-shadow: 0 2px 20px rgba(0,0,0,0.08); }
    .login-box h1 { text-align: center; font-weight: 300; margin-bottom: 24px; }
    .login-box label { display: block; font-size: 0.85rem; font-weight: 600; margin-top: 16px; margin-bottom: 4px; }
    .login-box input { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem; }
    .login-box input:focus { border-color: #d4a574; outline: none; }
    .login-box .btn { width: 100%; margin-top: 24px; }
    .login-box .error { background: #fce4ec; color: #c62828; padding: 10px; border-radius: 6px; margin-bottom: 16px; text-align: center; font-size: 0.9rem; }
  </style>
</head>
<body>
  <div class="login-box">
    <h1>Painel do Enxoval</h1>
    <?php if ($erro): ?><div class="error"><?= $erro ?></div><?php endif; ?>
    <form method="post">
      <label>E-mail</label>
      <input type="email" name="email" required>
      <label>Senha</label>
      <input type="password" name="senha" required>
      <button type="submit" class="btn btn-primary">Entrar</button>
    </form>
  </div>
</body>
</html>
