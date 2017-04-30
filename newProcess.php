<?php include_once 'includes/newProcess.inc.php'; ?>

<!DOCTYPE html>
<html lang="pt">
    <head> 
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="author" content="Darlan Santana Farias">
		<title>Create Process</title>
		
         <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="js/jquery.min.js"></script>

		<!-- Bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
		
		<link rel="stylesheet" href="styles/main.css" />
		<link rel="stylesheet" href="styles/sticky-footer-navbar.css" />
    
		<script type="text/Javascript">
			var NB = false;
			$(document).ready(function(){				
				//If the suggestion algorithm is frequence or PMI based
				//then show extra options
				/*$("#lpAspectSuggestionAlgorithm").change(function() {
					var polarityAlgorithm = $("#lpSuggestionAlgorithm option:selected").val();*/
					var algorithm = $("#lpAspectSuggestionAlgorithm option:selected").val();
					if (algorithm == 'frequenceBased'){
						showFrequenceInput();
					}else{
						hideFrequenceInput();
					}
				//});
				
			});
			
			//showing/hiding input for Naive Bayes
			function handleClick(cb){
				if (cb.checked && cb.value == 'naiveBayes'){
					showNBInput();
				}else if(!cb.checked && cb.value == 'naiveBayes'){
					hideNBInput();
				}
			}
			
			function showPolaritySuggestionRow(){
				//Showing language row
				$("#polaritySuggestionRow").css("display","");
			}
			
			function showFrequenceInput(){
				//Showing min frequence row
				$("#minFrequenceRow").css("display","");
			}
			
			function hideFrequenceInput(){
				//Hiding min frequence row
				$("#minFrequenceRow").css("display","none");
			}
			
			function showNBInput(){
				//Showing min frequence row
				$("#trainingSetRow").css("display","");
				NB = true;
			}
			
			function hideNBInput(){
				//Hiding min frequence row
				$("#trainingSetRow").css("display","none");
				NB = false;
			}
			
			function validadeForm(){
				//Checking if settings for frequence-based algorithm are valid
				if($("#lpAspectSuggestionAlgorithm option:selected").val() == "frequenceBased"){
					if($("#min_frequency").val() < 1){
						alert("Minimum Frequence must be greater than 0");
						return false;
					}
				}
				
				//checking naiveBayes input
				if(NB){
					if(!$("#trainingSet").val()){
						alert("You must submit a training set when using Naive Bayes");
						return false;
					}
				}
				
				//Checking if translator options are correct
				if($("#lpIdiom option:selected").val() != "pt" && $("#lpIdiom option:selected").val() != "en" 
							&& $("#tNegative").get(0).checked){
					alert("You must use the translator when the documents are not in Portuguese or English");
					return false;
				}

				//Checking if the documents(to be labelled) were sent
				if(($("#lpDocs").val()=="")){
					alert("You must upload at least one document")
					return false;
				}

				//If everything is all right, then we submit the form 
				$("#newLPForm").submit(); 
				alert("Opinion-meter is going to pre-process the input data. This might take a while.");
				return true;
			};
		</script>
    </head>
    <body>
		<?php showAlert(); ?>
		<?php if ((login_check($mysqli) == true)) : ?>
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
			
			<h1 align="center">Create new Process</h1>
			<div class="container" style= "border:1px solid #ddd;padding-top:30px;">
				<form 	action = "<?php echo esc_url($_SERVER['PHP_SELF']); ?>" 
						id="newLPForm" 
						name="newLPForm" 
						method="post" 
						enctype="multipart/form-data"
						class = "form-horizontal "> 
						
						<div class="form-group">
							<label for="lpName" class="col-sm-6 control-label">Process Name</label>
							<div class="col-sm-3">
								<input required type="text" name="lpName" id="lpName" class="form-control input-sm">
							</div>
						</div>	

						<div class="form-group">
							<label for="lpDocs" class="col-sm-6 control-label">
								<abbr title=".txt Only">
									Upload documents to be analyzed
								</abbr>						
							</label>
							<div class="col-sm-2">
								<input required multiple type="file" id="lpDocs" name="lpDocs[]" accept=".txt" >
							</div>
						</div>						
						
						<div class="form-group" id="aspecSuggestionRow">
							<label for="lpAspectSuggestionAlgorithm" class="col-sm-6 control-label">Aspect Identification Algorithm</label>
							<div class="col-sm-3">
								<select name="lpAspectSuggestionAlgorithm" id="lpAspectSuggestionAlgorithm" class="form-control input-sm">
								  <option value="frequenceBased">Frequence-Based</option>
								</select>
							</div>
						</div>
						
						<div class="form-group" id="polaritySuggestionRow">
							<label for="lpSuggestionAlgorithm" class="col-sm-6 control-label">Polarity Classification Algorithm</label>
							<div class="col-sm-3">
								<!--<input type="checkbox" onclick='handleClick(this);' name="polarityClassifier[]" value="PMIBased">PMI-Based<br>
								Microsoft's search API has become too restrictive 
								for free tier users. We're commenting this portion of the code
								util we find a suitable alternative, but nothing keeps you
								from uncommenting it and using it as you like-->
								<input type="checkbox" onclick='handleClick(this);' name="polarityClassifier[]" value="lexiconBased">Lexicon-Based<br>
								<input type="checkbox" onclick='handleClick(this);' name="polarityClassifier[]" value="naiveBayes">Naive Bayes<br>
							<!--	<select name="lpSuggestionAlgorithm" id="lpSuggestionAlgorithm" class="form-control input-sm">
								  <option value="PMIBased">PMI-Based</option>
								  <option value="lexiconBased">Lexicon-Based</option>
								  <option value="naiveBayes">ML-Based: Naive Bayes</option>
								</select>-->
							</div>
						</div>
						
						<div class="form-group" id="languageRow">
							<label for="lpIdiom" class="col-sm-6 control-label">Language of the documents</label>
							<div class="col-sm-2">
								<select name="language" id="lpIdiom" class="form-control input-sm">
								  <option id='optionPT' selected value="pt">Portuguese</option>
								  <option id='optionEN'value="en">English</option>
								  <option id='optionES'value="es">Spanish</option>
								  <option id='optionDE'value="de">German</option>
								  <option id='optionFR'value="fr">French</option>
								  <option id='optionIT'value="it">Italian</option>
								  <option id='optionBS'value="bs-Latn">Bosnian (Latin)</option>
								  <option id='optionCA'value="ca">Catalan</option>
								  <option id='optionHR'value="hr">Croatian</option>
								  <option id='optionCS'value="cs">Czech</option>
								  <option id='optionCS'value="da">Danish</option>
								  <option id='optionNL'value="nl">Dutch</option>
								  <option id='optionET'value="et">Estonian</option>
								  <option id='optionFI'value="fi">Finnish</option>
								  <option id='optionHT'value="ht">Haitian Creole</option>
								  <option id='optionHU'value="hu">Hungarian</option>
								  <option id='optionID'value="id">Indonesian</option>
								  <option id='optionLV'value="lv">Latvian</option>
								  <option id='optionLT'value="lt">Lithuanian</option>
								  <option id='optionMS'value="ms">Malay</option>
								  <option id='optionMT'value="mt">Maltese</option>
								  <option id='optionNO'value="no">Norwegian</option>
								  <option id='optionPL'value="pl">Polish</option>
								  <option id='optionRO'value="ro">Romanian</option>
								  <option id='optionSR'value="sr-Latn">Serbian (Latin)</option>
								  <option id='optionSK'value="sk">Slovak</option>
								  <option id='optionSV'value="sv">Swedish</option>
								  <option id='optionTR'value="tr">Turkish</option>
								  <option id='optionVI'value="vi">Vietnamese</option>
								  <option id='optionCY'value="cy">Welsh</option>
								</select>
							</div>
						</div>  
						
						<div class="form-group" id="translatorUseRow">
							<label for="translator" class="col-sm-6 control-label">
								<abbr title="Use automatic translator to execute the method in English">
									Use Automatic Translator
								</abbr>	
							</label>
							<div class="col-sm-2">
								<label class="radio-inline">
									<input type="radio" checked="checked" name="translator" id="tNegative" value="false"> No
								</label>
								<label class="radio-inline">
									<input type="radio" name="translator" id="tPositive" value="true"> Yes
								</label>
							</div>
						</div>
						
						<div class="form-group" id="minFrequenceRow" style="display:none;">
							<label for="min_frequency" class="col-sm-6 control-label">
								<abbr title="Minimum number of occurences of a word to be considered by the algorithm">
									Min Frequence
								</abbr>						
							</label>
							<div class="col-sm-2">
								<input type="number" id="min_frequency" name="min_frequency" 
									min="1" value='5' class="form-control input-sm">
							</div>
						</div>
						
						<div class="form-group" id="trainingSetRow" style="display:none;">
							<label for="trainingSet" class="col-sm-6 control-label">
								<abbr title=".arff Only">
									Upload training set (<a href="english_sentences.arff" target="_blank">example</a>)
								</abbr>						
							</label>
							<div class="col-sm-2">
								<input type="file" id="trainingSet" name="trainingSet" accept=".arff">
							</div>
						</div>
				</form>
			</div>
			
			<div align="center" >
					<button type="button" 
					class="btn btn-default" style="margin:20px;"
					id="submitButton" onClick="validadeForm()" >Create</button>
			</div>
			
		<?php else : ?>
            <p>
                <span class="error">You are not authorized to access this page.</span> 
				<a href="index.php">Return</a>
            </p>
        <?php endif; ?>
		<footer class="footer">
			<div class="container">
				<p class="text-muted">
					This work a is from <a xmlns:cc="http://creativecommons.org/ns#" href="http://labic.icmc.usp.br/" property="cc:attributionName" rel="cc:attributionURL">LABIC | ICMC-USP</a> 
					and it is licensed by <a rel="license" href="http://creativecommons.org/licenses/by/4.0/">Creative Commons Atribuição 4.0 Internacional</a>.				
					<a rel="license" href="http://creativecommons.org/licenses/by/4.0/"><img alt="Licença Creative Commons" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/80x15.png" /></a>
				</p>
			</div>
		</footer>		
    </body>
</html>
