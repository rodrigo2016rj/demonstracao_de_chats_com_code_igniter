<?php

namespace App\Models\Entidades;

final class ChatMensagem{
  private $pk_chat_mensagem;
  private $fk_usuario;
  private $usuario;

  public function __construct($array_chat_mensagem = array()){
    if(isset($array_chat_mensagem['pk_chat_mensagem'])){
      $this->pk_chat_mensagem = $array_chat_mensagem['pk_chat_mensagem'];
    }
    if(isset($array_chat_mensagem['fk_usuario'])){
      $this->fk_usuario = $array_chat_mensagem['fk_usuario'];
    }
    if(isset($array_chat_mensagem['usuario'])){
      $this->usuario = $array_chat_mensagem['usuario'];
    }
  }

  public function set_pk_chat_mensagem($pk_chat_mensagem){
    $this->pk_chat_mensagem = $pk_chat_mensagem;
  }

  public function set_fk_usuario($fk_usuario){
    $this->fk_usuario = $fk_usuario;
  }

  public function set_usuario($usuario){
    $this->usuario = $usuario;
  }

  public function get_pk_chat_mensagem(){
    return $this->pk_chat_mensagem;
  }

  public function get_fk_usuario(){
    return $this->fk_usuario;
  }

  public function get_usuario(){
    return $this->usuario;
  }

}
