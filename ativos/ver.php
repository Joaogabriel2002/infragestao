<?php
// /ativos/ver.php (ATUALIZADO COM "DONO")

require_once '../includes/header.php'; // Sobe 1 nível
if ($usuario_role_logado == 'USUARIO') { /* ... (Segurança) ... */ }

// Validação do ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { /* ... (Validação) ... */ }
$id_ativo = (int)$_GET['id'];

// =======================================================
// !! SQL PRINCIPAL ATUALIZADO !!
// Adicionamos JOINs para buscar o nome do SETOR e do USUÁRIO (dono)
// =======================================================
$sql_ativo = "SELECT 
                a.*, 
                m.nome AS nome_modelo,
                cat.nome_categoria AS nome_categoria_ativo,
                u.nome_unidade,
                s.nome_setor AS nome_setor_dono,
                usr.nome AS nome_usuario_dono
            FROM ativos a
            LEFT JOIN catalogo_modelos m ON a.modelo_id = m.id_modelo
            LEFT JOIN categorias_ativo cat ON m.categoria_ativo_id = cat.id_categoria_ativo
            LEFT JOIN unidades u ON a.unidade_id = u.id_unidade
            LEFT JOIN setores s ON a.setor_id = s.id_setor
            LEFT JOIN usuarios usr ON a.usuario_id = usr.id_usuario
            WHERE a.id_ativo = ?";
$stmt_ativo = $pdo->prepare($sql_ativo);
$stmt_ativo->execute([$id_ativo]);
$ativo = $stmt_ativo->fetch();
if (!$ativo) { /* ... (Validação) ... */ }

// Busca o HISTÓRICO DE CHAMADOS (SQL existente)
$sql_chamados = "SELECT c.id_chamado, c.titulo, c.status_chamado, c.dt_abertura, u_autor.nome AS nome_autor
                FROM chamados c
                LEFT JOIN usuarios u_autor ON c.autor_id = u_autor.id_usuario
                WHERE c.ativo_id = ?
                ORDER BY c.dt_abertura DESC";
$stmt_chamados = $pdo->prepare($sql_chamados);
$stmt_chamados->execute([$id_ativo]);
$historico_chamados = $stmt_chamados->fetchAll();

?>

<div class="mb-6">
    <h2 class="text-3xl font-bold text-gray-800">
        Ativo: <?= htmlspecialchars($ativo['nome_ativo']) ?>
    </h2>
    <p class="text-sm text-gray-500">
        Patrimônio: <?= htmlspecialchars($ativo['patrimonio'] ?? 'N/A') ?>
    </p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-xl font-semibold mb-4 border-b pb-2">Histórico de Chamados</h3>
            
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 px-4 text-left text-gray-600 font-semibold">ID</th>
                            <th class="py-2 px-4 text-left text-gray-600 font-semibold">Título</th>
                            <th class="py-2 px-4 text-left text-gray-600 font-semibold">Autor</th>
                            <th class="py-2 px-4 text-left text-gray-600 font-semibold">Status</th>
                            <th class="py-2 px-4 text-left text-gray-600 font-semibold">Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($historico_chamados) > 0): ?>
                            <?php foreach ($historico_chamados as $chamado): ?>
                                <tr class="border-b">
                                    <td class="py-2 px-4">
                                        <a href="<?= $base_url ?>/chamados/ver.php?id=<?= $chamado['id_chamado'] ?>" 
                                           class="text-blue-600 hover:underline font-medium">
                                           #<?= $chamado['id_chamado'] ?>
                                        </a>
                                    </td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($chamado['titulo']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($chamado['nome_autor']) ?></td>
                                    <td class="py-2 px-4">
                                        <?php 
                                        $status_class = 'bg-gray-500 text-white'; 
                                        if ($chamado['status_chamado'] == 'Aberto') $status_class = 'bg-red-500 text-white';
                                        if ($chamado['status_chamado'] == 'Em Atendimento') $status_class = 'bg-yellow-500 text-black';
                                        if ($chamado['status_chamado'] == 'Fechado') $status_class = 'bg-green-500 text-white';
                                        ?>
                                        <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded-full <?= $status_class ?>">
                                            <?= htmlspecialchars($chamado['status_chamado']) ?>
                                        </span>
                                    </td>
                                    <td class="py-2 px-4 text-sm text-gray-600">
                                        <?= (new DateTime($chamado['dt_abertura']))->format('d/m/Y') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-6 text-center text-gray-500">
                                    Nenhum chamado registrado para este ativo.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="lg:col-span-1 space-y-6">
        
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h3 class="text-xl font-semibold">Detalhes do Ativo</h3>
                <a href="<?= $base_url ?>/ativos/editar.php?id=<?= $ativo['id_ativo'] ?>"
                   class="text-sm text-blue-600 hover:underline">
                   Editar
                </a>
            </div>
            
            <ul class="text-gray-700 space-y-3">
                <li><strong>Status:</strong>
                    <?php 
                    $status_class = 'bg-green-500 text-white'; // Ativo
                    if ($ativo['status_ativo'] == 'EM_MANUTENCAO') $status_class = 'bg-yellow-500 text-black';
                    if ($ativo['status_ativo'] == 'BAIXADO') $status_class = 'bg-gray-500 text-white';
                    ?>
                    <span class="ml-2 inline-block px-3 py-1 text-xs font-semibold rounded-full <?= $status_class ?>">
                        <?= htmlspecialchars($ativo['status_ativo']) ?>
                    </span>
                </li>
                
                <li class="pt-2 border-t mt-2">
                    <strong>Alocado para:</strong>
                    <?php if ($ativo['nome_usuario_dono']): ?>
                        <span class="font-medium text-blue-700">
                            (Usuário) <?= htmlspecialchars($ativo['nome_usuario_dono']) ?>
                        </span>
                    <?php elseif ($ativo['nome_setor_dono']): ?>
                        <span class="font-medium text-green-700">
                            (Setor) <?= htmlspecialchars($ativo['nome_setor_dono']) ?>
                        </span>
                    <?php else: ?>
                        <span class="text-gray-500">Não alocado</span>
                    <?php endif; ?>
                </li>
                <li><strong>Local Físico (Unidade):</strong> <?= htmlspecialchars($ativo['nome_unidade']) ?></li>
                <li><strong>Modelo:</strong> <?= htmlspecialchars($ativo['nome_modelo']) ?></li>
                <li><strong>Tipo:</strong> <?= htmlspecialchars($ativo['nome_categoria_ativo']) ?></li>
                
                <hr>
                
                <li><strong>IP:</strong> <?= htmlspecialchars($ativo['ip_address'] ?? 'N/A') ?></li>
                <li><strong>ID Remoto:</strong> <?= htmlspecialchars($ativo['remote_id'] ?? 'N/A') ?></li>
                <li><strong>SO:</strong> <?= htmlspecialchars($ativo['operating_system'] ?? 'N/A') ?></li>
            </ul>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php'; // Sobe 1 nível
?>