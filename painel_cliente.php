<?php
// filepath: c:\xampp\htdocs\FRONT-ASBI\painel_cliente.php
session_start();

// Verifica se o usuário está logado, passou pelo 2FA se necessário, e é um cliente
if (!isset($_SESSION['id']) || $_SESSION['tipo'] != 'cliente' || isset($_SESSION['pre_2fa'])) {
    if (isset($_SESSION['pre_2fa'])) {
        header("Location: twofa.php");
    } else {
        header("Location: login.php");
    }
    exit();
}

// Simular dados do cliente (em produção, buscar do banco de dados)
$cliente = [
    'nome' => $_SESSION['nome'],
    'cpf' => '123.456.789-00',
    'rg' => '12.345.678-9',
    'data_nascimento' => '15/03/1990',
    'telefone' => '(11) 99999-9999',
    'email' => $_SESSION['email'] ?? 'cliente@exemplo.com',
    'endereco' => 'Rua das Flores, 123 - São Paulo, SP',
    'cep' => '01234-567',
    'plano' => 'ASBI Básico',
    'numero_carteira' => 'ASBI-2024-0001',
    'validade' => '31/12/2024',
    'status' => 'Ativo',
    'ultimo_login' => date('d/m/Y H:i')
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Cliente - ASBI</title>
    <style>
:root {
    --primary: #2563eb;
    --primary-dark: #1d4ed8;
    --primary-light: #dbeafe;
    --secondary: #f8fafc;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --text: #1e293b;
    --text-muted: #64748b;
    --border: #e2e8f0;
    --bg: #f8fafc;
    --card: #ffffff;
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    --radius: 12px;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: var(--bg);
    color: var(--text);
    overflow-x: hidden;
}

/* Container principal */
.dashboard-container {
    display: flex;
    min-height: 100vh;
    position: relative;
}

/* Toggle Button - SEMPRE VISÍVEL */
.sidebar-toggle {
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 1002;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 12px;
    font-size: 18px;
    cursor: pointer;
    box-shadow: var(--shadow-lg);
    transition: all 0.3s ease;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.sidebar-toggle:hover {
    background: var(--primary-dark);
    transform: scale(1.05);
}

.sidebar-toggle.active {
    left: 340px;
}

/* Menu Lateral */
.sidebar {
    width: 320px;
    background: var(--card);
    box-shadow: var(--shadow-lg);
    position: fixed;
    top: 0;
    left: -320px; /* Inicialmente escondido */
    height: 100vh;
    overflow-y: auto;
    z-index: 1001;
    transition: left 0.3s ease;
}

.sidebar.open {
    left: 0; /* Quando aberto */
}

.sidebar-header {
    padding: 24px 20px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    text-align: center;
    margin-top: 60px; /* Espaço para o botão toggle */
}

.user-avatar {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    margin: 0 auto 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: bold;
    border: 3px solid rgba(255, 255, 255, 0.3);
}

.user-info h3 {
    font-size: 18px;
    margin-bottom: 4px;
    font-weight: 600;
}

.user-info p {
    opacity: 0.9;
    font-size: 14px;
    margin-bottom: 2px;
}

/* Informações de Login */
.login-info {
    background: rgba(255, 255, 255, 0.1);
    margin: 16px 0;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.login-info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    font-size: 12px;
}

.login-info-item:last-child {
    margin-bottom: 0;
}

.login-info-label {
    opacity: 0.8;
    font-weight: 500;
}

.login-info-value {
    font-weight: 600;
    background: rgba(255, 255, 255, 0.2);
    padding: 2px 8px;
    border-radius: 12px;
}

.status-online {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.status-dot {
    width: 6px;
    height: 6px;
    background: #10b981;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.sidebar-nav {
    padding: 20px 0;
}

.nav-item {
    display: flex;
    align-items: center;
    padding: 14px 20px;
    color: var(--text);
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
    font-weight: 500;
}

.nav-item:hover, .nav-item.active {
    background: var(--primary-light);
    border-left-color: var(--primary);
    color: var(--primary);
}

.nav-item span {
    width: 24px;
    margin-right: 12px;
    font-size: 18px;
    text-align: center;
}

/* Separador no menu */
.nav-separator {
    height: 1px;
    background: var(--border);
    margin: 12px 20px;
}

/* Item de logout destacado */
.nav-item.logout {
    border-top: 1px solid var(--border);
    margin-top: 12px;
    color: var(--danger);
}

.nav-item.logout:hover {
    background: rgba(239, 68, 68, 0.1);
    border-left-color: var(--danger);
    color: var(--danger);
}

/* Conteúdo Principal */
.main-content {
    flex: 1;
    margin-left: 0; /* Sem margem inicial */
    padding: 30px; /* Removido padding superior extra */
    min-height: 100vh;
    background: linear-gradient(135deg, var(--primary-light) 0%, var(--bg) 100%);
    transition: margin-left 0.3s ease;
}

.main-content.shifted {
    margin-left: 320px; /* Quando sidebar está aberto */
}

.content-header {
    margin-bottom: 30px;
    margin-top: 60px; /* Espaço para o botão toggle */
}

.content-header h1 {
    font-size: 28px;
    color: var(--text);
    margin-bottom: 8px;
    font-weight: 700;
}

.content-header p {
    color: var(--text-muted);
    font-size: 16px;
}

/* Header com informações rápidas */
.quick-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--card);
    padding: 16px 24px;
    border-radius: var(--radius);
    margin-bottom: 24px;
    box-shadow: var(--shadow);
    border-left: 4px solid var(--primary);
}

.quick-info-item {
    text-align: center;
}

.quick-info-label {
    font-size: 12px;
    color: var(--text-muted);
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.quick-info-value {
    font-size: 18px;
    font-weight: 700;
    color: var(--text);
}

/* Cards Grid */
.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 24px;
    margin-bottom: 30px;
}

.info-card {
    background: var(--card);
    border-radius: var(--radius);
    padding: 24px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.info-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.card-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid var(--primary-light);
}

.card-header span {
    width: 40px;
    height: 40px;
    background: var(--primary-light);
    color: var(--primary);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    font-size: 18px;
}

.card-header h3 {
    font-size: 18px;
    color: var(--text);
    font-weight: 600;
}

/* Carteirinha Digital */
.carteirinha {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    border-radius: var(--radius);
    padding: 24px;
    position: relative;
    overflow: hidden;
    grid-column: span 2;
}

.carteirinha::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 200px;
    height: 200px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
}

