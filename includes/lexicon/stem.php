<?php
include_once '../lpbhn.php';

$language = "en";
$polarity = "negative";
$fileName = $polarity."-".$language.".txt";
$handle = fopen($fileName, "r");
$lexicon = array();

$handle = fopen($fileName, "r");
if ($handle) {
	while (($line = fgets($handle)) !== false) {
		$word = $line;
		$word = (trim(preg_replace('/\s\s+/', '', $word)));
		$word = doStem($word, $language);
		if(array_search($word, $lexicon) == "")
			array_push($lexicon, $word);
	}
	asort($lexicon);
	foreach($lexicon as $word){
		echo "$word<br>";
	}
	
	fclose($handle);
} else {
	phpAlert("Error when accessing lexicon file.");
}

?>