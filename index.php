<?php
require_once 'includes/config.php';

$casal = buscarCasal($pdo);
if (!$casal) {
  if (file_exists('instalar.php')) {
    header('Location: instalar.php');
    exit;
  }
  die('Sistema não instalado.');
}

$categorias = buscarCategorias($pdo, $casal['id']);
$itens = buscarItens($pdo, $casal['id']);

// AJAX: reservar item
if (isset($_POST['ajax_reservar'])) {
  header('Content-Type: application/json');
  $item_id = (int)$_POST['item_id'];
  $nome = trim($_POST['nome'] ?? '');
  if (!$nome) { echo json_encode(['ok' => false, 'erro' => 'Nome é obrigatório']); exit; }

  $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas WHERE item_id = ?");
  $stmt->execute([$item_id]);
  if ($stmt->fetchColumn() > 0) { echo json_encode(['ok' => false, 'erro' => 'Item já reservado']); exit; }

  $stmt = $pdo->prepare("INSERT INTO reservas (item_id, convidado_nome) VALUES (?, ?)");
  $stmt->execute([$item_id, $nome]);
  echo json_encode(['ok' => true, 'nome' => $nome]);
  exit;
}

$data_casamento = $casal['data_casamento'] ? date('d/m/Y', strtotime($casal['data_casamento'])) : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Enxoval - <?= htmlspecialchars($casal['nome1']) ?> & <?= htmlspecialchars($casal['nome2']) ?></title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <header class="hero">
    <h1><?= htmlspecialchars($casal['nome1']) ?> & <?= htmlspecialchars($casal['nome2']) ?></h1>
    <div class="hearts">&#10084; &#10084; &#10084;</div>
    <?php if ($data_casamento): ?>
      <p class="date"><?= $data_casamento ?></p>
    <?php endif; ?>
    <p class="subtitle">Ajude-nos a construir nosso lar escolhendo um presente</p>
  </header>

  <?php
  $total = count($itens);
  $reservados = count(array_filter($itens, fn($i) => $i['reservado'] > 0));
  $disponiveis = $total - $reservados;
  ?>
  <div class="summary-bar">
    <div class="counts">
      <span>Itens: <strong id="total-items"><?= $total ?></strong></span>
      <span>Reservados: <strong id="reserved-items"><?= $reservados ?></strong></span>
      <span>Disponíveis: <strong id="available-items"><?= $disponiveis ?></strong></span>
    </div>
  </div>

  <nav class="filters" id="filters">
    <button class="filter-btn active" data-category="Todos">Todos</button>
    <?php foreach ($categorias as $cat): ?>
      <button class="filter-btn" data-category="<?= htmlspecialchars($cat['nome']) ?>">
        <?= htmlspecialchars($cat['nome']) ?>
      </button>
    <?php endforeach; ?>
  </nav>

  <main class="items-grid" id="items-grid"></main>

  <div class="modal-overlay" id="modal-overlay">
    <div class="modal">
      <h2>Reservar Item</h2>
      <p>Você está reservando: <strong id="modal-item-name"></strong></p>
      <div class="form-group">
        <label for="guest-name">Seu nome</label>
        <input type="text" id="guest-name" placeholder="Digite seu nome..." maxlength="60">
      </div>
      <div id="modal-error" style="color:#c62828;font-size:0.85rem;display:none;margin-top:8px"></div>
      <div class="modal-actions">
        <button class="btn-cancel" id="btn-cancel-modal">Cancelar</button>
        <button class="btn-confirm" id="btn-confirm-modal">Confirmar Reserva</button>
      </div>
    </div>
  </div>

  <div class="toast" id="toast"></div>

  <script>
  const items = <?= json_encode($itens) ?>;
  </script>
  <script src="assets/js/script.js"></script>
</body>
</html>
