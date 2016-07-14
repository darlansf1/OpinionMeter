<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
include_once 'includes/lpbhn.php';	
 
sec_session_start();
unsetLabelingProcessData();

if (login_check($mysqli) == true) {
    //Redirects to home.php
	header('Location: ./home.php');
} 
?>
<!DOCTYPE html>
<html lang="pt">
    <head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="author" content="Darlan Santana Farias, Rafael Paravia">
		<title>Log In</title>
		
         <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<!-- Bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
		
		<link rel="stylesheet" href="styles/main.css" />
		<link rel="stylesheet" href="styles/signin.css" />
		<link rel="stylesheet" href="styles/sticky-footer-navbar.css" />
		
        <script type="text/JavaScript" src="js/sha512.js"></script> 
        <script type="text/JavaScript" src="js/forms.js"></script> 
		
    </head>
    <body>
        <?php
        if (isset($_GET['error'])) {
			phpAlert( "Login Error. Try again." );
        }
		
        if (!empty( $_GET["signup"] )) {
			phpAlert( "You have been successfully signed up." );
        }
        ?>
		<header>
		    <nav class="navbar navbar-default">
				<div class="container">
					<div class="navbar-header navbar-left">
						<img src="images/logo.png" alt="LABIC - Laboratório de Inteligência Computacional" height="100" width="187">
					</div>	
					<div class="navbar-header navbar-left">
						<br> <br> <br>
						<a class="navbar-brand" href="index.php">Opinion-meter: Multi-language Aspect-Based Sentiment Analysis</a>
					</div>
					<div id="navbar" class="collapse navbar-collapse navbar-right">
						<ul class="nav navbar-nav">
							<br> <br> <br>
							<li><a href="help.php">Help</a></li>
						</ul>
					</div><!--/.nav-collapse -->
				</div>
			</nav>
		</header>
		
		<div class="container">
			<form class="form-signin" action="includes/process_login.php" method="post" name="login_form">
				
				<h2 class="form-signin-heading text-center">Opinion-meter Login</h2>
				
				<label for="email" class="sr-only">Email</label>
				<input type="email" id="email" name="email" class="form-control" placeholder="Type in your email" required autofocus>
				
				<label for="password" class="sr-only">Password</label>
				<input type="password"name="password" id="password" class="form-control" placeholder="Type in you password" required/>
				
				<button type="button" onclick="formhash(this.form, this.form.password);" class="btn btn-lg btn-primary btn-block">Log in</button> 
			</form>
			<form action="register.php" class="form-signin">
				<button class="btn btn-lg btn-primary btn-block" type="submit">Sign up</button>
			</form>
		</div>
		<footer class="footer">
			<div class="container">
				<p class="text-muted">
					This work is from <a xmlns:cc="http://creativecommons.org/ns#" href="http://labic.icmc.usp.br/" property="cc:attributionName" rel="cc:attributionURL">LABIC | ICMC-USP</a> 
					and it is licensed by <a rel="license" href="http://creativecommons.org/licenses/by/4.0/">Creative Commons Atribuição 4.0 Internacional</a>.				
					<a rel="license" href="http://creativecommons.org/licenses/by/4.0/"><img alt="Licença Creative Commons" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/80x15.png" /></a>
				</p>
			</div>
		</footer>
    </body>
</html>