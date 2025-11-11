<?php
// /ativos/ver.php (ATUALIZADO COM HISTÓRICO "ACORDEÃO")

require_once '../includes/header.php'; // Sobe 1 nível

// Segurança: Só Técnicos/Admins
if ($usuario_role_logado == 'USUARIO') {
    header("Location: {$base_url}/index.php");
    exit;
}

// 1. Validação do ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: {$base_url}/ativos/index.php");
    exit;
}
$id_ativo = (int)$_GET['id'];

// 2. Busca os dados do Ativo Específico (SQL v3.3 - Sem mudanças)
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

if (!$ativo) {
    header("Location: {$base_url}/ativos/index.php");
    exit;
}

// =======================================================
// !! SQL DO HISTÓRICO ATUALIZADO !!
// Buscando todos os campos necessários para o layout "acordeão"
// =======================================================
$sql_chamados = "SELECT 
                    c.id_chamado,
                    c.titulo,
                    c.problema_relatado,
                    c.solucao_aplicada,
                    c.status_chamado,
                    c.dt_abertura,
                    c.dt_fechamento,
                    c.prioridade,
                    u_autor.nome AS nome_autor,
                    cat.nome_categoria
                FROM chamados c
                LEFT JOIN usuarios u_autor ON c.autor_id = u_autor.id_usuario
                LEFT JOIN categorias cat ON c.categoria_id = cat.id_categoria
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
            
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h3 class="text-xl font-semibold">
                    Histórico de Chamados (<?= count($historico_chamados) ?>)
                </h3>
                <a href="<?= $base_url ?>/chamados/novo.php?ativo_id=<?= $id_ativo ?>" 
                   class="bg-blue-600 text-white font-bold py-2 px-3 rounded-lg hover:bg-blue-700 text-sm">
                   + Abrir Chamado
                </a>
            </div>

            <div class="space-y-2">
                <?php if (count($historico_chamados) > 0): ?>
                    <?php foreach ($historico_chamados as $index => $chamado): ?>
                        
                        <details class="border rounded-lg" <?= ($index == 0) ? 'open' : '' ?>>
                            
                            <summary class="flex justify-between items-center p-3 cursor-pointer hover:bg-gray-50">
                                <div class="flex items-center space-x-3">
                                    <span class="font-medium text-gray-800">#<?= $chamado['id_chamado'] ?></span>
                                    <span class="text-sm text-gray-600"><?= (new DateTime($chamado['dt_abertura']))->format('d/m/Y H:i') ?></span>
                                    <?php 
                                    $status_class = 'bg-gray-500 text-white'; 
                                    if ($chamado['status_chamado'] == 'Aberto') $status_class = 'bg-red-500 text-white';
                                    if ($chamado['status_chamado'] == 'Em Atendimento') $status_class = 'bg-yellow-500 text-black';
                                    if ($chamado['status_chamado'] == 'Fechado') $status_class = 'bg-green-500 text-white';
                                    ?>
                                    <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded-full <?= $status_class ?>">
                                        <?= htmlspecialchars($chamado['status_chamado']) ?>
                                    </span>
                                </div>
                                <span class="text-gray-700 font-semibold truncate" style="max-width: 300px;">
                                    <?= htmlspecialchars($chamado['titulo']) ?>
                                </span>
                            </summary>

                            <div class="p-4 bg-gray-50 border-t">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <strong>Problema Relatado:</strong>
                                        <p class="text-gray-700 whitespace-pre-wrap"><?= nl2br(htmlspecialchars($chamado['problema_relatado'])) ?></p>
                                    </div>
                                    <div>
                                        <strong>Solução Aplicada:</strong>
                                        <p class="text-green-700 whitespace-pre-wrap"><?= nl2br(htmlspecialchars($chamado['solucao_aplicada'] ?? 'Nenhuma solução registrada.')) ?></p>
                                    </div>
                                </div>
                                <hr class="my-3">
                                <div class="text-sm text-gray-600 space-y-1">
                                    <p><strong>Autor:</strong> <?= htmlspecialchars($chamado['nome_autor']) ?></p>
                                    <p><strong>Categoria:</strong> <?= htmlspecialchars($chamado['nome_categoria'] ?? 'N/A') ?></p>
                                    <p><strong>Prioridade:</strong> <?= htmlspecialchars($chamado['prioridade']) ?></p>
                                    <?php if ($chamado['dt_fechamento']): ?>
                                        <p><strong>Fechado em:</strong> <?= (new DateTime($chamado['dt_fechamento']))->format('d/m/Y H:i') ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </details>
                        
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-gray-500 py-6">
                        Nenhum chamado registrado para este ativo.
                    </div>
                <?php endif; ?>
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