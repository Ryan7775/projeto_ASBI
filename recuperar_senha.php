 <?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("conexao.php");
include("header.php");

$feedback = ['message'=>'','type'=>''];
$etapa = $_POST['etapa'] ?? '1';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if ($etapa == '1') {
        $identificador = trim($_POST['identificador']);
        $data_nascimento = $_POST['data_nascimento'] ?? null;
        $nome_mae = trim($_POST['nome_mae'] ?? '');

        try {
            $stmt_medico = $pdo->prepare("SELECT * FROM medicos WHERE (cro = ? OR email = ?) AND data_nascimento = ?");
            $stmt_medico->execute([$identificador, $identificador, $data_nascimento]);
            $usuario = $stmt_medico->fetch(PDO::FETCH_ASSOC);
            $tipo = 'medico';

            if (!$usuario) {
                $stmt_cliente = $pdo->prepare("SELECT * FROM clientes WHERE (cpf = ? OR email = ?) AND nome_mae = ? AND data_nascimento = ?");
                $stmt_cliente->execute([$identificador, $identificador, $nome_mae, $data_nascimento]);
                $usuario = $stmt_cliente->fetch(PDO::FETCH_ASSOC);
                $tipo = 'cliente';
            }

            if ($usuario) {
                $_SESSION['recupera_id'] = $usuario['id'];
                $_SESSION['recupera_tipo'] = $tipo;
                $feedback = ['message'=>'✅ Usuário encontrado! Agora defina sua nova senha.', 'type'=>'success'];
                $etapa = '2';
            } else {
                $feedback = ['message'=>'❌ Usuário não encontrado ou dados incorretos.', 'type'=>'error'];
                $etapa = '1';
            }
        } catch (PDOException $e) {
            $feedback = ['message'=>"❌ Erro na consulta: ".$e->getMessage(), 'type'=>'error'];
            $etapa = '1';
        }

    } elseif ($etapa == '2') {
        $nova_senha = $_POST['nova_senha'];
        $confirma_senha = $_POST['confirma_senha'];

        if (strlen($nova_senha) < 6) {
            $feedback = ['message'=>'❌ A senha deve ter pelo menos 6 caracteres.', 'type'=>'error'];
        } elseif ($nova_senha !== $confirma_senha) {
            $feedback = ['message'=>'❌ As senhas não coincidem.', 'type'=>'error'];
        } else {
            $id_usuario = $_SESSION['recupera_id'] ?? null;
            $tipo = $_SESSION['recupera_tipo'] ?? null;

            if ($id_usuario && $tipo) {
                $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

                $table = ($tipo == 'medico') ? 'medicos' : 'clientes';
                $stmt_update = $pdo->prepare("UPDATE {$table} SET senha = ? WHERE id = ?");
                $stmt_update->execute([$nova_senha_hash, $id_usuario]);

                unset($_SESSION['recupera_id']);
                unset($_SESSION['recupera_tipo']);

                $feedback = ['message'=>'✅ Senha alterada com sucesso! Faça login.', 'type'=>'success'];
                $etapa = '3';
            } else {
                $feedback = ['message'=>'❌ Ocorreu um erro. Tente novamente.', 'type'=>'error'];
                $etapa = '1';
            }
        }
    }
}
?>

<div class="form-container">
    <h2>Recuperar Senha</h2>

    <?php if (!empty($feedback['message'])): ?>
        <div class="feedback-message <?php echo $feedback['type']; ?>">
            <?php echo htmlspecialchars($feedback['message'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <?php if ($etapa == '1'): ?>
        <form action="recuperar_senha.php" method="POST">
            <input type="hidden" name="etapa" value="1">

            <label for="identificador">CRO (Médico) ou CPF/E‑mail (Cliente):</label>
            <input type="text" id="identificador" name="identificador" required>

            <label for="data_nascimento">Data de Nascimento:</label>
            <input type="date" id="data_nascimento" name="data_nascimento" required>

            <label for="nome_mae">Nome da Mãe (só para clientes):</label>
            <input type="text" id="nome_mae" name="nome_mae">

            <button type="submit">Continuar</button>
        </form>

    <?php elseif ($etapa == '2'): ?>
        <form action="recuperar_senha.php" method="POST">
            <input type="hidden" name="etapa" value="2">

            <label for="nova_senha">Nova Senha (mín. 6 caracteres):</label>
            <input type="password" id="nova_senha" name="nova_senha" required minlength="6">

            <label for="confirma_senha">Confirmar Senha:</label>
            <input type="password" id="confirma_senha" name="confirma_senha" required>

            <button type="submit">Alterar Senha</button>
        </form>

    <?php else: ?>
        <p><a href="login.php">Voltar para Login</a></p>
    <?php endif; ?>
</div>

<?php include('footer.php'); ?>
