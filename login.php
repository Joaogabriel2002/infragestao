<?php
// /login.php

// Inicia a sessão. Isso é fundamental para o login.
session_start();

// Se o usuário JÁ ESTIVER LOGADO, ele não deve ver a tela de login.
// Redireciona ele para o dashboard (index.php).
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

// Inclui a conexão com o banco (mas não é usada aqui, será no processar)
// include_once 'config/conexao.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Chamados v3.0</title>
    
    <style>
        body { font-family: Arial, sans-serif; background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-container { background-color: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 300px; }
        .login-container h2 { text-align: center; margin-bottom: 1.5rem; }
        .login-container div { margin-bottom: 1rem; }
        .login-container label { display: block; margin-bottom: 0.5rem; }
        .login-container input { width: 100%; padding: 0.5rem; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        .login-container button { width: 100%; padding: 0.7rem; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        .login-container button:hover { background-color: #0056b3; }
        .error-message { background-color: #f8d7da; color: #721c24; padding: 0.5rem; border-radius: 4px; text-align: center; margin-bottom: 1rem; }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Sistema de Chamados</h2>
        
        <?php
        // Se houver uma mensagem de erro (vinda do processar.php), mostre-a
        if (isset($_GET['erro'])) {
            echo '<div class="error-message">Usuário ou senha inválidos.</div>';
        }
        ?>

        <form action="login_processar.php" method="POST">
            <div>
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div>
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <button type="submit">Entrar</button>
        </form>
    </div>

</body>
</html>