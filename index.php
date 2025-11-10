<?php
// /index.php (O Dashboard - VERSÃO FINAL COM PERMISSÕES)

// 1. Inclui o Header
// $pdo, $base_url, $usuario_role_logado, $usuario_id_logado já estão disponíveis
require_once 'includes/header.php';

// --- INÍCIO DAS CONSULTAS (AGORA SÃO DINÂMICAS) ---

// Prepara os parâmetros para as queries
$params_abertos = [];
$params_andamento = [];
$params_recentes = [];
$sql_recentes_join_ativo = "LEFT JOIN ativos a ON c.ativo_id = a.id_ativo
                            LEFT JOIN unidades u ON a.unidade_id = u.id_unidade";

// LÓGICA SQL PARA USUÁRIO COMUM
if ($usuario_role_logado == 'USUARIO') {
    
    // Contagem de chamados ABERTOS (só dele)
    $sql_abertos = "SELECT COUNT(*) FROM chamados WHERE status_chamado = 'Aberto' AND autor_id = ?";
    $params_abertos = [$usuario_id_logado];
    
    // Contagem de chamados EM ATENDIMENTO (só dele)
    $sql_andamento = "SELECT COUNT(*) FROM chamados WHERE status_chamado = 'Em Atendimento' AND autor_id = ?";
    $params_andamento = [$usuario_id_logado];

    // Lista de chamados recentes (só dele)
    $sql_recentes_where = "WHERE (c.status_chamado = 'Aberto' OR c.status_chamado = 'Em Atendimento') AND c.autor_id = ?";
    $params_recentes = [$usuario_id_logado];

    // Usuário não vê contagem de ativos
    $total_ativos = 0;
    $total_manutencao = 0;

} else {
// LÓGICA SQL PARA TÉCNICO / ADMIN (Visão Global)
    
    // Contagem de chamados ABERTOS (Global)
    $sql_abertos = "SELECT COUNT(*) FROM chamados WHERE status_chamado = 'Aberto'";
    
    // Contagem de chamados EM ATENDIMENTO (Global)
    $sql_andamento = "SELECT COUNT(*) FROM chamados WHERE status_chamado = 'Em Atendimento'";
    
    // Contagem de Ativos (só para admin/tecnico)
    $stmt_ativos = $pdo->query("SELECT COUNT(*) FROM ativos");
    $total_ativos = $stmt_ativos->fetchColumn();
    $stmt_manutencao = $pdo->query("SELECT COUNT(*) FROM ativos WHERE status_ativo = 'EM_MANUTENCAO'");
    $total_manutencao = $stmt_manutencao->fetchColumn();

    // Lista de chamados recentes (Global)
    $sql_recentes_where = "WHERE (c.status_chamado = 'Aberto' OR c.status_chamado = 'Em Atendimento')";
}

// --- Execução das Queries ---

// 2. Contagem de Chamados
$stmt_abertos = $pdo->prepare($sql_abertos);
$stmt_abertos->execute($params_abertos);
$total_abertos = $stmt_abertos->fetchColumn();

$stmt_andamento = $pdo->prepare($sql_andamento);
$stmt_andamento->execute($params_andamento);
$total_andamento = $stmt_andamento->fetchColumn();

// 4. Buscar os 5 últimos chamados (com a lógica condicional)
$sql_recentes = "SELECT 
                    c.id_chamado,
                    c.titulo,
                    c.prioridade,
                    a.nome_ativo,
                    u.nome_unidade
                FROM chamados c
                $sql_recentes_join_ativo
                $sql_recentes_where
                ORDER BY c.dt_abertura DESC
                LIMIT 5";
$stmt_recentes = $pdo->prepare($sql_recentes);
$stmt_recentes->execute($params_recentes);
$chamados_recentes = $stmt_recentes->fetchAll();

// --- FIM DAS CONSULTAS ---
?>

<?php if ($usuario_role_logado == 'USUARIO'): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
<?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
<?php endif; ?>

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

    <?php if ($usuario_role_logado != 'USUARIO'): ?>
        <div class="bg-blue-400 shadow-lg rounded-lg p-6 text-white">
            <h5 class="text-lg font-semibold mb-2">Ativos em Manutenção</h5>
            <p class="text-5xl font-bold"><?= $total_manutencao ?></p>
            <a href="<?= $base_url ?>/ativos/index.php" class="text-blue-100 hover:text-white mt-4 inline-block">Ver Inventário</a>
        </div>

        <div class="bg-gray-700 shadow-lg rounded-lg p-6 text-white">
            <h5 class="text-lg font-semibold mb-2">Total de Ativos</h5>
            <p class="text-5xl font-bold"><?= $total_ativos ?></p>
            <a href="<?= $base_ci ?>/ativos/index.php" class="text-gray-300 hover:text-white mt-4 inline-block">Ver Inventário</a>
        </div>
    <?php endif; ?>
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
                <a href="<?= $base_url ?>/admin/unidades/index.php" class="w-full text-center bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-300 transition duration-300">
                    Gerenciar Unidades
                </a>
                <a href="<?= $base_url ?>/admin/categorias/index.php" class="w-full text-center bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-300 transition duration-300">
                    Gerenciar Categorias
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2 bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-xl font-semibold">
                <?php if ($usuario_role_logado == 'USUARIO'): ?>
                    Meus Últimos Chamados
                <?php else: ?>
                    Últimos Chamados Urgentes (Global)
                <?php endif; ?>
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
// 3. Inclui o footer
require_once 'includes/footer.php';
?>