<?php

$address = 'localhost';
$port = 12345;
$null = null;

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($socket, $address, $port);
socket_listen($socket);

$array_clientes = array($socket);

while(true){
  $array_clientes_temp = $array_clientes;
  socket_select($array_clientes_temp, $null, $null, 0, 10);

  if(in_array($socket, $array_clientes_temp)){
    $cliente = socket_accept($socket);

    $array_clientes[] = $cliente;

    //Realiza o handshake:
    $requisicao = socket_read($cliente, 5000);
    preg_match('#Sec-WebSocket-Key: (.*)\r\n#', $requisicao, $incidencias);
    $chave = base64_encode(pack(
        'H*',
        sha1($incidencias[1].'258EAFA5-E914-47DA-95CA-C5AB0DC85B11')
    ));
    $cabecalho = "HTTP/1.1 101 Switching Protocols\r\n";
    $cabecalho .= "Upgrade: websocket\r\n";
    $cabecalho .= "Connection: Upgrade\r\n";
    $cabecalho .= "Sec-WebSocket-Version: 13\r\n";
    $cabecalho .= "Sec-WebSocket-Accept: $chave\r\n\r\n";
    socket_write($cliente, $cabecalho, strlen($cabecalho));

    //Obtem o IP do socket:
    socket_getpeername($cliente, $ip);

    $resultado_da_procura = array_search($socket, $array_clientes_temp);
    unset($array_clientes_temp[$resultado_da_procura]);
  }

  //Considera cada socket conectado:
  foreach($array_clientes_temp as $cliente){
    //Verifica mensagem recebida:
    while(socket_recv($cliente, $mensagem_recebida, 5000, 0) >= 1){
      $mensagem = decodificar_mensagem($mensagem_recebida);
      if($mensagem !== 'Atualizações no chat!'){
        break 2;
      }
      $resposta = codificar_mensagem($mensagem);

      //Envia mensagem para cada socket conectado:
      foreach($array_clientes as $cliente){
        @socket_write($cliente, $resposta, strlen($resposta));
      }

      break 2;
    }

    $mensagem_recebida = @socket_read($cliente, 5000, PHP_NORMAL_READ);

    //Verifica se o cliente já se desconectou:
    if($mensagem_recebida === false){
      //Remove este cliente do $array_clientes:
      $socket_encontrado = array_search($cliente, $array_clientes);
      socket_getpeername($cliente, $ip);
      unset($array_clientes[$socket_encontrado]);
    }
  }
}

//Encerra o socket aberto:
socket_close($socket);

//Método que codifica a mensagem:
function codificar_mensagem($texto){
  $b1 = 0x80 | (0x1 & 0x0f);
  $comprimento = strlen($texto);

  if($comprimento <= 125){
    $cabecalho = pack('CC', $b1, $comprimento);
  }elseif($comprimento > 125 && $comprimento < 65536){
    $cabecalho = pack('CCn', $b1, 126, $comprimento);
  }elseif($comprimento >= 65536){
    $cabecalho = pack('CCNN', $b1, 127, $comprimento);
  }

  return $cabecalho.$texto;
}

//Método que decodifica a mensagem:
function decodificar_mensagem($texto){
  $comprimento = ord($texto[1]) & 127;

  if($comprimento == 126){
    $pedaco = substr($texto, 4, 4);
    $outro_pedaco = substr($texto, 8);
  }elseif($comprimento == 127){
    $pedaco = substr($texto, 10, 4);
    $outro_pedaco = substr($texto, 14);
  }else{
    $pedaco = substr($texto, 2, 4);
    $outro_pedaco = substr($texto, 6);
  }

  $texto = '';
  for($i = 0; $i < strlen($outro_pedaco); ++$i){
    $texto .= $outro_pedaco[$i] ^ $pedaco[$i % 4];
  }

  return $texto;
}
