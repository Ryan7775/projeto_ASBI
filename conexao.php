<?php
$host = "localhost";     // servidor do MySQL (XAMPP usa localhost)
$usuario = "root";       // usuário padrão do MySQL no XAMPP
$senha = "";             // senha padrão do XAMPP é vazia
$banco = "clinica";      // nome do database que você criou no phpMyAdmin

try {
    // Cria a conexão usando PDO
    $pdo = new PDO("mysql:host=$host;dbname=$banco;charset=utf8", $usuario, $senha);

    // Configura para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("❌ Erro ao conectar: " . $e->getMessage());
}
?>