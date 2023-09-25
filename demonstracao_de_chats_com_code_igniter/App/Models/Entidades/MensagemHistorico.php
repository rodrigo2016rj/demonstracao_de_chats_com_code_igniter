<?php

namespace App\Models\Entidades;

final class MensagemHistorico{
  private $pk_mensagem_historico;
  private $fk_chat_mensagem;
  private $momento_da_mensagem;
  private $texto_da_mensagem;
  private $chat_mensagem;

  public function __construct($array_mensagem_historico = array()){
    if(isset($array_mensagem_historico['pk_mensagem_historico'])){
      $this->pk_mensagem_historico = $array_mensagem_historico['pk_mensagem_historico'];
    }
    if(isset($array_mensagem_historico['fk_chat_mensagem'])){
      $this->fk_chat_mensagem = $array_mensagem_historico['fk_chat_mensagem'];
    }
    if(isset($array_mensagem_historico['momento_da_mensagem'])){
      $this->momento_da_mensagem = $array_mensagem_historico['momento_da_mensagem'];
    }
    if(isset($array_mensagem_historico['texto_da_mensagem'])){
      $this->texto_da_mensagem = $array_mensagem_historico['texto_da_mensagem'];
    }
    if(isset($array_mensagem_historico['chat_mensagem'])){
      $this->chat_mensagem = $array_mensagem_historico['chat_mensagem'];
    }
  }

  public function set_pk_mensagem_historico($pk_mensagem_historico){
    $this->pk_mensagem_historico = $pk_mensagem_historico;
  }

  public function set_fk_chat_mensagem($fk_chat_mensagem){
    $this->fk_chat_mensagem = $fk_chat_mensagem;
  }

  public function set_momento_da_mensagem($momento_da_mensagem){
    $this->momento_da_mensagem = $momento_da_mensagem;
  }

  public function set_texto_da_mensagem($texto_da_mensagem){
    $this->texto_da_mensagem = $texto_da_mensagem;
  }

  public function set_chat_mensagem($chat_mensagem){
    $this->chat_mensagem = $chat_mensagem;
  }

  public function get_pk_mensagem_historico(){
    return $this->pk_mensagem_historico;
  }

  public function get_fk_chat_mensagem(){
    return $this->fk_chat_mensagem;
  }

  public function get_momento_da_mensagem(){
    return $this->momento_da_mensagem;
  }

  public function get_texto_da_mensagem(){
    return $this->texto_da_mensagem;
  }

  public function get_chat_mensagem(){
    return $this->chat_mensagem;
  }

  // O método abaixo deve ser sempre igual ou mais restritivo que o banco de dados.
  public function quantidade_maxima_de_caracteres($atributo){
    switch($atributo){
      case 'texto_da_mensagem':
        return 400;
    }
    return -1;
  }

}
