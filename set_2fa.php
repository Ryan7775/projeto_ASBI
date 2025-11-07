<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('conexao.php');
include('header.php');

// Permite tanto o fluxo pós-cadastro (pending_2fa) quanto usuário já logado
$pending = $_SESSION['pending_2fa'] ?? null;
$logged = isset($_SESSION['id']) ? ['id' => $_SESSION['id'], 'tipo' => $_SESSION['tipo']] : null;
$context = $pending ?: $logged;

if (!$context) {
    // Nada a fazer aqui
    header('Location: login.php');
    exit();
}

$feedback = ['message'=>'','type'=>''];

if ($_SERVER["REQUEST_METHOD"] == "POST") 
    $pin = trim($_POST['pin'] ?? '');
    $pin_conf = trim($_POST['pin_conf'] ?? '');

    if (!preg_match('/^\d{6}$/', $pin)) {
        $feedback = ['message' => '❌ O PIN deve ter exatamente 6 dígitos numéricos.', 'type' => 'error'];
    } elseif ($pin !== $pin_conf) {
        $feedback = ['message' => '❌ PINs não conferem.', 'type' => 'error'];
    } else {
        $hash = password_hash($pin, PASSWORD_DEFAULT);
        $id = (int)$context['id'];
        $tipo = $context['tipo'];
        $table = ($tipo === 'medico') ? 'medicos' : 'clientes';

        // Tenta atualizar; se a coluna não existir, tenta criar e repetir
        try {
            $stmt = $pdo->prepare("UPDATE {$table} SET twofa_pin = ? WHERE id = ?");
            $stmt->execute([$hash, $id]);
        } catch (PDOException $e) {
            // Tenta criar coluna e repetir
            try {
                $pdo->exec("ALTER TABLE {$table} ADD COLUMN IF NOT EXISTS twofa_pin VARCHAR(255) DEFAULT NULL");
                $stmt = $pdo->prepare("UPDATE {$table} SET twofa_pin = ? WHERE id = ?");
                $stmt->execute([$hash, $id]);
            } catch (PDOException $e2) {
                $feedback = ['message' => '❌ Erro ao salvar PIN 2FA: ' . $e2->getMessage(), 'type' => 'error'];
            }
        }

        if (!$feedback['message']) {
            // Busca nome para logar automaticamente
            $col_nome = ($tipo === 'medico') ? 'nome' : 'nome_responsavel';
            $stmt2 = $pdo->prepare("SELECT id, {$col_nome} AS nome FROM {$table} WHERE id = ?");
            $stmt2->execute([$id]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);

            // Limpa pending se existia
            unset($_SESSION['pending_2fa']);

            // Faz login automático
            session_regenerate_id(true);
            $_SESSION['id'] = $row['id'];
            $_SESSION['nome'] = $row['nome'];
            $_SESSION['tipo'] = $tipo;

            // Atualiza ultimo_login
            try {
                $stmt3 = $pdo->prepare("UPDATE {$table} SET ultimo_login = NOW() WHERE id = ?");
                $stmt3->execute([$id]);
            } catch (Exception $ex) {}

            $dashboard = ($tipo == 'medico') ? 'painel_medico.php' : 'painel_cliente.php';
            header('Location: ' . $dashboard);
            exit();
        }
    }

?>

<div class="form-container">
    <h2>Configurar PIN 2FA</h2>

    <?php if (!empty($feedback['message'])): ?>
        <div class="feedback-message <?php echo $feedback['type']; ?>"><?php echo $feedback['message']; ?></div>
    <?php endif; ?>

    <p>Escolha um PIN de 6 dígitos que será usado como segundo fator de autenticação.</p>

    <form method="POST" action="set_2fa.php">
        <label for="pin">PIN (6 dígitos)</label>
        <input type="password" id="pin" name="pin" pattern="\d{6}" maxlength="6" required>

        <label for="pin_conf">Confirme o PIN</label>
        <input type="password" id="pin_conf" name="pin_conf" pattern="\d{6}" maxlength="6" required>

        <button type="submit">Salvar PIN e Entrar</button>
    </form>

    <p style="margin-top:12px;"><a href="login.php">Voltar ao login</a></p>
</div>

<?php include('footer.php'); ?>
