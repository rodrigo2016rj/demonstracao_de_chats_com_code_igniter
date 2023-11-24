<?php

namespace App\Controllers;

use App\Models\ChatWebSocketModel;
use App\Models\Entidades\MensagemHistorico;
use DateTime;
use DateTimeZone;

final class ChatWebSocketController extends TemplateController{
  private const QUANTIDADE_POR_PAGINA = 16; //Quantidade por página da lista de mensagens.

  public function index(){
    /* Obtendo valores da URL */
    $requisicao = service('request');
    $pagina = trim($requisicao->getGet('pagina') ?? '');

    /* Validações */
    if(!is_numeric($pagina) or $pagina <= 0 or floor($pagina) != $pagina){
      $pagina = 1;
    }

    /* Carregando chat */
    $this->carregar_chat($pagina);
    //$this->carregar_chat_de_outro_jeito($pagina);

    /* Colocando campo anti csrf no formulário e no array de chaves desta página */
    $chave_anti_csrf = $this->criar_hash_da_chave_anti_csrf();
    $this->get_smarty()->assign('chave_anti_csrf', $chave_anti_csrf);
    if(!$this->get_sessao()->has('chaves_anti_csrf_da_pagina_chat_web_socket')){
      $this->get_sessao()->set('chaves_anti_csrf_da_pagina_chat_web_socket', array());
    }
    $chaves_anti_csrf = $this->get_sessao()->get('chaves_anti_csrf_da_pagina_chat_web_socket');
    $chaves_anti_csrf[] = $chave_anti_csrf;
    $this->get_sessao()->set('chaves_anti_csrf_da_pagina_chat_web_socket', $chaves_anti_csrf);

    $this->get_smarty()->display('chat_web_socket/chat_web_socket.html');
    die;
  }

  private function carregar_chat($pagina_da_lista = 1){
    $chat_web_socket_model = new ChatWebSocketModel();

    $quantidade_de_paginas = $this->calcular_quantidade_de_paginas_das_mensagens(self::QUANTIDADE_POR_PAGINA);
    if($pagina_da_lista > $quantidade_de_paginas){
      $pagina_da_lista = $quantidade_de_paginas;
    }

    $this->get_smarty()->assign('pagina_atual_das_mensagens', $pagina_da_lista);
    $this->get_smarty()->assign('ultima_pagina_das_mensagens', $quantidade_de_paginas);

    $descartar = self::QUANTIDADE_POR_PAGINA * $pagina_da_lista - self::QUANTIDADE_POR_PAGINA;
    $descartar = max($descartar, 0);
    $chat_mensagens = $chat_web_socket_model->selecionar_mensagens(self::QUANTIDADE_POR_PAGINA, $descartar);

    $array_mensagens = array();
    foreach($chat_mensagens as $chat_mensagem){
      $pk_chat_mensagem = $chat_mensagem->get_pk_chat_mensagem();

      $array_resultado = $chat_web_socket_model->selecionar_primeira_mensagem_do_historico($pk_chat_mensagem);
      if(isset($array_resultado['mensagem_do_model'])){
        break;
      }
      $primeira = $array_resultado[0];

      $array_resultado = $chat_web_socket_model->selecionar_ultima_mensagem_do_historico($pk_chat_mensagem);
      if(isset($array_resultado['mensagem_do_model'])){
        break;
      }
      $ultima = $array_resultado[0];

      $fk_usuario = $chat_mensagem->get_fk_usuario();
      $array_resultado = $chat_web_socket_model->selecionar_usuario($fk_usuario);
      if(isset($array_resultado['mensagem_do_model'])){
        break;
      }
      $usuario = $array_resultado[0];

      $momento = $primeira->get_momento_da_mensagem();
      $sem_fuso_horario = new DateTimeZone('GMT');
      $fuso_horario_de_brasilia = new DateTimeZone('-0300');
      $objeto_date_time = new DateTime($momento, $sem_fuso_horario);
      $objeto_date_time->setTimeZone($fuso_horario_de_brasilia);
      $momento = $objeto_date_time->format('Y-m-d H:i:s');
      $momento = $this->converter_para_horario_data_do_html($momento);
      $array_mensagem['enviada_em'] = $momento;

      $array_mensagem['editada_em'] = null;
      if($primeira->get_pk_mensagem_historico() !== $ultima->get_pk_mensagem_historico()){
        $momento = $ultima->get_momento_da_mensagem();
        $sem_fuso_horario = new DateTimeZone('GMT');
        $fuso_horario_de_brasilia = new DateTimeZone('-0300');
        $objeto_date_time = new DateTime($momento, $sem_fuso_horario);
        $objeto_date_time->setTimeZone($fuso_horario_de_brasilia);
        $momento = $objeto_date_time->format('Y-m-d H:i:s');
        $momento = $this->converter_para_horario_data_do_html($momento);
        $array_mensagem['editada_em'] = $momento;
      }

      $nome_de_usuario_do_autor = $usuario->get_nome_de_usuario();
      $nome_de_usuario_do_autor = esc($nome_de_usuario_do_autor);

      $texto_da_mensagem = $ultima->get_texto_da_mensagem();
      $texto_da_mensagem = esc($texto_da_mensagem);

      $autor_e_texto_da_mensagem = "<b>$nome_de_usuario_do_autor:</b> $texto_da_mensagem";
      $autor_e_texto_da_mensagem = $this->acrescentar_quebras_de_linha_xhtml($autor_e_texto_da_mensagem);
      $array_mensagem['autor_e_texto_da_mensagem'] = $autor_e_texto_da_mensagem;

      $array_mensagens[] = $array_mensagem;
    }

    $this->get_smarty()->assign('mensagens', $array_mensagens);
    return $array_mensagens;
  }

