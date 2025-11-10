<?php
// /includes/header.php

// 1. O GUARDIÃO (O CÓDIGO DE SEGURANÇA)
session_start();

if (!isset($_SESSION['usuario_id'])) {
    session_destroy();
    // Redireciona para o login
    // Estamos definindo a $base_url aqui para que o redirecionamento funcione
    $base_url = "/sistema_chamados"; // !! MUDE SE SUA PASTA FOR OUTRA !!
    header("Location: $base_url/login.php");
    exit;
}

// 2. DEFINIÇÕES GLOBAIS
// Definindo a URL base para todos os links e assets
$base_url = "/infragestao"; // !! MUDE SE SUA PASTA FOR OUTRA !!

// Dados do usuário logado
$usuario_id_logado = $_SESSION['usuario_id'];
$usuario_nome_logado = $_SESSION['usuario_nome'];
$usuario_role_logado = $_SESSION['usuario_role'];

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
        /* Estilo básico para o layout (provisório) */
        /* Vamos deixar o Tailwind cuidar da maior parte */
        body { font-family: Arial, sans-serif; }
        .topbar { background-color: #fff; border-bottom: 1px solid #ddd; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; height: 60px; }
        .topbar-logo { font-size: 1.2rem; font-weight: bold; }
        .topbar-user { font-style: italic; }
        .topbar-user a { text-decoration: none; color: #d9534f; margin-left: 15px; }
        .main-container { display: flex; }
    </style>
</head>
<body class="h-full">

<header class="topbar">
    <div class="topbar-logo">Helpdesk v3.0</div>
    <div class="topbar-user">
        Olá, <strong><?php echo htmlspecialchars($usuario_nome_logado); ?></strong>
        (<?php echo htmlspecialchars($usuario_role_logado); ?>)
        <a href="<?php echo $base_url; ?>/logout.php">[ Sair ]</a>
    </div>
</header>

<div class="main-container">
    
    <?php include_once 'sidebar.php'; ?>

    <main class="content bg-gray-100 p-6 flex-1">