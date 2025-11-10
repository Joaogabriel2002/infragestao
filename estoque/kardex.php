<?php
// /estoque/kardex.php (ARQUIVO NOVO)

require_once '../includes/header.php'; // Sobe 1 nível

// Segurança: Só Técnicos/Admins
if ($usuario_role_logado == 'USUARIO') {
    header("Location: {$base_url}/index.php");
    exit;
}

// 1. Validação do ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: {$base_url}/estoque/index.php");
    exit;
}
$modelo_id = (int)$_GET['id'];

// 2. Busca o nome do item para o título
$stmt_item = $pdo->prepare("SELECT nome FROM catalogo_modelos WHERE id_modelo = ?");
$stmt_item->execute([$modelo_id]);
$item = $stmt_item->fetch();

if (!$item) {
    header("Location: {$base_url}/estoque/index.php");
    exit;
}

// 3. Busca o Histórico (O KARDEX)
// TEM QUE SER 'ASC' (do mais antigo pro mais novo) para o saldo bater
$sql_mov = "SELECT 
                m.data_movimentacao, 
                m.quantidade, 
                m.tipo_movimentacao, 
                m.chamado_id, 
                u.nome AS nome_tecnico
            FROM movimentacoes_estoque m
            LEFT JOIN usuarios u ON m.usuario_id = u.id_usuario
            WHERE m.modelo_id = ?
            ORDER BY m.data_movimentacao ASC";
$stmt_mov = $pdo->prepare($sql_mov);
$stmt_mov->execute([$modelo_id]);
$movimentacoes = $stmt_mov->fetchAll();

// Variável para calcular o saldo corrente
$saldo_corrente = 0;

?>

<div class="bg-white shadow-md rounded-lg p-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold">Kardex do Item</h2>
        <p class="text-xl text-gray-700"><?= htmlspecialchars($item['nome']) ?></p>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-4 text-left text-gray-600 font-semibold">Data</th>
                    <th class="py-3 px-4 text-left text-gray-600 font-semibold">Tipo</th>
                    <th class="py-3 px-4 text-left text-gray-600 font-semibold">Técnico</th>
                    <th class="py-3 px-4 text-left text-gray-600 font-semibold">Ref. (Chamado)</th>
                    <th class="py-3 px-4 text-center text-gray-600 font-semibold">Qtd.</th>
                    <th class="py-3 px-4 text-center text-gray-600 font-semibold">Saldo</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b bg-gray-50">
                    <td colspan="5" class="py-2 px-4 font-semibold text-gray-700">Saldo Inicial</td>
                    <td class="py-2 px-4 text-center font-bold text-gray-700">0</td>
                </tr>

                <?php if (count($movimentacoes) > 0): ?>
                    <?php foreach ($movimentacoes as $mov): ?>
                        
                        <?php
                        // A MÁGICA DO KARDEX: Calcula o saldo linha a linha
                        $saldo_corrente += $mov['quantidade']; 
                        
                        // Define a cor (Verde para entrada, Vermelho para saída)
                        $cor_quantidade = ($mov['quantidade'] > 0) ? 'text-green-600' : 'text-red-600';
                        $tipo_mov = ($mov['quantidade'] > 0) ? 'ENTRADA' : 'SAÍDA';
                        ?>

                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            
                            <td class="py-3 px-4 text-gray-700">
                                <?= (new DateTime($mov['data_movimentacao']))->format('d/m/Y H:i') ?>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-medium <?= $cor_quantidade ?>"><?= $tipo_mov ?></span>
                                <div class="text-xs text-gray-500">(<?= htmlspecialchars($mov['tipo_movimentacao']) ?>)</div>
                            </td>
                            <td class="py-3 px-4 text-gray-700"><?= htmlspecialchars($mov['nome_tecnico']) ?></td>
                            <td class="py-3 px-4">
                                <?php if ($mov['chamado_id']): ?>
                                    <a href="<?= $base_url ?>/chamados/ver.php?id=<?= $mov['chamado_id'] ?>" 
                                       class="text-blue-600 hover:underline" target="_blank">
                                       Chamado #<?= $mov['chamado_id'] ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-center font-bold <?= $cor_quantidade ?>">
                                <?= ($mov['quantidade'] > 0) ? '+' : '' ?><?= $mov['quantidade'] ?>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-gray-900">
                                <?= $saldo_corrente ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="py-6 text-center text-gray-500">
                            Nenhuma movimentação registrada para este item.
                        </td>
                    </tr>
                <?php endif; ?>

                <tr class="border-t-2 border-gray-300 bg-gray-100">
                    <td colspan="5" class="py-3 px-4 text-right font-bold text-gray-800">Saldo Atual Total:</td>
                    <td class="py-3 px-4 text-center font-extrabold text-gray-900 text-lg">
                        <?= $saldo_corrente ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>


<?php
// 3. Inclui o Footer
require_once '../includes/footer.php';
?>