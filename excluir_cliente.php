 <?php
session_start();
include("conexao.php");

if (!isset($_SESSION['admin_logado'])) {
    header("Location: 2fa.html");
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM clientes WHERE id=?");
    $stmt->execute([$id]);
}

header("Location: painel_admin.php");
exit;
