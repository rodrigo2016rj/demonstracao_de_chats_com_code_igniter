$(window).on("load", function(){
  /* Removendo o foco do botão enviar mensagem quando o cursor sai de cima dele e após o clique */
  const $botao_enviar_mensagem = $("#botao_enviar_mensagem");
  
  $botao_enviar_mensagem.mouseleave(function(){
    $(this).blur();
  });
  $botao_enviar_mensagem.click(function(){
    $(this).blur();
  });
});
