<?php
// /estoque/entrada.php

require_once '../includes/header.php'; // Sobe 1 nível

// =======================================================
// !! CORREÇÃO DE SEGURANÇA !!
// =======================================================
// Em vez de quebrar a página, vamos redirecionar para o index
if ($usuario_role_logado == 'USUARIO') {
    // Redireciona o usuário comum para o dashboard
    header("Location: {$base_url}/index.php");
    exit;
}
// =======================================================
// FIM DA CORREÇÃO
// =======================================================


// --- INÍCIO DAS CONSULTAS PARA OS DROPDOWNS ---

// 1. Buscar os ITENS CONSUMÍVEIS
$sql_itens = "SELECT 
                m.id_modelo, 
                m.nome 
            FROM catalogo_modelos m
            JOIN categorias_ativo cat ON m.categoria_ativo_id = cat.id_categoria_ativo
            WHERE 
                cat.controla_estoque = true 
            ORDER BY m.nome";
$lista_itens = $pdo->query($sql_itens)->fetchAll();


// 2. Buscar todos os FORNECEDORES
$sql_fornecedores = "SELECT id_fornecedor, nome FROM fornecedores ORDER BY nome";
$lista_fornecedores = $pdo->query($sql_fornecedores)->fetchAll();

// --- FIM DAS CONSULTAS ---
?>

<div class="bg-white shadow-md rounded-lg p-6 max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">Registrar Entrada de Estoque</h2>

    <?php
    if (isset($_GET['sucesso'])) {
        echo "<div class='bg-green-100 text-green-700 p-3 rounded mb-4'>Entrada de estoque registrada com sucesso!</div>";
    }
    if (isset($_GET['erro'])) {
        echo "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>Ocorreu um erro ao registrar a entrada.</div>";
    }
    ?>

    <form action="processar.php" method="POST" class="space-y-4">
        
        <input type="hidden" name="acao" value="nova_entrada">

        <div>
            <label for="modelo_id" class="block text-gray-700 font-semibold mb-2">Item (Consumível) *</label>
            <select id="modelo_id" name="modelo_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                
                <option value="">[Selecione o item que chegou]</option>
                
                <?php foreach ($lista_itens as $item): ?>
                    <option value="<?= $item['id_modelo'] ?>">
                        <?= htmlspecialchars($item['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="text-sm text-gray-500 mt-1">
                Se o item não está na lista, cadastre-o primeiro no 
                <a href="<?= $base_url ?>/estoque/index.php" class="text-blue-600 hover:underline">Catálogo de Modelos</a>.
            </p>
        </div>

        <div>
            <label for="quantidade" class="block text-gray-700 font-semibold mb-2">Quantidade *</label>
            <input type="number" id="quantidade" name="quantidade" min="1" value="1"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
        </div>

        <hr class="my-4">
        <h3 class="text-lg font-semibold text-gray-600">Informações de Compra (Opcional)</h3>

        <div>
            <label for="fornecedor_id" class="block text-gray-700 font-semibold mb-2">Fornecedor</label>
            <select id="fornecedor_id" name="fornecedor_id" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                
                <option value="">[Selecione o fornecedor]</option>

                <?php foreach ($lista_fornecedores as $fornecedor): ?>
                    <option value="<?= $fornecedor['id_fornecedor'] ?>">
                        <?= htmlspecialchars($fornecedor['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mt-6">
            <button type="submit" 
                    class="w-full bg-green-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-green-700 transition duration-300">
                Adicionar ao Estoque
            </button>
        </div>

    </form>
</div>

<?php
// 3. Inclui o Footer
require_once '../includes/footer.php';
?>