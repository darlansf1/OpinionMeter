<?php include_once 'includes/processInfo.inc.php'; ?>
<!DOCTYPE html>
<html lang="pt">
    <head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Results</title>
		
		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		
		<!-- Charts.js visualization library -->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.6/Chart.bundle.min.js"></script>
		
		<!-- Bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
		
		<link rel="stylesheet" href="styles/main.css" />
		<link rel="stylesheet" href="styles/sticky-footer-navbar.css" />	
		<script>
			//aspects is a vector whose elements are vectors, one for each sentiment classification algorithm
				//each of those inner vector has the data about each aspect, in order:
				// aspect, total count, negative count, positive count, neutral count
			var aspects = <?php echo getAspects($mysqli, $lpID, true)?>;
			
			function drawHorizontalBarChart(chartNumber){				
				var alllabels = [];
				var positives = [];
				var negatives = [];
				var neutrals = [];
				
				
				for(var j = 0; j < aspects[chartNumber].length && j < 10*5; j+=5){
					alllabels.push(aspects[chartNumber][j]);
					positives.push(aspects[chartNumber][j+2]);
					negatives.push(aspects[chartNumber][j+3]);
					neutrals.push(aspects[chartNumber][j+4]);
				}
				
				var alldata;
				
				alldata = ({
							labels: alllabels,
							datasets: [
								{
									label: "Positive",
									backgroundColor: "rgba(0,255,0,0.7)",
									borderWidth: 0,
									hoverBackgroundColor: "rgba(0,255,0,1)",
									data: positives
								},
								{
									label: "Neutral",
									backgroundColor: "rgba(255,255,0,0.7)",
									borderWidth: 0,
									hoverBackgroundColor: "rgba(255,255,0,1)",
									data: neutrals
								},
								{
									label: "Negative",
									backgroundColor: "rgba(255,0,0,0.7)",
									borderWidth: 0,
									hoverBackgroundColor: "rgba(255,0,0,1)",
									data: negatives
								}
							]
					});
					
					var option = {
							scales: {
									xAxes: [{
											stacked: true
									}],
									yAxes: [{
											stacked: true
									}]
								}
							};
							
					var ctx = $("#myChart"+chartNumber);
					var chart = new Chart(ctx, {
							type: "horizontalBar",
							data: alldata,
							options: option
						}
					);
			}
			
			function drawPieChart(chartNumber){
				labels = [
					"Positive",
					"Neutral",
					"Negative"
				];
				
				var positive = 0, negative = 0, neutral = 0;
				for(var i = 0; i < aspects.length; i++){
					positive += aspects[i][chartNumber*5+2];
					negative += aspects[i][chartNumber*5+3];
					neutral += aspects[i][chartNumber*5+4];
				}
				
				positive /= aspects.length;
				negative /= aspects.length;
				neutral /= aspects.length;
				
				/*total = positive+negative+neutral;
				
				positive = positive/total*100;
				negative = negative/total*100;
				neutral = neutral/total*100;
				
				positive = Math.round(positive * 100)/100; 
				negative = Math.round(negative * 100)/100;
				neutral = Math.round(neutral * 100)/100;*/
				
				var data = {
					labels: labels,
					datasets: [
						{
							data: [positive, neutral, negative],
							backgroundColor: [
								"rgba(0,255,0,0.7)",
								"rgba(255,255,0,0.7)",
								"rgba(255,0,0,0.7)",
							],
							hoverBackgroundColor: [
								"rgba(0,255,0,1)",
								"rgba(255,255,0,1)",
								"rgba(255,0,0,1)",
							]
						}]
				};
				
				var ctx = $("#myAspectChart"+chartNumber);
				var chart = new Chart(ctx,{
					type:"doughnut",
					data: data
				});
			}
		</script>
    </head>
    <body>
		<?php showAlert(); ?>
		<?php if (login_check($mysqli) == true) : ?>	
			<?php showAlert(); ?>
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
								<li><a href="includes/logout.php">Log Out</a></li>
							</ul>
						</div><!--/.nav-collapse -->
					</div>
				</nav>
			</header>
			<div class="container">
				<?php getLPInfo($mysqli,$lpID); ?>
			</div>
			<hr>
			<div class="container">
				<h2>Results by algorithm (top 10 frequence)</h2>
				<hr>
				<?php
					for($i = 0; $i < count(getAlgorithms($mysqli, $lpID)); $i++){
						echo "
							<div style='float:left;border:1px solid #ddd; text-align: center'>
								Classification Algorithm ".($i+1)."
								<canvas id=\"myChart$i\" width=\"400\" height=\"400\"></canvas>
								<script>drawHorizontalBarChart($i);</script>
							</div>";
					}
				?>
			</div>
			<hr>
			<div class="container">
				<h2>Average for each aspect (across all classifiers)</h2>
				Ordered by frequence
				<hr>
				<?php
					$aspects = getAspects($mysqli, $lpID, false);
					for($i = 0; $i < count($aspects); $i++){
						$aspect = $aspects[$i];
						echo "
							<div style='float:left;border:1px solid #ddd; text-align: center'>
								Aspect: $aspect
								<canvas id=\"myAspectChart$i\" width=\"200\" height=\"200\"></canvas>
								<script>drawPieChart($i);</script>
							</div>";
					}
				?>
			</div>
		<?php else : ?>
            <p>
                <span class="error">You are not authorized to access this page.</span> 
				Try <a href="index.php">logging in</a> first.
            </p>
        <?php endif; ?>
		<footer class="footer">
			<div class="container">
				<p class="text-muted">
					This is a project from <a xmlns:cc="http://creativecommons.org/ns#" href="http://labic.icmc.usp.br/" property="cc:attributionName" rel="cc:attributionURL">LABIC | ICMC-USP</a> 
					and it is licensed with the <a rel="license" href="http://creativecommons.org/licenses/by/4.0/">Creative Commons Atribuição 4.0 Internacional</a>.				
					<a rel="license" href="http://creativecommons.org/licenses/by/4.0/"><img alt="Licença Creative Commons" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/80x15.png" /></a>
				</p>
			</div>
		</footer>
	</body>
</html>
