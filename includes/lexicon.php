<?php
include_once 'functions.php';
include_once 'lpbhn.php';
include_once 'HTTPTranslator.php';

function getLexiconAsArray($polarity, $language){
	if($language != 'pt' && $language != 'en')
		return array();
	
	$lexicon = array();
	$fileName = "includes/lexicon/".$polarity."-".$language."-stem.txt";
	//$fileName = "includes/lexicon/negative-en.txt";
	//echo $fileName;
	$handle = fopen($fileName, "r");
	if ($handle) {
		while (($line = fgets($handle)) !== false) {
			$line = trim(preg_replace('/\s\s+/', '', $line));
			array_push($lexicon, $line);
		}

		fclose($handle);
	} else {
		phpAlert("Error when accessing lexicon file.");
	}
	
	return $lexicon;
}

function getPolaritiesFromLexicon($mysqli, $aspect_suggestions, $translator, $language, $neighborhood){
	$aspects = $aspect_suggestions[0];
	$polarities = array();
	$sentences = array();
	$sentences_nospecial = array();
	
	//retrieving document text
	$docText = getCurrDocText($mysqli);
	if($docText == "")
		return $polarities;
	
	$positive_lexicon = array();
	$negative_lexicon = array();
	$stemLang = 'en';
	if($language == 'pt' && $translator == 0){
		$positive_lexicon = getLexiconAsArray('positive', $language);
		$negative_lexicon = getLexiconAsArray('negative', $language);
		$stemLang = 'pt';
	}else{
		$positive_lexicon = getLexiconAsArray('positive', 'en');
		$negative_lexicon = getLexiconAsArray('negative', 'en');
	}
	
	$pointseparated = explode(".", ($docText));
	$exclamationseparated = array();
	
	foreach($pointseparated as $sentence){
		foreach(explode("!", $sentence) as $word){
				array_push($exclamationseparated, $word);
		}
	}
	
	//$commaseparated = array();
	foreach($exclamationseparated as $sentence){
		foreach(explode("?", $sentence) as $word){
				//array_push($commaseparated, $word);
				array_push($sentences, $word);
		}
	}
	
	$noSpecials = array();
	$docText = array();
	$text = "";
	$sentence_index = 0;
	$word_positioning = array();
	foreach($sentences as $sentence){
		array_push($docText, array());
		array_push($noSpecials, array());
		$word_index = 0;
		foreach(explode(" ", $sentence) as $word){
			$word = processWord($word);
			if(strlen($word) > 0){
				//echo "<ORIGINAL>".$word."</ORIGINAL>";
				$clean_word = removeSpecialChars(utf8_encode(strtoupper(removeSpecialChars($word))));
				array_push($noSpecials[$sentence_index], $clean_word);		
				array_push($docText[$sentence_index], $word);
				$text = $text." ".$word;
				if(array_key_exists($clean_word, $word_positioning)){
					$word_positioning[$clean_word] = array();
				}
				array_push($word_positioning[$clean_word], $sentence_index);
				array_push($word_positioning[$clean_word], $word_index);
				$word_index++;
			}
		}
		$sentence_index++;
	}
	
	$sizes = array();
	$size = 0;
	for($i = 0; $i < count($docText); $i++){
		array_push($sizes, $size);
		$size = $size+count($docText[$i]);
	}
	
	$translations = array();
	if($translator == 1){
		$httpTranslator = new HTTPTranslator();
		//echo "////text to be translated:$text////";
		$translations = $httpTranslator->translate($text, $language);
		
		//echo "////translated length:".count($translations)."////";
	}
	
	$keys = array_keys($aspect_suggestions[1]);
	foreach($keys as $i){
		$aspect = $aspects[$i];
		
		
		$index1 = array_shift($word_positioning[$aspect]);
		$index2 = array_shift($word_positioning[$aspect]);
		
		$polarity = 0;
		$palavra = $noSpecials[$index1][$index2];
		
		//look around of the aspect for positive and negative words
		for($j = max(0, $index2-$neighborhood); $j < min(count($docText[$index1]), $index2+$neighborhood); $j++){//goes word by word on the sentence
			//if($j == $index2) continue;	//skip the aspect itself
			
			$term = $noSpecials[$index1][$j];
			if($language != 'en' && $translator == 1)// && count($translations) > ($sizes[$index1])+$j)
				$term = $translations[($sizes[$index1])+$j];
			
			//echo "<$aspect>".$term."</$aspect>";
			$term = strtolower($term);
			$term = doStem($term, $stemLang);
			
			if(array_search($term, $negative_lexicon) != ""){//give negative points if term is in the negative_lexicon, penalizes distance to aspect
				$polarity-=count($docText[$index1])-abs($j-$index2);
				//echo "<$aspect: negative>".$term."</$aspect>";
			}if(array_search($term, $positive_lexicon) != ""){//give positive if term is in the positive_lexicon, penalizes distance to aspect
				$polarity+=count($docText[$index1])-abs($j-$index2);
				//echo "<$aspect: positive>".$term."</$aspect>";
			}
		}
		
		$polarity = intval($polarity);
		if($polarity > 0)
			array_push($polarities, "POSITIVE");
		else if($polarity < 0)
			array_push($polarities, "NEGATIVE");
		else
			array_push($polarities, "NEUTRAL");
	}
	
	return $polarities;
}


/* Unity test
$fileName = "lexicon/negative-en.txt";
	$handle = fopen($fileName, "r");
	if ($handle) {
		while (($line = fgets($handle)) !== false) {
			echo("$line<br>");
		}

		fclose($handle);
	} else {
		phpAlert("Error when accessing lexicon file.");
	}
*/
?>