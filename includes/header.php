<?php
// /includes/header.php (ATUALIZADO COM AVATAR)

// 1. O GUARDIÃO
session_start();

if (!isset($_SESSION['usuario_id'])) {
    session_destroy();
    $base_url = "/infragestao"; 
    header("Location: $base_url/login.php");
    exit;
}

// 2. DEFINIÇÕES GLOBAIS
$base_url = "/infragestao"; 

// Dados do usuário logado (agora com avatar)
$usuario_id_logado = $_SESSION['usuario_id'];
$usuario_nome_logado = $_SESSION['usuario_nome'];
$usuario_role_logado = $_SESSION['usuario_role'];
$usuario_avatar = $_SESSION['usuario_avatar'] ?? null; // Pega o avatar ou define como null

// Inclui a conexão
require_once $_SERVER['DOCUMENT_ROOT'] . $base_url . '/config/conexao.php';

?>
<!DOCTYPE html>
<html lang="pt-br" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Helpdesk v3.0</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: Arial, sans-serif; }
        .main-container { display: flex; }
    </style>
</head>
<body class="h-full">

<header class="bg-white shadow-md px-6 flex justify-between items-center h-16">
    
    <div>
        <h1 class="text-xl font-bold text-blue-600">Helpdesk v3.0</h1>
    </div>
    
    <div class="flex items-center space-x-4">
        
        <div class="text-right">
            <span class="text-gray-800 font-medium"><?php echo htmlspecialchars($usuario_nome_logado); ?></span>
            <span class="block text-xs text-gray-500 font-medium"><?php echo htmlspecialchars($usuario_role_logado); ?></span>
        </div>
        
        <div class="w-10 h-10 rounded-full bg-gray-200 overflow-hidden border-2 border-gray-300">
            <?php if ($usuario_avatar): ?>
                <img src="<?= $base_url ?>/uploads/avatars/<?= htmlspecialchars($usuario_avatar) ?>" 
                     alt="Avatar" class="w-full h-full object-cover">
            <?php else: ?>
                <svg class="w-full h-full text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                </svg>
            <?php endif; ?>
        </div>

        <span class="text-gray-300">|</span>

        <a href="<?php echo $base_url; ?>/logout.php" 
           class="text-sm text-gray-600 hover:text-red-500 hover:underline font-medium">
           Sair
        </a>
    </div>
</header>

<div class="main-container">
    
    <?php include_once 'sidebar.php'; ?>

    <main class="content bg-gray-100 p-6 flex-1">