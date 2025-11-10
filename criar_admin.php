<?php
// /criar_admin.php
// ATENÇÃO: DELETE ESTE ARQUIVO APÓS USAR!

require_once 'config/conexao.php';

echo "<h1>Script de Criação do Admin</h1>";

try {
    // --- Definições do seu primeiro Admin ---
    $nome = "Administrador";
    $email = "admin@admin.com";
    $senha_para_logar = "123456"; // Mude para uma senha forte
    $role = "ADMIN";
    // ----------------------------------------

    // Criptografa a senha com o método seguro do PHP
    $senha_hash = password_hash($senha_para_logar, PASSWORD_DEFAULT);

    // Prepara o SQL para inserir o usuário
    $sql = "INSERT INTO usuarios (nome, email, senha_hash, role, ativo) 
            VALUES (?, ?, ?, ?, true)";
            
    $stmt = $pdo->prepare($sql);
    
    // Executa a query
    $stmt->execute([$nome, $email, $senha_hash, $role]);

    echo "<p>Usuário <strong>$nome</strong> ($email) criado com sucesso!</p>";
    echo "<p>A senha é: <strong>$senha_para_logar</strong></p>";
    echo "<hr>";
    echo "<h2>!!! IMPORTANTE !!!</h2>";
    echo "<p>Agora você pode ir para <a href='login.php'>login.php</a> e entrar.</p>";
    echo "<p style='color:red; font-weight:bold;'>DELETE ESTE ARQUIVO ('criar_admin.php') AGORA MESMO!</p>";

} catch (PDOException $e) {
    // Verifica se o erro é de "email duplicado"
    if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'Duplicate entry')) {
        echo "<h2 style='color:orange;'>AVISO: O usuário ($email) já existe.</h2>";
        echo "<p>Se você esqueceu a senha, terá que resetá-la manualmente no banco de dados.</p>";
        echo "<p>Vá para <a href='login.php'>login.php</a>.</p>";
    } else {
        // Outro erro de banco
        echo "<h2 style='color:red;'>Erro ao criar usuário:</h2>";
        echo "<p>" . $e->getMessage() . "</p>";
    }
}