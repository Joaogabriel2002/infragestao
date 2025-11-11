<?php
// /chamados/ver.php (ATUALIZADO COM LÓGICA DE CHAMADO FECHADO)

// 1. Inclui o Header
require_once '../includes/header.php'; 

// 2. Validação do ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { /* ... */ }
$chamado_id = (int)$_GET['id'];

// --- BUSCA PRINCIPAL (DETALHES DO CHAMADO) ---
$sql = "SELECT c.*, u_autor.nome AS nome_autor, u_autor.email AS email_autor, u_tecnico.nome AS nome_tecnico, cat.nome_categoria, a.nome_ativo, un.nome_unidade
        FROM chamados c
        LEFT JOIN usuarios u_autor ON c.autor_id = u_autor.id_usuario
        LEFT JOIN usuarios u_tecnico ON c.tecnico_id = u_tecnico.id_usuario
        LEFT JOIN categorias cat ON c.categoria_id = cat.id_categoria
        LEFT JOIN ativos a ON c.ativo_id = a.id_ativo
        LEFT JOIN unidades un ON a.unidade_id = un.id_unidade
        WHERE c.id_chamado = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$chamado_id]);
$chamado = $stmt->fetch();

if (!$chamado) { /* ... */ }

// =======================================================
// !! NOVA VARIÁVEL DE CONTROLE !!
// =======================================================
$is_fechado = ($chamado['status_chamado'] == 'Fechado');
// =======================================================

// --- BUSCA HISTÓRICO (ATUALIZAÇÕES) ---
$sql_updates = "SELECT cu.*, u.nome AS nome_autor_update
                FROM chamado_atualizacoes cu
                JOIN usuarios u ON cu.autor_id = u.id_usuario
                WHERE cu.chamado_id = ?
                ORDER BY cu.dt_atualizacao ASC";
$stmt_updates = $pdo->prepare($sql_updates);
$stmt_updates->execute([$chamado_id]);
$atualizacoes = $stmt_updates->fetchAll();

// --- BUSCA ITENS DE ESTOQUE USADOS ---
$sql_estoque = "SELECT mov.id_movimentacao, mov.quantidade, mov.data_movimentacao, cat.nome AS nome_item
                FROM movimentacoes_estoque mov
                JOIN catalogo_modelos cat ON mov.modelo_id = cat.id_modelo
                WHERE mov.chamado_id = ?
                ORDER BY mov.data_movimentacao ASC";
$stmt_estoque = $pdo->prepare($sql_estoque);
$stmt_estoque->execute([$chamado_id]);
$itens_usados = $stmt_estoque->fetchAll();

// --- BUSCA LISTA DE TÉCNICOS (Para o <select>) ---
$sql_tecnicos = "SELECT id_usuario, nome FROM usuarios WHERE role IN ('TECNICO', 'ADMIN') AND ativo = true";
$lista_tecnicos = $pdo->query($sql_tecnicos)->fetchAll();

// --- BUSCA LISTA DE ITENS DE ESTOQUE (Para o <select>) ---
$sql_itens = "SELECT m.id_modelo, m.nome 
            FROM catalogo_modelos m
            JOIN categorias_ativo cat ON m.categoria_ativo_id = cat.id_categoria_ativo
            WHERE cat.controla_estoque = true AND m.quantidade_em_estoque > 0
            ORDER BY m.nome";
$lista_itens_estoque = $pdo->query($sql_itens)->fetchAll();

?>

<div class="mb-6">
    <h2 class="text-3xl font-bold text-gray-800">
        Chamado #<?= $chamado['id_chamado'] ?>: <?= htmlspecialchars($chamado['titulo']) ?>
    </h2>
    <p class="text-sm text-gray-500">
        Aberto por <?= htmlspecialchars($chamado['nome_autor']) ?> em 
        <?= (new DateTime($chamado['dt_abertura']))->format('d/m/Y \à\s H:i') ?>
    </p>
</div>

