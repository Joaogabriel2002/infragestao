<?php
// /estoque/relatorio.php (CORRIGIDO)

require_once '../includes/header.php'; // Sobe 1 nível

// Segurança: Só Técnicos/Admins
if ($usuario_role_logado == 'USUARIO') {
    header("Location: {$base_url}/index.php");
    exit;
}

// --- LÓGICA DE FILTRAGEM ---

// 1. Pegar os filtros da URL (via GET)
$filtro_item = $_GET['filtro_item'] ?? '';
$filtro_zerado = isset($_GET['filtro_zerado']); // true se o checkbox estiver marcado

// 2. Montar o SQL dinamicamente
$sql_where_parts = []; // Array para guardar as condições
$params = []; // Array para guardar os valores do prepared statement

// =======================================================
// !! CORREÇÃO AQUI !!
// A condição base deve usar o apelido 'c', e não 'cat'
// =======================================================
$sql_where_parts[] = "c.controla_estoque = true";
// =======================================================

// Se o usuário digitou algo no filtro de item
if (!empty($filtro_item)) {
    $sql_where_parts[] = "m.nome LIKE ?";
    $params[] = "%" . $filtro_item . "%"; // Ex: %Toner%
}

// Se o usuário marcou o checkbox "Estoque Zerado"
if ($filtro_zerado) {
    $sql_where_parts[] = "m.quantidade_em_estoque <= 0";
}

// 3. Juntar todas as partes do WHERE
$sql_where = "";
if (count($sql_where_parts) > 0) {
    $sql_where = "WHERE " . implode(" AND ", $sql_where_parts);
}

// 4. Query SQL Final
// (O JOIN aqui usa 'c' como apelido)
$sql_modelos = "SELECT 
                    m.id_modelo,
                    m.nome, 
                    m.quantidade_em_estoque, 
                    c.nome_categoria
                FROM catalogo_modelos m
                LEFT JOIN categorias_ativo c ON m.categoria_ativo_id = c.id_categoria_ativo
                $sql_where
                ORDER BY m.nome";

$stmt = $pdo->prepare($sql_modelos);
$stmt->execute($params);
$lista_itens = $stmt->fetchAll();

?>

<div class="bg-white shadow-md rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-6">Relatório de Estoque (Consumíveis)</h2>

    <form action="relatorio.php" method="GET" class="mb-6 p-4 bg-gray-50 rounded-lg border">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <div>
                <label for="filtro_item" class="block text-gray-700 font-semibold mb-2">Filtrar por Item</label>
                <input type="text" id="filtro_item" name="filtro_item" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg" 
                       placeholder="Ex: Toner"
                       value="<?= htmlspecialchars($filtro_item) ?>">
            </div>
            
            <div class="flex items-end">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" id="filtro_zerado" name="filtro_zerado" 
                           class="h-5 w-5 text-blue-600 border-gray-300 rounded"
                           <?= $filtro_zerado ? 'checked' : '' ?>>
                    <span class="text-gray-700 font-semibold">
                        Mostrar apenas estoque zerado/negativo
                    </span>
                </label>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" 
                        class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700">
                    Filtrar
                </button>
                <a href="relatorio.php" 
                   class="bg-gray-200 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-300">
                    Limpar
                </a>
            </div>
        </div>
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-4 text-left text-gray-600 font-semibold">Nome do Item</th>
                    <th class="py-3 px-4 text-left text-gray-600 font-semibold">Categoria</th>
                    <th class="py-3 px-4 text-center text-gray-600 font-semibold">Qtd. em Estoque</th>
                    <th class="py-3 px-4 text-left text-gray-600 font-semibold">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($lista_itens) > 0): ?>
                    <?php foreach ($lista_itens as $item): ?>
                        
                        <?php
                        // Define a cor (Vermelho se zerado/negativo)
                        $cor_estoque = 'text-gray-900';
                        if ($item['quantidade_em_estoque'] <= 0) {
                            $cor_estoque = 'text-red-600 font-bold';
                        }
                        ?>

                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            
                            <td class="py-3 px-4 font-medium"><?= htmlspecialchars($item['nome']) ?></td>
                            <td class="py-3 px-4 text-gray-700"><?= htmlspecialchars($item['nome_categoria'] ?? 'N/A') ?></td>
                            <td class="py-3 px-4 text-center text-lg font-bold <?= $cor_estoque ?>">
                                <?= $item['quantidade_em_estoque'] ?>
                            </td>
                            <td class="py-3 px-4">
                                <a href="kardex.php?id=<?= $item['id_modelo'] ?>"
                                   class="bg-gray-200 text-gray-700 px-3 py-1 rounded-lg text-sm hover:bg-gray-300">
                                   Kardex
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="py-6 text-center text-gray-500">
                            Nenhum item encontrado com os filtros aplicados.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<?php
// 3. Inclui o Footer
require_once '../includes/footer.php';
?>