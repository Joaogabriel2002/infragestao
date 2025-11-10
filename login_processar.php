<?php
// /login_processar.php

// 1. O Ponto de Partida OBRIGATÓRIO
// Inicia a sessão (tem que ser a primeira coisa no arquivo)
session_start();

// Inclui o arquivo de conexão com o banco
require_once 'config/conexao.php'; // $pdo estará disponível aqui

// 2. Validação Básica
// Verifica se os dados vieram via POST e se não estão vazios
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['email']) || empty($_POST['senha'])) {
    // Se não veio por POST ou está vazio, volta ao login
    header('Location: login.php?erro=1');
    exit;
}

// 3. O Processo de Login
try {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // 4. Buscar o Usuário no Banco
    // Prepara a query com "prepared statements" para EVITAR SQL INJECTION
    $sql = "SELECT id_usuario, nome, email, senha_hash, role 
            FROM usuarios 
            WHERE email = ? AND ativo = true";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    
    // Pega o resultado (um usuário ou nada)
    $usuario = $stmt->fetch();

    // 5. Verificar a Senha
    // password_verify() é a função mágica que compara a senha digitada
    // com o hash salvo no banco.
    if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
        
        // SUCESSO! Senha correta!
        
        // 6. Criar a Sessão
        // Regenera o ID da sessão (segurança contra "session fixation")
        session_regenerate_id(true); 

        // 7. Armazenar os dados do usuário na sessão
        // Estes dados ficarão disponíveis em TODAS as páginas
        $_SESSION['usuario_id'] = $usuario['id_usuario'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_role'] = $usuario['role']; // Ex: 'ADMIN', 'TECNICO'

        // 8. Redirecionar para o Dashboard
        header('Location: index.php');
        exit;

    } else {
        // FALHA! Usuário não encontrado ou senha errada
        header('Location: login.php?erro=1');
        exit;
    }

} catch (PDOException $e) {
    // Em caso de erro de banco, não mostre o erro real.
    // Em produção, você deveria logar $e->getMessage() em um arquivo.
    die("Ocorreu um erro ao tentar logar. Tente novamente.");
    // header('Location: login.php?erro=db');
}