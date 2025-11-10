<?php
// /admin/categorias_ativo/index.php

require_once '../../includes/header.php'; // Sobe 2 níveis

// Segurança: Só Admins
if ($usuario_role_logado != 'ADMIN') {
    echo "<p>Acesso negado.</p>";
    require_once '../../includes/footer.php';
    exit;
}

// Lógica de Processamento (Salvar)
$feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao']) && $_POST['acao'] == 'nova_categoria_ativo') {
        $nome = $_POST['nome_categoria'];
        // O 'controla_estoque' vem como 'on' (se marcado) ou não vem (se desmarcado)
        $controla_estoque = isset($_POST['controla_estoque']) ? 1 : 0; 
        
        if (!empty($nome)) {
            $stmt = $pdo->prepare("INSERT INTO categorias_ativo (nome_categoria, controla_estoque) VALUES (?, ?)");
            $stmt->execute([$nome, $controla_estoque]);
            $feedback = "<div class='bg-green-100 text-green-700 p-3 rounded mb-4'>Categoria salva!</div>";
        }
    }
}

// Busca as categorias existentes
$lista_categorias = $pdo->query("SELECT * FROM categorias_ativo ORDER BY nome_categoria")->fetchAll();

?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    
    <div class="md:col-span-1">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Nova Categoria de Ativo</h2>
            <?= $feedback ?>
            <form action="index.php" method="POST">
                <input type="hidden" name="acao" value="nova_categoria_ativo">
                
                <div class="mb-4">
                    <label for="nome_categoria" class="block text-gray-700 font-semibold mb-2">Nome da Categoria</label>
                    <input type="text" id="nome_categoria" name="nome_categoria" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg" 
                           placeholder="Ex: Computador, Toner, Software" required>
                </div>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="controla_estoque" class="h-5 w-5 text-blue-600 border-gray-300 rounded">
                        <span class="ml-2 text-gray-700 font-semibold">
                            Esta categoria controla estoque?
                        </span>
                    </label>
                    <p class="text-sm text-gray-500 mt-1">(Marque se for um consumível, como Toner ou Mouse).</p>
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
                        <th class="py-2 px-4 text-left text-gray-600 font-semibold">Controla Estoque?</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lista_categorias as $cat): ?>
                        <tr class="border-b">
                            <td class="py-2 px-4"><?= $cat['id_categoria_ativo'] ?></td>
                            <td class="py-2 px-4"><?= htmlspecialchars($cat['nome_categoria']) ?></td>
                            <td class="py-2 px-4 font-medium">
                                <?php if ($cat['controla_estoque']): ?>
                                    <span class="text-green-600">Sim (Consumível)</span>
                                <?php else: ?>
                                    <span class="text-red-600">Não (Ativo)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once '../../includes/footer.php'; // Sobe 2 níveis
?>