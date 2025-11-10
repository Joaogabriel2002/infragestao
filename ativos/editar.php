<?php
// /ativos/editar.php

// 1. Inclui o Header
require_once '../includes/header.php'; // Sobe um nível

// 2. Lógica de Segurança
if ($usuario_role_logado == 'USUARIO') {
    echo "<p>Acesso negado.</p>";
    require_once '../includes/footer.php';
    exit;
}

// 3. Validação do ID
// Pega o ID da URL (ex: editar.php?id=5)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: {$base_url}/ativos/index.php");
    exit;
}
$id_ativo = (int)$_GET['id'];

// 4. Busca os dados do Ativo Específico
$stmt_ativo = $pdo->prepare("SELECT * FROM ativos WHERE id_ativo = ?");
$stmt_ativo->execute([$id_ativo]);
$ativo = $stmt_ativo->fetch();

// Se o ativo não for encontrado, redireciona
if (!$ativo) {
    header("Location: {$base_url}/ativos/index.php");
    exit;
}

// --- INÍCIO DAS CONSULTAS PARA OS DROPDOWNS ---

// 5. Buscar os MODELOS de Equipamentos
$sql_modelos = "SELECT 
                    m.id_modelo, 
                    m.nome 
                FROM catalogo_modelos m
                JOIN categorias_ativo cat ON m.categoria_ativo_id = cat.id_categoria_ativo
                WHERE cat.controla_estoque = false
                ORDER BY m.nome";
$stmt_modelos = $pdo->query($sql_modelos);
$lista_modelos = $stmt_modelos->fetchAll();


// 6. Buscar todas as UNIDADES (locais)
$sql_unidades = "SELECT id_unidade, nome_unidade FROM unidades ORDER BY nome_unidade";
$stmt_unidades = $pdo->query($sql_unidades);
$lista_unidades = $stmt_unidades->fetchAll();

// --- FIM DAS CONSULTAS ---
?>

<div class="bg-white shadow-md rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-6">Editar Ativo: <?= htmlspecialchars($ativo['nome_ativo']) ?></h2>

    <form action="processar.php" method="POST">
        
        <input type="hidden" name="acao" value="editar_ativo">
        <input type="hidden" name="id_ativo" value="<?= $ativo['id_ativo'] ?>">

        <div class="mb-4">
            <label for="nome_ativo" class="block text-gray-700 font-semibold mb-2">Nome do Ativo *</label>
            <input type="text" id="nome_ativo" name="nome_ativo" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" 
                   value="<?= htmlspecialchars($ativo['nome_ativo']) ?>" required>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
            
            <div>
                <label for="modelo_id" class="block text-gray-700 font-semibold mb-2">Modelo *</label>
                <select id="modelo_id" name="modelo_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                    <option value="">[Selecione o modelo]</option>
                    <?php foreach ($lista_modelos as $modelo): ?>
                        <option value="<?= $modelo['id_modelo'] ?>" <?= ($modelo['id_modelo'] == $ativo['modelo_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($modelo['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="unidade_id" class="block text-gray-700 font-semibold mb-2">Unidade (Local) *</label>
                <select id="unidade_id" name="unidade_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                    <option value="">[Selecione a unidade]</option>
                    <?php foreach ($lista_unidades as $unidade): ?>
                        <option value="<?= $unidade['id_unidade'] ?>" <?= ($unidade['id_unidade'] == $ativo['unidade_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($unidade['nome_unidade']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <hr class="my-6">
        <h3 class="text-lg font-semibold text-gray-600 mb-4">Informações Adicionais</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
            <div>
                <label for="patrimonio" class="block text-gray-700 font-semibold mb-2">Patrimônio</label>
                <input type="text" id="patrimonio" name="patrimonio" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" 
                       value="<?= htmlspecialchars($ativo['patrimonio'] ?? '') ?>">
            </div>
            <div>
                <label for="ip_address" class="block text-gray-700 font-semibold mb-2">Endereço de IP</label>
                <input type="text" id="ip_address" name="ip_address" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" 
                       value="<?= htmlspecialchars($ativo['ip_address'] ?? '') ?>">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
            <div>
                <label for="remote_id" class="block text-gray-700 font-semibold mb-2">ID de Acesso Remoto</label>
                <input type="text" id="remote_id" name="remote_id" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" 
                       value="<?= htmlspecialchars($ativo['remote_id'] ?? '') ?>">
            </div>
            <div>
                <label for="operating_system" class="block text-gray-700 font-semibold mb-2">Sistema Operacional</label>
                <input type="text" id="operating_system" name="operating_system" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" 
                       value="<?= htmlspecialchars($ativo['operating_system'] ?? '') ?>">
            </div>
        </div>
        
        <div class="mb-6">
            <label for="status_ativo" class="block text-gray-700 font-semibold mb-2">Status do Ativo *</label>
            <select id="status_ativo" name="status_ativo" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                <option value="Ativo" <?= ($ativo['status_ativo'] == 'Ativo') ? 'selected' : '' ?>>Ativo</option>
                <option value="EM_MANUTENCAO" <?= ($ativo['status_ativo'] == 'EM_MANUTENCAO') ? 'selected' : '' ?>>Em Manutenção</option>
                <option value="BAIXADO" <?= ($ativo['status_ativo'] == 'BAIXADO') ? 'selected' : '' ?>>Baixado (Descartado)</option>
            </select>
        </div>

        <div class="mt-6">
            <button type="submit" 
                    class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-300">
                Salvar Alterações
            </button>
        </div>

    </form>
</div>


<?php
// 3. Inclui o Footer
require_once '../includes/footer.php';
?>