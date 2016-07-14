<?php
include_once 'functions.php';
include_once 'frequence.php';
include_once 'lexicon.php';
include_once 'PMI.php';

function getAspectSuggestions($mysqli){
	$lpID = $_SESSION['cur_lpID'];
	
	$query = "SELECT process_aspect_suggestion_algorithm 
				FROM tbl_labeling_process
				WHERE process_id = ?";
				
	
	$stmt = $mysqli->prepare($query);	
	$stmt->bind_param('i',$lpID);
	$algorithm;
	
	if($stmt->execute()){
		$stmt->store_result();
		$stmt->bind_result($algorithm);
		$stmt->fetch();	
		
	}else{
		phpAlert("Error retrieving process information");
		$algorithm = 'none';
	}
	$stmt->close();
	
	$suggestions = array();
	
	#ADD CALLS TO NEW ASPECT IDENTIFICATION METHODS HERE
	#the function should return a bidimensional vector with:
	#	the aspects in the first dimension and the corresponding start position in the document in the second
	if($algorithm == 'frequenceBased'){
		$suggestions = getAspectSuggestionsFrequence($mysqli, $lpID);
	}else{
		return $suggestions;
	}
	
	if(!empty($suggestions))
		asort($suggestions[1]);
		
	/*$str = "[";
	
	$j = 0;
	$count = count($suggestions[0]);
	if($count == 0)
		$str .= "]";
	$keys = array_keys($suggestions[1]);
	foreach($keys as $i){
		if($j < $count-1)
			$str = $str."\"".$suggestions[0][$i]."\"".",".$suggestions[1][$i].",";
		else
			$str = $str."\"".$suggestions[0][$i]."\",".$suggestions[1][$i]."]";
		$j++;
	}
	
	return $str;
	*/
	return $suggestions;
}

function getAspectPolarities($mysqli, $aspects){
	$lpID = $_SESSION['cur_lpID'];
	$neighborhood = 10;
	$query = "SELECT process_translator, process_language 
				FROM tbl_labeling_process
				WHERE process_id = ?";
				
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	
	$translator; $language;
	
	if($stmt->execute()){
		$stmt->store_result();
		$stmt->bind_result($translator, $language);
		$stmt->fetch();	
	}else{
		phpAlert("Error retrieving process information");
		return;
	}
	$stmt->close();
	
	$query = "SELECT ua_algorithm
				FROM tbl_used_algorithm
				WHERE ua_lp = ?";
				
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	$algorithms = array();
	if($stmt->execute()){
		$result = $stmt->get_result();
		while($data = mysqli_fetch_row($result)){
			array_push($algorithms, $data[0]);
		}
	}else{
		setAlert("Error retrieving process information");
	}
	$stmt->close();
	
	$suggestions = array();
	foreach($algorithms as $algorithm){
		$aux = false;
		#ADD CALLS TO NEW SENTIMENT CLASSIFICATION METHODS HERE
		#the function should return a vector with the polarity of each aspect in the array $aspects
		if($algorithm == 'lexiconBased'){
			$aux = getPolaritiesFromLexicon($mysqli, $aspects, $translator, $language, $neighborhood);
		}else if($algorithm == 'PMIBased'){
			$aux = getPolaritiesFromPMI($mysqli, $aspects, $translator, $language);
		}
		if($aux !== false){
			array_push($suggestions, array($algorithm, $aux));
		}
	}
	
	return $suggestions;
}