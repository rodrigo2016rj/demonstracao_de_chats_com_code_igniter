<?php

namespace App\Controllers;

use Smarty\Smarty;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class TemplateController extends Controller{
  private $smarty; //Armazena objeto do smarty.
  private $sessao; //Armazena a sessão.

  public function initController(RequestInterface $requisicao, ResponseInterface $resposta,
    LoggerInterface $logger){
    parent::initController($requisicao, $resposta, $logger);

    $this->sessao = session();

    $this->smarty = new Smarty();
    $this->smarty->setTemplateDir(APPPATH.'Views/');
    $this->smarty->setCompileDir(APPPATH.'Views/views_cache_smarty/');

    $this->smarty->assign('visual_template', 'visual_cinza');
  }

  protected final function get_smarty(){
    return $this->smarty;
  }

  protected final function get_sessao(){
    return $this->sessao;
  }

  /** ---------------------------------------------------------------------------------------------
    Cria hash para a chave anti csrf. */
  protected final function criar_hash_da_chave_anti_csrf(){
    //Baseado no método generateHash da framework CodeIgniter.
    return bin2hex(random_bytes(17));
  }

  /** ---------------------------------------------------------------------------------------------
    Acrescenta quebras de linha no padrão XHTML. */
  protected function acrescentar_quebras_de_linha_xhtml($texto){
    //Armazena em array todos os padrões de quebra de linha de sistemas operacionais diferentes
    $tipos_de_quebras_de_sistemas_operacionais = array("\r\n", "\r", "\n");
    //Substitui quebras de linha presentes na string por: termina parágrafo </p> começa parágrafo <p>
    $texto_modificado = str_replace($tipos_de_quebras_de_sistemas_operacionais, '</p><p>', $texto);
    //Substitui parágrafo vazio por: quebra de linha <br/>
    $texto_resultante = str_replace('<p></p>', '<br/>', $texto_modificado);
    //Retorna o texto resultante dentro da tag <p></p>
    return "<p>$texto_resultante</p>";
  }

  /** ---------------------------------------------------------------------------------------------
    Converte yyyy-MM-dd xx:yy:zz para: dd/MM/yyyy às xxhyy */
  protected function converter_para_horario_data_do_html($string){
    if(!preg_match('/^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$/', $string)){
      //Caso não venha no formato certo, retorna a string sem conversão.
      return $string;
    }
    $ano = substr($string, 0, 4);
    $mes = substr($string, 5, 2);
    $dia = substr($string, 8, 2);
    $horas = substr($string, 11, 2);
    $minutos = substr($string, 14, 2);
    return "$dia/$mes/$ano às ".$horas.'h'.$minutos;
  }

}
