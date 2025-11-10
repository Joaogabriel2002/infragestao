<?php
// /ativos/novo.php

// 1. Inclui o Header
require_once '../includes/header.php'; // Sobe um nível

// 2. Lógica de Segurança
if ($usuario_role_logado == 'USUARIO') {
    echo "<p>Acesso negado.</p>";
    require_once '../includes/footer.php';
    exit;
}

// --- INÍCIO DAS CONSULTAS PARA OS DROPDOWNS ---

// 3. Buscar os MODELOS de Equipamentos
// (Usando nossa nova v3.1: buscamos no catálogo onde a categoria NÃO controla estoque)
$sql_modelos = "SELECT 
                    m.id_modelo, 
                    m.nome 
                FROM catalogo_modelos m
                JOIN categorias_ativo cat ON m.categoria_ativo_id = cat.id_categoria_ativo
                WHERE cat.controla_estoque = false
                ORDER BY m.nome";
$stmt_modelos = $pdo->query($sql_modelos);
$lista_modelos = $stmt_modelos->fetchAll();


// 4. Buscar todas as UNIDADES (locais)
$sql_unidades = "SELECT id_unidade, nome_unidade FROM unidades ORDER BY nome_unidade";
$stmt_unidades = $pdo->query($sql_unidades);
$lista_unidades = $stmt_unidades->fetchAll();

// --- FIM DAS CONSULTAS ---
?>

<div class="bg-white shadow-md rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-6">Cadastrar Novo Ativo</h2>

    <form action="processar.php" method="POST">
        
        <input type="hidden" name="acao" value="novo_ativo">

        <div class="mb-4">
            <label for="nome_ativo" class="block text-gray-700 font-semibold mb-2">Nome do Ativo *</label>
            <input type="text" id="nome_ativo" name="nome_ativo" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" 
                   placeholder="Ex: PC-FINANCEIRO-01, PDV-CAIXA-02, IMPRESSORA-RH" required>
            <p class="text-sm text-gray-500 mt-1">Um nome único para identificar o equipamento.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
            
            <div>
                <label for="modelo_id" class="block text-gray-700 font-semibold mb-2">Modelo *</label>
                <select id="modelo_id" name="modelo_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                    
                    <option value="">[Selecione o modelo]</option>
                    
                    <?php foreach ($lista_modelos as $modelo): ?>
                        <option value="<?= $modelo['id_modelo'] ?>">
                            <?= htmlspecialchars($modelo['nome']) ?>
                        </option>
                    <?php endforeach; ?>

                </select>
                <p class="text-sm text-gray-500 mt-1">
                    Se o modelo não está na lista, cadastre-o primeiro no 
                    <a href="<?= $base_url ?>/estoque/index.php" class="text-blue-600 hover:underline">Catálogo de Modelos</a>.
                </p>
            </div>

            <div>
                <label for="unidade_id" class="block text-gray-700 font-semibold mb-2">Unidade (Local) *</label>
                <select id="unidade_id" name="unidade_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                    
                    <option value="">[Selecione a unidade]</option>

                    <?php foreach ($lista_unidades as $unidade): ?>
                        <option value="<?= $unidade['id_unidade'] ?>">
                            <?= htmlspecialchars($unidade['nome_unidade']) ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>
        </div>

        <hr class="my-6">
        <h3 class="text-lg font-semibold text-gray-600 mb-4">Informações Adicionais (Opcional)</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
            <div>
                <label for="patrimonio" class="block text-gray-700 font-semibold mb-2">Patrimônio</label>
                <input type="text" id="patrimonio" name="patrimonio" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" 
                       placeholder="Ex: 1000456">
            </div>
            <div>
                <label for="ip_address" class="block text-gray-700 font-semibold mb-2">Endereço de IP</label>
                <input type="text" id="ip_address" name="ip_address" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" 
                       placeholder="Ex: 192.168.1.50">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
            <div>
                <label for="remote_id" class="block text-gray-700 font-semibold mb-2">ID de Acesso Remoto</label>
                <input type="text" id="remote_id" name="remote_id" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" 
                       placeholder="Ex: AnyDesk, TeamViewer...">
            </div>
            <div>
                <label for="operating_system" class="block text-gray-700 font-semibold mb-2">Sistema Operacional</label>
                <input type="text" id="operating_system" name="operating_system" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" 
                       placeholder="Ex: Windows 11 Pro">
            </div>
        </div>
        
        <div class="mt-6">
            <button type="submit" 
                    class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-300">
                Salvar Ativo
            </button>
        </div>

    </form>
</div>


<?php
// 3. Inclui o Footer
require_once '../includes/footer.php';
?>