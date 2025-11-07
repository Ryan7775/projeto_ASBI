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

// Buscar dados do médico
$medico = $pdo->prepare("SELECT * FROM medicos WHERE id=?");
$medico->execute([$id]);
$dado = $medico->fetch(PDO::FETCH_ASSOC);

if (!$dado) {
    echo "Médico não encontrado!";
    exit;
}

// Atualizar médico
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $cro = $_POST['cro'];
    $especialidade = $_POST['especialidade'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];

    $stmt = $pdo->prepare("UPDATE medicos SET nome=?, cro=?, especialidade=?, email=?, telefone=? WHERE id=?");
    $stmt->execute([$nome, $cro, $especialidade, $email, $telefone, $id]);

    echo "<script>alert('✅ Médico atualizado com sucesso!'); window.location='painel_admin.php';</script>";
    exit;
}
?>

<div class="form-container">   
<h2>Editar Médico</h2>
<form method="post">
    <input type="text" name="nome" value="<?= htmlspecialchars($dado['nome']) ?>" required><br>
    <input type="text" name="cro" value="<?= htmlspecialchars($dado['cro']) ?>" required><br>
    <input type="text" name="especialidade" value="<?= htmlspecialchars($dado['especialidade']) ?>"><br>
    <input type="email" name="email" value="<?= htmlspecialchars($dado['email']) ?>" required><br>
    <input type="text" name="telefone" value="<?= htmlspecialchars($dado['telefone']) ?>"><br>
    <button type="submit">Salvar Alterações</button>
</form>
</div>
