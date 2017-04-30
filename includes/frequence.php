<?php
include_once 'functions.php';
include_once 'lpbhn.php';
include_once 'HTTPTranslator.php';

function executeTagger($command, &$counts, $explodedText){
	@chdir("./includes");
	//echo("////".$command."////<br>");
	//$command = 'java -Djava.awt.headless=true -jar "./opennlp/POSTagger/dist/POSTagger.jar" ./opennlp/pos-models/pt-pos-maxent.bin A comida, e as sobremesas! deste restaurante sao?. deliciosas.';
	exec($command, $result);
	//$words = array();
	//$tags = array();
	//$counts = array();
	$retval = array("tags"=>array(), "exploded"=>$explodedText);
	
	if($result == 0){
		phpAlert("Failed to run POSTagger");
	}else{
		$retval["tags"] = $result;
		//phpAlert("count: ".count($result));
		//phpAlert($command);
		for($i = 0; $i < count($result); $i+=2){
			//phpAlert($result[$i].", ".$result[$i+1]);
			//array_push($words, $result[$i]);
			//array_push($tags, $result[$i+1]);
			
			/* POS Tags:
			*
			* ENGLISH
			*	 NN = noun, singular or mass
			*	 NNS = noun, plural
			*	 NNP = proper noun, singular
			*	 NNPS = proper noun, plural 
			*
			* PORTUGUESE
			* 	N = noun
			*	PROP = proper noun
			*/
			//phpAlert("word: ".$result[$i].", tag: ".$result[$i+1].", idioma original: ".$explodedText[floor($i/2)]."<br>");
			$result[$i+1] = strtoupper($result[$i+1]);
			if(($result[$i+1]) == 'NN' || ($result[$i+1]) == 'NNS' ||
					//($result[$i+1]) == 'NNP' || ($result[$i+1]) == 'NNPS' ||
						($result[$i+1]) == 'N' /*|| ($result[$i+1]) == 'PROP'*/){
					
				$word = strtoupper($explodedText[floor($i/2)]);
				$word = processWord($word);
				//phpAlert($result[$i+1]);
				//phpAlert("BEING INCLUDED: ".$word.", len: ".strlen(utf8_decode($word)));
				if(strtolower(gettype($word)) == "string" and strlen(utf8_decode($word)) >= 4){
					#phpAlert("noun: ".);
					if(!array_key_exists($word, $counts)){
						$counts[$word] = 1;
					}else{ 
						$counts[$word] += 1;
					}
				}
			}
		}
	}
	return $retval;
}

function calculateFrequency($originalLanguage, $model, $useTranslator, $lpID, $mysqli, $minFreq){
	//phpAlert($useTranslator);
	//echo($useTranslator);
	$command = 'java -Djava.awt.headless=true -jar "./opennlp/POSTagger/dist/POSTagger.jar"';
	$modelPtME = "./opennlp/pos-models/pt-pos-maxent.bin";
	$modelEnME = "./opennlp/pos-models/en-pos-maxent.bin";
	$modelPtP = "./opennlp/pos-models/pt-pos-perceptron.bin";
	$modelEnP = "./opennlp/pos-models/en-pos-perceptron.bin";
	//$model = $modelPtME;
	
	$usedModel = "./opennlp/pos-models/";
	if($originalLanguage == 'pt' && $useTranslator == 'false'){
		$usedModel = $usedModel."pt-pos-";
	}else if($originalLanguage == 'en' || $useTranslator == 'true'){
		$usedModel = $usedModel."en-pos-";
	}else{
		return;
	}
	
	if($model == 'perceptron'){
		$usedModel = $usedModel."perceptron.bin";
	}else{
		$usedModel = $usedModel."maxent.bin";
	}
	//$text = "Oi, eu sou o Goku!";

	$counts = array();
	
	$docs = array(); //Options of labels for this Labeling Process
    $query = "	SELECT document_text 
					FROM tbl_document 
					WHERE document_process = ?"; 
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	if($stmt->execute()){
		$result = $stmt->get_result();
		while($data = mysqli_fetch_row($result)){
			$data[0] =utf8_decode($data[0]);
			array_push($docs, $data[0]);
		}
	}else{
		setAlert("Error retrieving texts");
	}
	$stmt->close();
	$retval = array();
	foreach($docs as $text){
		$originalText = $text;
		$text = removeSpecialChars(utf8_encode(removeSpecialChars($originalText)));
		$explodedText = explode(" ", $text);
		
		if($useTranslator == 'true'){// going to use the translator
			$translator = new HTTPTranslator();
			$translations = $translator->translate($originalText, $originalLanguage);
			if(count($translations) == 0) array_push($translations, "");
			$text = $translations[0];
			for($j = 1; $j < count($translations); $j++){
				$text = $text." ".$translations[$j];
			}
		}
		//phpAlert($usedModel." ".$text);
		$result = executeTagger($command." ".$usedModel." ".$text, $counts, $explodedText);
		array_push($retval, $result);
	}
	saveFrequencies($counts, $minFreq, $lpID, $mysqli);
	return $retval;
}

