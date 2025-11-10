<?php
// /admin/usuarios/editar.php (ATUALIZADO COM MENSAGEM DE ERRO)

require_once '../../includes/header.php'; 

// Segurança
if ($usuario_role_logado != 'ADMIN') {
    echo "<p>Acesso negado.</p>";
    require_once '../../includes/footer.php';
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$id_usuario = (int)$_GET['id'];

// Busca os dados do usuário
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$id_usuario]);
$usuario = $stmt->fetch();

if (!$usuario) {
    echo "Usuário não encontrado.";
    require_once '../../includes/footer.php';
    exit;
}

// Dropdowns
$lista_setores = $pdo->query("SELECT * FROM setores ORDER BY nome_setor")->fetchAll();
$lista_unidades = $pdo->query("SELECT * FROM unidades ORDER BY nome_unidade")->fetchAll();

?>

<div class="bg-white shadow-md rounded-lg p-6 max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">Editar Usuário: <?= htmlspecialchars($usuario['nome']) ?></h2>

    <?php
    if (isset($_GET['erro']) && $_GET['erro'] == 'avatar_invalido') {
        echo "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>
                <strong>Erro no Upload:</strong> O arquivo é inválido. 
                Envie apenas imagens (JPG, PNG) com menos de 2MB.
              </div>";
    }
    ?>
    <form action="processar.php" method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="acao" value="editar_usuario">
        <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">
        
        <div>
            <label class="block text-gray-700 font-semibold mb-2">Foto de Perfil (Avatar)</label>
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 rounded-full bg-gray-200 overflow-hidden border-2 border-gray-300">
                    <?php if ($usuario['avatar_path']): ?>
                        <img src="<?= $base_url ?>/uploads/avatars/<?= htmlspecialchars($usuario['avatar_path']) ?>" 
                             alt="Avatar" class="w-full h-full object-cover">
                    <?php else: ?>
                        <svg class="w-full h-full text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                    <?php endif; ?>
                </div>
                <div>
                    <input type="file" name="avatar_upload" id="avatar_upload" class="text-sm">
                    <p class="text-xs text-gray-500 mt-1">Envie uma imagem (JPG, PNG) de até 2MB.</p>
                </div>
            </div>
            <?php if ($usuario['avatar_path']): ?>
            <div class="mt-2">
                <label class="flex items-center">
                    <input type="checkbox" name="remover_avatar" class="h-4 w-4 text-red-600 border-gray-300 rounded">
                    <span class="ml-2 text-sm text-red-700">Remover foto atual</span>
                </label>
            </div>
            <?php endif; ?>
        </div>
        
        <hr>

        <div>
            <label for="nome" class="block text-gray-700 font-semibold mb-2">Nome Completo *</label>
            <input type="text" id="nome" name="nome" 
                   value="<?= htmlspecialchars($usuario['nome']) ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
        </div>

        <div>
            <label for="email" class="block text-gray-700 font-semibold mb-2">E-mail (Login) *</label>
            <input type="email" id="email" name="email" 
                   value="<?= htmlspecialchars($usuario['email']) ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
        </div>

        <div>
            <label for="senha" class="block text-gray-700 font-semibold mb-2">Nova Senha</label>
            <input type="password" id="senha" name="senha" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg" 
                   placeholder="Deixe em branco para não alterar">
        </div>
        
        <div>
            <label for="role" class="block text-gray-700 font-semibold mb-2">Permissão (Role) *</label>
            <select id="role" name="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                <option value="USUARIO" <?= ($usuario['role'] == 'USUARIO') ? 'selected' : '' ?>>Usuário (Padrão)</option>
                <option value="TECNICO" <?= ($usuario['role'] == 'TECNICO') ? 'selected' : '' ?>>Técnico</option>
                <option value="ADMIN" <?= ($usuario['role'] == 'ADMIN') ? 'selected' : '' ?>>Administrador</option>
            </select>
        </div>

        <div>
            <label for="setor_id" class="block text-gray-700 font-semibold mb-2">Setor (Departamento)</label>
            <select id="setor_id" name="setor_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">[Nenhum setor]</option>
                <?php foreach ($lista_setores as $setor): ?>
                    <option value="<?= $setor['id_setor'] ?>" <?= ($usuario['setor_id'] == $setor['id_setor']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($setor['nome_setor']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="unidade_id" class="block text-gray-700 font-semibold mb-2">Unidade (Local)</label>
            <select id="unidade_id" name="unidade_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">[Nenhuma unidade]</option>
                <?php foreach ($lista_unidades as $unidade): ?>
                    <option value="<?= $unidade['id_unidade'] ?>" <?= ($usuario['unidade_id'] == $unidade['id_unidade']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($unidade['nome_unidade']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label class="flex items-center">
                <input type="checkbox" name="ativo" class="h-5 w-5 text-blue-600 border-gray-300 rounded"
                       <?= ($usuario['ativo']) ? 'checked' : '' ?>>
                <span class="ml-2 text-gray-700 font-semibold">Usuário Ativo</span>
            </label>
        </div>

        <button type="submit" 
                class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700">
            Salvar Alterações
        </button>
    </form>
</div>

<?php
require_once '../../includes/footer.php'; 
?>