<?php
// Configurações de segurança e funções auxiliares
session_start();

// Configurações de sessão mais seguras
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // ative em produção com HTTPS
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.gc_maxlifetime', 1800); // 30 minutos

// Função para registrar tentativas de login
function logAttempt($tipo, $identificador, $sucesso, $detalhes = '') {
    $ip = $_SERVER['REMOTE_ADDR'];
    $timestamp = date('Y-m-d H:i:s');
    $log = "$timestamp\t$ip\t$tipo\t$identificador\t" . ($sucesso ? 'OK' : 'FAIL') . "\t$detalhes\n";
    error_log($log, 3, __DIR__ . '/logs/auth.log');
}

// Verifica tentativas recentes do IP
function getRateLimit($ip, $tipo, $janela = 300) {
    $arquivo = __DIR__ . '/logs/ratelimit.json';
    $agora = time();
    $dados = [];
    
    if (file_exists($arquivo)) {
        $dados = json_decode(file_get_contents($arquivo), true) ?: [];
    }
    
    // Limpa registros antigos
    foreach ($dados as $checkIp => $tipos) {
        foreach ($tipos as $checkTipo => $tentativas) {
            $dados[$checkIp][$checkTipo] = array_filter($tentativas, function($timestamp) use ($agora, $janela) {
                return ($agora - $timestamp) < $janela;
            });
            if (empty($dados[$checkIp][$checkTipo])) {
                unset($dados[$checkIp][$checkTipo]);
            }
        }
        if (empty($dados[$checkIp])) {
            unset($dados[$checkIp]);
        }
    }
    
    // Salva dados limpos
    if (!is_dir(dirname($arquivo))) {
        mkdir(dirname($arquivo), 0755, true);
    }
    file_put_contents($arquivo, json_encode($dados));
    
    return isset($dados[$ip][$tipo]) ? count($dados[$ip][$tipo]) : 0;
}

// Registra nova tentativa para rate limit
function addRateLimit($ip, $tipo) {
    $arquivo = __DIR__ . '/logs/ratelimit.json';
    $dados = [];
    
    if (file_exists($arquivo)) {
        $dados = json_decode(file_get_contents($arquivo), true) ?: [];
    }
    
    if (!isset($dados[$ip])) {
        $dados[$ip] = [];
    }
    if (!isset($dados[$ip][$tipo])) {
        $dados[$ip][$tipo] = [];
    }
    
    $dados[$ip][$tipo][] = time();
    
    if (!is_dir(dirname($arquivo))) {
        mkdir(dirname($arquivo), 0755, true);
    }
    file_put_contents($arquivo, json_encode($dados));
}

// Função para verificar força da senha
function validarSenha($senha) {
    $erros = [];
    
    if (strlen($senha) < 8) {
        $erros[] = "A senha deve ter pelo menos 8 caracteres";
    }
    if (!preg_match('/[A-Z]/', $senha)) {
        $erros[] = "A senha deve conter pelo menos uma letra maiúscula";
    }
    if (!preg_match('/[a-z]/', $senha)) {
        $erros[] = "A senha deve conter pelo menos uma letra minúscula";
    }
    if (!preg_match('/[0-9]/', $senha)) {
        $erros[] = "A senha deve conter pelo menos um número";
    }
    if (!preg_match('/[^A-Za-z0-9]/', $senha)) {
        $erros[] = "A senha deve conter pelo menos um caractere especial";
    }
    
    return $erros;
}

// Função para sanitizar entrada
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Função para gerar token CSRF
function gerarCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Função para validar token CSRF
function validarCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Função para registrar ações sensíveis
function logAcaoSensivel($acao, $usuario_id, $detalhes = []) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $timestamp = date('Y-m-d H:i:s');
    $log = [
        'timestamp' => $timestamp,
        'ip' => $ip,
        'acao' => $acao,
        'usuario_id' => $usuario_id,
        'detalhes' => $detalhes,
        'user_agent' => $_SERVER['HTTP_USER_AGENT']
    ];
    
    if (!is_dir(__DIR__ . '/logs')) {
        mkdir(__DIR__ . '/logs', 0755, true);
    }
    
    error_log(
        json_encode($log) . "\n",
        3,
        __DIR__ . '/logs/audit.log'
    );
}