function saveFrequencies($counts, $minFreq, $lpID, $mysqli){
	$words = array_keys($counts);
	$query = 	"INSERT INTO `tbl_word_frequence`(`wf_process`, `wf_frequence`, 
						`wf_word`) VALUES (?,?,?)";
	for($i = 0; $i < count($counts); $i++){
		if($counts[$words[$i]] >= $minFreq){
			$frequence = $counts[$words[$i]];
			$word = utf8_encode($words[$i]);
			$stmt = $mysqli->prepare($query);	
			$stmt->bind_param("iis",$lpID,$frequence,$word);
			if(!$stmt->execute()){	
				echo $mysqli->error;
				$mysqli->rollback();
				setAlert("Failed to insert data into the database");
			}
			$stmt->close();
		}
	}
}

function getAspectSuggestionsFrequence($mysqli, $lpID){
	$suggestions = array();
	$words = array();
	
	//Retrieving the frequent nouns in the current document
    $query = "SELECT wf_word 
					FROM tbl_word_frequence 
					WHERE wf_process = ?"; 
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	if($stmt->execute()){
		$result = $stmt->get_result();
		while($data = mysqli_fetch_row($result)){
			array_push($words,utf8_decode($data[0]));
		}
	}else{
		setAlert("Error retrieving words for frequence-based algorithm");
	}
	$stmt->close();

	//retrieving document text to identify the words that are present on it
	$docID = $_SESSION['curDocID'];
	
	$docText = strtoupper(removeSpecialChars(getCurrDocText($mysqli)));
	
	$docText = removeSpecialChars(utf8_encode($docText));
	
	if($docText == "")
		return $suggestions;
	
	$regex = "/^[.,\\b\\s\\n\\~\\!\\@\\#\\$\\%\\^\\&\\*\\(\\)\\_\\+\\=\\[\\]\\{\\}\\;\\:\\'\\\"\\\\\\/\\<\\>\\?]+$/";
	$swords = array();
	$spositions = array();
	foreach($words as $word){
		$offset = 0;
		$position = strpos($docText, $word, $offset);
		while($position !== false){
			if(($position > 0 && !preg_match($regex, substr($docText, $position-1, 1))) 
				|| ($position+strlen($word) < strlen($docText) && !preg_match($regex, substr($docText, $position+strlen($word), 1)))){
				$offset = $position+1;
				$position = strpos($docText, $word, $offset);
				///phpAlert("entrou");
				continue;
			}
			//phpAlert($word);
			//phpAlert($position);
			//array_push($suggestions, array($word, $position));
			array_push($swords, $word);
			array_push($spositions, $position);
			$offset = $position+1;
			$position = strpos($docText, $word, $offset);
		}
	}
	
	//return $suggestions;
	return array($swords, $spositions);
}
?>