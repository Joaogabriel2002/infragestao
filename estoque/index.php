<?php
// /estoque/index.php

require_once '../includes/header.php'; // Sobe 1 nível

// Segurança: Só Técnicos/Admins
if ($usuario_role_logado == 'USUARIO') {
    echo "<p>Acesso negado.</p>";
    require_once '../includes/footer.php';
    exit;
}

// Lógica de Processamento (Salvar novo modelo)
$feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao']) && $_POST['acao'] == 'novo_modelo') {
        $nome = $_POST['nome'];
        $categoria_id = $_POST['categoria_ativo_id'];
        
        if (!empty($nome) && !empty($categoria_id)) {
            $stmt = $pdo->prepare("INSERT INTO catalogo_modelos (nome, categoria_ativo_id) VALUES (?, ?)");
            $stmt->execute([$nome, $categoria_id]);
            $feedback = "<div class='bg-green-100 text-green-700 p-3 rounded mb-4'>Modelo salvo com sucesso!</div>";
        }
    }
}

// Busca as categorias de ativo (PC, Toner, etc.) para o <select>
$lista_categorias_ativo = $pdo->query("SELECT * FROM categorias_ativo ORDER BY nome_categoria")->fetchAll();

// Busca os modelos/itens já cadastrados
$sql_modelos = "SELECT 
                    m.nome, 
                    m.quantidade_em_estoque, 
                    c.nome_categoria,
                    c.controla_estoque
                FROM catalogo_modelos m
                JOIN categorias_ativo c ON m.categoria_ativo_id = c.id_categoria_ativo
                ORDER BY c.nome_categoria, m.nome";
$lista_modelos = $pdo->query($sql_modelos)->fetchAll();

?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    
    <div class="md:col-span-1">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Novo Modelo ou Item</h2>
            <?= $feedback ?>
            <form action="index.php" method="POST">
                <input type="hidden" name="acao" value="novo_modelo">
                
                <div class="mb-4">
                    <label for="nome" class="block text-gray-700 font-semibold mb-2">Nome do Modelo/Item</label>
                    <input type="text" id="nome" name="nome" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg" 
                           placeholder="Ex: PC Dell Vostro, Toner HP 105A" required>
                </div>

                <div class="mb-4">
                    <label for="categoria_ativo_id" class="block text-gray-700 font-semibold mb-2">Categoria do Item</label>
                    <select id="categoria_ativo_id" name="categoria_ativo_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                        <option value="">[Selecione]</option>
                        <?php foreach ($lista_categorias_ativo as $cat): ?>
                            <option value="<?= $cat['id_categoria_ativo'] ?>">
                                <?= htmlspecialchars($cat['nome_categoria']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <p class="text-sm text-gray-500 mb-4">
                    Se a categoria não existe, cadastre-a primeiro (faremos essa tela em breve).
                </p>

                <button type="submit" 
                        class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700">
                    Salvar Item
                </button>
            </form>
        </div>
    </div>

    <div class="md:col-span-2">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Catálogo de Modelos e Estoque</h2>
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 text-left text-gray-600 font-semibold">Nome</th>
                        <th class="py-2 px-4 text-left text-gray-600 font-semibold">Categoria</th>
                        <th class="py-2 px-4 text-left text-gray-600 font-semibold">Estoque Atual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lista_modelos as $modelo): ?>
                        <tr class="border-b">
                            <td class="py-2 px-4 font-medium"><?= htmlspecialchars($modelo['nome']) ?></td>
                            <td class="py-2 px-4"><?= htmlspecialchars($modelo['nome_categoria']) ?></td>
                            <td class="py-2 px-4">
                                <?php if ($modelo['controla_estoque']): ?>
                                    <span class="font-bold text-lg"><?= $modelo['quantidade_em_estoque'] ?></span>
                                <?php else: ?>
                                    <span class="text-gray-400">N/A (Ativo)</span>
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
require_once '../includes/footer.php'; // Sobe 1 nível
?>