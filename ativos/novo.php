<?php
// /ativos/novo.php (ATUALIZADO COM ALOCAÇÃO)

require_once '../includes/header.php'; // Sobe 1 nível
if ($usuario_role_logado == 'USUARIO') { /* ... (Segurança) ... */ }

// --- INÍCIO DAS CONSULTAS PARA OS DROPDOWNS ---

// 3. Buscar os MODELOS (existente)
$sql_modelos = "SELECT m.id_modelo, m.nome 
                FROM catalogo_modelos m
                JOIN categorias_ativo cat ON m.categoria_ativo_id = cat.id_categoria_ativo
                WHERE cat.controla_estoque = false ORDER BY m.nome";
$lista_modelos = $pdo->query($sql_modelos)->fetchAll();

// 4. Buscar todas as UNIDADES (existente)
$sql_unidades = "SELECT id_unidade, nome_unidade FROM unidades ORDER BY nome_unidade";
$lista_unidades = $pdo->query($sql_unidades)->fetchAll();

// 5. !! NOVO !! Buscar SETORES (para alocação)
$sql_setores = "SELECT id_setor, nome_setor FROM setores ORDER BY nome_setor";
$lista_setores = $pdo->query($sql_setores)->fetchAll();

// 6. !! NOVO !! Buscar USUÁRIOS (para alocação)
$sql_usuarios = "SELECT id_usuario, nome FROM usuarios WHERE ativo = true ORDER BY nome";
$lista_usuarios = $pdo->query($sql_usuarios)->fetchAll();

?>

<div class="bg-white shadow-md rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-6">Cadastrar Novo Ativo</h2>

    <form action="processar.php" method="POST">
        <input type="hidden" name="acao" value="novo_ativo">

        <div class="mb-4">
            <label for="nome_ativo" class="block text-gray-700 font-semibold mb-2">Nome do Ativo *</label>
            <input type="text" id="nome_ativo" name="nome_ativo" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg" 
                   placeholder="Ex: PC-FINANCEIRO-01" required>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
            <div>
                <label for="modelo_id" class="block text-gray-700 font-semibold mb-2">Modelo *</label>
                <select id="modelo_id" name="modelo_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                    <option value="">[Selecione o modelo]</option>
                    <?php foreach ($lista_modelos as $modelo): ?>
                        <option value="<?= $modelo['id_modelo'] ?>"><?= htmlspecialchars($modelo['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="unidade_id" class="block text-gray-700 font-semibold mb-2">Unidade (Local Físico) *</label>
                <select id="unidade_id" name="unidade_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                    <option value="">[Selecione a unidade]</option>
                    <?php foreach ($lista_unidades as $unidade): ?>
                        <option value="<?= $unidade['id_unidade'] ?>"><?= htmlspecialchars($unidade['nome_unidade']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <hr class="my-6">
        <h3 class="text-lg font-semibold text-gray-600 mb-4">Alocação (Opcional)</h3>
        <p class="text-sm text-gray-500 mb-4">
            A quem este ativo pertence? (Preencha um, ou outro, mas não ambos).
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
            <div>
                <label for="setor_id" class="block text-gray-700 font-semibold mb-2">Alocar ao Setor</label>
                <select id="setor_id" name="setor_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">[Nenhum setor]</option>
                    <?php foreach ($lista_setores as $setor): ?>
                        <option value="<?= $setor['id_setor'] ?>"><?= htmlspecialchars($setor['nome_setor']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-sm text-gray-500 mt-1">Para equipamentos compartilhados (Ex: Impressora do Financeiro).</p>
            </div>
            
            <div>
                <label for="usuario_id" class="block text-gray-700 font-semibold mb-2">Alocar ao Usuário</label>
                <select id="usuario_id" name="usuario_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">[Nenhum usuário]</option>
                    <?php foreach ($lista_usuarios as $usuario): ?>
                        <option value="<?= $usuario['id_usuario'] ?>"><?= htmlspecialchars($usuario['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-sm text-gray-500 mt-1">Para equipamentos pessoais (Ex: Notebook do João).</p>
            </div>
        </div>
        <hr class="my-6">
        <h3 class="text-lg font-semibold text-gray-600 mb-4">Informações Adicionais (Opcional)</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
            <div>
                <label for="patrimonio" class="block text-gray-700 font-semibold mb-2">Patrimônio</label>
                <input type="text" id="patrimonio" name="patrimonio" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label for="ip_address" class="block text-gray-700 font-semibold mb-2">Endereço de IP</label>
                <input type="text" id="ip_address" name="ip_address" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
            <div>
                <label for="remote_id" class="block text-gray-700 font-semibold mb-2">ID de Acesso Remoto</label>
                <input type="text" id="remote_id" name="remote_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label for="operating_system" class="block text-gray-700 font-semibold mb-2">Sistema Operacional</label>
                <input type="text" id="operating_system" name="operating_system" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
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
require_once '../includes/footer.php';
?>