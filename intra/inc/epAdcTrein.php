<?php
include ('inc.php');
include ('../config/config.php');
$descricao = $_REQUEST['descricao'];
$categoria = $_REQUEST['categoria'];
if(!$categoria || !$descricao){
	?> <script type="text/javascript">alert('Campos obrigatórios ficaram vazios, tente novamente...');</script><?php
	echo '<meta http-equiv="refresh" content="0.01; URL=http://gamp-web/ep/ep.php?tela=epAdcTrein" />';
	//print "Campos obrigatórios ficaram vazios, tente novamente... <br> Você está sendo redirecionado à página 	anterior...";
	//echo '<br> <a href="javascript:window.history.go(-1)">Voltar</a>';
	
}else{
	epInsertTreinamento($descricao,$categoria);
	?> <script type="text/javascript">alert('Treinamento adicionado... Você está sendo redirecionado...');</script><?php
	echo '<meta http-equiv="refresh" content="0.01; URL=http://gamp-web/ep/ep.php?tela=epViewTrein" />';
	//print "Treinamento adicionado... <br> Você está sendo redirecionado à página anterior...";
	//echo '<br> <a href="javascript:window.history.go(-1)">Voltar</a>';
}

?>