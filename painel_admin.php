 <?php
session_start();
include("conexao.php");

// Se não estiver logado, manda pro 2FA
if (!isset($_SESSION['admin_logado'])) {
    header("Location: 2fa.html");
    exit;
}

// Busca médicos e clientes
$medicos = $pdo->query("SELECT * FROM medicos")->fetchAll(PDO::FETCH_ASSOC);
$clientes = $pdo->query("SELECT * FROM clientes")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Painel Administrativo</title>
<link rel="stylesheet" href="style.css">
<style>
body {
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #e0f7fa 0%, #fffde7 100%);
    margin: 0;
    padding: 0;
}
h1, h2 {
    text-align: center;
    color: #2ec6f7;
}
.logout {
    display: block;
    text-align: center;
    margin: 20px auto;
    width: 120px;
    padding: 10px;
    background: #ff6fd8;
    color: white;
    text-decoration: none;
    border-radius: 12px;
    font-weight: bold;
    transition: all 0.2s;
}
.logout:hover {
    background: #ffe156;
    color: #2ec6f7;
}
.panel-container {
    display: flex;
    flex-direction: column;
    gap: 40px;
    width: 90%;
    max-width: 1100px;
    margin: 40px auto;
}
.panel {
    background-color: #ffffff;
    border-radius: 24px;
    box-shadow: 0 4px 24px rgba(46,198,247,0.15);
    padding: 30px;
}
.panel h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #7be141;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
    text-align: center;
}
th {
    background-color: #2ec6f7;
    color: white;
}
tr:hover {
    background-color: #f1f1f1;
}
a {
    text-decoration: none;
    font-weight: bold;
}
a[href*="editar"] {
    color: #2ec6f7;
}
a[href*="excluir"] {
    color: #ff6f61;
}
a:hover {
    text-decoration: underline;
}
</style>
</head>
<body>
<h1>Painel Administrativo</h1>
<h2>Bem-vindo, <strong><?= htmlspecialchars($_SESSION['admin_logado']) ?></strong></h2>
<a href="logout.php" class="logout">Sair</a>

<div class="panel-container">

    <!-- Painel de Médicos -->
    <div class="panel">
        <h2>Médicos Cadastrados</h2>
        <table>
            <tr>
                <th>ID</th><th>Nome</th><th>CRO</th><th>Email</th><th>Telefone</th><th>Ações</th>
            </tr>
            <?php foreach ($medicos as $m): ?>
            <tr>
                <td><?= $m['id'] ?></td>
                <td><?= htmlspecialchars($m['nome']) ?></td>
                <td><?= htmlspecialchars($m['cro']) ?></td>
                <td><?= htmlspecialchars($m['email']) ?></td>
                <td><?= htmlspecialchars($m['telefone']) ?></td>
                <td>
                    <a href="editar_medico.php?id=<?= $m['id'] ?>">✏️ Editar</a> |
                    <a href="excluir_medico.php?id=<?= $m['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir este médico?')">🗑️ Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- Painel de Clientes -->
    <div class="panel">
        <h2> Associados Cadastrados</h2>
        <table>
            <tr>
                <th>ID</th><th>Responsável</th><th>CPF</th><th>Email</th><th>Telefone</th><th>Ações</th>
            </tr>
            <?php foreach ($clientes as $c): ?>
            <tr>
                <td><?= $c['id'] ?></td>
                <td><?= htmlspecialchars($c['nome_responsavel']) ?></td>
                <td><?= htmlspecialchars($c['cpf']) ?></td>
                <td><?= htmlspecialchars($c['email']) ?></td>
                <td><?= htmlspecialchars($c['telefone']) ?></td>
                <td>
                    <a href="editar_cliente.php?id=<?= $c['id'] ?>">✏️ Editar</a> |
                    <a href="excluir_cliente.php?id=<?= $c['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir este cliente?')">🗑️ Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

</div>

</body>
</html>
