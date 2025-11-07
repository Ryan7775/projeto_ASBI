 <?php
 if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("conexao.php");
include("header.php");

$feedback = ['message'=>'','type'=>''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_responsavel = trim($_POST['nome_responsavel']);
    $cpf = trim($_POST['cpf']);
    $telefone = trim($_POST['telefone']);
    $email = trim($_POST['email']);
    $cep = trim($_POST['cep']);
    $rua = trim($_POST['rua']);
    $numero = trim($_POST['numero']);
    $bairro = trim($_POST['bairro']);
    $cidade = trim($_POST['cidade']);
    $estado = trim($_POST['estado']);
    $senha = $_POST['senha'];
    $confirma_senha = $_POST['confirma_senha'];
    $nome_crianca = trim($_POST['nome_crianca']);
    $data_nascimento = $_POST['data_nascimento'];
    $nome_mae = trim($_POST['nome_mae']);
    $sexo = $_POST['sexo'];

    // Valida
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $feedback = ['message'=>"❌ E‑mail em formato inválido.", 'type'=>'error'];
    }
    elseif (strlen($senha) < 6) {
        $feedback = ['message'=>"❌ A senha deve ter pelo menos 6 caracteres.", 'type'=>'error'];
    }
    elseif ($senha !== $confirma_senha) {
        $feedback = ['message'=>"❌ As senhas não coincidem.", 'type'=>'error'];
    }
    else {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare(
              "INSERT INTO clientes (nome_responsavel, cpf, telefone, email, cep, rua, numero, bairro, cidade, estado, senha, nome_crianca, data_nascimento, nome_mae, sexo)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$nome_responsavel, $cpf, $telefone, $email, $cep, $rua, $numero, $bairro, $cidade, $estado, $senha_hash, $nome_crianca, $data_nascimento, $nome_mae, $sexo]);

            // seta sessão temporária para configurar 2FA imediatamente
            $_SESSION['pending_2fa'] = ['id' => $pdo->lastInsertId(), 'tipo' => 'cliente'];
            header("Location: set_2fa.php");
            exit();
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $feedback = ['message'=>"❌ Erro: O CPF ou E‑mail informado já está cadastrado.", 'type'=>'error'];
            } else {
                $feedback = ['message'=>"❌ Erro ao cadastrar: " . $e->getMessage(), 'type'=>'error'];
            }
        }
    }
}
?>

<div class="form-container">
    <h2>Cadastro de Associado</h2>
    <?php if (!empty($feedback['message'])): ?>
        <div class="feedback-message <?php echo $feedback['type']; ?>"><?php echo $feedback['message']; ?></div>
    <?php endif; ?>

    <form id="formCliente" method="POST" action="cadastro_cliente.php">
        <h4>Dados do Responsável</h4>
        <label>Nome Completo do Responsável:</label>
        <input type="text" name="nome_responsavel" required>

        <label>CPF:</label>
        <input type="text" name="cpf" id="cpf" required>

        <label>Telefone:</label>
        <input type="text" name="telefone" id="telefone">

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Senha (mín. 6 caracteres):</label>
        <input type="password" name="senha" required minlength="6">

        <label>Confirmar Senha:</label>
        <input type="password" name="confirma_senha" required>

        <h4>Dados da Criança</h4>
        <label>Nome Completo da Criança:</label>
        <input type="text" name="nome_crianca" required>

        <label>Data de Nascimento:</label>
        <input type="date" name="data_nascimento" required>

        <label>Nome da Mãe:</label>
        <input type="text" name="nome_mae" required>

        <label>Sexo:</label>
        <select name="sexo" required>
            <option value="">Selecione...</option>
            <option value="M">Masculino</option>
            <option value="F">Feminino</option>
        </select>

        <h4>Endereço:</h4>
        <label>CEP:</label>
        <input type="text" name="cep" id="cep">

        <label>Rua:</label>
        <input type="text" name="rua" id="rua">

        <label>Número:</label>
        <input type="text" name="numero">

        <label>Bairro:</label>
        <input type="text" name="bairro" id="bairro">

        <label>Cidade:</label>
        <input type="text" name="cidade" id="cidade">

        <label>Estado:</label>
        <input type="text" name="estado" id="estado">

        <button type="submit">Cadastrar</button>
    </form>
