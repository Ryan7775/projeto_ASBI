<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('conexao.php');
include('header.php');

$pre = $_SESSION['pre_2fa'] ?? null;
if (!$pre) {
    header('Location: login.php');
    exit();
}

$feedback = ['message'=>'','type'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pin = trim($_POST['pin'] ?? '');
    if (!preg_match('/^\d{6}$/', $pin)) {
        $feedback = ['message' => '❌ PIN inválido. Use 6 dígitos numéricos.', 'type' => 'error'];
    } else {
        $id = (int)$pre['id'];
        $tipo = $pre['tipo'];
        $table = ($tipo === 'medico') ? 'medicos' : 'clientes';
        // Busca hash
        try {
            $stmt = $pdo->prepare("SELECT twofa_pin FROM {$table} WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $row = false;
        }

        if (!$row || empty($row['twofa_pin'])) {
            $feedback = ['message' => '❌ PIN 2FA não configurado para esta conta.', 'type' => 'error'];
        } else {
            if (password_verify($pin, $row['twofa_pin'])) {
                // Completa login
                session_regenerate_id(true);
                $_SESSION['id'] = $id;
                $_SESSION['nome'] = $pre['nome'] ?? '';
                $_SESSION['tipo'] = $tipo;
                unset($_SESSION['pre_2fa']);

                // Atualiza ultimo_login
                try {
                    $stmt2 = $pdo->prepare("UPDATE {$table} SET ultimo_login = NOW() WHERE id = ?");
                    $stmt2->execute([$id]);
                } catch (Exception $ex) {}

                $dashboard = ($tipo == 'medico') ? 'painel_medico.php' : 'painel_cliente.php';
                header('Location: ' . $dashboard);
                exit();
            } else {
                $feedback = ['message' => '❌ PIN incorreto.', 'type' => 'error'];
            }
        }
    }
}
?>

<div class="form-container">
    <h2>Verificação 2FA</h2>
    <p>Insira o PIN de 6 dígitos configurado para sua conta.</p>

    <?php if (!empty($feedback['message'])): ?>
        <div class="feedback-message <?php echo $feedback['type']; ?>"><?php echo $feedback['message']; ?></div>
    <?php endif; ?>

    <form method="POST" action="twofa.php">
        <label for="pin">PIN</label>
        <input type="password" id="pin" name="pin" pattern="\d{6}" maxlength="6" required>
        <button type="submit">Verificar</button>
    </form>

    <p style="margin-top:12px;"><a href="login.php">Voltar ao login</a></p>
</div>

<?php include('footer.php'); ?> 