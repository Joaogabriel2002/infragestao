<?php
// /admin/usuarios/index.php (ATUALIZADO)

require_once '../../includes/header.php'; // Sobe 2 níveis

// Segurança: Só Admins
if ($usuario_role_logado != 'ADMIN') {
    echo "<p>Acesso negado.</p>";
    require_once '../../includes/footer.php';
    exit;
}

// --- Lógica de Feedback (Mensagens de sucesso/erro) ---
$feedback = '';
if (isset($_GET['sucesso'])) {
    if ($_GET['sucesso'] == 'novo') $feedback = "<div class='bg-green-100 text-green-700 p-3 rounded mb-4'>Usuário salvo com sucesso!</div>";
    if ($_GET['sucesso'] == 'editado') $feedback = "<div class='bg-green-100 text-green-700 p-3 rounded mb-4'>Usuário atualizado com sucesso!</div>";
    if ($_GET['sucesso'] == 'excluido') $feedback = "<div class='bg-green-100 text-green-700 p-3 rounded mb-4'>Usuário excluído com sucesso.</div>";
}
if (isset($_GET['erro'])) {
    if ($_GET['erro'] == 'email_duplicado') $feedback = "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>Erro: Este e-mail já está cadastrado.</div>";
    if ($_GET['erro'] == 'auto_delete') $feedback = "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>Erro: Você não pode excluir a si mesmo.</div>";
    if ($_GET['erro'] == 'admin_raiz') $feedback = "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>Erro: Este usuário (Admin Raiz) não pode ser excluído.</div>";
}
// --- Fim da Lógica de Feedback ---


// --- Busca de Dados para a Página ---
$lista_usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY nome")->fetchAll();
$lista_setores = $pdo->query("SELECT * FROM setores ORDER BY nome_setor")->fetchAll();
$lista_unidades = $pdo->query("SELECT * FROM unidades ORDER BY nome_unidade")->fetchAll();

?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    
    <div class="md:col-span-1">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Novo Usuário</h2>
            <?= $feedback ?> <form action="processar.php" method="POST" class="space-y-4">
                <input type="hidden" name="acao" value="novo_usuario">
                
                <div>
                    <label for="nome" class="block text-gray-700 font-semibold mb-2">Nome Completo *</label>
                    <input type="text" id="nome" name="nome" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                </div>

                <div>
                    <label for="email" class="block text-gray-700 font-semibold mb-2">E-mail (Login) *</label>
                    <input type="email" id="email" name="email" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                </div>

                <div>
                    <label for="senha" class="block text-gray-700 font-semibold mb-2">Senha Provisória *</label>
                    <input type="password" id="senha" name="senha" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                </div>
                
                <div>
                    <label for="role" class="block text-gray-700 font-semibold mb-2">Permissão (Role) *</label>
                    <select id="role" name="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                        <option value="USUARIO">Usuário (Padrão)</option>
                        <option value="TECNICO">Técnico</option>
                        <option value="ADMIN">Administrador</option>
                    </select>
                </div>

                <div>
                    <label for="setor_id" class="block text-gray-700 font-semibold mb-2">Setor (Departamento)</label>
                    <select id="setor_id" name="setor_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="">[Nenhum setor]</option>
                        <?php foreach ($lista_setores as $setor): ?>
                            <option value="<?= $setor['id_setor'] ?>"><?= htmlspecialchars($setor['nome_setor']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="unidade_id" class="block text-gray-700 font-semibold mb-2">Unidade (Local)</label>
                    <select id="unidade_id" name="unidade_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="">[Nenhuma unidade]</option>
                        <?php foreach ($lista_unidades as $unidade): ?>
                            <option value="<?= $unidade['id_unidade'] ?>"><?= htmlspecialchars($unidade['nome_unidade']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" 
                        class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700">
                    Salvar Usuário
                </button>
            </form>
        </div>
    </div>

    <div class="md:col-span-2">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Usuários Cadastrados</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 px-4 text-left text-gray-600 font-semibold">Nome</th>
                            <th class="py-2 px-4 text-left text-gray-600 font-semibold">E-mail</th>
                            <th class="py-2 px-4 text-left text-gray-600 font-semibold">Permissão</th>
                            <th class="py-2 px-4 text-left text-gray-600 font-semibold">Status</th>
                            <th class="py-2 px-4 text-left text-gray-600 font-semibold">Ações</th> </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lista_usuarios as $usuario): ?>
                            <tr class="border-b">
                                <td class="py-2 px-4 font-medium"><?= htmlspecialchars($usuario['nome']) ?></td>
                                <td class="py-2 px-4"><?= htmlspecialchars($usuario['email']) ?></td>
                                <td class="py-2 px-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        <?= ($usuario['role'] == 'ADMIN') ? 'bg-red-200 text-red-800' : '' ?>
                                        <?= ($usuario['role'] == 'TECNICO') ? 'bg-blue-200 text-blue-800' : '' ?>
                                        <?= ($usuario['role'] == 'USUARIO') ? 'bg-gray-200 text-gray-800' : '' ?>
                                    ">
                                        <?= htmlspecialchars($usuario['role']) ?>
                                    </span>
                                </td>
                                <td class="py-2 px-4">
                                    <?php if ($usuario['ativo']): ?>
                                        <span class="text-green-600 font-bold">Ativo</span>
                                    <?php else: ?>
                                        <span class="text-red-600">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2 px-4 flex space-x-2">
                                    <a href="editar.php?id=<?= $usuario['id_usuario'] ?>" 
                                       class="bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-sm hover:bg-blue-200">
                                       Editar
                                    </a>
                                    
                                    <?php // Guardrail: Não deixa excluir a si mesmo ou o ID 1 ?>
                                    <?php if ($usuario['id_usuario'] != $usuario_id_logado && $usuario['id_usuario'] != 1): ?>
                                        <a href="processar.php?acao=excluir_usuario&id=<?= $usuario['id_usuario'] ?>" 
                                           class="bg-red-100 text-red-700 px-3 py-1 rounded-lg text-sm hover:bg-red-200"
                                           onclick="return confirm('Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.')">
                                           Excluir
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../../includes/footer.php'; // Sobe 2 níveis
?>