$(document).ready(function(){
  /* Funcionamento do chat */
  atualizar_chat();
  
  const $campo_mensagem = $("#campo_mensagem");
  const $div_mensagem_do_sistema = $("#div_mensagem_do_sistema");
  const $span_mensagem_do_sistema = $("#span_mensagem_do_sistema");
  const $campo_anti_csrf = $("#campo_anti_csrf");
	const $botao_enviar_mensagem = $("#botao_enviar_mensagem");
  const $div_paginacao_de_cima_da_lista_de_mensagens = $("#div_paginacao_de_cima_da_lista_de_mensagens");
  const $div_lista_de_mensagens = $("#div_lista_de_mensagens");
  const $div_paginacao_de_baixo_da_lista_de_mensagens = $("#div_paginacao_de_baixo_da_lista_de_mensagens");
  
	$botao_enviar_mensagem.click(function(){
		let mensagem = $campo_mensagem.val();
    let chave_anti_csrf = $campo_anti_csrf.val();
    
    $.ajax({
			url: "/chat_ajax_reverso/enviar_mensagem_ajax",
			type: "POST",
			data: {mensagem: mensagem, chave_anti_csrf: chave_anti_csrf},
			success: function(resposta){
        if(typeof resposta.mensagem_de_falha != "undefined"){
          $div_mensagem_do_sistema.removeClass("tag_oculta");
          $span_mensagem_do_sistema.text(resposta.mensagem_de_falha);
          return;
        }
        $div_mensagem_do_sistema.addClass("tag_oculta");
        $span_mensagem_do_sistema.text("");
        $div_paginacao_de_cima_da_lista_de_mensagens.html(resposta.paginacao);
        $div_lista_de_mensagens.html(resposta.mensagens);
        $div_paginacao_de_baixo_da_lista_de_mensagens.html(resposta.paginacao);
        $div_lista_de_mensagens.animate({scrollTop: 0}, 175, "swing");
        $campo_mensagem.val("");
			},
			dataType:"json"
		});
	});
  
  function atualizar_chat(){
    $.ajax({
      url: "/chat_ajax_reverso/carregar_chat_ajax_reverso",
      type: "POST",
      success: function(resposta){
        $div_paginacao_de_cima_da_lista_de_mensagens.html(resposta.paginacao);
        $div_lista_de_mensagens.html(resposta.mensagens);
        $div_paginacao_de_baixo_da_lista_de_mensagens.html(resposta.paginacao);
        $div_lista_de_mensagens.animate({scrollTop: 0}, 175, "swing");
        atualizar_chat();
      },
      dataType:"json"
    });
  }
  
  /* Paginação ajax da lista de mensagens */
  $div_paginacao_de_cima_da_lista_de_mensagens.on("click", "a", function(evento){
    evento.preventDefault();
    
    let valor_do_href = $(this).attr("href");
    let pagina = valor_do_href.substring(26);
    
    if(isNaN(pagina) || pagina % 1 != 0 || pagina <= 0){
      return;
    }
    
    $.ajax({
      url: "/chat_ajax_reverso/carregar_chat_ajax",
      type: "GET",
      data: {pagina: pagina},
      success: function(resposta){
        $div_paginacao_de_cima_da_lista_de_mensagens.html(resposta.paginacao);
        $div_lista_de_mensagens.html(resposta.mensagens);
        $div_paginacao_de_baixo_da_lista_de_mensagens.html(resposta.paginacao);
        $div_lista_de_mensagens.animate({scrollTop: 0}, 175, "swing");
      },
      dataType:"json"
    });
  });
  
  $div_paginacao_de_baixo_da_lista_de_mensagens.on("click", "a", function(evento){
    evento.preventDefault();
    
    let valor_do_href = $(this).attr("href");
    let pagina = valor_do_href.substring(26);
    
    if(isNaN(pagina) || pagina % 1 != 0 || pagina <= 0){
      return;
    }
    
    $.ajax({
      url: "/chat_ajax_reverso/carregar_chat_ajax",
      type: "GET",
      data: {pagina: pagina},
      success: function(resposta){
        $div_paginacao_de_cima_da_lista_de_mensagens.html(resposta.paginacao);
        $div_lista_de_mensagens.html(resposta.mensagens);
        $div_paginacao_de_baixo_da_lista_de_mensagens.html(resposta.paginacao);
        $div_lista_de_mensagens.animate({scrollTop: 0}, 175, "swing");
      },
      dataType:"json"
    });
  });
});