<?php
if (isset($_GET['erro'])) { /* ... (bloco de erro) ... */ }
if (isset($_GET['sucesso'])) {
    if ($_GET['sucesso'] == 'novo') echo "<div class='bg-green-100 text-green-700 p-4 rounded mb-6'><strong>Sucesso!</strong> Chamado aberto.</div>";
    if ($_GET['sucesso'] == 'removido') echo "<div class='bg-green-100 text-green-700 p-4 rounded mb-6'><strong>Sucesso!</strong> Item de estoque estornado.</div>";
    if ($_GET['sucesso'] == 'add_estoque') echo "<div class='bg-green-100 text-green-700 p-4 rounded mb-6'><strong>Sucesso!</strong> Item baixado do estoque.</div>";
    // !! NOVA MENSAGEM !!
    if ($_GET['sucesso'] == 'reaberto') echo "<div class='bg-blue-100 text-blue-700 p-4 rounded mb-6'><strong>Aviso!</strong> O chamado foi reaberto.</div>";
}
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 space-y-6">
        
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-xl font-semibold mb-4 border-b pb-2">Detalhes do Problema</h3>
            <p class="text-gray-700 whitespace-pre-wrap"><?= htmlspecialchars($chamado['problema_relatado']) ?></p>
            
            <?php if ($chamado['solucao_aplicada']): ?>
                <h3 class="text-xl font-semibold mt-6 mb-4 border-b pb-2">Solução Aplicada</h3>
                <p class="text-gray-700 whitespace-pre-wrap"><?= htmlspecialchars($chamado['solucao_aplicada']) ?></p>
            <?php endif; ?>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-xl font-semibold mb-4">Peças e Consumíveis Usados</h3>
            <?php if (count($itens_usados) > 0): ?>
                <ul class="list-none space-y-2 text-gray-700">
                    <?php foreach ($itens_usados as $item): ?>
                        <li class="flex justify-between items-center">
                            <span>
                                <strong><?= $item['quantidade'] ?>x</strong> <?= htmlspecialchars($item['nome_item']) ?>
                                <span class="text-sm text-gray-500">(em <?= (new DateTime($item['data_movimentacao']))->format('d/m/Y') ?>)</span>
                            </span>
                            
                            <?php if (($usuario_role_logado == 'TECNICO' || $usuario_role_logado == 'ADMIN') && !$is_fechado): ?>
                                <a href="processar.php?acao=remover_estoque&id=<?= $chamado_id ?>&mov_id=<?= $item['id_movimentacao'] ?>" 
                                   class="text-red-500 hover:text-red-700 hover:underline text-sm font-medium"
                                   onclick="return confirm('Tem certeza que deseja estornar este item? A quantidade voltará ao estoque.')">
                                    [Remover]
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-500">Nenhuma peça ou consumível foi vinculado a este chamado ainda.</p>
            <?php endif; ?>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-xl font-semibold mb-4">Histórico do Chamado</h3>
            <div class="space-y-4">
                <?php foreach ($atualizacoes as $update): ?>
                    <div class="border-b pb-4">
                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($update['nome_autor_update']) ?></p>
                        <p class="text-sm text-gray-500 mb-2">
                            em <?= (new DateTime($update['dt_atualizacao']))->format('d/m/Y \à\s H:i') ?>
                        </p>
                        <p class="text-gray-700 whitespace-pre-wrap"><?= htmlspecialchars($update['comentario']) ?></p>
                    </div>
                <?php endforeach; ?>
                <?php if (count($atualizacoes) === 0): ?>
                    <p class="text-gray-500">Nenhum comentário adicionado ainda.</p>
                <?php endif; ?>
            </div>

            <?php if (($usuario_role_logado == 'TECNICO' || $usuario_role_logado == 'ADMIN') && !$is_fechado): ?>
                <hr class="my-6">
                <h4 class="text-lg font-semibold mb-3">Adicionar Comentário</h4>
                <form action="processar.php" method="POST">
                    <input type="hidden" name="acao" value="add_comentario">
                    <input type="hidden" name="chamado_id" value="<?= $chamado_id ?>">
                    <div>
                        <textarea name="comentario" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" 
                                  placeholder="Digite seu comentário..." required></textarea>
                    </div>
                    <div class="mt-3 text-right">
                        <button type="submit" 
                                class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-300">
                            Enviar Comentário
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>

    </div>

    <div class="lg:col-span-1 space-y-6">
        
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-xl font-semibold mb-4 border-b pb-2">Informações</h3>
            <div class="flex space-x-2 mb-4">
                <?php 
                $status_class = 'bg-gray-500 text-white'; 
                if ($chamado['status_chamado'] == 'Aberto') $status_class = 'bg-red-500 text-white';
                if ($chamado['status_chamado'] == 'Em Atendimento') $status_class = 'bg-yellow-500 text-black';
                if ($chamado['status_chamado'] == 'Fechado') $status_class = 'bg-green-500 text-white';
                ?>
                <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full <?= $status_class ?>">
                    Status: <?= htmlspecialchars($chamado['status_chamado']) ?>
                </span>
                <?php 
                $p_class = 'bg-gray-500 text-white'; 
                if ($chamado['prioridade'] == 'URGENTE') $p_class = 'bg-red-600 text-white';
                elseif ($chamado['prioridade'] == 'ALTA') $p_class = 'bg-yellow-500 text-black';
                elseif ($chamado['prioridade'] == 'MEDIA') $p_class = 'bg-blue-500 text-white';
                elseif ($chamado['prioridade'] == 'BAIXA') $p_class = 'bg-green-500 text-white';
                ?>
                <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full <?= $p_class ?>">
                    Prioridade: <?= htmlspecialchars($chamado['prioridade']) ?>
                </span>
            </div>
            <ul class="text-gray-700 space-y-2">
                <li><strong>Autor:</strong> <?= htmlspecialchars($chamado['nome_autor']) ?></li>
                <li><strong>Email:</strong> <?= htmlspecialchars($chamado['email_autor']) ?></li>
                <li><strong>Categoria:</strong> <?= htmlspecialchars($chamado['nome_categoria']) ?></li>
                <li><strong>Ativo:</strong> <?= htmlspecialchars($chamado['nome_ativo'] ?? 'N/A') ?></li>
                <li><strong>Unidade:</strong> <?= htmlspecialchars($chamado['nome_unidade'] ?? 'N/A') ?></li>
                <li><strong>Técnico:</strong> <?= htmlspecialchars($chamado['nome_tecnico'] ?? 'Não atribuído') ?></li>
            </ul>
        </div>

        <?php if (($usuario_role_logado == 'TECNICO' || $usuario_role_logado == 'ADMIN') && !$is_fechado): ?>
            
            <div class="bg-white shadow-md rounded-lg p-6">
                <h3 class="text-xl font-semibold mb-4">Ações do Técnico</h3>
                <form action="processar.php" method="POST" class="space-y-4">
                    <input type="hidden" name="acao" value="update_chamado">
                    <input type="hidden" name="chamado_id" value="<?= $chamado_id ?>">
                    
                    <?php if (empty($chamado['tecnico_id'])): ?>
                        <button type="submit" name="assumir" value="1" 
                                class="w-full bg-green-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-green-700 transition duration-300">
                            Assumir Chamado
                        </button>
                        <hr>
                    <?php endif; ?>

                    <div>
                        <label for="status" class="block text-gray-700 font-semibold mb-2">Mudar Status</label>
                        <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="Aberto" <?= ($chamado['status_chamado'] == 'Aberto') ? 'selected' : '' ?>>Aberto</option>
                            <option value="Em Atendimento" <?= ($chamado['status_chamado'] == 'Em Atendimento') ? 'selected' : '' ?>>Em Atendimento</option>
                            <option value="Fechado" <?= ($chamado['status_chamado'] == 'Fechado') ? 'selected' : '' ?>>Fechado</option>
                        </select>
                    </div>

                    <div>
                        <label for="tecnico_id" class="block text-gray-700 font-semibold mb-2">Atribuir Técnico</label>
                        <select id="tecnico_id" name="tecnico_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="">[Não atribuído]</option>
                            <?php foreach ($lista_tecnicos as $tecnico): ?>
                                <option value="<?= $tecnico['id_usuario'] ?>" <?= ($chamado['tecnico_id'] == $tecnico['id_usuario']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tecnico['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="solucao_aplicada" class="block text-gray-700 font-semibold mb-2">Solução Aplicada</label>
                        <textarea id="solucao_aplicada" name="solucao_aplicada" rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg" 
                                  placeholder="Descreva a solução aplicada (obrigatório para fechar)."><?= htmlspecialchars($chamado['solucao_aplicada']) ?></textarea>
                    </div>

                    <button type="submit" 
                            class="w-full bg-gray-700 text-white font-bold py-2 px-4 rounded-lg hover:bg-gray-800 transition duration-300">
                        Salvar Alterações
                    </button>
                </form>
            </div>

            <div class="bg-white shadow-md rounded-lg p-6">
                <h3 class="text-xl font-semibold mb-4">Adicionar Peça / Consumível</h3>
                <form action="processar.php" method="POST">
                    <input type="hidden" name="acao" value="add_estoque">
                    <input type="hidden" name="chamado_id" value="<?= $chamado_id ?>">

                    <div class="mb-4">
                        <label for="modelo_id" class="block text-gray-700 font-semibold mb-2">Item</label>
                        <select id="modelo_id" name="modelo_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                            <option value="">[Selecione um item]</option>
                            <?php if (count($lista_itens_estoque) > 0): ?>
                                <?php foreach ($lista_itens_estoque as $item): ?>
                                    <option value="<?= $item['id_modelo'] ?>"><?= htmlspecialchars($item['nome']) ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>Nenhum item com estoque disponível</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                         <label for="quantidade" class="block text-gray-700 font-semibold mb-2">Quantidade</label>
                        <input type="number" id="quantidade" name="quantidade" value="1" min="1" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <button type="submit" 
                            class="w-full bg-cyan-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-cyan-700 transition duration-300"
                            <?= (count($lista_itens_estoque) == 0) ? 'disabled' : '' ?>>
                        Dar Baixa no Estoque
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($usuario_role_logado == 'ADMIN' && $is_fechado): ?>
            <div class="bg-white shadow-md rounded-lg p-6 border-l-4 border-orange-500">
                <h3 class="text-xl font-semibold mb-4">Ações de Admin</h3>
                <form action="processar.php" method="POST">
                    <input type="hidden" name="acao" value="reabrir_chamado">
                    <input type="hidden" name="chamado_id" value="<?= $chamado_id ?>">
                    <p class="text-sm text-gray-600 mb-4">Este chamado está fechado. Reabri-lo irá movê-lo para o status "Aberto" e apagar a data de fechamento.</p>
                    <button type="submit" 
                            class="w-full bg-orange-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-orange-600 transition duration-300">
                        Reabrir Chamado
                    </button>
                </form>
            </div>
        <?php endif; ?>

    </div>

</div>


<?php
// 3. Inclui o Footer
require_once '../includes/footer.php';
?>