<?php
// /admin/fornecedores/index.php

require_once '../../includes/header.php'; // Sobe 2 níveis

// Segurança: Só Admins
if ($usuario_role_logado != 'ADMIN') {
    echo "<p>Acesso negado.</p>";
    require_once '../../includes/footer.php';
    exit;
}

// --- Lógica de Processamento (Salvar Novo Fornecedor) ---
$feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao']) && $_POST['acao'] == 'novo_fornecedor') {
        
        $nome = $_POST['nome'];
        $cnpj = empty($_POST['cnpj']) ? null : $_POST['cnpj'];
        $email = empty($_POST['email']) ? null : $_POST['email'];

        if (!empty($nome)) {
            try {
                $sql = "INSERT INTO fornecedores (nome, cnpj, email) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nome, $cnpj, $email]);
                
                $feedback = "<div class='bg-green-100 text-green-700 p-3 rounded mb-4'>Fornecedor salvo com sucesso!</div>";

            } catch (PDOException $e) {
                // Erro 23000 é de chave duplicada (CNPJ já existe)
                if ($e->getCode() == 23000) {
                    $feedback = "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>Erro: Este CNPJ já está cadastrado.</div>";
                } else {
                    $feedback = "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>Erro: " . $e->getMessage() . "</div>";
                }
            }
        } else {
            $feedback = "<div class='bg-yellow-100 text-yellow-700 p-3 rounded mb-4'>O campo Nome é obrigatório.</div>";
        }
    }
}

// --- Busca de Dados para a Página ---
$lista_fornecedores = $pdo->query("SELECT * FROM fornecedores ORDER BY nome")->fetchAll();

?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    
    <div class="md:col-span-1">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Novo Fornecedor</h2>
            <?= $feedback ?>
            <form action="index.php" method="POST" class="space-y-4">
                <input type="hidden" name="acao" value="novo_fornecedor">
                
                <div>
                    <label for="nome" class="block text-gray-700 font-semibold mb-2">Nome do Fornecedor *</label>
                    <input type="text" id="nome" name="nome" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg" 
                           placeholder="Ex: PC Peças & Cia" required>
                </div>

                <div>
                    <label for="cnpj" class="block text-gray-700 font-semibold mb-2">CNPJ (Opcional)</label>
                    <input type="text" id="cnpj" name="cnpj" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg" 
                           placeholder="Ex: 00.000.000/0001-00">
                </div>
                
                <div>
                    <label for="email" class="block text-gray-700 font-semibold mb-2">E-mail (Opcional)</label>
                    <input type="email" id="email" name="email" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg" 
                           placeholder="Ex: contato@pcpecas.com">
                </div>

                <button type="submit" 
                        class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700">
                    Salvar Fornecedor
                </button>
            </form>
        </div>
    </div>

    <div class="md:col-span-2">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Fornecedores Cadastrados</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 px-4 text-left text-gray-600 font-semibold">Nome</th>
                            <th class="py-2 px-4 text-left text-gray-600 font-semibold">CNPJ</th>
                            <th class="py-2 px-4 text-left text-gray-600 font-semibold">E-mail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($lista_fornecedores) > 0): ?>
                            <?php foreach ($lista_fornecedores as $fornecedor): ?>
                                <tr class="border-b">
                                    <td class="py-2 px-4 font-medium"><?= htmlspecialchars($fornecedor['nome']) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($fornecedor['cnpj'] ?? 'N/A') ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($fornecedor['email'] ?? 'N/A') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="py-4 text-center text-gray-500">Nenhum fornecedor cadastrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../../includes/footer.php'; // Sobe 2 níveis
?>