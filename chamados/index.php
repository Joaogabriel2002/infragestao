<?php
// /chamados/index.php (ATUALIZADO COM FILTROS)

// 1. Inclui o Header
require_once '../includes/header.php'; // Sobe um nível

// =======================================================
// !! NOVO: Busca de dados para os filtros !!
// =======================================================
$sql_usuarios = "SELECT id_usuario, nome FROM usuarios WHERE ativo = true ORDER BY nome";
$lista_usuarios_filtro = $pdo->query($sql_usuarios)->fetchAll();
// =======================================================


// --- LÓGICA DE VISUALIZAÇÃO (Refatorada com Filtros) ---

// 1. Filtros da URL
$view_mode = $_GET['view'] ?? 'meus'; // 'meus' ou 'todos'
$status_filtro = $_GET['status'] ?? ''; // 'Aberto', 'Em Atendimento', 'Fechado', ou ''

// 2. NOVOS Filtros
$filtro_id = $_GET['filtro_id'] ?? '';
$filtro_pessoa = $_GET['filtro_pessoa'] ?? '';
$filtro_data_inicio = $_GET['filtro_data_inicio'] ?? '';
$filtro_data_fim = $_GET['filtro_data_fim'] ?? '';

// 3. Montagem da Query
$sql_where_parts = []; // Array para guardar as condições WHERE
$params = []; // Array para guardar os valores do prepared statement
$page_title = ""; // Título dinâmico da página

// 3.1 Filtro de Visualização (Meus vs. Todos)
if ($view_mode == 'meus' || $usuario_role_logado == 'USUARIO') {
    $page_title = "Meus Chamados";
    $sql_where_parts[] = "c.autor_id = ?";
    $params[] = $usuario_id_logado;
} else {
    $page_title = "Todos os Chamados";
}

// 3.2 Filtro de Status
if ($status_filtro == 'Aberto') {
    $sql_where_parts[] = "c.status_chamado = 'Aberto'";
    $page_title .= " (Abertos)";
} elseif ($status_filtro == 'Em Atendimento') {
    $sql_where_parts[] = "c.status_chamado = 'Em Atendimento'";
    $page_title .= " (Em Atendimento)";
} elseif ($status_filtro == 'Fechado') {
    $sql_where_parts[] = "c.status_chamado = 'Fechado'";
    $page_title .= " (Histórico/Fechados)";
} else {
    // Padrão: Abertos + Em Atendimento
    $sql_where_parts[] = "c.status_chamado IN ('Aberto', 'Em Atendimento')";
    if ($view_mode != 'meus') $page_title .= " (Ativos)";
}

// 3.3 NOVOS FILTROS (ID, Pessoa, Data)
if (!empty($filtro_id)) {
    $sql_where_parts[] = "c.id_chamado = ?";
    $params[] = $filtro_id;
}
if (!empty($filtro_pessoa)) {
    $sql_where_parts[] = "c.autor_id = ?";
    $params[] = $filtro_pessoa;
}
if (!empty($filtro_data_inicio)) {
    // Adiciona a hora para pegar o dia inteiro
    $sql_where_parts[] = "c.dt_abertura >= ?";
    $params[] = $filtro_data_inicio . ' 00:00:00';
}
if (!empty($filtro_data_fim)) {
    // Adiciona a hora para pegar o dia inteiro
    $sql_where_parts[] = "c.dt_abertura <= ?";
    $params[] = $filtro_data_fim . ' 23:59:59';
}

// 3.4 Monta a string final do WHERE
$sql_where = "WHERE " . implode(" AND ", $sql_where_parts);


// 4. Query SQL Principal
$sql = "SELECT 
            c.id_chamado, c.titulo, c.status_chamado, c.prioridade, c.dt_abertura,
            a.nome_ativo,
            cat.nome_categoria,
            u_autor.nome AS nome_autor
        FROM chamados c
        LEFT JOIN ativos a ON c.ativo_id = a.id_ativo
        LEFT JOIN categorias cat ON c.categoria_id = cat.id_categoria
        LEFT JOIN usuarios u_autor ON c.autor_id = u_autor.id_usuario
        $sql_where
        ORDER BY c.dt_abertura DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$lista_chamados = $stmt->fetchAll();

// --- FIM DA LÓGICA ---
?>

