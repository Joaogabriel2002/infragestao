-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 10/11/2025 às 21:47
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `infragestao`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `anexos_chamados`
--

CREATE TABLE `anexos_chamados` (
  `id_anexo` int(11) NOT NULL,
  `chamado_id` int(11) NOT NULL,
  `caminho_arquivo` varchar(500) NOT NULL,
  `nome_arquivo` varchar(255) DEFAULT NULL,
  `data_upload` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ativos`
--

CREATE TABLE `ativos` (
  `id_ativo` int(11) NOT NULL,
  `modelo_id` int(11) NOT NULL COMMENT 'O que ele é (do catálogo)',
  `unidade_id` int(11) NOT NULL COMMENT 'Onde ele está fisicamente',
  `nome_ativo` varchar(100) NOT NULL COMMENT 'Ex: PDV-01, PC-FINANCEIRO-02',
  `patrimonio` varchar(50) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `remote_id` varchar(50) DEFAULT NULL COMMENT 'AnyDesk, TeamViewer',
  `operating_system` varchar(50) DEFAULT NULL,
  `status_ativo` varchar(50) NOT NULL DEFAULT 'Ativo' COMMENT 'Ativo, Manutenção, Baixado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `ativos`
--

INSERT INTO `ativos` (`id_ativo`, `modelo_id`, `unidade_id`, `nome_ativo`, `patrimonio`, `ip_address`, `remote_id`, `operating_system`, `status_ativo`) VALUES
(1, 1, 1, 'PC-TI', NULL, '10.1.106.45', '1480379164', 'Windows 11', 'Ativo');

-- --------------------------------------------------------

--
-- Estrutura para tabela `base_conhecimento`
--

CREATE TABLE `base_conhecimento` (
  `id_artigo` int(11) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `conteudo` text NOT NULL,
  `data_criacao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `catalogo_modelos`
--

CREATE TABLE `catalogo_modelos` (
  `id_modelo` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL COMMENT 'Ex: PC Dell Vostro, Impressora HP 107w, Toner HP 105A',
  `categoria_ativo_id` int(11) DEFAULT NULL,
  `quantidade_em_estoque` int(11) NOT NULL DEFAULT 0,
  `estoque_minimo` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `catalogo_modelos`
--

INSERT INTO `catalogo_modelos` (`id_modelo`, `nome`, `categoria_ativo_id`, `quantidade_em_estoque`, `estoque_minimo`) VALUES
(1, 'Notebook Acer Aspire AG15-51P', 2, 0, 0),
(2, 'EPSON 544 - Tinta Amarela', 5, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nome_categoria` varchar(100) NOT NULL COMMENT 'Ex: Hardware, Software, Solicitação de Toner'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nome_categoria`) VALUES
(1, 'Hardware');

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias_ativo`
--

CREATE TABLE `categorias_ativo` (
  `id_categoria_ativo` int(11) NOT NULL,
  `nome_categoria` varchar(100) NOT NULL,
  `controla_estoque` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'TRUE se for consumível (Toner, Mouse), FALSE se for ativo (PC)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `categorias_ativo`
--

INSERT INTO `categorias_ativo` (`id_categoria_ativo`, `nome_categoria`, `controla_estoque`) VALUES
(1, 'Computador', 0),
(2, 'Notebook', 0),
(3, 'Impressora', 0),
(4, 'Servidor', 0),
(5, 'Toner', 1),
(6, 'Mouse/Teclado', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `chamados`
--

CREATE TABLE `chamados` (
  `id_chamado` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `problema_relatado` text NOT NULL,
  `solucao_aplicada` text DEFAULT NULL,
  `autor_id` int(11) NOT NULL COMMENT 'FK para usuarios.id (quem abriu)',
  `tecnico_id` int(11) DEFAULT NULL COMMENT 'FK para usuarios.id (quem atendeu)',
  `ativo_id` int(11) DEFAULT NULL COMMENT 'O chamado é sobre este ativo? (Opcional)',
  `categoria_id` int(11) DEFAULT NULL COMMENT 'Qual o tipo de problema?',
  `status_chamado` varchar(50) NOT NULL DEFAULT 'Aberto' COMMENT 'Aberto, Em Atendimento, Fechado',
  `prioridade` varchar(20) DEFAULT 'Média',
  `dt_abertura` datetime DEFAULT current_timestamp(),
  `dt_fechamento` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `chamados`
--

INSERT INTO `chamados` (`id_chamado`, `titulo`, `problema_relatado`, `solucao_aplicada`, `autor_id`, `tecnico_id`, `ativo_id`, `categoria_id`, `status_chamado`, `prioridade`, `dt_abertura`, `dt_fechamento`) VALUES
(1, 'Teste', 'teste', '', 3, 1, 1, 1, 'Em Atendimento', 'MEDIA', '2025-11-10 17:09:46', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `chamado_atualizacoes`
--

CREATE TABLE `chamado_atualizacoes` (
  `id_atualizacao` int(11) NOT NULL,
  `chamado_id` int(11) NOT NULL,
  `autor_id` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `dt_atualizacao` datetime DEFAULT current_timestamp(),
  `is_privado` tinyint(1) DEFAULT 0 COMMENT 'Nota interna do técnico'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `chamado_atualizacoes`
--

INSERT INTO `chamado_atualizacoes` (`id_atualizacao`, `chamado_id`, `autor_id`, `comentario`, `dt_atualizacao`, `is_privado`) VALUES
(1, 1, 3, 'preciso de ajuda', '2025-11-10 17:11:52', 0),
(2, 1, 1, 'Verificando', '2025-11-10 17:12:15', 0),
(3, 1, 1, 'Verificando', '2025-11-10 17:12:30', 0),
(4, 1, 1, 'TESTE', '2025-11-10 17:13:27', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `fornecedores`
--

CREATE TABLE `fornecedores` (
  `id_fornecedor` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `cnpj` varchar(18) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `fornecedores`
--

INSERT INTO `fornecedores` (`id_fornecedor`, `nome`, `cnpj`, `email`) VALUES
(1, 'Teste', '02.125.191/0001-43', 'joaoogbriel3meia@gmail.com');

-- --------------------------------------------------------

--
-- Estrutura para tabela `manutencoes`
--

CREATE TABLE `manutencoes` (
  `id_manutencao` int(11) NOT NULL,
  `ativo_id` int(11) NOT NULL,
  `fornecedor_id` int(11) DEFAULT NULL,
  `dt_envio` date NOT NULL,
  `dt_retorno` date DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'EM_CONSERTO',
  `descricao_problema` text DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `movimentacoes_estoque`
--

CREATE TABLE `movimentacoes_estoque` (
  `id_movimentacao` int(11) NOT NULL,
  `modelo_id` int(11) NOT NULL COMMENT 'FK para catalogo_modelos (Toner, Mouse)',
  `chamado_id` int(11) DEFAULT NULL COMMENT 'A saída foi por causa deste chamado?',
  `fornecedor_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL COMMENT 'O técnico que registrou',
  `quantidade` int(11) NOT NULL COMMENT '+10 (Entrada), -1 (Saída)',
  `tipo_movimentacao` enum('ENTRADA_NF','SAIDA_CHAMADO','AJUSTE_MANUAL') NOT NULL,
  `data_movimentacao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `movimentacoes_estoque`
--

INSERT INTO `movimentacoes_estoque` (`id_movimentacao`, `modelo_id`, `chamado_id`, `fornecedor_id`, `usuario_id`, `quantidade`, `tipo_movimentacao`, `data_movimentacao`) VALUES
(1, 2, NULL, 1, 1, 1, 'ENTRADA_NF', '2025-11-10 17:21:17'),
(4, 2, 1, NULL, 1, -1, 'SAIDA_CHAMADO', '2025-11-10 17:44:23');

-- --------------------------------------------------------

--
-- Estrutura para tabela `setores`
--

CREATE TABLE `setores` (
  `id_setor` int(11) NOT NULL,
  `nome_setor` varchar(100) NOT NULL COMMENT 'Ex: Financeiro, RH, Suporte TI'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `setores`
--

INSERT INTO `setores` (`id_setor`, `nome_setor`) VALUES
(1, 'Teste');

-- --------------------------------------------------------

--
-- Estrutura para tabela `unidades`
--

CREATE TABLE `unidades` (
  `id_unidade` int(11) NOT NULL,
  `nome_unidade` varchar(100) NOT NULL COMMENT 'Ex: Supermercado Matriz, Posto Centro'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `unidades`
--

INSERT INTO `unidades` (`id_unidade`, `nome_unidade`) VALUES
(1, 'Mariano PG 02');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `setor_id` int(11) DEFAULT NULL,
  `unidade_id` int(11) DEFAULT NULL,
  `role` enum('USUARIO','TECNICO','ADMIN') NOT NULL DEFAULT 'USUARIO',
  `avatar_path` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nome`, `email`, `senha_hash`, `setor_id`, `unidade_id`, `role`, `avatar_path`, `ativo`) VALUES
(1, 'Administrador', 'admin@admin.com', '$2y$10$VPAUC0rd0C3TGRjluNuOZuvv11lWI3DM6a4qlDF4CxHoC4RLIYwbS', NULL, NULL, 'ADMIN', 'user_1.jpg', 1),
(3, 'Teste', 'teste@teste.com', '$2y$10$j2WwdlowceLe5sxbk3uwV.5d8T0efTjhydvAEVOJFj2nP4jXnZE4q', 1, 1, 'USUARIO', NULL, 1);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `anexos_chamados`
--
ALTER TABLE `anexos_chamados`
  ADD PRIMARY KEY (`id_anexo`),
  ADD KEY `chamado_id` (`chamado_id`);

--
-- Índices de tabela `ativos`
--
ALTER TABLE `ativos`
  ADD PRIMARY KEY (`id_ativo`),
  ADD UNIQUE KEY `patrimonio` (`patrimonio`),
  ADD KEY `modelo_id` (`modelo_id`),
  ADD KEY `unidade_id` (`unidade_id`);

--
-- Índices de tabela `base_conhecimento`
--
ALTER TABLE `base_conhecimento`
  ADD PRIMARY KEY (`id_artigo`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Índices de tabela `catalogo_modelos`
--
ALTER TABLE `catalogo_modelos`
  ADD PRIMARY KEY (`id_modelo`),
  ADD KEY `categoria_ativo_id` (`categoria_ativo_id`);

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Índices de tabela `categorias_ativo`
--
ALTER TABLE `categorias_ativo`
  ADD PRIMARY KEY (`id_categoria_ativo`);

--
-- Índices de tabela `chamados`
--
ALTER TABLE `chamados`
  ADD PRIMARY KEY (`id_chamado`),
  ADD KEY `autor_id` (`autor_id`),
  ADD KEY `tecnico_id` (`tecnico_id`),
  ADD KEY `ativo_id` (`ativo_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Índices de tabela `chamado_atualizacoes`
--
ALTER TABLE `chamado_atualizacoes`
  ADD PRIMARY KEY (`id_atualizacao`),
  ADD KEY `chamado_id` (`chamado_id`),
  ADD KEY `autor_id` (`autor_id`);

--
-- Índices de tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  ADD PRIMARY KEY (`id_fornecedor`),
  ADD UNIQUE KEY `cnpj` (`cnpj`);

--
-- Índices de tabela `manutencoes`
--
ALTER TABLE `manutencoes`
  ADD PRIMARY KEY (`id_manutencao`),
  ADD KEY `ativo_id` (`ativo_id`),
  ADD KEY `fornecedor_id` (`fornecedor_id`);

--
-- Índices de tabela `movimentacoes_estoque`
--
ALTER TABLE `movimentacoes_estoque`
  ADD PRIMARY KEY (`id_movimentacao`),
  ADD KEY `modelo_id` (`modelo_id`),
  ADD KEY `chamado_id` (`chamado_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `fornecedor_id` (`fornecedor_id`);

--
-- Índices de tabela `setores`
--
ALTER TABLE `setores`
  ADD PRIMARY KEY (`id_setor`);

--
-- Índices de tabela `unidades`
--
ALTER TABLE `unidades`
  ADD PRIMARY KEY (`id_unidade`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `setor_id` (`setor_id`),
  ADD KEY `unidade_id` (`unidade_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `anexos_chamados`
--
ALTER TABLE `anexos_chamados`
  MODIFY `id_anexo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ativos`
--
ALTER TABLE `ativos`
  MODIFY `id_ativo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `base_conhecimento`
--
ALTER TABLE `base_conhecimento`
  MODIFY `id_artigo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `catalogo_modelos`
--
ALTER TABLE `catalogo_modelos`
  MODIFY `id_modelo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `categorias_ativo`
--
ALTER TABLE `categorias_ativo`
  MODIFY `id_categoria_ativo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `chamados`
--
ALTER TABLE `chamados`
  MODIFY `id_chamado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `chamado_atualizacoes`
--
ALTER TABLE `chamado_atualizacoes`
  MODIFY `id_atualizacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  MODIFY `id_fornecedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `manutencoes`
--
ALTER TABLE `manutencoes`
  MODIFY `id_manutencao` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `movimentacoes_estoque`
--
ALTER TABLE `movimentacoes_estoque`
  MODIFY `id_movimentacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `setores`
--
ALTER TABLE `setores`
  MODIFY `id_setor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `unidades`
--
ALTER TABLE `unidades`
  MODIFY `id_unidade` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `anexos_chamados`
--
ALTER TABLE `anexos_chamados`
  ADD CONSTRAINT `anexos_chamados_ibfk_1` FOREIGN KEY (`chamado_id`) REFERENCES `chamados` (`id_chamado`) ON DELETE CASCADE;

--
-- Restrições para tabelas `ativos`
--
ALTER TABLE `ativos`
  ADD CONSTRAINT `ativos_ibfk_1` FOREIGN KEY (`modelo_id`) REFERENCES `catalogo_modelos` (`id_modelo`),
  ADD CONSTRAINT `ativos_ibfk_2` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id_unidade`);

--
-- Restrições para tabelas `base_conhecimento`
--
ALTER TABLE `base_conhecimento`
  ADD CONSTRAINT `base_conhecimento_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id_categoria`);

--
-- Restrições para tabelas `catalogo_modelos`
--
ALTER TABLE `catalogo_modelos`
  ADD CONSTRAINT `catalogo_modelos_ibfk_1` FOREIGN KEY (`categoria_ativo_id`) REFERENCES `categorias_ativo` (`id_categoria_ativo`);

--
-- Restrições para tabelas `chamados`
--
ALTER TABLE `chamados`
  ADD CONSTRAINT `chamados_ibfk_1` FOREIGN KEY (`autor_id`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `chamados_ibfk_2` FOREIGN KEY (`tecnico_id`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `chamados_ibfk_3` FOREIGN KEY (`ativo_id`) REFERENCES `ativos` (`id_ativo`),
  ADD CONSTRAINT `chamados_ibfk_4` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id_categoria`);

--
-- Restrições para tabelas `chamado_atualizacoes`
--
ALTER TABLE `chamado_atualizacoes`
  ADD CONSTRAINT `chamado_atualizacoes_ibfk_1` FOREIGN KEY (`chamado_id`) REFERENCES `chamados` (`id_chamado`) ON DELETE CASCADE,
  ADD CONSTRAINT `chamado_atualizacoes_ibfk_2` FOREIGN KEY (`autor_id`) REFERENCES `usuarios` (`id_usuario`);

--
-- Restrições para tabelas `manutencoes`
--
ALTER TABLE `manutencoes`
  ADD CONSTRAINT `manutencoes_ibfk_1` FOREIGN KEY (`ativo_id`) REFERENCES `ativos` (`id_ativo`),
  ADD CONSTRAINT `manutencoes_ibfk_2` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id_fornecedor`);

--
-- Restrições para tabelas `movimentacoes_estoque`
--
ALTER TABLE `movimentacoes_estoque`
  ADD CONSTRAINT `movimentacoes_estoque_ibfk_1` FOREIGN KEY (`modelo_id`) REFERENCES `catalogo_modelos` (`id_modelo`),
  ADD CONSTRAINT `movimentacoes_estoque_ibfk_2` FOREIGN KEY (`chamado_id`) REFERENCES `chamados` (`id_chamado`),
  ADD CONSTRAINT `movimentacoes_estoque_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `movimentacoes_estoque_ibfk_4` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id_fornecedor`);

--
-- Restrições para tabelas `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`setor_id`) REFERENCES `setores` (`id_setor`),
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id_unidade`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
