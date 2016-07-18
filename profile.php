<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
include_once 'includes/profile.inc.php';
 
sec_session_start();
unsetLabelingProcessData();
?>

<!DOCTYPE html>
<html lang="pt">
    <head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
        <title>User Profile</title>
		
		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		
		<!-- Bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
		
		<link rel="stylesheet" href="styles/main.css" />
		<link rel="stylesheet" href="styles/sticky-footer-navbar.css" />	
    </head>
    <body>
		<header></header>
		<nav></nav>
		<?php showAlert(); ?>
		<?php if (login_check($mysqli) == true) : ?>
			<header>
				<nav class="navbar navbar-default">
					<div class="container">
						<div class="navbar-header navbar-left">
							<a class="navbar-brand" href="index.php">Opinion-meter</a>
						</div>
						<p class="navbar-text">
							--  Hello, <?php echo htmlentities($_SESSION['username']); ?>!
						</p>
						<div id="navbar" class="collapse navbar-collapse navbar-right">
							<ul class="nav navbar-nav">
								<li><a href="profile.php">Profile</a></li>
								<li><a href="help.php">Help</a></li>
								<li><a href="includes/logout.php">Log out</a></li>
							</ul>
						</div><!--/.nav-collapse -->
					</div>
				</nav>
			</header>
			<div class="container" >
				<div class="row" align="center" >
					<div class="col-md-6">
						<div class="panel panel-primary">
							<div class="panel-heading text-center " >
								<h1 class="panel-title">Profile</h1>
							</div>
						
							<table class="table table-hover table-bordered  table-condensed" >
								<tr>
									<td>User</td>
									<td><?php echo $_SESSION['username']; ?></td>
								</tr>
								<tr>
									<td>Email</td>
									<td><?php echo getUserEmail($mysqli); ?></td>
								</tr>
							</table>
						
						</div>
					</div>
				</div>
				<div class= "row">
					<div class="col-md-6">
						<form action="changePassword.php" ">
							<button class="btn btn-default btn-block " type="submit">Change Password</button>
						</form>
					</div>
				</div>
				
			</div>
		
		<?php else : ?>
            <p>
                <span class="error">Access Denied.</span> 
				You need to <a href="index.php">log in</a>.
            </p>
        <?php endif; ?>
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
