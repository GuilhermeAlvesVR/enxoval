<?php
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['admin'])) { header('Location: ../login.php'); exit; }

$casal_id = $_SESSION['admin'];
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM itens WHERE id = ? AND casal_id = ?");
$stmt->execute([$id, $casal_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$item) { header('Location: index.php'); exit; }

$categorias = buscarCategorias($pdo, $casal_id);
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $stmt = $pdo->prepare("UPDATE itens SET categoria_id=?, nome=?, descricao=?, preco=?, imagem=?, loja=?, url=?, ordem=? WHERE id=? AND casal_id=?");
  $stmt->execute([
    $_POST['categoria_id'] ?: null,
    $_POST['nome'],
    $_POST['descricao'],
    str_replace(',', '.', $_POST['preco']),
    $_POST['imagem'],
    $_POST['loja'],
    $_POST['url'],
    (int)$_POST['ordem'],
    $id,
    $casal_id
  ]);
  $mensagem = 'Item atualizado!';
  $stmt = $pdo->prepare("SELECT * FROM itens WHERE id = ?");
  $stmt->execute([$id]);
  $item = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Item - Enxoval</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <nav class="admin-nav">
    <span class="admin-nav-title">✏️ Editar Item</span>
    <a href="index.php" class="btn btn-sm">← Voltar</a>
  </nav>
  <div class="admin-container" style="max-width:600px">
    <?php if ($mensagem): ?><div class="msg"><?= $mensagem ?></div><?php endif; ?>
    <div class="admin-section">
      <form method="post" class="item-form" style="display:flex">
        <div class="form-group">
          <label>Nome</label>
          <input type="text" name="nome" value="<?= htmlspecialchars($item['nome']) ?>" required>
        </div>
        <div class="form-group">
          <label>Categoria</label>
          <select name="categoria_id">
            <option value="">Sem categoria</option>
            <?php foreach ($categorias as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $item['categoria_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['nome']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Descrição</label>
          <textarea name="descricao" rows="2"><?= htmlspecialchars($item['descricao']) ?></textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Preço (R$)</label>
            <input type="text" name="preco" value="<?= number_format($item['preco'], 2, ',', '') ?>">
          </div>
          <div class="form-group">
            <label>Ordem</label>
            <input type="number" name="ordem" value="<?= $item['ordem'] ?>">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Imagem (emoji ou URL)</label>
            <input type="text" name="imagem" value="<?= htmlspecialchars($item['imagem']) ?>">
          </div>
          <div class="form-group">
            <label>Loja</label>
            <input type="text" name="loja" value="<?= htmlspecialchars($item['loja']) ?>">
          </div>
        </div>
        <div class="form-group">
          <label>Link</label>
          <input type="url" name="url" value="<?= htmlspecialchars($item['url']) ?>">
        </div>
        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Salvar</button>
          <a href="index.php" class="btn btn-outline">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
