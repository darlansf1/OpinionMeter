<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
include_once 'includes/lpbhn.php';	//Using functions 'setTransductiveData' and 'unsetTransductiveData'

sec_session_start();
unsetLabelingProcessData();

?>
<!DOCTYPE html>
<html lang="pt">
    <head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="author" content="Rafael Paravia">
		<title>Manual</title>
		
        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<!-- Bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
		
		<link rel="stylesheet" href="styles/main.css" />
		<link rel="stylesheet" href="styles/sticky-footer-navbar.css" />
		
		<style> .panel-group {margin-bottom: 10px;}	</style>
		
    </head>
    <body>
		<?php showAlert(); ?>
        <?php if (login_check($mysqli) == true) : ?>
			<header>
				<nav class="navbar navbar-default">
					<div class="container">
						<div class="navbar-header navbar-left">
							<a class="navbar-brand" href="index.php">RotuLabic</a>
						</div>
						<p class="navbar-text">
							--  Olá, <?php echo htmlentities($_SESSION['username']); ?>!
						</p>
						<div id="navbar" class="collapse navbar-collapse navbar-right">
							<ul class="nav navbar-nav">
								<li><a href="profile.php">Perfil</a></li>
								<?php if (($_SESSION['user_role'] == 'processAdmin')  ){
										echo 	'<li><a href="helpAdmin.php">Manual do administrador</a></li>
												<li><a href="help.php">Manual do usuário</a></li>';
									}else{
										echo '<li><a href="help.php">Manual</a></li>';
									}
								?>
								<li><a href="includes/logout.php">Sair</a></li>
							</ul>
						</div><!--/.nav-collapse -->
					</div>
				</nav>
			</header>		
		<?php else : ?>
			<header>
				<nav class="navbar navbar-default">
					<div class="container">
						<div class="navbar-header navbar-left">
							<a class="navbar-brand" href="index.php">RotuLabic - Sistema de Apoio à Rotulação Manual de Textos</a>
						</div>
						<div id="navbar" class="collapse navbar-collapse navbar-right">
							<ul class="nav navbar-nav">
							<li><a href="help.php">Manual</a></li>
							</ul>
						</div><!--/.nav-collapse -->
					</div>
				</nav>
			</header>
        <?php endif; ?>
		
		<div class="jumbotron" style="padding-top: 0px;">
			<div class="container">
				<div class="page-header ">
					<h1 class="text-center">Instruções / FAQ</h1>
				</div>
      
				<div class="panel-group" id="accordion">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapse1">
									Qual o intuito deste website?        
								</a>
							</h4>
						</div>
						<div id="collapse1" class="panel-collapse collapse ">
							<div class="panel-body">
								&nbsp;&nbsp;&nbsp;Temos, no campo de Mineração de Textos, existe a necessidade de coleções de documentos rotulados para, por exemplo, trabalhar com classificação e/ou agrupamento de textos. A título de exemplo, para que seu correio eletrônico automaticamente classifique um email como spam, é necessário que outros emails tenham sido marcados/rotulados como spam para que haja uma base de exemplo para esta classificação automática.
								<br>&nbsp;&nbsp;&nbsp;Entretanto, o trabalho de rotular coleções de textos é geralmente lento e cansativo. Assim, este website surge como uma ferramenta para auxiliar/melhorar o processo de rotulação manual de documentos, podendo colaborar com outros trabalhos em andamento na área de Mineração de Textos.
							</div>
						</div>
					</div>
				</div> 
		
				<div class="panel-group" id="accordion">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapse3">
									Como funciona um processo de rotulação?        
								</a>
							</h4>
						</div>
						<div id="collapse3" class="panel-collapse collapse ">
							<div class="panel-body">
								&nbsp;&nbsp;&nbsp;Existem dois tipos de usuários: administradores e rotuladores.<br>
								&nbsp;&nbsp;&nbsp;Para criar um Processo de Rotulação (PR), o administrador deverá fornecer os documentos a serem rotulados e as configurações deste PR. Assim, após um PR ser criado, o administrador deverá adicionar os usuários (rotuladores) que desejar ao PR criado.
								Finalmente, após um rotulador ser adicionado a um PR, este poderá iniciá-lo, atrelando rótulos aos documentos fornecidos.
							</div>
						</div>
					</div>
				</div> 
				<div class="panel-group" id="accordion">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapse4">
									Posso criar um processo de rotulação?        
								</a>
							</h4>
						</div>
						<div id="collapse4" class="panel-collapse collapse ">
							<div class="panel-body">
								&nbsp;&nbsp;&nbsp;Apenas administradores podem criar um processo de rotulação.<br>
								&nbsp;&nbsp;&nbsp;Para tornar-se um administrador, contate o administrador do sistema.
							</div>
						</div>
					</div>
				</div> 	
				<div class="panel-group" id="accordion">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapse5">
									Como que aprendizado de máquina é utilizado para sugerir rótulos?        
								</a>
							</h4>
						</div>
						<div id="collapse5" class="panel-collapse collapse ">
							<div class="panel-body">
								&nbsp;&nbsp;&nbsp;O principal método de sugestão de rótulos deste projeto é o baseado em classificação transdutiva. 
								Analisando de uma maneira geral, este método faz o ‘peso’ do rótulo escolhido propagar
								para documentos semelhantes ao atual (ou seja, que contém bastante termos iguais).<br>
								&nbsp;&nbsp;&nbsp;Exemplificando, imaginemos que um rotulador escolheu o rótulo ‘Esporte’ para um certo
								documento e suponhamos que neste documento a palavra ‘futebol’ aparece por diversas vezes. Deste modo e 
								utilizando este algoritmo, outros documentos que contenham a palavra ‘futebol’ terão maiores 
								chances de terem o rótulo ‘Esporte’ sugerido.<br><br>
								&nbsp;&nbsp;&nbsp;<b>Referência</b>: Rossi, R. G., Lopes, A. A., e Rezende, S. O. (2014). A parameter-free label propagation algorithm using bipartite heterogeneous networks for text classification. In Proc. Symposium on Applied Computing, páginas 79-84 <a href="http://dl.acm.org/citation.cfm?id=2554901">[download]</a>
							</div>
						</div>
					</div>
				</div> 					
			</div>
		</div>
		<footer class="footer">
			<div class="container">
				<p class="text-muted">
					Esta obra de <a xmlns:cc="http://creativecommons.org/ns#" href="http://labic.icmc.usp.br/" property="cc:attributionName" rel="cc:attributionURL">LABIC | ICMC-USP</a> 
					está licenciado com uma Licença <a rel="license" href="http://creativecommons.org/licenses/by/4.0/">Creative Commons Atribuição 4.0 Internacional</a>.				
					<a rel="license" href="http://creativecommons.org/licenses/by/4.0/"><img alt="Licença Creative Commons" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/80x15.png" /></a>
				</p>
			</div>
		</footer>
    </body>
</html>