</div>

<?php include('footer.php'); ?>
<!-- Validador e máscaras para CPF, CEP e CRO - colar antes do include('footer.php') -->

<script>
function onlyDigits(str) {
    return str.replace(/\D/g, '');
}

function maskCPF(value) {
    const v = onlyDigits(value).slice(0,11);
    let out = v;
    if (v.length > 9) out = v.replace(/^(\d{3})(\d{3})(\d{3})(\d{2}).*/, '$1.$2.$3-$4');
    else if (v.length > 6) out = v.replace(/^(\d{3})(\d{3})(\d{1,3}).*/, '$1.$2.$3');
    else if (v.length > 3) out = v.replace(/^(\d{3})(\d{1,3}).*/, '$1.$2');
    return out;
}

function maskCEP(value) {
    const v = onlyDigits(value).slice(0,8);
    if (v.length > 5) return v.replace(/^(\d{5})(\d{1,3}).*/, '$1-$2');
    return v;
}

function validaCPF(cpf) {
    cpf = onlyDigits(cpf);
    if (cpf.length !== 11) return false;
    if (/^(\d)\1+$/.test(cpf)) return false;

    let sum = 0;
    for (let i = 0; i < 9; i++) sum += parseInt(cpf.charAt(i)) * (10 - i);
    let rev = 11 - (sum % 11);
    if (rev === 10 || rev === 11) rev = 0;
    if (rev !== parseInt(cpf.charAt(9))) return false;

    sum = 0;
    for (let i = 0; i < 10; i++) sum += parseInt(cpf.charAt(i)) * (11 - i);
    rev = 11 - (sum % 11);
    if (rev === 10 || rev === 11) rev = 0;
    if (rev !== parseInt(cpf.charAt(10))) return false;

    return true;
}

function validaCEP(cep) {
    const digits = onlyDigits(cep);
    return /^\d{8}$/.test(digits);
}

function validaCRO(cro) {
    const c = cro.trim();
    return /^[A-Za-z0-9-]{4,12}$/.test(c);
}

document.addEventListener('DOMContentLoaded', function() {
    const cpfInput = document.querySelector('#cpf');
    const cepInput = document.querySelector('#cep');
    const croInput = document.querySelector('input[name="cro"]');
    const form = document.querySelector('#formMedico');

    const ruaInput = document.querySelector('#rua');
    const bairroInput = document.querySelector('#bairro');
    const cidadeInput = document.querySelector('#cidade');
    const estadoInput = document.querySelector('#estado');

    if (cpfInput) {
        cpfInput.addEventListener('input', function() {
            this.value = maskCPF(this.value);
            this.setSelectionRange(this.value.length, this.value.length);
        });
    }

    if (cepInput) {
        cepInput.addEventListener('input', function() {
            this.value = maskCEP(this.value);
            this.setSelectionRange(this.value.length, this.value.length);
        });

        cepInput.addEventListener('blur', function() {
            const cep = onlyDigits(this.value);
            if (cep.length === 8) {
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            if (ruaInput) ruaInput.value = data.logradouro || '';
                            if (bairroInput) bairroInput.value = data.bairro || '';
                            if (cidadeInput) cidadeInput.value = data.localidade || '';
                            if (estadoInput) estadoInput.value = data.uf || '';
                        } else {
                            alert('❌ CEP não encontrado.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('❌ Erro ao buscar CEP.');
                    });
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            const cpfVal = cpfInput ? cpfInput.value : '';
            const cepVal = cepInput ? cepInput.value : '';
            const croVal = croInput ? croInput.value : '';

            if (!validaCPF(cpfVal)) {
                e.preventDefault();
                alert('❌ CPF inválido.');
                if (cpfInput) cpfInput.focus();
                return false;
            }

            if (!validaCEP(cepVal)) {
                e.preventDefault();
                alert('❌ CEP inválido.');
                if (cepInput) cepInput.focus();
                return false;
            }

            if (!validaCRO(croVal)) {
                e.preventDefault();
                alert('❌ CRO inválido.');
                if (croInput) croInput.focus();
                return false;
            }
        });
    }
});
</script>
