<?php
// /login_processar.php (ATUALIZADO)

session_start();
require_once 'config/conexao.php'; // $pdo estará disponível aqui

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['email']) || empty($_POST['senha'])) {
    header('Location: login.php?erro=1');
    exit;
}

try {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // ***** MUDANÇA AQUI *****
    // Adicionamos a coluna 'avatar_path' na query
    $sql = "SELECT id_usuario, nome, email, senha_hash, role, avatar_path 
            FROM usuarios 
            WHERE email = ? AND ativo = true";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
        
        session_regenerate_id(true); 

        // ***** MUDANÇA AQUI *****
        // Salvamos os 4 dados na sessão
        $_SESSION['usuario_id'] = $usuario['id_usuario'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_role'] = $usuario['role'];
        $_SESSION['usuario_avatar'] = $usuario['avatar_path']; // O novo dado!

        header('Location: index.php');
        exit;

    } else {
        header('Location: login.php?erro=1');
        exit;
    }

} catch (PDOException $e) {
    die("Ocorreu um erro ao tentar logar. Tente novamente.");
}