  private function carregar_chat_de_outro_jeito($pagina_da_lista = 1){
    $chat_web_socket_model = new ChatWebSocketModel();

    $quantidade_de_paginas = $this->calcular_quantidade_de_paginas_das_mensagens(self::QUANTIDADE_POR_PAGINA);
    if($pagina_da_lista > $quantidade_de_paginas){
      $pagina_da_lista = $quantidade_de_paginas;
    }

    $this->get_smarty()->assign('pagina_atual_das_mensagens', $pagina_da_lista);
    $this->get_smarty()->assign('ultima_pagina_das_mensagens', $quantidade_de_paginas);

    $descartar = self::QUANTIDADE_POR_PAGINA * $pagina_da_lista - self::QUANTIDADE_POR_PAGINA;
    $descartar = max($descartar, 0);
    $mensagens_historico = $chat_web_socket_model->selecionar_mensagens_com_os_nomes_dos_usuarios(self::QUANTIDADE_POR_PAGINA, $descartar);

    $array_mensagens = array();
    foreach($mensagens_historico as $mensagem_historico){
      $pk_chat_mensagem = $mensagem_historico->get_fk_chat_mensagem();

      $array_resultado = $chat_web_socket_model->selecionar_primeira_mensagem_do_historico($pk_chat_mensagem);
      if(isset($array_resultado['mensagem_do_model'])){
        break;
      }
      $original = $array_resultado[0];

      $momento = $original->get_momento_da_mensagem();
      $sem_fuso_horario = new DateTimeZone('GMT');
      $fuso_horario_de_brasilia = new DateTimeZone('-0300');
      $objeto_date_time = new DateTime($momento, $sem_fuso_horario);
      $objeto_date_time->setTimeZone($fuso_horario_de_brasilia);
      $momento = $objeto_date_time->format('Y-m-d H:i:s');
      $momento = $this->converter_para_horario_data_do_html($momento);
      $array_mensagem['enviada_em'] = $momento;

      $array_mensagem['editada_em'] = null;
      if($original->get_pk_mensagem_historico() !== $mensagem_historico->get_pk_mensagem_historico()){
        $momento = $mensagem_historico->get_momento_da_mensagem();
        $sem_fuso_horario = new DateTimeZone('GMT');
        $fuso_horario_de_brasilia = new DateTimeZone('-0300');
        $objeto_date_time = new DateTime($momento, $sem_fuso_horario);
        $objeto_date_time->setTimeZone($fuso_horario_de_brasilia);
        $momento = $objeto_date_time->format('Y-m-d H:i:s');
        $momento = $this->converter_para_horario_data_do_html($momento);
        $array_mensagem['editada_em'] = $momento;
      }

      $nome_de_usuario_do_autor = $mensagem_historico->get_chat_mensagem()->get_usuario()->get_nome_de_usuario();
      $nome_de_usuario_do_autor = esc($nome_de_usuario_do_autor);

      $texto_da_mensagem = $mensagem_historico->get_texto_da_mensagem();
      $texto_da_mensagem = esc($texto_da_mensagem);

      $autor_e_texto_da_mensagem = "<b>$nome_de_usuario_do_autor:</b> $texto_da_mensagem";
      $autor_e_texto_da_mensagem = $this->acrescentar_quebras_de_linha_xhtml($autor_e_texto_da_mensagem);
      $array_mensagem['autor_e_texto_da_mensagem'] = $autor_e_texto_da_mensagem;

      $array_mensagens[] = $array_mensagem;
    }

    $this->get_smarty()->assign('mensagens', $array_mensagens);
    return $array_mensagens;
  }

