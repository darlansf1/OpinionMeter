<?php
$lexPol = "positive";
$fileName = "lexicon/".$lexPol."1.txt";
$handle = fopen($fileName, "r");
$lexicon = array();
if ($handle) {
	while (($line = fgets($handle)) !== false) {
		$line = trim(preg_replace('/\s\s+/', '', $line));
		array_push($lexicon, $line);
	}
	
	fclose($handle);
} else {
	phpAlert("Error when accessing lexicon file.");
}

$fileName = "lexicon/subjclueslen1-HLTEMNLP05.tff";
$handle = fopen($fileName, "r");
if ($handle) {
	while (($line = fgets($handle)) !== false) {
		$line = explode(" ", $line);
		$word = substr($line[2], 6);
		$polarity = substr($line[5], 14);
		$polarity = trim(preg_replace('/\s\s+/', '', $polarity));
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