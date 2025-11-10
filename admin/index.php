<?php
// /admin/index.php (O NOVO "HUB" DE ADMIN)

require_once '../includes/header.php'; // Sobe 1 nível

// Segurança: Só Admins
if ($usuario_role_logado != 'ADMIN') {
    header("Location: {$base_url}/index.php");
    exit;
}
?>

<h2 class="text-3xl font-bold text-gray-800 mb-6">Configurações Gerais</h2>
<p class="text-lg text-gray-600 mb-8">Selecione uma área para gerenciar as configurações do sistema.</p>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

    <a href="<?= $base_url ?>/admin/usuarios/index.php" 
       class="block p-6 bg-white shadow-md rounded-lg hover:shadow-lg hover:bg-gray-50 transition-all duration-300">
        <h3 class="text-xl font-semibold mb-2 text-gray-900">Gerenciar Usuários</h3>
        <p class="text-gray-600">Cadastrar, editar e definir permissões de usuários e técnicos.</p>
    </a>

    <a href="<?= $base_url ?>/admin/unidades/index.php" 
       class="block p-6 bg-white shadow-md rounded-lg hover:shadow-lg hover:bg-gray-50 transition-all duration-300">
        <h3 class="text-xl font-semibold mb-2 text-gray-900">Gerenciar Unidades (Locais)</h3>
        <p class="text-gray-600">Cadastrar os locais físicos (Ex: Matriz, Posto Centro).</p>
    </a>

    <a href="<?= $base_url ?>/admin/setores/index.php" 
       class="block p-6 bg-white shadow-md rounded-lg hover:shadow-lg hover:bg-gray-50 transition-all duration-300">
        <h3 class="text-xl font-semibold mb-2 text-gray-900">Gerenciar Setores (Deptos)</h3>
        <p class="text-gray-600">Cadastrar os departamentos (Ex: Financeiro, RH).</p>
    </a>

    <a href="<?= $base_url ?>/admin/categorias/index.php" 
       class="block p-6 bg-white shadow-md rounded-lg hover:shadow-lg hover:bg-gray-50 transition-all duration-300">
        <h3 class="text-xl font-semibold mb-2 text-gray-900">Categorias de Chamados</h3>
        <p class="text-gray-600">Definir os tipos de problema (Ex: Hardware, Software).</p>
    </a>
    
    <a href="<?= $base_url ?>/admin/categorias_ativo/index.php" 
       class="block p-6 bg-white shadow-md rounded-lg hover:shadow-lg hover:bg-gray-50 transition-all duration-300">
        <h3 class="text-xl font-semibold mb-2 text-gray-900">Categorias de Ativo/Estoque</h3>
        <p class="text-gray-600">Definir os tipos de item (Ex: PC, Toner, Software).</p>
    </a>

    <a href="<?= $base_url ?>/admin/fornecedores/index.php" 
       class="block p-6 bg-white shadow-md rounded-lg hover:shadow-lg hover:bg-gray-50 transition-all duration-300">
        <h3 class="text-xl font-semibold mb-2 text-gray-900">Gerenciar Fornecedores</h3>
        <p class="text-gray-600">Cadastrar empresas de manutenção ou venda.</p>
    </a>

</div>

<?php
require_once '../includes/footer.php'; // Sobe 1 nível
?>