.carteirinha-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    position: relative;
    z-index: 2;
}

.logo-asbi {
    font-size: 24px;
    font-weight: bold;
}

.numero-carteira {
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 16px;
    border-radius: 25px;
    font-size: 12px;
    font-weight: 600;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.carteirinha-body {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-top: 20px;
    position: relative;
    z-index: 2;
}

.carteirinha-info {
    display: flex;
    flex-direction: column;
}

.info-label {
    font-size: 12px;
    opacity: 0.8;
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    font-size: 14px;
    font-weight: 600;
}

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-ativo {
    background: rgba(16, 185, 129, 0.1);
    color: var(--success);
    border: 1px solid var(--success);
}

/* Botões de Ação */
.action-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-top: 30px;
}

.action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px 24px;
    background: var(--card);
    border: 2px solid var(--border);
    border-radius: var(--radius);
    text-decoration: none;
    color: var(--text);
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: var(--shadow);
}

.action-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.action-btn span {
    margin-right: 8px;
    font-size: 18px;
}

/* Overlay para quando sidebar estiver aberto */
.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.sidebar-overlay.active {
    opacity: 1;
    visibility: visible;
}

/* Responsividade */
@media (max-width: 768px) {
    .main-content.shifted {
        margin-left: 0; /* Em mobile, não desloca o conteúdo */
    }
    
    .sidebar-toggle.active {
        left: 20px; /* Mantém posição em mobile */
    }
    
    .cards-grid {
        grid-template-columns: 1fr;
    }
    
    .carteirinha {
        grid-column: span 1;
    }
    
    .carteirinha-body {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .action-buttons {
        grid-template-columns: 1fr;
    }
    
    .quick-info {
        flex-direction: column;
        gap: 16px;
    }
}

@media (max-width: 480px) {
    .main-content {
        padding: 15px;
    }
    
    .content-header h1 {
        font-size: 24px;
    }
    
    .cards-grid {
        gap: 16px;
    }
    
    .info-card {
        padding: 20px;
    }
}

/* Animação do ícone do toggle */
.sidebar-toggle .toggle-icon {
    transition: transform 0.3s ease;
}

.sidebar-toggle.active .toggle-icon {
    transform: rotate(90deg);
}
</style>
</head>

<body>
    <!-- Botão Toggle do Menu -->
    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
        <span class="toggle-icon">☰</span>
    </button>

    <!-- Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="dashboard-container">
        <!-- Menu Lateral -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($cliente['nome'], 0, 2)); ?>
                </div>
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($cliente['nome']); ?></h3>
                    <p>Cliente ASBI</p>
                    <p>Carteira: <?php echo $cliente['numero_carteira']; ?></p>
                </div>
                
                <!-- Informações de Login -->
                <div class="login-info">
                    <div class="login-info-item">
                        <span class="login-info-label">Status:</span>
                        <span class="login-info-value status-online">
                            <span class="status-dot"></span>
                            Online
                        </span>
                    </div>
                    <div class="login-info-item">
                        <span class="login-info-label">Último login:</span>
                        <span class="login-info-value"><?php echo $cliente['ultimo_login']; ?></span>
                    </div>
                    <div class="login-info-item">
                        <span class="login-info-label">ID Sessão:</span>
                        <span class="login-info-value"><?php echo $_SESSION['id']; ?></span>
                    </div>
                    <div class="login-info-item">
                        <span class="login-info-label">Plano:</span>
                        <span class="login-info-value"><?php echo $cliente['plano']; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="sidebar-nav">
                <a href="#" class="nav-item active" onclick="setActiveItem(this)">
                    <span>🏠</span> Dashboard
                </a>
                <a href="#" class="nav-item" onclick="setActiveItem(this)">
                    <span>📅</span> Agendamentos
                </a>
                <a href="#" class="nav-item" onclick="setActiveItem(this)">
                    <span>🦷</span> Histórico Médico
                </a>
                <a href="#" class="nav-item" onclick="setActiveItem(this)">
                    <span>👤</span> Meus Dados
                </a>
                <a href="#" class="nav-item" onclick="setActiveItem(this)">
                    <span>💳</span> Carteirinha Digital
                </a>
                <a href="#" class="nav-item" onclick="setActiveItem(this)">
                    <span>📊</span> Relatórios
                </a>
                <a href="#" class="nav-item" onclick="setActiveItem(this)">
                    <span>⚙️</span> Configurações
                </a>
                
                <div class="nav-separator"></div>
                
                <a href="logout.php" class="nav-item logout">
                    <span>🚪</span> Sair
                </a>
            </div>
        </nav>

        <!-- Conteúdo Principal -->
        <main class="main-content" id="mainContent">
            <div class="content-header">
                <h1>Bem-vindo, <?php echo htmlspecialchars($cliente['nome']); ?>!</h1>
                <p>Gerencie suas informações e acompanhe seus cuidados de saúde bucal</p>
            </div>

            <!-- Informações Rápidas -->
            <div class="quick-info">
                <div class="quick-info-item">
                    <div class="quick-info-label">Hoje</div>
                    <div class="quick-info-value"><?php echo date('d/m/Y'); ?></div>
                </div>
                <div class="quick-info-item">
                    <div class="quick-info-label">Horário</div>
                    <div class="quick-info-value" id="current-time"><?php echo date('H:i'); ?></div>
                </div>
                <div class="quick-info-item">
                    <div class="quick-info-label">Próxima Consulta</div>
                    <div class="quick-info-value">Nenhuma</div>
                </div>
                <div class="quick-info-item">
                    <div class="quick-info-label">Status da Conta</div>
                    <div class="quick-info-value status-online">
                        <span class="status-dot"></span>
                        <?php echo $cliente['status']; ?>
                    </div>
                </div>
            </div>

            <div class="cards-grid">
                <!-- Carteirinha Digital -->
                <div class="carteirinha">
                    <div class="carteirinha-header">
                        <div class="logo-asbi">ASBI</div>
                        <div class="numero-carteira"><?php echo $cliente['numero_carteira']; ?></div>
                    </div>
                    
                    <div style="border-bottom: 1px solid rgba(255,255,255,0.3); padding-bottom: 12px; margin-bottom: 16px; position: relative; z-index: 2;">
                        <h3><?php echo htmlspecialchars($cliente['nome']); ?></h3>
                        <p style="opacity: 0.9;">Associação de Saúde Bucal Infantil</p>
                    </div>

                    <div class="carteirinha-body">
                        <div class="carteirinha-info">
                            <span class="info-label">CPF</span>
                            <span class="info-value"><?php echo $cliente['cpf']; ?></span>
                        </div>
                        <div class="carteirinha-info">
                            <span class="info-label">Data Nasc.</span>
                            <span class="info-value"><?php echo $cliente['data_nascimento']; ?></span>
                        </div>
                        <div class="carteirinha-info">
                            <span class="info-label">Plano</span>
                            <span class="info-value"><?php echo $cliente['plano']; ?></span>
                        </div>
                        <div class="carteirinha-info">
                            <span class="info-label">Validade</span>
                            <span class="info-value"><?php echo $cliente['validade']; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Informações Pessoais -->
                <div class="info-card">
                    <div class="card-header">
                        <span>👤</span>
                        <h3>Dados Pessoais</h3>
                    </div>
                    <div style="display: grid; gap: 12px;">
                        <div>
                            <strong>Telefone:</strong> <?php echo $cliente['telefone']; ?>
                        </div>
                        <div>
                            <strong>E-mail:</strong> <?php echo $cliente['email']; ?>
                        </div>
                        <div>
                            <strong>Status:</strong> 
                            <span class="status-badge status-ativo"><?php echo $cliente['status']; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Endereço -->
                <div class="info-card">
                    <div class="card-header">
                        <span>📍</span>
                        <h3>Endereço</h3>
                    </div>
                    <div style="display: grid; gap: 12px;">
                        <div>
                            <strong>Endereço:</strong><br>
                            <?php echo $cliente['endereco']; ?>
                        </div>
                        <div>
                            <strong>CEP:</strong> <?php echo $cliente['cep']; ?>
                        </div>
                    </div>
                </div>

                <!-- Próximas Consultas -->
                <div class="info-card">
                    <div class="card-header">
                        <span>📅</span>
                        <h3>Próximas Consultas</h3>
                    </div>
                    <div style="text-align: center; padding: 20px;">
                        <p style="color: var(--text-muted); margin-bottom: 16px;">Nenhuma consulta agendada</p>
                        <a href="#" class="action-btn" style="display: inline-flex; padding: 12px 20px;">
                            <span>➕</span> Agendar Consulta
                        </a>
                    </div>
                </div>

                <!-- Último Atendimento -->
                <div class="info-card">
                    <div class="card-header">
                        <span>🦷</span>
                        <h3>Último Atendimento</h3>
                    </div>
                    <div style="display: grid; gap: 12px;">
                        <div>
                            <strong>Data:</strong> 15/09/2024
                        </div>
                        <div>
                            <strong>Procedimento:</strong> Limpeza e Profilaxia
                        </div>
                        <div>
                            <strong>Profissional:</strong> Dra. Ana Silva
                        </div>
                        <div>
                            <strong>Observações:</strong> Paciente em dia com higiene bucal
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação Rápida -->
            <div class="action-buttons">
                <a href="#" class="action-btn">
                    <span>📅</span> Agendar Nova Consulta
                </a>
                <a href="#" class="action-btn">
                    <span>📋</span> Ver Histórico Completo
                </a>
                <a href="#" class="action-btn">
                    <span>💳</span> Baixar Carteirinha
                </a>
                <a href="#" class="action-btn">
                    <span>📞</span> Entrar em Contato
                </a>
            </div>
        </main>
    </div>

    <script>
    let sidebarOpen = false;

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggleBtn = document.getElementById('sidebarToggle');
        const mainContent = document.getElementById('mainContent');
        
        sidebarOpen = !sidebarOpen;
        
        if (sidebarOpen) {
            sidebar.classList.add('open');
            overlay.classList.add('active');
            toggleBtn.classList.add('active');
            
            // Em desktop, desloca o conteúdo
            if (window.innerWidth > 768) {
                mainContent.classList.add('shifted');
            }
        } else {
            closeSidebar();
        }
    }

    function closeSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggleBtn = document.getElementById('sidebarToggle');
        const mainContent = document.getElementById('mainContent');
        
        sidebarOpen = false;
        
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
        toggleBtn.classList.remove('active');
        mainContent.classList.remove('shifted');
    }

    function setActiveItem(element) {
        // Remove active de todos os itens
        document.querySelectorAll('.nav-item').forEach(item => {
            item.classList.remove('active');
        });
        
        // Adiciona active ao item clicado
        element.classList.add('active');
        
        // Fecha o menu em mobile após clicar
        if (window.innerWidth <= 768) {
            closeSidebar();
        }
    }

    // Atualizar horário em tempo real
    function updateTime() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('pt-BR', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        const timeElement = document.getElementById('current-time');
        if (timeElement) {
            timeElement.textContent = timeStr;
        }
    }

    // Atualizar a cada minuto
    setInterval(updateTime, 60000);

    // Fechar sidebar quando redimensionar para desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768 && sidebarOpen) {
            document.getElementById('mainContent').classList.add('shifted');
        } else if (window.innerWidth <= 768) {
            document.getElementById('mainContent').classList.remove('shifted');
        }
    });

    // Atalho de teclado para toggle (Ctrl + M)
    document.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.key === 'm') {
            e.preventDefault();
            toggleSidebar();
        }
    });

    // Fechar com ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebarOpen) {
            closeSidebar();
        }
    });
    </script>
</body>
</html>