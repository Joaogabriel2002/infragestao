<?php
// /admin/categorias/index.php

require_once '../../includes/header.php'; // Sobe 2 níveis

// Segurança: Só Admins
if ($usuario_role_logado != 'ADMIN') {
    echo "<p>Acesso negado.</p>";
    require_once '../../includes/footer.php';
    exit;
}

// Lógica de Processamento (Salvar/Deletar)
$feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao']) && $_POST['acao'] == 'nova_categoria') {
        $nome = $_POST['nome_categoria'];
        if (!empty($nome)) {
            // A tabela é 'categorias' (de chamados)
            $stmt = $pdo->prepare("INSERT INTO categorias (nome_categoria) VALUES (?)");
            $stmt->execute([$nome]);
            $feedback = "<div class='bg-green-100 text-green-700 p-3 rounded mb-4'>Categoria salva com sucesso!</div>";
        }
    }
}

// Busca as categorias existentes
$lista_categorias = $pdo->query("SELECT * FROM categorias ORDER BY nome_categoria")->fetchAll();

?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    
    <div class="md:col-span-1">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Nova Categoria (de Chamados)</h2>
            <?= $feedback ?>
            <form action="index.php" method="POST">
                <input type="hidden" name="acao" value="nova_categoria">
                <div class="mb-4">
                    <label for="nome_categoria" class="block text-gray-700 font-semibold mb-2">Nome da Categoria</label>
                    <input type="text" id="nome_categoria" name="nome_categoria" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg" 
                           placeholder="Ex: Hardware, Software, Impressora" required>
                </div>
                <button type="submit" 
                        class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700">
                    Salvar Categoria
                </button>
            </form>
        </div>
    </div>

    <div class="md:col-span-2">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Categorias Cadastradas</h2>
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 text-left text-gray-600 font-semibold">ID</th>
                        <th class="py-2 px-4 text-left text-gray-600 font-semibold">Nome</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($lista_categorias) > 0): ?>
                        <?php foreach ($lista_categorias as $categoria): ?>
                            <tr class="border-b">
                                <td class="py-2 px-4"><?= $categoria['id_categoria'] ?></td>
                                <td class="py-2 px-4"><?= htmlspecialchars($categoria['nome_categoria']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" class="py-4 text-center text-gray-500">Nenhuma categoria cadastrada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once '../../includes/footer.php'; // Sobe 2 níveis
?>