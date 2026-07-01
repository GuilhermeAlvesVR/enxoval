<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['admin'])) {
  header('Location: ../login.php');
  exit;
}

$casal_id = $_SESSION['admin'];
$mensagem = '';

// CRUD Categorias
if (isset($_POST['add_categoria'])) {
  $nome = trim($_POST['nome']);
  if ($nome) {
    $stmt = $pdo->prepare("INSERT INTO categorias (casal_id, nome) VALUES (?, ?)");
    $stmt->execute([$casal_id, $nome]);
    $mensagem = 'Categoria adicionada!';
  }
}
if (isset($_GET['del_cat'])) {
  $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ? AND casal_id = ?");
  $stmt->execute([$_GET['del_cat'], $casal_id]);
  $mensagem = 'Categoria removida!';
}

// CRUD Itens
if (isset($_POST['add_item'])) {
  $stmt = $pdo->prepare("INSERT INTO itens (casal_id, categoria_id, nome, descricao, preco, imagem, loja, url, ordem) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->execute([
    $casal_id,
    $_POST['categoria_id'] ?: null,
    $_POST['nome'],
    $_POST['descricao'],
    str_replace(',', '.', $_POST['preco']),
    $_POST['imagem'],
    $_POST['loja'],
    $_POST['url'],
    (int)$_POST['ordem']
  ]);
  $mensagem = 'Item adicionado!';
}

if (isset($_POST['edit_item'])) {
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
    $_POST['item_id'],
    $casal_id
  ]);
  $mensagem = 'Item atualizado!';
}

if (isset($_GET['del_item'])) {
  $stmt = $pdo->prepare("DELETE FROM itens WHERE id = ? AND casal_id = ?");
  $stmt->execute([$_GET['del_item'], $casal_id]);
  $mensagem = 'Item removido!';
}

// Cancelar reserva
if (isset($_GET['cancelar_reserva'])) {
  $item_id = (int)$_GET['cancelar_reserva'];
  $stmt = $pdo->prepare("DELETE FROM reservas WHERE item_id = ? AND item_id IN (SELECT id FROM itens WHERE casal_id = ?)");
  $stmt->execute([$item_id, $casal_id]);
  $mensagem = 'Reserva cancelada!';
}

