<?php
include_once '../lpbhn.php';
$lexPol = "-1";
$fileName = "lexico-pt.txt";
$handle = fopen($fileName, "r");
$lexicon = array();

$handle = fopen($fileName, "r");
if ($handle) {
	while (($line = fgets($handle)) !== false) {
		$line = explode(",", $line);
		$word = $line[0];
		$polarity = $line[2];
		$polarity = trim(preg_replace('/\s\s+/', '', $polarity));
		$word = (trim(preg_replace('/\s\s+/', '', $word)));
		if($polarity == $lexPol && array_search($word, $lexicon) == "")
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