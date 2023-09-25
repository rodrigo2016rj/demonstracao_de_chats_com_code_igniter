-- -------------------------------------------------------------------------
-- banco_de_dados_demonstracao_de_chats

DROP SCHEMA IF EXISTS banco_de_dados_demonstracao_de_chats;

CREATE SCHEMA IF NOT EXISTS banco_de_dados_demonstracao_de_chats 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE banco_de_dados_demonstracao_de_chats;

-- -------------------------------------------------------------------------
-- Tabela usuario

DROP TABLE IF EXISTS usuario;

CREATE TABLE IF NOT EXISTS usuario(
  pk_usuario INT NOT NULL AUTO_INCREMENT,
  nome_de_usuario VARCHAR(80) NOT NULL,
  email VARCHAR(160) NOT NULL,
  senha VARCHAR(120) NOT NULL,
  momento_do_cadastro DATETIME NOT NULL,
  fuso_horario VARCHAR(100) NOT NULL DEFAULT '-0300',
  PRIMARY KEY (pk_usuario),
  UNIQUE INDEX nome_de_usuario_UNICA (nome_de_usuario ASC),
  UNIQUE INDEX email_UNICA (email ASC)
)
ENGINE = InnoDB;

-- -------------------------------------------------------------------------
-- Tabela chat_mensagem

DROP TABLE IF EXISTS chat_mensagem;

CREATE TABLE IF NOT EXISTS chat_mensagem(
  pk_chat_mensagem INT NOT NULL AUTO_INCREMENT,
  fk_usuario INT NOT NULL,
  PRIMARY KEY (pk_chat_mensagem),
  INDEX fk_usuario_INDICE (fk_usuario ASC),
  CONSTRAINT fk_usuario_tabela_chat_mensagem
    FOREIGN KEY (fk_usuario)
    REFERENCES usuario (pk_usuario)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
)
ENGINE=InnoDB;

-- -------------------------------------------------------------------------
-- Tabela mensagem_historico

CREATE TABLE IF NOT EXISTS mensagem_historico(
  pk_mensagem_historico INT NOT NULL AUTO_INCREMENT,
  fk_chat_mensagem INT NOT NULL,
  momento_da_mensagem DATETIME NOT NULL,
  texto_da_mensagem VARCHAR(5000) NOT NULL,
  PRIMARY KEY (pk_mensagem_historico),
  INDEX fk_chat_mensagem_INDICE (fk_chat_mensagem ASC),
  CONSTRAINT fk_chat_mensagem_tabela_mensagem_historico
    FOREIGN KEY (fk_chat_mensagem)
    REFERENCES chat_mensagem (pk_chat_mensagem)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
)
ENGINE=InnoDB;

-- -------------------------------------------------------------------------
-- Dados de exemplo:

-- Usuário:
INSERT INTO usuario (nome_de_usuario, email, senha, momento_do_cadastro) VALUES(
  'usuario', 
  'usuario@emailfalso.rds', 
  '', 
  '2023-09-22 20:00:00'
);
