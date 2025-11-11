<?php
// /ativos/index.php (Refatorado - AGORA É UM DASHBOARD)

require_once '../includes/header.php'; // Sobe 1 nível

// Segurança
if ($usuario_role_logado == 'USUARIO') {
    echo "<p>Acesso negado.</p>";
    require_once '../includes/footer.php';
    exit;
}

// =======================================================
// !! NOVO: CONSULTAS PARA O DASHBOARD DE ATIVOS !!
// =======================================================
$stmt_total = $pdo->query("SELECT COUNT(*) FROM ativos");
$total_ativos = $stmt_total->fetchColumn();

$stmt_manutencao = $pdo->query("SELECT COUNT(*) FROM ativos WHERE status_ativo = 'EM_MANUTENCAO'");
$total_manutencao = $stmt_manutencao->fetchColumn();

$stmt_baixado = $pdo->query("SELECT COUNT(*) FROM ativos WHERE status_ativo = 'BAIXADO'");
$total_baixado = $stmt_baixado->fetchColumn();
// =======================================================


// Busca dos Ativos no Banco (para a lista)
$sql = "SELECT 
            a.id_ativo, a.nome_ativo, a.patrimonio, a.ip_address, a.status_ativo,
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

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    
    <div class="bg-gray-700 shadow-lg rounded-lg p-6 text-white">
        <h5 class="text-lg font-semibold mb-2">Total de Ativos</h5>
        <p class="text-5xl font-bold"><?= $total_ativos ?></p>
        <span class="text-gray-300 mt-4 inline-block">Inventário completo</span>
    </div>

    <div class="bg-blue-400 shadow-lg rounded-lg p-6 text-white">
        <h5 class="text-lg font-semibold mb-2">Em Manutenção</h5>
        <p class="text-5xl font-bold"><?= $total_manutencao ?></p>
         <span class="text-blue-100 mt-4 inline-block">Ativos em reparo</span>
    </div>

    <div class="bg-gray-400 shadow-lg rounded-lg p-6 text-white">
        <h5 class="text-lg font-semibold mb-2">Baixados / Descartados</h5>
        <p class="text-5xl font-bold"><?= $total_baixado ?></p>
         <span class="text-gray-200 mt-4 inline-block">Fora de operação</span>
    </div>

    <a href="<?= $base_url ?>/ativos/novo.php" 
       class="bg-green-500 shadow-lg rounded-lg p-6 text-white flex flex-col justify-center items-center hover:bg-green-600 transition-all duration-300">
        <span class="text-5xl font-bold">+</span>
        <h5 class="text-lg font-semibold mt-2">Cadastrar Novo Ativo</h5>
    </a>
</div>
<div class="bg-white shadow-md rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Inventário de Ativos</h2>
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