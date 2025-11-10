<?php
// /admin/setores/index.php

require_once '../../includes/header.php'; // Sobe 2 níveis

// Segurança: Só Admins
if ($usuario_role_logado != 'ADMIN') {
    echo "<p>Acesso negado.</p>";
    require_once '../../includes/footer.php';
    exit;
}

// Lógica de Processamento (Salvar/Deletar)
$feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao']) && $_POST['acao'] == 'novo_setor') {
        $nome = $_POST['nome_setor'];
        if (!empty($nome)) {
            $stmt = $pdo->prepare("INSERT INTO setores (nome_setor) VALUES (?)");
            $stmt->execute([$nome]);
            $feedback = "<div class='bg-green-100 text-green-700 p-3 rounded mb-4'>Setor salvo com sucesso!</div>";
        }
    }
}

// Busca os setores existentes
$lista_setores = $pdo->query("SELECT * FROM setores ORDER BY nome_setor")->fetchAll();

?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    
    <div class="md:col-span-1">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Novo Setor (Departamento)</h2>
            <?= $feedback ?>
            <form action="index.php" method="POST">
                <input type="hidden" name="acao" value="novo_setor">
                <div class="mb-4">
                    <label for="nome_setor" class="block text-gray-700 font-semibold mb-2">Nome do Setor</label>
                    <input type="text" id="nome_setor" name="nome_setor" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg" 
                           placeholder="Ex: Financeiro, RH, TI" required>
                </div>
                <button type="submit" 
                        class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700">
                    Salvar Setor
                </button>
            </form>
        </div>
    </div>

    <div class="md:col-span-2">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Setores Cadastrados</h2>
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 text-left text-gray-600 font-semibold">ID</th>
                        <th class="py-2 px-4 text-left text-gray-600 font-semibold">Nome</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($lista_setores) > 0): ?>
                        <?php foreach ($lista_setores as $setor): ?>
                            <tr class="border-b">
                                <td class="py-2 px-4"><?= $setor['id_setor'] ?></td>
                                <td class="py-2 px-4"><?= htmlspecialchars($setor['nome_setor']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" class="py-4 text-center text-gray-500">Nenhum setor cadastrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once '../../includes/footer.php'; // Sobe 2 níveis
?>