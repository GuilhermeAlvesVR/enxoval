<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Instalar - Sistema de Enxoval</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .install-box { max-width: 520px; margin: 60px auto; padding: 40px; background: #fff; border-radius: 12px; box-shadow: 0 2px 20px rgba(0,0,0,0.08); }
    .install-box h1 { text-align: center; font-weight: 300; margin-bottom: 24px; }
    .install-box label { display: block; font-size: 0.85rem; font-weight: 600; margin-top: 16px; margin-bottom: 4px; }
    .install-box input { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem; }
    .install-box input:focus { border-color: #d4a574; outline: none; }
    .install-box .btn { width: 100%; margin-top: 24px; }
    .install-box .step { color: #888; font-size: 0.85rem; text-align: center; margin-bottom: 8px; }
    .success { background: #e8f5e9; color: #2e7d32; padding: 12px; border-radius: 6px; margin-bottom: 16px; text-align: center; }
    .error { background: #fce4ec; color: #c62828; padding: 12px; border-radius: 6px; margin-bottom: 16px; text-align: center; }
  </style>
</head>
<body>
  <?php
  $passo = isset($_GET['passo']) ? (int)$_GET['passo'] : 1;
  $erro = '';
  $sucesso = '';

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($passo === 1) {
      try {
        $pdo = new PDO("mysql:host={$_POST['db_host']};charset=utf8mb4", $_POST['db_user'], $_POST['db_pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$_POST['db_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$_POST['db_name']}`");

        $sql = file_get_contents(__DIR__ . '/schema.sql');
        $pdo->exec($sql);

        $config = "<?php\n";
        $config .= "\$host = '{$_POST['db_host']}';\n";
        $config .= "\$dbname = '{$_POST['db_name']}';\n";
        $config .= "\$user = '{$_POST['db_user']}';\n";
        $config .= "\$pass = '{$_POST['db_pass']}';\n";

        $conteudo = file_get_contents(__DIR__ . '/includes/config.php');
        $novo = "<?php\nsession_start();\n\n\$host = '{$_POST['db_host']}';\n\$dbname = '{$_POST['db_name']}';\n\$user = '{$_POST['db_user']}';\n\$pass = '{$_POST['db_pass']}';\n";
        $novo .= "\ntry {\n  \$pdo = new PDO(\"mysql:host=\$host;dbname=\$dbname;charset=utf8mb4\", \$user, \$pass);\n  \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);\n} catch (PDOException \$e) {\n  die('Erro na conexão: ' . \$e->getMessage());\n}\n";
        $resto = substr($conteudo, strpos($conteudo, 'function'));
        file_put_contents(__DIR__ . '/includes/config.php', $novo . $resto);

        file_put_contents(__DIR__ . '/includes/.db_ok', 'ok');
        $passo = 2;
      } catch (Exception $e) {
        $erro = 'Erro: ' . $e->getMessage();
      }
    } elseif ($passo === 2) {
      $nome1 = $_POST['nome1'] ?? '';
      $nome2 = $_POST['nome2'] ?? '';
      $email = $_POST['email'] ?? '';
      $senha = password_hash($_POST['senha'] ?? '', PASSWORD_DEFAULT);
      $data = $_POST['data_casamento'] ?? '';
      $slug = slugificar($nome1 . '-' . $nome2);

      try {
        $stmt = $pdo->prepare("INSERT INTO casal (nome1, nome2, email, senha, data_casamento, slug) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nome1, $nome2, $email, $senha, $data, $slug]);

        $stmt = $pdo->prepare("INSERT INTO categorias (casal_id, nome) VALUES (?, 'Cozinha'), (?, 'Sala de Estar'), (?, 'Quarto'), (?, 'Banheiro'), (?, 'Eletrodomésticos'), (?, 'Decoração')");
        $stmt->execute([$pdo->lastInsertId(), $pdo->lastInsertId(), $pdo->lastInsertId(), $pdo->lastInsertId(), $pdo->lastInsertId(), $pdo->lastInsertId()]);

        $sucesso = 'Sistema instalado com sucesso!';
        $passo = 3;
      } catch (Exception $e) {
        $erro = 'Erro: ' . $e->getMessage();
      }
    }
  }
  ?>

  <div class="install-box">
    <h1>📦 Instalar Enxoval</h1>

    <?php if ($erro): ?><div class="error"><?= $erro ?></div><?php endif; ?>
    <?php if ($sucesso): ?><div class="success"><?= $sucesso ?></div><?php endif; ?>

    <?php if ($passo === 1): ?>
      <p class="step">Passo 1 de 2 — Banco de Dados</p>
      <form method="post">
        <input type="hidden" name="passo" value="1">
        <label>Host do MySQL</label>
        <input type="text" name="db_host" value="localhost" required>
        <label>Banco de dados</label>
        <input type="text" name="db_name" value="enxoval" required>
        <label>Usuário</label>
        <input type="text" name="db_user" value="root" required>
        <label>Senha</label>
        <input type="password" name="db_pass">
        <button type="submit" class="btn btn-primary">Instalar Banco</button>
      </form>

    <?php elseif ($passo === 2): ?>
      <p class="step">Passo 2 de 2 — Dados do Casal</p>
      <form method="post">
        <input type="hidden" name="passo" value="2">
        <label>Nome do(a) Noivo(a) 1</label>
        <input type="text" name="nome1" required>
        <label>Nome do(a) Noivo(a) 2</label>
        <input type="text" name="nome2" required>
        <label>E-mail (para login)</label>
        <input type="email" name="email" required>
        <label>Senha (para login)</label>
        <input type="password" name="senha" required minlength="4">
        <label>Data do Casamento</label>
        <input type="date" name="data_casamento">
        <button type="submit" class="btn btn-primary">Finalizar</button>
      </form>

    <?php elseif ($passo === 3): ?>
      <div style="text-align:center">
        <p style="margin-bottom:16px">Tudo pronto! Agora é só acessar:</p>
        <p><strong>Site público:</strong><br><a href="index.php"><?= rtrim((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']), '/') ?>/</a></p>
        <p style="margin-top:8px"><strong>Painel admin:</strong><br><a href="login.php"><?= rtrim((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']), '/') ?>/login.php</a></p>
        <p style="margin-top:24px;color:#888;font-size:0.85rem">⚠️ Delete o arquivo <strong>instalar.php</strong> por segurança!</p>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
