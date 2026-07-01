<?php
session_start();

$host = 'localhost';
$dbname = 'enxoval';
$user = 'root';
$pass = '';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die('Erro na conexão: ' . $e->getMessage());
}

function formatarPreco($valor) {
  return 'R$ ' . number_format($valor, 2, ',', '.');
}

function buscarCasal($pdo, $slug = null) {
  if ($slug) {
    $stmt = $pdo->prepare("SELECT * FROM casal WHERE slug = ?");
    $stmt->execute([$slug]);
  } else {
    $stmt = $pdo->query("SELECT * FROM casal LIMIT 1");
  }
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

function buscarCategorias($pdo, $casal_id) {
  $stmt = $pdo->prepare("SELECT * FROM categorias WHERE casal_id = ? ORDER BY ordem, nome");
  $stmt->execute([$casal_id]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function buscarItens($pdo, $casal_id) {
  $stmt = $pdo->prepare("
    SELECT i.*, c.nome AS categoria_nome,
           (SELECT COUNT(*) FROM reservas WHERE item_id = i.id) AS reservado,
           (SELECT convidado_nome FROM reservas WHERE item_id = i.id LIMIT 1) AS convidado_nome
    FROM itens i
    LEFT JOIN categorias c ON i.categoria_id = c.id
    WHERE i.casal_id = ?
    ORDER BY c.ordem, i.ordem, i.nome
  ");
  $stmt->execute([$casal_id]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function slugificar($texto) {
  $texto = preg_replace('/[^a-zA-Z0-9_-]/', '-', $texto);
  $texto = preg_replace('/-+/', '-', $texto);
  return trim(strtolower($texto), '-');
}
