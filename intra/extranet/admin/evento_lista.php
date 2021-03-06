<?
/*
	Modelo de p�gina que apresenta uma lista de registros
*/
include("../inc/common.php");

/*
	verifica��o do n�vel do usu�rio, altere conforme sua necessidade, os n�meros na string representam os grupos permitidos
*/
verificaPermissaoPagina("10,1,");

/*
	conex�o com o banco de dados, altere somente se a conex�o for diferente do default
*/
$conn = new db();
$conn->open();

/*
	determina a p�gina a ser exibida, n�o precisa alterar
*/
$pg = getParam("pagina");
if ($pg == "") $pg = 1;

/*
	Limpa ordena��o e filtro, n�o deve ser alterado
*/
if (getParam("clear")==1) {
	setSession("sOrder","");
	setSession("where","");
	setSession("pagina_atual","");
}

/*
	Salva o status da p�gina atual, n�o deve ser alterado
*/
if ($_SERVER['PHP_SELF'] != $pagina_atual) {
	$mesma_pagina = false;
	setSession("pagina_atual",$_SERVER['PHP_SELF']);
} else {
	$mesma_pagina = true;
}

/*
	constru��o da ordena��o
*/
$iSort = getParam("Sorting");
$iSorted = getParam("Sorted");
if ((!$iSort)&&(!$mesma_pagina)) {
	$form_sorting = "";
	$iSort = 2; // configure a ordena��o inicial da lista de acordo com as colunas da tabela 
	$iSorted = ""; // se a ordena��o estiver DESCENDENTE, repita o mesmo valor abaixo
}
if ($iSort) {
	if ($iSort == $iSorted) {
		$form_sorting = "";
		$sDirection = " DESC";
		$sSortParams = "Sorting=" . $iSort . "&Sorted=" . $iSort . "&";
	} else {
		$form_sorting = $iSort;
		$sDirection = " ASC";
		$sSortParams = "Sorting=" . $iSort . "&Sorted=" . "&";
	}
	/*
		coloque aqui a defini��o das ordena��es das colunas de acordo com as colunas da tabela
	*/
	if ($iSort == 2) setSession("sOrder"," order by evento.txt_titulo" . $sDirection); 
	if ($iSort == 3) setSession("sOrder"," order by evento.dt_data" . $sDirection);
	if ($iSort == 4) setSession("sOrder"," order by evento.fonte" . $sDirection);
}

if (getParam("rodou")=="s") { // se ocorreu pesquisa...
	$onde = "";
	/*
		construa a string WHERE conforme o exemplo abaixo
	*/
	if (getParam("pesq_nome_usuario") != "")    $onde .= "AND evento.txt_titulo LIKE '%" . getParam("pesq_nome_usuario") . "%'";
	if (getParam("pesq_nome_real") != "")       $onde .= "AND evento.dt_data LIKE '%" .    getParam("pesq_nome_real") . "%'";
	if (getParam("pesq_departamento_id") != "") $onde .= "AND evento.fonte = " .   getParam("pesq_departamento_id") . "";
	setSession("where",$onde);
}

/*
	flag pra informar se o filtro est� ou n�o ativo
*/
if (strlen(getSession("where"))>0) {
	$filtrado = FILTRO_ATIVO;
} else {
	$filtrado = "";
}

/*
	express�o SQL que define a lista, construa livremente observando a concatena��o com as
	sessions WHERE e sOrder, conforme exemplo abaixo
*/
$sql = "SELECT * "
     . "FROM evento "
     . "WHERE 1=1 " 
	 . getSession("where") 
	 . getSession("sOrder");
	// echo $sql;
	 
/*
	cria��o do recordset, altere somente o �ltimo par�metro que	corresponde a quantidade de
	registros por p�gina
*/
$rs = new query($conn, $sql, $pg, 10);
?>
<html>
<head>
	<title>evento-lista</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
	<script language="javascript" src="../inc/js/checkall.js"></script>
	
	<script language="JavaScript">
	/*
		fun��o que chama a rotina de exclus�o de registros, altere somente o nome da p�gina
		a ser chamada
	*/
	function excluir() {
		if (confirm('Excluir registros selecionados?')) {
			parent.content.document.frm.target = "controle";
			parent.content.document.frm.action = "../admin/evento_excluir.php";
			parent.content.document.frm.submit();
		}
	}
	
	</script>
</head>
<body class="contentBODY">

<?
pageTitle("Quadro de Avisos","Lista");

/*
	bot�es de a��es
*/
$button = new Button;

/*
	bot�es de navega��o da lista, n�o deve ser alterada
*/
$pg_ant = $pg-1;
$pg_prox = $pg+1;
if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,$_SERVER['PHP_SELF']."?pagina=$pg_ant" ,"content");
if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,$_SERVER['PHP_SELF']."?pagina=$pg_prox","content");

/*
	bot�es de a��es da lista, altere conforme suas necessidades
*/
$button->addItem("Novo","../admin/evento_edicao.php","content");
//$button->addItem("Pesquisa",   "../admin/evento_pesquisa.php","content");
$button->addItem("Excluir",    "javascript:excluir()","content");
echo $button->writeHTML();
?>
<br>
<!-- Lista -->
<div align="center">
<form name="frm" method="post">
<?
/*
	inicializa��o da tabela
*/
$table = new Table("","100%",6); // T�tulo, Largura, Quantidade de colunas

/*
	Configura��o das colunas da tabela
*/
$table->addColumnHeader("<input type=\"checkbox\" name=\"checkall\"onclick=\"CheckAll()\">"); // Coluna com checkbox
$table->addColumnHeader("T�tulo",true,"50%", "L"); // T�tulo, Ordenar?, Largura, Alinhamento
$table->addColumnHeader("Data",true,"10%","L");
$table->addColumnHeader("Publicado",true,"40%","L");
$table->addRow(); // adiciona linha (TR)

while ($rs->getrow()) {
	$id = $rs->field("cod_evento"); // captura a chave prim�ria do recordset
	
	$table->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\">");
	$table->addData(addLink($rs->field("txt_titulo"),"../admin/evento_edicao.php?id=$id&pagina=$pg","Clique para consultar ou editar registro"));
	$table->addData(stod(substr($rs->field("dt_data"),0,10)));
	$table->addData($rs->field("int_publicado")==1?"Sim":"N�o","L");
	$table->addRow();
}

echo "<div class='DataFONT' align='right'><b>$filtrado</b></div>";

/*
	Desenha a tabela
*/
if ($rs->numrows()>0) {
	echo $table->writeHTML();
	echo "<div class='DataFONT'>P�gina ".$pg." de ".$rs->totalpages()."</div>";
} else {
	echo "<div class='DataFONT'>Nenhum registro encontrado!</div>";
}
?>
</form>
</div>

</body>
</html>
<?
/*
	Fecha a conex�o com o banco de dados.
*/
$conn->close();
?>