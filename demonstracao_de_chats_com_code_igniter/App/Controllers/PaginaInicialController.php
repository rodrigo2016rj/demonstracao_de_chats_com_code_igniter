<?php

namespace App\Controllers;

final class PaginaInicialController extends TemplateController{

  public function index(){
    $this->get_smarty()->display('pagina_inicial/pagina_inicial.html');
    die;
  }

}
