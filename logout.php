 <?php
// logout_admin.php
session_start();
// Limpa todas as variáveis de sessão relacionadas ao 2FA e login
if (isset($_SESSION['pre_2fa'])) unset($_SESSION['pre_2fa']);
if (isset($_SESSION['pending_2fa'])) unset($_SESSION['pending_2fa']);
if (isset($_SESSION['admin_logado'])) unset($_SESSION['admin_logado']);
session_unset();
session_destroy();
session_write_close();
setcookie(session_name(),'',0,'/');
header("Location: index.html"); 
exit;
