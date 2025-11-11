<?php
// /ativos/index.php (ATUALIZADO COM LINK "VER")

require_once '../includes/header.php'; // Sobe 1 nível

// Segurança
if ($usuario_role_logado == 'USUARIO') {
    echo "<p>Acesso negado.</p>";
    require_once '../includes/footer.php';
    exit;
}

// Busca dos Ativos no Banco
$sql = "SELECT 
            a.id_ativo,
            a.nome_ativo,
            a.patrimonio,
            a.ip_address,
            a.status_ativo,
            m.nome AS nome_modelo,
            cat_a.nome_categoria AS nome_tipo,
            u.nome_unidade
        FROM ativos a
        JOIN catalogo_modelos m ON a.modelo_id = m.id_modelo
        LEFT JOIN unidades u ON a.unidade_id = u.id_unidade
        LEFT JOIN categorias_ativo cat_a ON m.categoria_ativo_id = cat_a.id_categoria_ativo
        ORDER BY u.nome_unidade, a.nome_ativo";

$stmt = $pdo->query($sql);
$lista_ativos = $stmt->fetchAll();

?>

<div class="bg-white shadow-md rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Inventário de Ativos</h2>
        <a href="<?= $base_url ?>/ativos/novo.php" 
           class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-300">
           + Novo Ativo
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-4 text-left text-gray-600 font-semibold">Nome / Patrimônio</th>
                    <th class="py-3 px-4 text-left text-gray-600 font-semibold">Tipo</th>
                    <th class="py-3 px-4 text-left text-gray-600 font-semibold">Unidade (Local)</th>
                    <th class="py-3 px-4 text-left text-gray-600 font-semibold">IP</th>
                    <th class="py-3 px-4 text-left text-gray-600 font-semibold">Status</th>
                    <th class="py-3 px-4 text-left text-gray-600 font-semibold">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($lista_ativos) > 0): ?>
                    <?php foreach ($lista_ativos as $ativo): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            
                            <td class="py-3 px-4">
                                <span class="font-semibold text-gray-800"><?= htmlspecialchars($ativo['nome_ativo']) ?></span>
                                <div class="text-sm text-gray-500">
                                    Patrimônio: <?= htmlspecialchars($ativo['patrimonio'] ?? 'N/A') ?>
                                </div>
                            </td>
                            
                            <td class="py-3 px-4 text-gray-700"><?= htmlspecialchars($ativo['nome_tipo'] ?? 'N/A') ?></td>
                            <td class="py-3 px-4 text-gray-700"><?= htmlspecialchars($ativo['nome_unidade']) ?></td>
                            <td class="py-3 px-4 text-gray-700"><?= htmlspecialchars($ativo['ip_address'] ?? 'N/A') ?></td>
                            
                            <td class="py-3 px-4">
                                <?php 
                                $status_class = 'bg-green-500 text-white'; // Ativo
                                if ($ativo['status_ativo'] == 'EM_MANUTENCAO') $status_class = 'bg-yellow-500 text-black';
                                if ($ativo['status_ativo'] == 'BAIXADO') $status_class = 'bg-gray-500 text-white';
                                ?>
                                <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full <?= $status_class ?>">
                                    <?= htmlspecialchars($ativo['status_ativo']) ?>
                                </span>
                            </td>

                            <td class="py-3 px-4 space-x-2">
                                <a href="<?= $base_url ?>/ativos/ver.php?id=<?= $ativo['id_ativo'] ?>" 
                                   class="bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-sm hover:bg-blue-200">
                                   Ver
                                </a>
                                <a href="<?= $base_url ?>/ativos/editar.php?id=<?= $ativo['id_ativo'] ?>" 
                                   class="bg-gray-200 text-gray-700 px-3 py-1 rounded-lg text-sm hover:bg-gray-300">
                                   Editar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="py-6 text-center text-gray-500">
                            Nenhum ativo cadastrado.
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