<div class="bg-white shadow-md rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold"><?= htmlspecialchars($page_title) ?></h2>
        <a href="<?= $base_url ?>/chamados/novo.php" 
           class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-300">
           + Abrir Novo Chamado
        </a>
    </div>

    <form action="index.php" method="GET" class="mb-6 p-4 bg-gray-50 rounded-lg border">
        <input type="hidden" name="status" value="<?= htmlspecialchars($status_filtro) ?>">
        <input type="hidden" name="view" value="<?= htmlspecialchars($view_mode) ?>">

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            
            <div>
                <label for="filtro_id" class="block text-sm font-medium text-gray-700">Nº do Chamado</label>
                <input type="text" id="filtro_id" name="filtro_id" 
                       class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg" 
                       placeholder="Ex: 123"
                       value="<?= htmlspecialchars($filtro_id) ?>">
            </div>
            
            <div>
                <label for="filtro_pessoa" class="block text-sm font-medium text-gray-700">Aberto Por (Pessoa)</label>
                <select id="filtro_pessoa" name="filtro_pessoa"
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">[Todas as Pessoas]</option>
                    <?php foreach ($lista_usuarios_filtro as $usuario): ?>
                        <option value="<?= $usuario['id_usuario'] ?>" <?= ($usuario['id_usuario'] == $filtro_pessoa) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($usuario['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="filtro_data_inicio" class="block text-sm font-medium text-gray-700">De (Data Abertura)</label>
                <input type="date" id="filtro_data_inicio" name="filtro_data_inicio" 
                       class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg"
                       value="<?= htmlspecialchars($filtro_data_inicio) ?>">
            </div>

            <div>
                <label for="filtro_data_fim" class="block text-sm font-medium text-gray-700">Até (Data Abertura)</label>
                <input type="date" id="filtro_data_fim" name="filtro_data_fim" 
                       class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg"
                       value="<?= htmlspecialchars($filtro_data_fim) ?>">
            </div>

        </div>
        
        <div class="mt-4 flex justify-end space-x-2">
            <button type="submit" 
                    class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700">
                Filtrar
            </button>
            <a href="index.php?status=<?= htmlspecialchars($status_filtro) ?>&view=<?= htmlspecialchars($view_mode) ?>" 
               class="bg-gray-200 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-300">
                Limpar Filtros
            </a>
        </div>
    </form>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-4 text-left text-gray-600 font-semibold">ID</th>
                    <th class="py-3 px-4 text-left text-gray-600 font-semibold">Título / Ativo</th>
                    <th class="py-3 px-4 text-left text-gray-600 font-semibold">Autor</th>
                    <th class="py-3 px-4 text-left text-gray-600 font-semibold">Status</th>
                    <th class="py-3 px-4 text-left text-gray-600 font-semibold">Prioridade</th>
                    <th class="py-3 px-4 text-left text-gray-600 font-semibold">Data</th>
                    <th class="py-3 px-4 text-left text-gray-600 font-semibold">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($lista_chamados) > 0): ?>
                    <?php foreach ($lista_chamados as $chamado): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            
                            <td class="py-3 px-4 font-medium text-gray-800">#<?= $chamado['id_chamado'] ?></td>
                            
                            <td class="py-3 px-4">
                                <a href="<?= $base_url ?>/chamados/ver.php?id=<?= $chamado['id_chamado'] ?>" 
                                   class="text-blue-600 hover:underline font-semibold">
                                   <?= htmlspecialchars($chamado['titulo']) ?>
                                </a>
                                <div class="text-sm text-gray-500">
                                    <?= htmlspecialchars($chamado['nome_ativo'] ?? $chamado['nome_categoria']) ?>
                                </div>
                            </td>
                            
                            <td class="py-3 px-4 text-gray-700"><?= htmlspecialchars($chamado['nome_autor']) ?></td>
                            
                            <td class="py-3 px-4">
                                <?php 
                                $status_class = 'bg-gray-500 text-white'; // Padrão
                                if ($chamado['status_chamado'] == 'Aberto') $status_class = 'bg-red-500 text-white';
                                if ($chamado['status_chamado'] == 'Em Atendimento') $status_class = 'bg-yellow-500 text-black';
                                if ($chamado['status_chamado'] == 'Fechado') $status_class = 'bg-green-500 text-white';
                                ?>
                                <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full <?= $status_class ?>">
                                    <?= htmlspecialchars($chamado['status_chamado']) ?>
                                </span>
                            </td>

                            <td class="py-3 px-4">
                                <?php 
                                $p_class = 'bg-gray-500 text-white'; // Padrão
                                if ($chamado['prioridade'] == 'URGENTE') $p_class = 'bg-red-600 text-white';
                                elseif ($chamado['prioridade'] == 'ALTA') $p_class = 'bg-yellow-500 text-black';
                                elseif ($chamado['prioridade'] == 'MEDIA') $p_class = 'bg-blue-500 text-white';
                                elseif ($chamado['prioridade'] == 'BAIXA') $p_class = 'bg-green-500 text-white';
                                ?>
                                <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full <?= $p_class ?>">
                                    <?= htmlspecialchars($chamado['prioridade']) ?>
                                </span>
                            </td>

                            <td class="py-3 px-4 text-gray-700">
                                <?= (new DateTime($chamado['dt_abertura']))->format('d/m/Y') ?>
                            </td>

                            <td class="py-3 px-4">
                                <a href="<?= $base_url ?>/chamados/ver.php?id=<?= $chamado['id_chamado'] ?>" 
                                   class="bg-gray-200 text-gray-700 px-3 py-1 rounded-lg text-sm hover:bg-gray-300">
                                   Ver
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="py-6 text-center text-gray-500">
                            Nenhum chamado encontrado com este filtro.
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