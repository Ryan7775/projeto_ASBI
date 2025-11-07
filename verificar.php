 <?php
session_start();
include("conexao.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $codigoDigitado = trim($_POST['code']);

    // Verifica se tem exatamente 6 números
    if (!preg_match('/^[0-9]{6}$/', $codigoDigitado)) {
        echo "<h2>⚠️ O código precisa ter exatamente 6 números!</h2>";
        echo '<a href="2fa.html">Voltar</a>';
        exit;
    }

    // Consulta o código na tabela admin_2fa
    $stmt = $pdo->prepare("SELECT * FROM admin_2fa WHERE codigo = :codigo");
    $stmt->execute(['codigo' => $codigoDigitado]);
    $acesso = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($acesso) {
        $_SESSION['admin_logado'] = true; // agora só marca como logado
        header("Location: painel_admin.php");
        exit;
    } else {
        echo "<h2>❌ Código incorreto!</h2>";
        echo '<a href="2fa.html">Voltar</a>';
    }
} else {
    header("Location: 2fa.html");
    exit;
}
?>
