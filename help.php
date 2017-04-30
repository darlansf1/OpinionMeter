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
		<meta name="author" content="Darlan Santana Farias">
		<title>Help</title>
		
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
		<?php else : ?>
			<header>
				<nav class="navbar navbar-default">
					<div class="container">
						<div class="navbar-header navbar-left">
							<a class="navbar-brand" href="index.php">Opinion-meter - Multi-language Aspect-Based Sentiment Analysis</a>
						</div>
						<div id="navbar" class="collapse navbar-collapse navbar-right">
							<ul class="nav navbar-nav">
							<li><a href="help.php">Help</a></li>
							</ul>
						</div><!--/.nav-collapse -->
					</div>
				</nav>
			</header>
        <?php endif; ?>
		
		<div class="jumbotron" style="padding-top: 0px;">
			<div class="container">
				<div class="page-header ">
					<h1 class="text-center">About Opinion-meter / FAQ</h1>
				</div>
      
				<div class="panel-group" id="accordion">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapse1">
									What is Opinion-meter?        
								</a>
							</h4>
						</div>
						<div id="collapse1" class="panel-collapse collapse ">
							<div class="panel-body">
								Opinion-meter is a system designed to enable any person to identify the main properties under evaluation in a collection of text files as well as the polarities of the opinions associated to those properties, such as negative and positive evaluations of the properties of whatever entity the opinions are direct to. The system uses some of the most classical approaches present in the literature of Sentiment Analysis to identify the aspects (the properties) and classify the polarities of the sentiments (or opinions) associated and the user is free to choose which of the methods to use to analyze their data, being also able to choose multiple methods in order to compare their results. In addition, the system uses machine translation to enable the analysis of texts in up to <abbr title="Take a look at 'What are the supported languages?'">30 different languages</abbr>.
							</div>
						</div>
					</div>
				</div> 
		
				<div class="panel-group" id="accordion">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapse2">
									What are the supported languages?        
								</a>
							</h4>
						</div>
						<div id="collapse2" class="panel-collapse collapse ">
							<div class="panel-body">
								The list of supported languages was defined considering those <a href="https://msdn.microsoft.com/en-us/library/hh456380.aspx" target="_blank">supported by the Microsoft Translate API</a> except those that do not use the <a href="https://en.wikipedia.org/wiki/List_of_languages_by_writing_system" target="_blank">Latin writing system</a>.<br>
								The supported languages are: Portuguese, English, Spanish, German, French, Italian, Bosnian, Catalan, Croatian, Czech, Danish, Dutch, Estonian, Finnish, Haitian Creole, Hungarian, Indonesian, Latvian, Lithuanian, Malay, Maltese, Norwegian, Polish, Romenian, Serbian, Slovak, Swedish, Turkish, Vietnamese, Welsh.
							</div>
						</div>
					</div>
				</div>
		
				<div class="panel-group" id="accordion">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapse3">
									How do I use it?        
								</a>
							</h4>
						</div>
						<div id="collapse3" class="panel-collapse collapse ">
							<div class="panel-body">
								In order to create an analysis process, the user needs to sign up and login. Once logged in, the user should click on "Start a new process" and then the following form should load.
								<br><img src="imagens_rotulacao/1.png" width="800" border="1">
								<br>Next, a brief explanation of each field.
								<br><b>Process Name: </b> The name you want to give your process. It has to be a unique name.
								<br><b>Upload documents to be analyzed: </b>Upload the text documents that are going to be analyzed.
								<br><b>Aspect Identification Algorithm: </b>Choose the method you want to use to identify the entity's properties being evaluated in the documents.
								<br><b>Polarity Classification Algorithm: </b>Choose as many classification algorithms as you want to classify the polarity (as negative, positive or neutral) of the opinions on the aspects identified.
								<br><b>Language of the documents: </b>Specify the language in which the documents are writen. Although Opinion-meter enables you to analyze documents in up to 30 languages, each process can work with only one language for all its documents.
								<br><b>Use Automatic Translator: </b>Specify whether or not the algorithms should use machine translation. The only two languages you can not use machine translation in the analysis process are Portuguese and English.
								<br><b>Min Frequence: </b>This field is an input to the Frequence-Based Aspect Identification Algorithm. It is the threshold of frequence that nouns have to reach throughout the documents to be classified as aspects.
								<br><b>Upload training set (only when using Naive Bayes): </b>This field is an input to the Naive Bayes Sentiment Classification Algorithm. It has to follow an <abbr title="Take a look at 'What is the format of the training set file?'">specific format</abbr> and is used by the algorithm to "learn" how to classify sentences as positive, negative or neutral.
								<br><br>After filling in all the fields, the user should click on "Create", a message will be displayed warning the user that the preprocessing step may take some time and, afterwards, the analysis will be executed and a progress bar will show the user how the process is going.
								<br><img src="imagens_rotulacao/2.png" width="800" border="1">
								<br>Once the process has finished executing, the user will be directed back to the home page, where it is possible to click on any of their processes to visualize the results.
								<br><img src="imagens_rotulacao/3.png" width="800" border="1">
								<br>Finally, in the Results page the user can see some information about the selected process and several graphs are shown to present the results of the analysis. The user may also download <abbr title="Take a look at 'What are the downloaded files like?'">XML files</abbr> with the aspects identified in each document and the classification of the respective sentiments.
								<br><img src="imagens_rotulacao/4.png" width="400" border="1">
								<br><img src="imagens_rotulacao/5.png" width="800" border="1">
								<br><img src="imagens_rotulacao/6.png" width="800" border="1">
							</div>
						</div>
					</div>
				</div> 
				<div class="panel-group" id="accordion">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapse4">
									What are the algorithms I can use in the analysis?        
								</a>
							</h4>
						</div>
						<div id="collapse4" class="panel-collapse collapse ">
							<div class="panel-body">
								The user can choose from a set of different algorithms to use in the analysis. For aspect identification, the algorithm available in the current version of the system is a frequence-based algorithm. For sentiment classification the user may choose between an PMI-Based, a Lexicon-based or a Machine Learning approach. Each of those algorithms are briefly explained below.
								<br><br>
								<b>Frequence-Based Aspect Identification</b>
								<br>    
									This algorithm is based on an approach proposed by Hu and Liu (2004). When using this algorithm, the system identifies the nouns in the texts under analysis. The frequence in the entire set of documents is calculated for each noun and those whose frequence reach the minimum frequence value, as set by the user in the moment of the creation of the analysis process, is considered as an aspect. This approach is based on the idea that frequent nouns tend to be the most relevant aspects of a collection.
								<br>
								<br>
								<b>Lexicon-Based Sentiment Classification</b>
								<br>    
									In this approach, the aspects identified in the previous step are searched on each document in the collection, once an aspect is found in a given document the sentence where this aspect is located is taken to evaluation. The algorithm then considers each word in the sentence, up to 10 words before or after the aspect whose sentiment polarity is being classified, those words are matched against a <abbr title="Take a look at 'What are the words in the lexicon used for sentiment classification?'">list of words</abbr> preclassified as negative or positive and the predominant polarity is assigned to the aspect. The formulae below sumarizes this process.
								<br> 
								<br><img src="imagens_rotulacao/lexiconformulae.png" width="600">
								<br>Where 'i' is the position of the aspect word in the sentence 's', the sentence 's' has 'n' words, 's(j)' is the j-th word in the sentence and
								<br><img src="imagens_rotulacao/lexiconposneg.png" width="400">
								<br>If 'p(i)' is greater than 0, the aspect's sentiment is considered to be positive, if the value of 'p(i)' smaller than 0, then the polarity is negative. The sentiment is classified as neutral if neither case happen.<br>
								
								<br>
								<b>PMI-Based Sentiment Classification</b>
								<br>    
									This algorithm is based on an approach proposed by Turney (2002). In this approach, for each sentence where an aspect is found in the Aspect Identification step, the algorithm looks for 3-word patterns, defined in terms of their grammatical classes, such as a noun followed by an adjetive followed by any words that is not an noun. Once a pattern is found, the phrase formed by the two first words is extracted for evaluation. The polarity of the given phrase is calculated according to the following formulae:
								<br><img src="imagens_rotulacao/SO.png" width="400">	
								<br>And the value of PMI(phrase, word) is estimated using seach engines results, as follows
								<br><img src="imagens_rotulacao/PMI.png" width="400">
								<br>If the resulting SO is greater than 0, the sentence and therefore the sentiment of any aspect in it is considered to be positive, if the SO comes to be less than 0, then the polarity is negative. Neutral is assigned if neither of those is true.
								<br>
								<br>
								<b>Sentiment Classification with Naive Bayes (Machine Learning)</b>
								<br>
									The Naive Bayes Classifier (Rish, 2001) is one of the most well-known and one of the most used Machine Learning techniques. The roots of this approach is on Bayes' Theorem (Koch, 1990). This algorithm uses probabilities estimated from a set of previously classified instances to calculate the most likely class (y) among all possible (y<sub>i</sub>) of an unclassified instance (x).<br>
								<br>
								<img src="imagens_rotulacao/NB.png" width="200"><br>
								<br>In order to use this method, the user needs to upload an <abbr title="Take a look at 'What is the format of the training set file?'">ARFF file</abbr> with classified sentences to be used as training set.
							</div>
						</div>
					</div>
				</div> 	
				<div class="panel-group" id="accordion">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapse5">
									What are the words in the lexicon used for sentiment classification?        
								</a>
							</h4>
						</div>
						<div id="collapse5" class="panel-collapse collapse ">
							<div class="panel-body">
								The system uses two different lexicons in the sentiment classification process, depending on the language of the text files and the options selected by the user.
								<br><br><b>English Lexicon</b> 
								<br>
									The english lexicon used is a mix of the <a href="http://mpqa.cs.pitt.edu/lexicons/subj_lexicon/" target="_blank">Subjectivity Lexicon</a> and the <a href="https://www.cs.uic.edu/~liub/FBS/sentiment-analysis.html#lexicon" target="_blank">Opinion Lexicon</a>. 
									After mixing them up to form a single lexicon the negative and positive words were separated and stemmized. The resulting lexicon has 1931 positive words and 3414 negative words.<br><br>
									The list of positive words can be found <a href="https://goo.gl/OiIoDJ" target="_blank">here</a>.<br>
									The list of negative words can be found <a href="https://goo.gl/dwyhRq" target="_blank">here</a>.<br>
								<br>
								<b>Portuguese Lexicon</b> 
								<br>
									The portuguese lexicon used was the <a href="http://ontolp.inf.pucrs.br/Recursos/downloads-OpLexicon.php" target="_blank">OpLexicon</a>. It was split into two files, one containing the negative and the other containing the positive words, every and each word of the lexicon was also stemmized. 
									The resulting lexicon has 5156 positive words and 5440 negative words.<br><br>
									The list of positive words can be found <a href="https://goo.gl/cHcP3k" target="_blank">here</a>.<br>
									The list of negative words can be found <a href="https://goo.gl/w57YKJ" target="_blank">here</a>.<br>
							</div>
						</div>
					</div>
				</div> 					
				<div class="panel-group" id="accordion">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapse6">
									What is the format of the training set file?        
								</a>
							</h4>
						</div>
						<div id="collapse6" class="panel-collapse collapse ">
							<div class="panel-body">
								The file the user needs to upload to be used as training set for the algorithms that need them, should be similar to this <a href="english_sentences.arff" target="_blank">example</a>.
								<br>Each tag @ATTRIBUTE should be followed by a different word you want to be considered by the algorithm, except for the last one, wich should have the class attribute and its possible values, exactly like the example provided.
								<br>After the tag @DATA, each line is a representation of a sentence, with exactly the same number of values as the number of attributes. Each numeric (positive integer) value indicates the number of occurences of the corresponding word in the sentence, except, again, for the last value, which should be the polarity classification of the sentence.
							</div>
						</div>
					</div>
				</div>
				<div class="panel-group" id="accordion">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapse8">
									What are the downloaded files like?        
								</a>
							</h4>
						</div>
						<div id="collapse8" class="panel-collapse collapse ">
							<div class="panel-body">
								The system allows the user to download the results of the analysis. The results come in a set of XML files, one for each text file uploaded for processing and for each method used. The files contain information about the aspects identified following the format presented in the example below.
								<p><img src="appendices/XML.png" width="800" border="1"></p>
								In this format, the tag 'aspectTerms' marks the beginning and the end of the list of aspects identified in the document. Each tag named 'aspectTerm' indicates an aspect, and enclosed within its delimiter there is a set of properties of the aspects that are, in the order they appear, the word identified as aspect, the polarity of the sentiment associated, and the positions where the word starts and ends in the document.
							</div>
						</div>
					</div>
				</div>
				<div class="panel-group" id="accordion">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapse7">
									More about Opinion-meter        
								</a>
							</h4>
						</div>
						<div id="collapse7" class="panel-collapse collapse ">
							<div class="panel-body">
								Opinion-meter was developed at the Laboratory of Computational Intelligence (<a href="labic.icmc.usp.br" target="_blank">LABIC</a>) at the Instituto de Ciências Matemáticas e de Comptação of the University of Sao Paulo, Brazil.
								<br><br>These are the people behind this project: Darlan Santana Farias, Ivone Penque Matsuno, Solange Oliveira Rezende.
								<br><br>And here are some references for the algorithms used for Aspect Identification and Sentiment Classification:
									<br>
									  <b>Hu, M. and Liu, B. (2004).</b> Mining opinion features in customer reviews. In AAAI, volume 4, pages 755–760.<br>
									
									  <b>Koch, K. R. (1990).</b> Bayes’ theorem. In Bayesian Inference with Geodetic Applications, pages 4–8. Springer.<br>
									
									  <b>Rish, I. (2001).</b> An empirical study of the naive bayes classifier. In IJCAI 2001 workshop on empirical methods in artificial intelligence, volume 3, pages 41–46. IBM New York.<br>
									
									  <b>Turney, P. D. (2002).</b> Thumbs up or thumbs down?: Semantic orientation applied to unsupervised classification of reviews. In Proceedings of the 40th Annual Meeting on Association for Computational Linguistics, ACL ’02, pages 417–424, Stroudsburg, PA, USA. Association for Computational Linguistics.
							</div>
						</div>
					</div>
				</div>				
			</div>
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
