<?php

// =========================================================
// ARQUIVO DE CONEXÃO PDO (Moderno e Seguro)
// Salve como: /config/conexao.php

$db_host = 'localhost';      
$db_name = 'infragestao'; //
$db_user = 'root';           // Usuário do XAMPP (padrão é 'root')
$db_pass = '';               
$db_char = 'utf8mb4';        

// 2. Criar a "DSN" (Data Source Name)
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$db_char";

// 3. Opções do PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,      // Lança exceções em caso de erro
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,            // Retorna resultados como arrays associativos
    PDO::ATTR_EMULATE_PREPARES   => false,                       // Para segurança (evita SQL Injection)
];

// 4. Tentar a conexão
try {
     $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
     // Em caso de falha, exibe o erro.
     // Em produção, você não deve exibir a mensagem de erro,
     // mas sim logar o erro em um arquivo.
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Se chegou até aqui, a variável $pdo está pronta para ser usada!