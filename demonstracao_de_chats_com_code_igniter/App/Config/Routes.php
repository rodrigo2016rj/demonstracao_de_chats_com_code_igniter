<?php

/*
 * @var RouteCollection $routes
 */

/* Página Padrão */
$routes->get('/', 'PaginaInicialController::index');

/* Página Inicial */
$routes->get('/pagina_inicial', 'PaginaInicialController::index');

/* Chat Ajax Reverso */
$routes->get('/chat_ajax_reverso', 'ChatAjaxReversoController::index');
$routes->post('/chat_ajax_reverso/carregar_chat_ajax_reverso', 'ChatAjaxReversoController::carregar_chat_ajax_reverso');
$routes->post('/chat_ajax_reverso/enviar_mensagem_ajax', 'ChatAjaxReversoController::enviar_mensagem_ajax');
$routes->get('/chat_ajax_reverso/carregar_chat_ajax', 'ChatAjaxReversoController::carregar_chat_ajax');

/* Chat Web Socket */
$routes->get('/chat_web_socket', 'ChatWebSocketController::index');
$routes->post('/chat_web_socket/enviar_mensagem_ajax', 'ChatWebSocketController::enviar_mensagem_ajax');
$routes->get('/chat_web_socket/carregar_chat_ajax', 'ChatWebSocketController::carregar_chat_ajax');
