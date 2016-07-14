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
		<title>Manual do Administrador</title>
		
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
        <?php if ((login_check($mysqli) == true) && ($_SESSION['user_role'] == 'processAdmin')  ) : ?>
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

			<div class="jumbotron" style="padding-top: 0px;">
				<div class="container">
					<div class="page-header ">
						<h1 class="text-center">Instruções / FAQ - Administradores</h1>
					</div>
		  
					<div class="panel-group" id="accordion">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" data-parent="#accordion" href="#collapse1">
										O que são os parâmetros do formulário "Criar Novo Processo de Rotulação"? 
									</a>
								</h4>
							</div>
							<div id="collapse1" class="panel-collapse collapse ">
								<div class="panel-body">
								<ul>
									<li><strong>Nome do Processo de Rotulação</strong>: define o nome/título do processo de rotulação (PR).</li>
									<li><strong>Instruções para os rotuladores</strong>: documento no formato ".txt". Seu conteúdo será utilizado para instruir os rotuladores sobre como prodeceder durante o PR e é apresentado em uma página anterior à página de rotulação. A ideia é que seu conteúdo tenha dicas e exemplos de como rotular um documento, mas seu conteúdo fica a cargo do administrador do PR. 
									<li><strong>Documentos a serem rotulados</strong>: coleção de documentos no formato ".txt" que deverá ser rotulada. 
									<li><strong>Multirótulo</strong>: indica se os rotuladores poderão escolher mais de um rótulado para um documento. Caso o PR seja multirótulo, então os rótulos serão apresentados em "checkboxes". Caso contrário, será utilizado "radio button".</li>
									<li><strong>Tipo de rótulos</strong>: se for fixado, então apenas o administrador poderá definir as opções de rótulos do PR. Caso contrário, rotuladores poderão sugerir rótulos durante o PR. Neste último caso, deverá ser definido a taxa mínima de concordância entre os rotuladores (ou seja, número mínimo de rotuladores sugerindo um certo rótulo) para que uma sugestão vire uma opção de rótulo do PR.</li>
									<li><strong>Taxa mínima de concordância de rótulos (documentos)</strong>: indica quantas vezes um rótulo deve ser atribuído a um documento por diferentes rotuladores para que o documento seja finalizado (ou seja, deixe de ser apresentado). </li>
									<li><strong>Opções de rótulos</strong>: define quais rótulos poderão ser escolhidos pelos rotuladores para os documentos supracitados.</li>
									<li><strong>Algoritmo para sugestão de rótulos</strong>: durante o processo de rotulação, o sistema utilizará o algoritmo/método selecionado para sugerir um rótulo para cada documento (que o rotulador poderá aceitar ou não). É possível escolher entre os seguintes algoritmos:
										<ul>
											<li>Sugerir o mais votado: sugere o rótulo que foi mais escolhido para o presente documento.</li>
											<li>Sugestão aleatória: sugere um rótulo sorteado aleatoriamente entre as opções de rótulos disponíveis</li>
											<li>Classificação transdutiva: sugere o rótulo de acordo com o algoritmo de classificação transdutiva, que determina um "rank" para cada rótulo de acordo com os termos de cada documento (mais informações no manual do usuário). Caso esta opção seja escolhida, deverá ser selecionado a taxa de reset do algoritmo, que determina a cada quantos documentos este algoritmo é reinicializado, e o idioma dos documentos para pré-processamento dos textos (e.g., stemming).</li>
											<li>Modo de teste: não apresenta nenhuma sugestão de rótulo ao rotulador. Esta deverá ser escolhida apenas caso for desejado comparar os três algoritmos anteriores. Assim, neste modo é calculado qual que seria o rótulo sugerido em cada um dos algoritmos anteriores para verificar/comparar posteriormente a taxa de acerto de cada um deles (ou seja, se o rótulo que o rotulador escolheu foi o que o algoritmo iria sugerir).  </li>
										</ul>
									</li>
								</ul>									
								</div>
							</div>
						</div>
					</div> 
					<div class="panel-group" id="accordion">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" data-parent="#accordion" href="#collapse2">
										O que são os parâmetros do formulário "Download de Documentos Rotulados"?     
										
									</a>
								</h4>
							</div>
							<div id="collapse2" class="panel-collapse collapse ">
								<div class="panel-body">
								<ul>
									<li><strong>Formato</strong>: define a estrutura de diretório em que os documentos rotulados irão se organizar.
									No primeiro formato, cada documento é inserido dentro de uma pasta referente ao seu rótulo.
									Já no segundo formato, todos os documentos rotulados são inserido em um único diretório e os rótulos destes documentos são inseridos no nome do arquivo. Segue um exemplo para ilustrar:<br>
									&nbsp;&nbsp;&nbsp;<strong>Primeiro Formato</strong><br>
									&nbsp;&nbsp;&nbsp;----diretório<br>
									&nbsp;&nbsp;&nbsp;--------rótulo1<br>
									&nbsp;&nbsp;&nbsp;------------documento1.txt<br>
									&nbsp;&nbsp;&nbsp;------------documento2.txt<br>
									&nbsp;&nbsp;&nbsp;--------rótulo2<br>
									&nbsp;&nbsp;&nbsp;------------documento3.txt<br><br>
									&nbsp;&nbsp;&nbsp;<strong>Segundo Formato</strong><br>
									&nbsp;&nbsp;&nbsp;----diretório<br>
									&nbsp;&nbsp;&nbsp;--------rótulo1_documento1.txt<br>
									&nbsp;&nbsp;&nbsp;--------rótulo1_documento2.txt<br>
									&nbsp;&nbsp;&nbsp;--------rótulo2_documento3.txt<br>
									</li><br>
									<li><strong>Taxa de concordância</strong>: define o número mínimo de usuários em concordância para que um rótulo seja atrelado à um documento. 
									Por exemplo, imaginemos que apenas "Fulano" e "Siclano" escolheram o rótulo "R" para o documento "D". 
									Se escolhermos 3 para taxa de concordância, então D não ficará rotulado como R. Porém, se a taxa de concordância for 1 ou 2, então R será atrelado a D no download. </li>
								</ul>
								
								</div>

							</div>
						</div>
					</div>				
				</div>
			</div>
		<?php else : ?>
            <p>
                <span class="error">Você não está autorizado a visualizar esta página.</span> 
				<a href="index.php">Voltar</a>
            </p>
        <?php endif; ?>

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