  public function enviar_mensagem_ajax(){
    $chat_web_socket_model = new ChatWebSocketModel();

    $mensagem_historico = new MensagemHistorico();

    /* Obtendo valores do formulário */
    $requisicao = service('request');
    $texto_da_mensagem = trim($requisicao->getPost('mensagem') ?? '');
    $chave_anti_csrf = $requisicao->getPost('chave_anti_csrf');

    /* Validações */
    $chaves_anti_csrf = $this->get_sessao()->get('chaves_anti_csrf_da_pagina_chat_web_socket');
    if(!in_array($chave_anti_csrf, $chaves_anti_csrf)){
      $aviso = 'A página expirou. Por meio do navegador recarregue esta página e tente novamente.';
      $retorno['mensagem_de_falha'] = $aviso;
      echo json_encode($retorno);
      die;
    }
    if($texto_da_mensagem === ''){
      $aviso = 'O campo mensagem precisa ser preenchido.';
      $retorno['mensagem_de_falha'] = $aviso;
      echo json_encode($retorno);
      die;
    }
    $maximo = $mensagem_historico->quantidade_maxima_de_caracteres('texto_da_mensagem');
    $quantidade = mb_strlen($texto_da_mensagem);
    if($quantidade > $maximo){
      $aviso = "O campo mensagem não pode ultrapassar $maximo caracteres.";
      $retorno['mensagem_de_falha'] = $aviso;
      echo json_encode($retorno);
      die;
    }
    $mensagem_historico->set_texto_da_mensagem($texto_da_mensagem);

    /* Momento atual sem fuso horário, pois no banco de dados armazeno sem fuso horário (timezone) */
    $sem_fuso_horario = new DateTimeZone('GMT');
    $objeto_date_time = new DateTime('now', $sem_fuso_horario);
    $momento_atual = $objeto_date_time->format('Y-m-d H:i:s');
    $mensagem_historico->set_momento_da_mensagem($momento_atual);

    $id_do_usuario = 1; //Por enquanto, todas as mensagens são do usuário de pk_usuario 1.
    $chat_web_socket_model->inserir_mensagem($mensagem_historico, $id_do_usuario);

    /* Carregando chat */
    $this->carregar_chat();
    //$this->carregar_chat_de_outro_jeito();

    /* Enviando resposta */
    $retorno['mensagens'] = $this->get_smarty()->fetch('chat_web_socket/mensagens_do_chat.html');
    $retorno['paginacao'] = $this->get_smarty()->fetch('chat_web_socket/paginacao_das_mensagens.html');
    echo json_encode($retorno);
    die;
  }

  public function carregar_chat_ajax(){
    /* Obtendo valores */
    $requisicao = service('request');
    $pagina = trim($requisicao->getGet('pagina') ?? '');

    /* Validações */
    if(!is_numeric($pagina) or $pagina <= 0 or floor($pagina) != $pagina){
      $pagina = 1;
    }

    /* Carregando chat */
    $this->carregar_chat($pagina);
    //$this->carregar_chat_de_outro_jeito($pagina);

    /* Enviando resposta */
    $retorno['mensagens'] = $this->get_smarty()->fetch('chat_web_socket/mensagens_do_chat.html');
    $retorno['paginacao'] = $this->get_smarty()->fetch('chat_web_socket/paginacao_das_mensagens.html');
    echo json_encode($retorno);
    die;
  }

  private function calcular_quantidade_de_paginas_das_mensagens($quantidade_por_pagina){
    $chat_web_socket_model = new ChatWebSocketModel();

    $array_resultado = $chat_web_socket_model->contar_mensagens();
    $quantidade_de_paginas = ceil($array_resultado['quantidade'] / $quantidade_por_pagina);

    return $quantidade_de_paginas;
  }

}
