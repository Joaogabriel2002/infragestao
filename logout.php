<?php
// /logout.php

// 1. Inicia a sessão (necessário para poder acessar a sessão atual)
session_start();

// 2. Limpa todas as variáveis da sessão
// Isso remove 'usuario_id', 'usuario_nome', etc.
$_SESSION = array();

// 3. Destrói a sessão
// Isso remove o cookie de sessão do navegador do usuário
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finaliza a sessão no servidor
session_destroy();

// 5. Redireciona de volta para a tela de login
header('Location: login.php');
exit;
?>