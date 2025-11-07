  <?php
session_start();
include("conexao.php");

// Só permite acesso se o admin estiver logado
if (!isset($_SESSION['admin_logado'])) {
    header("Location: 2fa.html");
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "ID inválido!";
    exit;
}

// Buscar dados do cliente
$cliente = $pdo->prepare("SELECT * FROM clientes WHERE id=?");
$cliente->execute([$id]);
$dado = $cliente->fetch(PDO::FETCH_ASSOC);

if (!$dado) {
    echo "Cliente não encontrado!";
    exit;
}

// Atualizar cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome_responsavel'];
    $cpf = $_POST['cpf'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];


        $stmt = $pdo->prepare("UPDATE clientes SET nome_responsavel=?, cpf=?, email=?, telefone=?, WHERE id=?");
  
        $stmt = $pdo->prepare("UPDATE clientes SET nome_responsavel=?, cpf=?, email=?, telefone=? WHERE id=?");
        $stmt->execute([$nome, $cpf, $email, $telefone, $id]);
    echo "<script>alert('✅ associado atualizado com sucesso!'); window.location='painel_admin.php';</script>";
    }

    
?>

<div class="form-container">
    <link rel="stylesheet" href="style.css">
  <h2>Editar Cliente</h2>
<form method="post">
    <input type="text" name="nome_responsavel" value="<?= htmlspecialchars($dado['nome_responsavel']) ?>" required><br>
    <input type="text" name="cpf" value="<?= htmlspecialchars($dado['cpf']) ?>" required><br>
    <input type="email" name="email" value="<?= htmlspecialchars($dado['email']) ?>" required><br>
    <input type="text" name="telefone" value="<?= htmlspecialchars($dado['telefone']) ?>"><br>
    <button type="submit">Salvar Alterações</button>
</form>

</div>

 
