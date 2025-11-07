<?php
include("conexão.php");

echo "<h2>🔍 Verificação do Banco de Dados</h2>";

try {
    // Verifica se o banco existe e a conexão está funcionando
    echo "✅ Conexão com o banco 'clinica' estabelecida com sucesso!<br><br>";
    
    // Verifica se as tabelas existem
    $tabelas = ['clientes', 'medicos'];
    
    foreach ($tabelas as $tabela) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$tabela]);
        
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabela '$tabela' existe<br>";
            
            // Mostra a estrutura da tabela
            $stmt_desc = $pdo->query("DESCRIBE $tabela");
            $colunas = $stmt_desc->fetchAll(PDO::FETCH_ASSOC);
            
            echo "&nbsp;&nbsp;&nbsp;Colunas: ";
            $nomes_colunas = array_column($colunas, 'Field');
            echo implode(', ', $nomes_colunas) . "<br><br>";
            
        } else {
            echo "❌ Tabela '$tabela' NÃO existe<br>";
            echo "&nbsp;&nbsp;&nbsp;🔧 Você precisa criar esta tabela no phpMyAdmin<br><br>";
        }
    }
    
    echo "<hr>";
    echo "<h3>📋 SQL para criar as tabelas (se necessário):</h3>";
    echo "<pre>";
    echo "-- Tabela de clientes
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_responsavel VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    telefone VARCHAR(15),
    email VARCHAR(100) UNIQUE NOT NULL,
    cep VARCHAR(9),
    rua VARCHAR(150),
    numero VARCHAR(10),
    bairro VARCHAR(50),
    cidade VARCHAR(50),
    estado VARCHAR(2),
    senha VARCHAR(255) NOT NULL,
    nome_crianca VARCHAR(100) NOT NULL,
    data_nascimento DATE NOT NULL,
    nome_mae VARCHAR(100) NOT NULL,
    sexo ENUM('M', 'F') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de médicos
CREATE TABLE medicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cro VARCHAR(20) UNIQUE NOT NULL,
    especialidade VARCHAR(100),
    email VARCHAR(100) UNIQUE NOT NULL,
    telefone VARCHAR(15),
    senha VARCHAR(255) NOT NULL,
    nome_clinica VARCHAR(150),
    cep VARCHAR(9),
    rua VARCHAR(150),
    numero VARCHAR(10),
    bairro VARCHAR(50),
    cidade VARCHAR(50),
    estado VARCHAR(2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "❌ Erro na verificação: " . $e->getMessage() . "<br>";
    echo "🔧 Possíveis soluções:<br>";
    echo "1. Certifique-se que o XAMPP está rodando<br>";
    echo "2. Verifique se o banco 'clinica' foi criado no phpMyAdmin<br>";
    echo "3. Confira as credenciais no arquivo conexão.php<br>";
}
?>