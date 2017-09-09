<?php
include_once 'functions.php';
include_once 'lpbhn.php';
include_once 'HTTPTranslator.php';

function getLexiconAsArray($polarity, $language){
	if($language != 'pt' && $language != 'en')
		return array();

	$lexicon = array();
	$fileName = "includes/lexicon/".$polarity."-".$language."-stem.txt";

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

function getLexiconAccordingly($language, $translator) {
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

	return array($stemLang, $positive_lexicon, $negative_lexicon);
}

function breakIntoWordArrays($sentences) {
	$wordArraySentencesNoSpecials = array();
	$wordArraySentencesOriginals = array();
	$text = "";
	$sentence_index = 0;
	$word_positioning = array();
	//echo "sentences: ".var_dump($sentences)."<br>";
	foreach($sentences as $sentence){
		//echo $sentence;
		array_push($wordArraySentencesOriginals, array());
		array_push($wordArraySentencesNoSpecials, array());
		$word_index = 0;
		foreach(explode(" ", $sentence) as $word){
			$word = processWord($word);
			if(strlen($word) > 0){
				$clean_word = removeSpecialChars(utf8_encode(strtoupper(removeSpecialChars($word))));
				//echo "<br>including $word into word arrays<br>";
				array_push($wordArraySentencesNoSpecials[$sentence_index], $clean_word);
				array_push($wordArraySentencesOriginals[$sentence_index], $word);
				$text = $text." ".$word;
				if(!array_key_exists($clean_word, $word_positioning)){
					$word_positioning[$clean_word] = array();
				}
				array_push($word_positioning[$clean_word], $sentence_index);
				array_push($word_positioning[$clean_word], $word_index);
				$word_index++;
			}
		}
		$sentence_index++;
	}
	return array($text, $wordArraySentencesNoSpecials, $wordArraySentencesOriginals, $word_positioning);
}

function getPolaritiesFromLexicon($mysqli, $aspect_suggestions, $translator, $language, $neighborhood){
	$neighborhood = 5;

	//retrieving document text
	$docText = getCurrDocText($mysqli);
	if($docText == "")
		return array();

	list($stemLang, $positive_lexicon, $negative_lexicon) = getLexiconAccordingly($language, $translator);
	$sentences = preg_split('/(?<=[.?!])\s+(?=[a-z])/i', $docText);
	list($text, $wordArraySentencesNoSpecials, $wordArraySentencesOriginals, $word_positioning) = breakIntoWordArrays($sentences);

	$sentence_offsets = array();
	$cumulative_size = 0;
	for($i = 0; $i < count($wordArraySentencesOriginals); $i++){
		array_push($sentence_offsets, $cumulative_size);
		$cumulative_size = $cumulative_size+count($wordArraySentencesOriginals[$i]);
	}

	$translations = array();
	if($translator == 1){
		$httpTranslator = new HTTPTranslator();
		$translations = $httpTranslator->translate($text, $language);
	}

	$polarities = array();
	$keys = array_keys($aspect_suggestions[1]);
	$aspects = $aspect_suggestions[0];
	foreach($keys as $i){
		$aspect = $aspects[$i];
		//echo "resolving for aspect $aspect<br>";

		$sentence_index = array_shift($word_positioning[$aspect]);
		$word_index = array_shift($word_positioning[$aspect]);
		$polarity = 0;
		$palavra = $wordArraySentencesNoSpecials[$sentence_index][$word_positioning];

		//look around of the aspect for positive and negative words
		for($j = max(0, $word_index-$neighborhood); $j < min(count($wordArraySentencesOriginals[$sentence_index]), $word_index+$neighborhood); $j++){//goes word by word on the sentence
			//if($j == $index2) continue;	//skip the aspect itself

			$term = $wordArraySentencesNoSpecials[$sentence_index][$j];
			if($language != 'en' && $translator == 1)// && count($translations) > ($sentence_offsets[$sentence_index])+$j)
				$term = $translations[($sentence_offsets[$sentence_index])+$j];

			$term = strtolower($term);
			$term = doStem($term, $stemLang);
			//echo "stemmed term $term<br>";

			if(array_search($term, $negative_lexicon) != ""){//give negative points if term is in the negative_lexicon, penalizes distance to aspect
				$polarity -= count($wordArraySentencesOriginals[$sentence_index])-abs($j-$word_index);
				//echo "NEGATIVE ---".$term."<br>";
			}if(array_search($term, $positive_lexicon) != ""){//give positive if term is in the positive_lexicon, penalizes distance to aspect
				$polarity += count($wordArraySentencesOriginals[$sentence_index])-abs($j-$word_index);
				//echo "POSITIVE --- ".$term."<br>";
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
  $text = "The bartender abandoned me";
  $aspects = array();
	array_push($aspects, array(0 => "BARTENDER"));
	array_push($aspects, array(0 => 4));
	echo var_dump($aspects);
	$result = getPolaritiesFromLexiconTest($aspects, 0,"en", $text);
	echo var_dump($result);

	if ($handle) {
		while (($line = fgets($handle)) !== false) {
			#echo("$line<br>");
		}

		fclose($handle);
	} else {
		phpAlert("Error when accessing lexicon file on unit test.");
	}*/
?>
