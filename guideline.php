<?php include_once 'includes/guideline.inc.php'; ?>
<!DOCTYPE html>
<html lang="pt">
    <head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="author" content="Rafael Paravia">
		<title>Instruções</title>
		
         <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<!-- Bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
		
		<link rel="stylesheet" href="styles/main.css" />
		<link rel="stylesheet" href="styles/sticky-footer-navbar.css" />	
		
		<script type="text/Javascript">
			function validateForm(btnSubmit){
				var input = $("<input>")
				.attr("type", "hidden")
				.attr("name", "btnChangePage").val(btnSubmit);
				$('#guidelineForm').append($(input)).submit();
				return false; //To do not go to href
			};	
		</script>
    </head>
    <body>
		<?php showAlert(); ?>
		<?php if (login_check($mysqli) == true ) : ?>
			<form action="<?php echo esc_url($_SERVER['PHP_SELF']); ?>" 
				id='guidelineForm' 
				name='guidelineForm' 
				method="post" >
				
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
				
				<?php getInstructions($mysqli,$lpID); ?>
				<div align="center">
					<input type="button" onclick="validateForm('next')" value="Continuar" class="btn btn-default"/>
				</div>
				
			</form>
		<?php else : ?>
            <p>
                <span class="error">Você não está autorizado a visualizar esta página.</span> 
				Primeiro você deve realizar o <a href="index.php">login</a>.
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
