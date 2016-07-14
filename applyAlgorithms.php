<?php include_once 'includes/applyAlgorithms.inc.php';?>

<!DOCTYPE html>
<html lang="pt">
    <head> 
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="author" content="Rafael Paravia, Darlan Santana Farias">
		<title>Executing...</title>
		
         <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="js/jquery.min.js"></script>
		<!-- RangyInputs (useful for dealing with selection on TextAreas) -->
		<script src="js/rangyinputs-jquery-src.js"></script>
		<!-- Bootstrap -->
		<link rel="stylesheet" href="styles/bootstrap.min.css">
		<link rel="stylesheet" href="styles/bootstrap-theme.min.css">
		<script src="js/bootstrap.min.js"></script>
		
		<link rel="stylesheet" href="styles/main.css" />
		<link rel="stylesheet" href="styles/sticky-footer-navbar.css" />
		<script>
			function validateForm(){
				var input = $("<input>")
					.attr("type", "hidden")
					.attr("name", "btnSubmit").val('next');
					$('#labelingForm').append($(input)).submit();
			};
		</script>
    </head>
    <body style='background-color:white;'>   
		<?php showAlert(); ?>
		<?php if (login_check($mysqli) == true ) : ?>
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
			
			<h2 align="center">
				<?php echo "Process : ". $_SESSION['cur_lpName']?>
				<?php //echo " Process ID: ". $_SESSION['cur_lpID']?>
			</h2>
			<?php 
				//echo "DocID : ". $_SESSION['curDocID'];
				showProgressBar();
			?>				
			<form action="<?php echo esc_url($_SERVER['PHP_SELF']); ?>" method ="post" id="labelingForm" name="labelingForm" >
			</form>
		<?php else : ?>
            <p>
                <span class="error">You are not authorized to access this page.</span> 
				You should try <a href="index.php">logging in</a> first.
            </p>
        <?php endif; ?>
		<footer class="footer">
			<div class="container">
				<p class="text-muted">
					This is a project from <a xmlns:cc="http://creativecommons.org/ns#" href="http://labic.icmc.usp.br/" property="cc:attributionName" rel="cc:attributionURL">LABIC | ICMC-USP</a> 
					and it is licensed by <a rel="license" href="http://creativecommons.org/licenses/by/4.0/">Creative Commons Atribuição 4.0 Internacional</a>.				
					<a rel="license" href="http://creativecommons.org/licenses/by/4.0/"><img alt="Licença Creative Commons" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/80x15.png" /></a>
				</p>
			</div>
		</footer>	
		<script>validateForm();</script>
    </body>
</html>
