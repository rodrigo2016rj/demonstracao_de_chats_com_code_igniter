<?php

namespace App\Models;

use App\Models\PrimordialModel;
use App\Models\Entidades\Usuario;
use App\Models\Entidades\ChatMensagem;
use App\Models\Entidades\MensagemHistorico;
use CodeIgniter\Database\RawSql;

final class ChatWebSocketModel extends PrimordialModel{

  public function __construct(){
    parent::__construct();
  }

  public function selecionar_mensagens($quantidade, $descartar){
    $builder = $this->get_banco_de_dados()->table('chat_mensagem');
    $builder->select('pk_chat_mensagem, fk_usuario');

    $builder->orderBy('pk_chat_mensagem', 'DESC');

    $builder->limit($quantidade, $descartar);

    $query = $builder->get();
    $array_resultado = $query->getResult('array');

    $array_melhorado = array();
    foreach($array_resultado as $array_chat_mensagem){
      $chat_mensagem = new ChatMensagem($array_chat_mensagem);
      $array_melhorado[] = $chat_mensagem;
    }
    $array_resultado = $array_melhorado;

    return $array_resultado;
  }

  public function selecionar_primeira_mensagem_do_historico($fk_chat_mensagem){
    $builder = $this->get_banco_de_dados()->table('mensagem_historico');
    $builder->select('pk_mensagem_historico, fk_chat_mensagem, momento_da_mensagem, texto_da_mensagem');

    $builder->orderBy('pk_mensagem_historico', 'ASC');

    $builder->limit(1, 0);

    $builder->where('fk_chat_mensagem =', $fk_chat_mensagem);

    $query = $builder->get();
    $array_resultado = $query->getResult('array');

    if(count($array_resultado) === 0){
      $mensagem_do_model = 'O texto da mensagem não foi encontrado.';
      $array_resultado['mensagem_do_model'] = $mensagem_do_model;
    }else{
      $mensagem_historico = new MensagemHistorico($array_resultado[0]);
      $array_melhorado[] = $mensagem_historico;
      $array_resultado = $array_melhorado;
    }

    return $array_resultado;
  }

  public function selecionar_ultima_mensagem_do_historico($fk_chat_mensagem){
    $builder = $this->get_banco_de_dados()->table('mensagem_historico');
    $builder->select('pk_mensagem_historico, fk_chat_mensagem, momento_da_mensagem, texto_da_mensagem');

    $builder->orderBy('pk_mensagem_historico', 'DESC');

    $builder->limit(1, 0);

    $builder->where('fk_chat_mensagem =', $fk_chat_mensagem);

    $query = $builder->get();
    $array_resultado = $query->getResult('array');

    if(count($array_resultado) === 0){
      $mensagem_do_model = 'O texto da mensagem não foi encontrado.';
      $array_resultado['mensagem_do_model'] = $mensagem_do_model;
    }else{
      $mensagem_historico = new MensagemHistorico($array_resultado[0]);
      $array_melhorado[] = $mensagem_historico;
      $array_resultado = $array_melhorado;
    }

    return $array_resultado;
  }

  public function selecionar_usuario($pk_usuario){
    $builder = $this->get_banco_de_dados()->table('usuario');
    $builder->select('pk_usuario, nome_de_usuario, email, momento_do_cadastro, fuso_horario');
    $builder->where('pk_usuario =', $pk_usuario);

    $query = $builder->get();
    $array_resultado = $query->getResult('array');

    if(count($array_resultado) === 0){
      $mensagem_do_model = "Nenhum usuário com ID $pk_usuario foi encontrado no banco de dados";
      $mensagem_do_model .= ' do sistema.';
      $array_resultado['mensagem_do_model'] = $mensagem_do_model;
    }else{
      $usuario = new Usuario($array_resultado[0]);
      $array_melhorado[] = $usuario;
      $array_resultado = $array_melhorado;
    }

    return $array_resultado;
  }

  public function selecionar_mensagens_com_os_nomes_dos_usuarios($quantidade, $descartar){
    $builder = $this->get_banco_de_dados()->table('mensagem_historico');
    $builder->select('usuario.pk_usuario, usuario.nome_de_usuario, chat_mensagem.pk_chat_mensagem, 
mensagem_historico.pk_mensagem_historico, mensagem_historico.texto_da_mensagem, 
mensagem_historico.momento_da_mensagem');

    $builder->join('chat_mensagem', 'fk_chat_mensagem = pk_chat_mensagem', 'INNER');

    $builder->join('usuario', 'fk_usuario = pk_usuario', 'INNER');

    $sql = '(SELECT fk_chat_mensagem, MAX(momento_da_mensagem) AS momento_da_mensagem 
FROM mensagem_historico GROUP BY fk_chat_mensagem) ultima_mensagem';
    $builder->join(new RawSql($sql), 'ultima_mensagem.fk_chat_mensagem = mensagem_historico.fk_chat_mensagem', 'INNER');

    $builder->where('mensagem_historico.momento_da_mensagem = ultima_mensagem.momento_da_mensagem');

    $builder->orderBy('pk_mensagem_historico', 'DESC');

    $builder->limit($quantidade, $descartar);

    $query = $builder->get();
    $array_resultado = $query->getResult('array');

    $array_melhorado = array();
    foreach($array_resultado as $array_valores){
      $usuario = new Usuario();
      $usuario->set_pk_usuario($array_valores['pk_usuario']);
      $usuario->set_nome_de_usuario($array_valores['nome_de_usuario']);

      $chat_mensagem = new ChatMensagem();
      $chat_mensagem->set_pk_chat_mensagem($array_valores['pk_chat_mensagem']);
      $chat_mensagem->set_fk_usuario($array_valores['pk_usuario']);
      $chat_mensagem->set_usuario($usuario);

      $mensagem_historico = new MensagemHistorico();
      $mensagem_historico->set_pk_mensagem_historico($array_valores['pk_mensagem_historico']);
      $mensagem_historico->set_fk_chat_mensagem($array_valores['pk_chat_mensagem']);
      $mensagem_historico->set_texto_da_mensagem($array_valores['texto_da_mensagem']);
      $mensagem_historico->set_momento_da_mensagem($array_valores['momento_da_mensagem']);
      $mensagem_historico->set_chat_mensagem($chat_mensagem);

      $array_melhorado[] = $mensagem_historico;
    }
    $array_resultado = $array_melhorado;

    return $array_resultado;
  }

  public function inserir_mensagem($mensagem_historico, $pk_usuario){
    $builder = $this->get_banco_de_dados()->table('chat_mensagem');
    $insert = array();
    $insert['fk_usuario'] = $pk_usuario;
    $builder->insert($insert);

    $pk_chat_mensagem = $builder->db()->insertID();
    $mensagem_historico->set_fk_chat_mensagem($pk_chat_mensagem);

    $builder = $this->get_banco_de_dados()->table('mensagem_historico');
    $insert = array();
    $insert['fk_chat_mensagem'] = $mensagem_historico->get_fk_chat_mensagem();
    $insert['momento_da_mensagem'] = $mensagem_historico->get_momento_da_mensagem();
    $insert['texto_da_mensagem'] = $mensagem_historico->get_texto_da_mensagem();
    $builder->insert($insert);
  }

  public function contar_mensagens(){
    $builder = $this->get_banco_de_dados()->table('chat_mensagem');
    $array_resultado['quantidade'] = $builder->countAllResults();
    return $array_resultado;
  }

}
