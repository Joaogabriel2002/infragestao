<?php
// Salve como: /teste_db.php

echo "<pre>"; // Para formatar a saída
echo "<h1>Teste de Conexão e Schema</h1>";

// 1. Inclui sua conexão
require_once 'config/conexao.php'; // $pdo, $db_name

// 2. Prova de Conexão
// Vamos ver qual banco a variável $db_name (do seu conexao.php) acha que está usando
echo "<h2>PASSO 1: Conexão</h2>";
echo "O arquivo 'conexao.php' está configurado para o banco: <strong>$db_name</strong>";
echo "<hr>";

// 3. Prova do Schema (A Prova Real)
// Vamos perguntar ao PHP (PDO) qual é a estrutura da tabela 'chamados'
// que ELE está enxergando.
echo "<h2>PASSO 2: O que o PHP realmente vê?</h2>";
echo "Executando: DESCRIBE chamados;";
echo "<hr>";

try {
    $stmt = $pdo->query("DESCRIBE chamados");
    $colunas = $stmt->fetchAll();

    echo "<h3>Colunas encontradas na tabela 'chamados' (pelo PDO):</h3>";
    
    $encontrou_titulo = false;
    
    foreach ($colunas as $coluna) {
        echo "<strong>Campo:</strong> " . htmlspecialchars($coluna['Field']);
        echo " | <strong>Tipo:</strong> " . htmlspecialchars($coluna['Type']) . "\n";
        
        if (strtolower($coluna['Field']) === 'titulo') {
            $encontrou_titulo = true;
        }
    }
    
    echo "<hr>";
    
    // 4. O Veredito
    echo "<h2>PASSO 3: Veredito</h2>";
    if ($encontrou_titulo) {
        echo "<h3 style='color:green;'>SUCESSO: A coluna 'titulo' FOI encontrada.</h3>";
        echo "Isso é estranho. Se o erro continua no index.php, pode ser um cache muito agressivo.";
    } else {
        echo "<h3 style='color:red;'>FALHA: A coluna 'titulo' NÃO FOI encontrada.</h3>";
        echo "<strong>Conclusão:</strong> O banco de dados '<strong>$db_name</strong>' (que o PHP está usando) NÃO tem a coluna 'titulo'.";
        echo "<br>Você precisa executar o script SQL v3.0 (que cria a coluna 'titulo') <strong>NESTE banco de dados</strong>.";
    }

} catch (PDOException $e) {
    echo "<h2 style='color:red;'>ERRO FATAL NO PASSO 2</h2>";
    echo "O PDO não conseguiu nem encontrar a tabela 'chamados'.";
    echo "<br>Mensagem: " . $e->getMessage();
}

echo "</pre>";
?>