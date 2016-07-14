<?php include_once 'includes/changePassword.inc.php';?>
<!DOCTYPE html>
<html lang="pt">
    <head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Alterar Senha</title>
		
         <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<!-- Bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
		
		<link rel="stylesheet" href="styles/main.css" />
		<link rel="stylesheet" href="styles/sticky-footer-navbar.css" />
		
        <script type="text/JavaScript" src="js/sha512.js"></script> 
        <script type="text/JavaScript" src="js/forms.js"></script> 
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
			<div class="container" >
				<form class="form-horizontal" action="<?php echo esc_url($_SERVER['PHP_SELF']); ?>" 
					method="post" 
					name="change_password_form">
					
					<fieldset>
					
						<div id="legend">
							<legend >Alterar Senha</legend>
						</div>
						
						<div class="form-group">
							<label for="username" class="col-sm-2 control-label">Usuário</label>
							<div class="col-sm-5">
								<input type="text" id="username" name="username" class="form-control" readonly value = "<?php echo $user_name; ?>"/> 
							</div>
						</div>
						
						<div class="form-group">
							<label for="email" class="col-sm-2 control-label">Email</label>
							<div class="col-sm-5">
								<input type="email" id="email" name="email" class="form-control" readonly  value = "<?php echo $user_email; ?>"/>
							</div>
						</div>
						
						<div class="form-group">
							<label for="password" class="col-sm-2 control-label">Senha</label>
							<div class="col-sm-5">
								<input type="password"name="password" id="password" class="form-control" placeholder="Senha" required/>
							</div>
						</div>
					
						<div class="form-group">
							<label for="confirmpwd" class="col-sm-2 control-label">Confirmar Senha</label>
							<div class="col-sm-5">
								<input type="password"name="confirmpwd" id="confirmpwd" class="form-control" placeholder="Confirmar senha" required/>
							</div>
						</div>
							
						<div class="form-group">	
							<div class="col-sm-offset-2 col-sm-5">
								<button type="button" class="btn btn-primary btn-lg btn-block"
									onclick="return regformhash(this.form,
														   this.form.username,
														   this.form.email,
														   this.form.password,
														   this.form.confirmpwd);" > Confirmar
								</button>
							</div>
						</div>
							
					</fieldset>
				</form>
			</div>
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