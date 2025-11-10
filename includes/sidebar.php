<?php
// /includes/sidebar.php (ATUALIZADO E LIMPO)

/*
 * Este arquivo é incluído pelo 'header.php'.
 * Ele já tem acesso às variáveis:
 * $usuario_role_logado (Ex: 'USUARIO', 'TECNICO', 'ADMIN')
 * $base_url (Ex: /infragestao)
 */
?>

<aside class="w-60 bg-gray-800 text-gray-300 min-h-[calc(100vh-60px)] p-4">
    <nav>
        <ul class="space-y-2">
            <li>
                <a href="<?= $base_url ?>/index.php" 
                   class="block px-4 py-2 rounded-lg hover:bg-gray-700 hover:text-white transition duration-200">
                   Dashboard
                </a>
            </li>
            <li>
                <a href="<?= $base_url ?>/chamados/index.php" 
                   class="block px-4 py-2 rounded-lg hover:bg-gray-700 hover:text-white transition duration-200">
                   Meus Chamados
                </a>
            </li>
            <li>
                <a href="<?= $base_url ?>/chamados/novo.php" 
                   class="block px-4 py-2 rounded-lg hover:bg-gray-700 hover:text-white transition duration-200">
                   Abrir Novo Chamado
                </a>
            </li>
            <li>
                <a href="<?= $base_url ?>/kb/index.php" 
                   class="block px-4 py-2 rounded-lg hover:bg-gray-700 hover:text-white transition duration-200">
                   Base de Conhecimento
                </a>
            </li>
            
            <?php if ($usuario_role_logado == 'TECNICO' || $usuario_role_logado == 'ADMIN'): ?>
                <hr class="border-gray-600 my-2">
                <li>
                    <a href="<?= $base_url ?>/chamados/index.php?view=todos" 
                       class="block px-4 py-2 rounded-lg hover:bg-gray-700 hover:text-white transition duration-200">
                       Todos os Chamados
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>/ativos/index.php" 
                       class="block px-4 py-2 rounded-lg hover:bg-gray-700 hover:text-white transition duration-200">
                       Gerenciar Ativos
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>/estoque/index.php" 
                       class="block px-4 py-2 rounded-lg hover:bg-gray-700 hover:text-white transition duration-200">
                       Catálogo/Estoque
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>/estoque/entrada.php" 
                       class="block px-4 py-2 rounded-lg hover:bg-gray-700 hover:text-white transition duration-200">
                       Registrar Entrada
                    </a>
                </li>
                <li>
                    <a href="<?= $base_url ?>/estoque/relatorio.php" 
                       class="block px-4 py-2 rounded-lg hover:bg-gray-700 hover:text-white transition duration-200">
                       Relatório de Estoque
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($usuario_role_logado == 'ADMIN'): ?>
                <hr class="border-gray-600 my-2">
                <li>
                    <a href="<?= $base_url ?>/admin/index.php" 
                       class="block px-4 py-2 rounded-lg hover:bg-gray-700 hover:text-white transition duration-200">
                       Configurações Gerais
                    </a>
                </li>
            <?php endif; ?>

        </ul>
    </nav>
</aside>