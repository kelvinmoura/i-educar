function FiltraCampo(codigo) {
    var s = "";

	tam = codigo.length;
	for (i = 0; i < tam ; i++) {
		if (codigo.substring(i,i + 1) == "0" ||
           	codigo.substring(i,i + 1) == "1" ||
            codigo.substring(i,i + 1) == "2" ||
            codigo.substring(i,i + 1) == "3" ||
            codigo.substring(i,i + 1) == "4" ||
            codigo.substring(i,i + 1) == "5" ||
            codigo.substring(i,i + 1) == "6" ||
            codigo.substring(i,i + 1) == "7" ||
            codigo.substring(i,i + 1) == "8" ||
            codigo.substring(i,i + 1) == "9"  )
		 		s = s + codigo.substring(i,i + 1);
	}
	return s;
}

function DvCnpjOk(e) {
    var dv = false;
    controle = "";
    s = FiltraCampo(e.value);
    tam = s.length
    if ( tam  == 14 ) {
        dv_cnpj = s.substring(tam-2,tam);
        for ( i = 0; i < 2; i++ ) {
            soma = 0;
            for ( j = 0; j < 12; j++ )
                soma += s.substring(j,j+1)*((11+i-j)%8+2);
            if ( i == 1 ) soma += digito * 2;
            digito = 11 - soma  % 11;
            if ( digito > 9 ) digito = 0;
            controle += digito;
        }
        if ( controle == dv_cnpj )
            dv = true;
     }
     if ( ! dv && tam > 0) {
         mensagem = "           Erro de digitação:\n";
         mensagem+= "          ===============\n\n";
         mensagem+= " O CNPJ: " + e.value + " não existe!!\n";
         alert(mensagem);
     }
     return dv;
}

function DvCpfOk(e) {
    var dv = false;

    controle = "";
    s = FiltraCampo(e.value);
    tam = s.length;
    if ( tam == 11 ) {
        dv_cpf = s.substring(tam-2,tam);
        for ( i = 0; i < 2; i++ ) {
            soma = 0;
            for ( j = 0; j < 9; j++ )
                soma += s.substring(j,j+1)*(10+i-j);
            if ( i == 1 ) soma += digito * 2;
            digito = (soma * 10) % 11;
            if ( digito == 10 ) digito = 0;
            controle += digito;
        }
        if ( controle == dv_cpf )
            dv = true;
    }
     if ( ! dv && tam > 0) {
         mensagem = "           Erro de digitação:\n";
         mensagem+= "          ===============\n\n";
         mensagem+= " O CPF: " + e.value + " não existe!!\n";
         alert(mensagem);
         e.value = "";
     }
    return dv;
}

function addSel( campo, valor, texto )
{
	obj = document.getElementById( campo );
	novoIndice = obj.options.length;
	obj.options[novoIndice] = new Option( texto );
	opcao = obj.options[novoIndice];
	opcao.value = valor;
	opcao.selected = true;
	setTimeout( "obj.onchange", 100 );
}

function addVal( campo, valor )
{
	obj = document.getElementById( campo );
	obj.value = valor;
}

function openPage(url_pagina, nome_pagina, largura, altura, scroll, top, left)
{
	janela = window.open(url_pagina,  nome_pagina, largura, altura, top, left, statusbar=scroll);
	janela.focus();
}

function verificaTamanhoEmail(campo, e)
{
	if( typeof window.event != "undefined" )
	{
		if(window.event.keyCode != 13 && window.event.keyCode != 8 && window.event.keyCode != 32)
		{
			if(document.getElementById(campo).value.length>16)
			{
				alert("Excedido nъmero maximo de caracteres, por favor use no mбximo e 16 caracteres!");
			}
		}

	}
	else
	{
		if(e.which != 13 && e.which != 8 && e.which != 32)
		{
			if(document.getElementById(campo).value.length>16)
			{
				alert("Excedido nъmero maximo de caracteres, por favor use no mбximo e 16 caracteres!");
			}
		}
	}
}

function trocaHora() 	{

	tempo++;
	dias = Math.floor(tempo / 86400);
	var temp_tempo;
	temp_tempo = tempo - dias*86400;
	horas = Math.floor( temp_tempo / 3600);
	temp_tempo = temp_tempo - horas*3600;
	min = Math.floor(temp_tempo / 60);
	temp_tempo = temp_tempo - min*60;
	seg = temp_tempo;
	var data = "";
	if(dias)
	{
		data = dias+" dias  ";
	}
	if(horas)
	{
		if(horas <10)
		{
			horas = "0"+horas;
		}
		data = data+horas;
	}else
	{
		data = data+"00";
	}
	if(min)
	{
		if(min <10)
		{
			min = "0"+min;
		}
		data = data+":"+min;
	}else
	{
		data = data+":00";
	}
	if(seg)
	{
		if(seg < 10)
		{
			seg = "0"+seg;
		}
		data = data+":"+seg;
	}else
	{
		data  = data+":00"
	}
	document.getElementById( 'tempo' ).innerHTML = data;
}

function move_pessoa_reuniao(idpes,acao,reuniao,grupo,div)
{
	DOM_execute_when_xmlhttpChange = function() {};
	DOM_loadXMLDoc( 'xml_reuniao_pessoa.php?pessoa='+idpes+'&acao='+acao+"&cod_reuniao="+reuniao+"&cod_grupo="+grupo);
	if(acao ==1)
	{
		document.getElementById(div).innerHTML = "<a href='#' onclick='move_pessoa_reuniao("+idpes+",2,"+reuniao+","+grupo+","+div+")'><img src='imagens/nvp_bot_sai_reuniao.gif' border='0'></a>";
	}else
	{
		document.getElementById(div).innerHTML = "<a href='#' onclick='move_pessoa_reuniao("+idpes+",1,"+reuniao+","+grupo+","+div+")'><img src='imagens/nvp_bot_entra_reuniao.gif' border='0'></a>";
	}

}

function marcar_todos()
{
		document.getElementById("desmarcar").checked = false;
		if(document.getElementById("marcar").checked )
		{
			for(i=0; i<marcar.length;i++)
			{
				document.getElementById("top_"+marcar[i]).checked = true;
			}
		}
}

function desmarcar_todos()
{

		document.getElementById("marcar").checked = false;
		if( document.getElementById("desmarcar").checked )
		{
			for(i=0; i<marcar.length;i++)
			{
				document.getElementById("top_"+marcar[i]).checked = false;
			}
		}
}

function desmarcar_marcar(id)
{
	if(!document.getElementById(id).checked )
	{
		document.getElementById("marcar").checked = false;
	}else
	{
		document.getElementById("desmarcar").checked = false;
	}
}

function definirOrdenacao(campo_ordenacao)
{
	controle = document.getElementById('ordenacao');
	if(controle.value != campo_ordenacao+" ASC")
	{
		setas = document.getElementById('fonte');
		setas.value = 'imagens/nvp_setinha_up.gif';
		controle.value = campo_ordenacao+" ASC";
	}
	else
	{
		setas = document.getElementById('fonte');
		setas.value = 'imagens/nvp_setinha_down.gif';
		controle.value = campo_ordenacao+" DESC";
	}
}

