<?php
// /admin/unidades/index.php

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
    if (isset($_POST['acao']) && $_POST['acao'] == 'nova_unidade') {
        $nome = $_POST['nome_unidade'];
        if (!empty($nome)) {
            $stmt = $pdo->prepare("INSERT INTO unidades (nome_unidade) VALUES (?)");
            $stmt->execute([$nome]);
            $feedback = "<div class='bg-green-100 text-green-700 p-3 rounded mb-4'>Unidade salva com sucesso!</div>";
        }
    }
}

// Busca as unidades existentes
$lista_unidades = $pdo->query("SELECT * FROM unidades ORDER BY nome_unidade")->fetchAll();

?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    
    <div class="md:col-span-1">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Nova Unidade (Local)</h2>
            <?= $feedback ?>
            <form action="index.php" method="POST">
                <input type="hidden" name="acao" value="nova_unidade">
                <div class="mb-4">
                    <label for="nome_unidade" class="block text-gray-700 font-semibold mb-2">Nome da Unidade</label>
                    <input type="text" id="nome_unidade" name="nome_unidade" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg" 
                           placeholder="Ex: Supermercado Matriz" required>
                </div>
                <button type="submit" 
                        class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700">
                    Salvar Unidade
                </button>
            </form>
        </div>
    </div>

    <div class="md:col-span-2">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Unidades Cadastradas</h2>
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 text-left text-gray-600 font-semibold">ID</th>
                        <th class="py-2 px-4 text-left text-gray-600 font-semibold">Nome</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lista_unidades as $unidade): ?>
                        <tr class="border-b">
                            <td class="py-2 px-4"><?= $unidade['id_unidade'] ?></td>
                            <td class="py-2 px-4"><?= htmlspecialchars($unidade['nome_unidade']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once '../../includes/footer.php'; // Sobe 2 níveis
?>