$categorias = buscarCategorias($pdo, $casal_id);
$itens = buscarItens($pdo, $casal_id);
$casal = buscarCasal($pdo);
$total_reservados = count(array_filter($itens, fn($i) => $i['reservado'] > 0));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Enxoval</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <nav class="admin-nav">
    <span class="admin-nav-title">👤 <?= $_SESSION['admin_nome'] ?></span>
    <div>
      <a href="../index.php" class="btn btn-sm" target="_blank">Ver Site</a>
      <a href="../admin/logout.php" class="btn btn-sm btn-outline">Sair</a>
    </div>
  </nav>

  <div class="admin-container">
    <?php if ($mensagem): ?><div class="msg"><?= $mensagem ?></div><?php endif; ?>

    <!-- Resumo -->
    <div class="admin-cards">
      <div class="admin-card">📦 <strong><?= count($itens) ?></strong> Itens</div>
      <div class="admin-card">🔖 <strong><?= count($categorias) ?></strong> Categorias</div>
      <div class="admin-card">✅ <strong><?= $total_reservados ?></strong> Reservados</div>
    </div>

    <!-- Categorias -->
    <div class="admin-section">
      <div class="admin-section-header">
        <h2>Categorias</h2>
        <button class="btn btn-sm btn-primary" onclick="toggleForm('cat-form')">+ Nova</button>
      </div>
      <form id="cat-form" method="post" class="inline-form" style="display:none">
        <input type="text" name="nome" placeholder="Nome da categoria" required>
        <button type="submit" name="add_categoria" class="btn btn-sm btn-primary">Adicionar</button>
        <button type="button" class="btn btn-sm btn-outline" onclick="toggleForm('cat-form')">Cancelar</button>
      </form>
      <div class="tag-list">
        <?php foreach ($categorias as $cat): ?>
          <span class="tag">
            <?= htmlspecialchars($cat['nome']) ?>
            <a href="?del_cat=<?= $cat['id'] ?>" class="tag-del" onclick="return confirm('Remover categoria?')">&times;</a>
          </span>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Itens -->
    <div class="admin-section">
      <div class="admin-section-header">
        <h2>Itens</h2>
        <button class="btn btn-sm btn-primary" onclick="toggleForm('item-form')">+ Novo Item</button>
      </div>

      <form id="item-form" method="post" class="item-form" style="display:none">
        <div class="form-row">
          <div class="form-group">
            <label>Nome do item</label>
            <input type="text" name="nome" required>
          </div>
          <div class="form-group">
            <label>Categoria</label>
            <select name="categoria_id">
              <option value="">Sem categoria</option>
              <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Descrição</label>
          <textarea name="descricao" rows="2"></textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Preço (R$)</label>
            <input type="text" name="preco" placeholder="199,90">
          </div>
          <div class="form-group">
            <label>Ordem</label>
            <input type="number" name="ordem" value="0">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Imagem (emoji ou URL)</label>
            <input type="text" name="imagem" placeholder="🛋️ ou img/sofá.jpg">
          </div>
          <div class="form-group">
            <label>Loja</label>
            <input type="text" name="loja" placeholder="Magazine Luiza">
          </div>
        </div>
        <div class="form-group">
          <label>Link do produto</label>
          <input type="url" name="url" placeholder="https://...">
        </div>
        <div class="form-actions">
          <button type="submit" name="add_item" class="btn btn-primary">Adicionar Item</button>
          <button type="button" class="btn btn-outline" onclick="toggleForm('item-form')">Cancelar</button>
        </div>
      </form>

      <div class="item-table">
        <div class="item-table-header">
          <span style="width:40px"></span>
          <span style="flex:1">Item</span>
          <span style="width:120px">Categoria</span>
          <span style="width:100px">Preço</span>
          <span style="width:140px">Status</span>
          <span style="width:80px">Ações</span>
        </div>
        <?php foreach ($itens as $item): ?>
          <div class="item-table-row">
            <span style="width:40px;font-size:1.5rem"><?= $item['imagem'] ?: '📦' ?></span>
            <span style="flex:1">
              <strong><?= htmlspecialchars($item['nome']) ?></strong>
              <?php if ($item['descricao']): ?>
                <br><small><?= htmlspecialchars($item['descricao']) ?></small>
              <?php endif; ?>
            </span>
            <span style="width:120px"><?= htmlspecialchars($item['categoria_nome'] ?? '-') ?></span>
            <span style="width:100px"><?= formatarPreco($item['preco']) ?></span>
            <span style="width:140px">
              <?php if ($item['reservado'] > 0): ?>
                <span class="badge badge-reservado">
                  Reservado por <?= htmlspecialchars($item['convidado_nome']) ?>
                </span>
                <a href="?cancelar_reserva=<?= $item['id'] ?>" class="btn btn-xs btn-outline" onclick="return confirm('Cancelar reserva?')">Cancelar</a>
              <?php else: ?>
                <span class="badge badge-disponivel">Disponível</span>
              <?php endif; ?>
            </span>
            <span style="width:80px">
              <button class="btn btn-xs btn-outline" onclick="editarItem(<?= $item['id'] ?>)">Editar</button>
              <a href="?del_item=<?= $item['id'] ?>" class="btn btn-xs btn-outline" style="color:#c62828" onclick="return confirm('Remover item?')">Remover</a>
            </span>
          </div>
        <?php endforeach; ?>
        <?php if (empty($itens)): ?>
          <div style="text-align:center;padding:32px;color:#888">Nenhum item ainda. Clique em "+ Novo Item" para começar.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script>
  function toggleForm(id) {
    const el = document.getElementById(id);
    el.style.display = el.style.display === 'none' ? 'flex' : 'none';
  }

  function editarItem(id) {
    window.location.href = 'editar-item.php?id=' + id;
  }
  </script>

  <style>
  .admin-nav { display: flex; justify-content: space-between; align-items: center; padding: 12px 24px; background: #2c2c2c; color: white; }
  .admin-nav-title { font-weight: 600; }
  .admin-container { max-width: 1100px; margin: 0 auto; padding: 24px; }
  .admin-cards { display: flex; gap: 16px; margin-bottom: 24px; }
  .admin-card { flex: 1; background: #fff; border-radius: 8px; padding: 20px; text-align: center; box-shadow: 0 1px 6px rgba(0,0,0,0.06); font-size: 0.95rem; }
  .admin-card strong { font-size: 1.5rem; display: block; margin-bottom: 4px; }
  .admin-section { background: #fff; border-radius: 8px; padding: 20px; margin-bottom: 24px; box-shadow: 0 1px 6px rgba(0,0,0,0.06); }
  .admin-section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
  .admin-section-header h2 { font-size: 1.1rem; font-weight: 600; }
  .inline-form { display: flex; gap: 8px; margin-bottom: 12px; align-items: end; }
  .inline-form input { flex: 1; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; }
  .tag-list { display: flex; flex-wrap: wrap; gap: 8px; }
  .tag { display: inline-flex; align-items: center; gap: 6px; background: #f5f0eb; padding: 4px 12px; border-radius: 16px; font-size: 0.85rem; }
  .tag-del { text-decoration: none; color: #c62828; font-weight: 700; }
  .item-form { margin-bottom: 20px; padding: 16px; background: #faf8f5; border-radius: 8px; flex-direction: column; gap: 8px; }
  .form-row { display: flex; gap: 16px; }
  .form-row .form-group { flex: 1; }
  .form-group { margin-bottom: 8px; }
  .form-group label { display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 2px; }
  .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.9rem; }
  .form-group textarea { resize: vertical; }
  .form-actions { display: flex; gap: 8px; margin-top: 8px; }
  .item-table { margin-top: 8px; }
  .item-table-header, .item-table-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #eee; font-size: 0.9rem; }
  .item-table-header { font-weight: 600; color: #888; font-size: 0.8rem; text-transform: uppercase; }
  .msg { background: #e8f5e9; color: #2e7d32; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; }
  .badge { font-size: 0.8rem; padding: 2px 10px; border-radius: 12px; display: inline-block; }
  .badge-reservado { background: #e8f5e9; color: #2e7d32; }
  .badge-disponivel { background: #f5f5f5; color: #888; }
  .btn-xs { font-size: 0.75rem; padding: 4px 8px; }
  @media (max-width: 768px) {
    .form-row { flex-direction: column; gap: 0; }
    .item-table-header { display: none; }
    .item-table-row { flex-wrap: wrap; gap: 4px; }
    .item-table-row span { width: auto !important; }
    .admin-cards { flex-direction: column; }
  }
  </style>
</body>
</html>
