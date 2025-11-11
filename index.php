<?php
// /index.php (Refatorado - Foco em Chamados)

// 1. Inclui o Header
// $pdo, $base_url, $usuario_role_logado, $usuario_id_logado já estão disponíveis
require_once 'includes/header.php';

// --- INÍCIO DAS CONSULTAS (Foco em Chamados) ---

// Prepara os parâmetros para as queries
$params = [];
$sql_where = "";

// LÓGICA SQL PARA USUÁRIO COMUM
if ($usuario_role_logado == 'USUARIO') {
    $sql_where = " WHERE autor_id = ?";
    $params = [$usuario_id_logado];
    $titulo_lista = "Meus Últimos Chamados";
} else {
// LÓGICA SQL PARA TÉCNICO / ADMIN (Visão Global)
    $titulo_lista = "Últimos Chamados Urgentes (Global)";
}

// --- Execução das Queries ---

// 2. Contagem de Chamados
$sql_abertos = "SELECT COUNT(*) FROM chamados WHERE status_chamado = 'Aberto'" . ($sql_where ? $sql_where : '');
$stmt_abertos = $pdo->prepare($sql_abertos);
$stmt_abertos->execute($params);
$total_abertos = $stmt_abertos->fetchColumn();

$sql_andamento = "SELECT COUNT(*) FROM chamados WHERE status_chamado = 'Em Atendimento'" . ($sql_where ? $sql_where : '');
$stmt_andamento = $pdo->prepare($sql_andamento);
$stmt_andamento->execute($params);
$total_andamento = $stmt_andamento->fetchColumn();

// !! NOVO CARD !!
$sql_fechado = "SELECT COUNT(*) FROM chamados WHERE status_chamado = 'Fechado'" . ($sql_where ? $sql_where : '');
$stmt_fechado = $pdo->prepare($sql_fechado);
$stmt_fechado->execute($params);
$total_fechado = $stmt_fechado->fetchColumn();


// 4. Buscar os 5 últimos chamados (com a lógica condicional)
$sql_recentes_join_ativo = "LEFT JOIN ativos a ON c.ativo_id = a.id_ativo
                            LEFT JOIN unidades u ON a.unidade_id = u.id_unidade";
$sql_recentes_where = ($sql_where ? $sql_where . " AND " : " WHERE ") . "(c.status_chamado = 'Aberto' OR c.status_chamado = 'Em Atendimento')";

$sql_recentes = "SELECT c.id_chamado, c.titulo, c.prioridade, a.nome_ativo, u.nome_unidade
                FROM chamados c
                $sql_recentes_join_ativo
                $sql_recentes_where
                ORDER BY c.dt_abertura DESC
                LIMIT 5";
$stmt_recentes = $pdo->prepare($sql_recentes);
$stmt_recentes->execute($params);
$chamados_recentes = $stmt_recentes->fetchAll();

// --- FIM DAS CONSULTAS ---
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

    <div class="bg-red-500 shadow-lg rounded-lg p-6 text-white">
        <h5 class="text-lg font-semibold mb-2">Chamados Abertos</h5>
        <p class="text-5xl font-bold"><?= $total_abertos ?></p>
        <a href="<?= $base_url ?>/chamados/index.php?status=Aberto" class="text-red-100 hover:text-white mt-4 inline-block">Ver Lista</a>
    </div>
    
    <div class="bg-yellow-400 shadow-lg rounded-lg p-6 text-gray-800">
        <h5 class="text-lg font-semibold mb-2">Em Atendimento</h5>
        <p class="text-5xl font-bold"><?= $total_andamento ?></p>
        <a href="<?= $base_url ?>/chamados/index.php?status=Em Atendimento" class="text-yellow-700 hover:text-black mt-4 inline-block">Ver Lista</a>
    </div>

    <div class="bg-green-500 shadow-lg rounded-lg p-6 text-white">
        <h5 class="text-lg font-semibold mb-2">Chamados Concluídos</h5>
        <p class="text-5xl font-bold"><?= $total_fechado ?></p>
        <a href="<?= $base_url ?>/chamados/index.php" class="text-green-100 hover:text-white mt-4 inline-block">Ver Histórico</a>
    </div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-1 bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-xl font-semibold">Ações Rápidas</h3>
        </div>
        <div class="p-4">
            <div class="flex flex-col space-y-3">
                
                <a href="<?= $base_url ?>/chamados/novo.php" class="w-full text-center bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-300">
                    Abrir Novo Chamado
                </a>
                
                <?php if ($usuario_role_logado == 'TECNICO' || $usuario_role_logado == 'ADMIN'): ?>
                <a href="<?= $base_url ?>/ativos/novo.php" class="w-full text-center bg-cyan-500 text-white font-bold py-3 px-4 rounded-lg hover:bg-cyan-600 transition duration-300">
                    Cadastrar Ativo
                </a>
                <?php endif; ?>
                
                <?php if ($usuario_role_logado == 'ADMIN'): ?>
                <a href="<?= $base_url ?>/admin/index.php" class="w-full text-center bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-300 transition duration-300">
                    Configurações Gerais
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2 bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-xl font-semibold">
                <?= $titulo_lista ?>
            </h3>
        </div>
        <div class="p-4">
            <table class="w-full">
                <tbody>
                    <?php foreach ($chamados_recentes as $chamado): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-2">
                                <a href="<?= $base_url ?>/chamados/ver.php?id=<?= $chamado['id_chamado'] ?>" class="text-blue-600 hover:underline">
                                    <strong><?= htmlspecialchars($chamado['titulo']) ?></strong>
                                </a>
                                <br>
                                <small class="text-gray-500">
                                    <?= htmlspecialchars($chamado['nome_unidade'] ?? 'Sem Unidade') ?> - 
                                    <?= htmlspecialchars($chamado['nome_ativo'] ?? 'Sem Ativo') ?>
                                </small>
                            </td>
                            <td class="py-3 px-2 text-right">
                                <?php 
                                $badge_class = 'bg-gray-500 text-white'; // Padrão
                                if ($chamado['prioridade'] == 'URGENTE') $badge_class = 'bg-red-600 text-white';
                                elseif ($chamado['prioridade'] == 'ALTA') $badge_class = 'bg-yellow-500 text-black';
                                elseif ($chamado['prioridade'] == 'MEDIA') $badge_class = 'bg-blue-500 text-white';
                                elseif ($chamado['prioridade'] == 'BAIXA') $badge_class = 'bg-green-500 text-white';
                                ?>
                                <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full <?= $badge_class ?>">
                                    <?= htmlspecialchars($chamado['prioridade']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (count($chamados_recentes) === 0): ?>
                        <tr>
                            <td colspan="2" class="py-4 text-center text-gray-500">
                                Nenhum chamado relevante encontrado. ✨
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>