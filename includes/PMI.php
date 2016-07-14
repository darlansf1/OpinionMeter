<?php
include_once 'psl-config.php';

function search($query) 
{
    // Replace this value with your account key
    $accountKey = ACCOUNT_KEY;            
    $ServiceRootURL =  'https://api.datamarket.azure.com/Data.ashx/Bing/Search/';                    
    $WebSearchURL = $ServiceRootURL . 'v1/Composite?Sources=%27web%27&$format=json&Query=';
	
    $cred = sprintf('Authorization: Basic %s', 
      base64_encode($accountKey . ":" . $accountKey) );

    $context = stream_context_create(array(
        'http' => array(
            'header'  => $cred
        )
    ));

    $request = $WebSearchURL . urlencode( '\'' .$query. '\'');
	
	//$request = '%27Dilma%27&$format=JSON';

    $response = file_get_contents($request, 0, $context);

    $jsonobj = json_decode($response);

	if($jsonobj == null || !$jsonobj)
		return 0;
	return $jsonobj->d->results[0]->WebTotal;
}

function english_hits($phrase, $type){
	if($type != 0)
		return search("$phrase near:10 excellent OR excellent near:10 $phrase");
	else
		return search("$phrase near:10 poor OR poor near:10 $phrase");
}

function portuguese_hits($phrase, $type){
	if($type != 0)
		return search("$phrase near:10 bom OR bom near:10 $phrase");
	else
		return search("$phrase near:10 ruim OR ruim near:10 $phrase");
}

function SO($phrase, $lpID, $mysqli){
	//phpAlert("phrase: $phrase, lpID:$lpID");
	$query = "	SELECT phrase_count, negative_count, positive_count
					FROM tbl_pmi_phrases
					WHERE pmi_lp = ? && phrase = ?"; 
	$stmt = $mysqli->prepare($query);
	$phrase = utf8_encode(addslashes($phrase));
	$stmt->bind_param('is', $lpID, $phrase);
	if($stmt->execute()){
		$result = $stmt->get_result();
		$data = mysqli_fetch_row($result);
		$phrase_hits = $data[0];
		
		//echo("///phrase: $phrase, hits: $phrase_hits///");
		//phpAlert("phrase hits: ".$phrase_hits);
		if($phrase_hits == 0)
			return 0;
		
		$phrase_negative = $data[1];
		$phrase_positive = $data[2];
		
		$data = get_hits($mysqli, $lpID);
		if($data == null)
			return;
		
		$negativehits = $data[1];
		$positivehits = $data[0];
		
		$val = (($phrase_positive/($phrase_hits*$positivehits+0.001))-($phrase_negative/($phrase_hits*$negativehits+0.001)))*10000000000000000;
		return $val;
	}else{
		setAlert("Error retrieving PMI data");
	}
	$stmt->close();
}

function get_hits($mysqli, $lpID){
	$result = array();
	$query = "	SELECT `process_translator`, `process_language`
					FROM tbl_labeling_process
					WHERE process_id = ?"; 
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i', $lpID);
	
	if($stmt->execute()){
		$result = $stmt->get_result();
		$data = mysqli_fetch_row($result);
		if($data[0] == 1 || $data[1] == 'en'){
			$select_query = "SELECT english_positive, english_negative FROM aux_pmi_hits";
		}else{
			$select_query = "SELECT portuguese_positive, portuguese_negative FROM aux_pmi_hits";
		}	
		$select_stmt = $mysqli->prepare($select_query);
		
		if($select_stmt->execute()){
			$result = $select_stmt->get_result();
			$data = mysqli_fetch_row($result);
			$result = $data;
		}else{
			phpAlert("".$mysqli->error);
			$select_stmt->close();
			dbError($mysqli);
			return null;	
		}
		$select_stmt->close();
	}else{
		setAlert("Error retrieving PMI data");
		return null;
	}
	$stmt->close();
								
	$select_stmt = $mysqli->prepare($select_query);
	return $result;
}

function update_hits($mysqli){
	$pt_negative = search('ruim');
	$pt_positive = search('bom');
	$en_negative = search('poor');
	$en_positive = search('excellent');
	
	$insert_query = "UPDATE aux_pmi_hits SET portuguese_positive = ?, portuguese_negative = ?, english_positive = ?, english_negative = ?;";
											
	$insert_stmt = $mysqli->prepare($insert_query);
			
	$insert_stmt->bind_param('iiii', $pt_positive, $pt_negative, $en_positive, $en_negative);
				
	if(!$insert_stmt->execute()){
		phpAlert("".$mysqli->error);
		$insert_stmt->close();
		dbError($mysqli);
		return;	
	}
	$insert_stmt->close();
}

function getAspectSuggestionsPMI($mysqli, $lpID){
	$docID = $_SESSION['curDocID'];
	$suggestions = array();
	$nouns = array();
	$frequences = array();
	
	//Retrieving the nouns in the current document
    $query = "SELECT wf_word, wf_frequence
					FROM tbl_word_frequence 
					WHERE wf_process = ? ORDER BY wf_frequence DESC"; 
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	if($stmt->execute()){
		$result = $stmt->get_result();
		while($data = mysqli_fetch_row($result)){
			//phpAlert("".$data[0]);
			array_push($nouns,($data[0]));
			array_push($frequences, $data[1]);
		}
	}else{
		setAlert("Error retrieving nouns for PMI-based algorithm");
	}
	$stmt->close();

	$phrases = array();
	
	//Retrieving phrases for the current process
    $query = "SELECT phrase
					FROM tbl_pmi_phrases 
					WHERE pmi_lp = ?"; 
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	if($stmt->execute()){
		$result = $stmt->get_result();
		while($data = mysqli_fetch_row($result)){
			array_push($phrases,($data[0]));
		}
	}else{
		setAlert("Error retrieving phrases for PMI-based algorithm");
	}
	$stmt->close();

	//retrieving document text to identify the words that are present on it
	$docText = strtoupper(removeSpecialChars(getCurrDocText($mysqli)));
	
	$docText = removeSpecialChars(utf8_encode($docText));
	if($docText == "")
		return $suggestions;
	
	$regex = "/^[0-9.,\\b\\s\\n\\~\\!\\@\\#\\$\\%\\^\\&\\*\\(\\)\\_\\+\\=\\[\\]\\{\\}\\;\\:\\'\\\"\\\\\\/\\<\\>\\?]+$/";
	$swords = array();
	$spositions = array();
	
	//goes through all phrases and nouns in the sentence
	//assign as aspect whatever noun is closest to a phrase in the same sentence
	foreach($phrases as $phrase){
		$p_offset = 0;
		$p_position = strpos($docText, $phrase, $p_offset);
		//echo("phrase: $phrase");
		while($p_position !== false){
			//phpAlert("phrase: $phrase passou");
			$cur_word = -1;
			$cur_pos = -1;
			//$distance = PHP_INT_MAX;
			$aspect_set = false;
			foreach($nouns as $word){
				$offset = 0;
				$position = strpos($docText, $word, $offset);
				
				//$cur_dis = abs($position-$p_position);
				
				while($position !== false){
					$cur_dis = abs($position-$p_position);
					
					$substring = substr($docText, min($position, $p_position), $cur_dis);
					
					//phpAlert("phrase: $phrase, position: $p_position");
					//phpAlert("word: $word, position: $position");
					//phpAlert("substring: $substring");
					//phpAlert("cur min: $distance, cur dis: $cur_dis");
					if(strpos($substring, ".", 0) === false && strpos($substring, "?", 0) === false && strpos($substring, "!", 0) === false){// && strpos($substring, ",", 0) === false){
						//check if it's part of another word before proceeding
						if(($position > 0 && !preg_match($regex, substr($docText, $position-1, 1))) 
							|| ($position+strlen($word) < strlen($docText) && !preg_match($regex, substr($docText, $position+strlen($word), 1)))){
							$offset = $position+1;
							$position = strpos($docText, $word, $offset);
							///phpAlert("entrou");
							continue;
						}
						
						//phpAlert("phrase: $phrase->$p_position, word: $word->$position, position-p_position: ".($position-$p_position).", cur_dis: ".$cur_dis.", strlen: ".strlen($phrase));
						
						//if(strpos($phrase, $word) !== false && $position-$p_position >= 0 && $cur_dis < strlen($phrase)){
							//$cur_dis = PHP_INT_MAX;
							//phpAlert("$word in $phrase");
						//}
						$index = array_search($word, $swords);
						if($index === false || $spositions[$index] != $position){
							array_push($swords, $word);
							array_push($spositions, $position);				
							$aspect_set = true;
						}
						
						//if($cur_dis < $distance){
							//$distance = $cur_dis;
							//$cur_word = $word;
							//$cur_pos = $position;
						//}
						//phpAlert("phrase: $phrase, position: $p_position");
						//phpAlert("word: $word, position: $position");
						//phpAlert("substring: $substring");
						//phpAlert("cur min: $distance, cur dis: $cur_dis");
						//phpAlert($position);
						//array_push($suggestions, array($word, $position));
					}
					if($aspect_set)
						break;
					
					$offset = $position+1;
					$position = strpos($docText, $word, $offset);
				}
				if($aspect_set)
					break;
			}
			//if($cur_pos >= 0){
				//$index = array_search($cur_word, $swords);
				//if($index === false || $spositions[$index] != $cur_pos){
					//array_push($swords, $cur_word);
					//array_push($spositions, $cur_pos);				
				//}
			//}
			$p_offset = $p_position+1;
			$p_position = strpos($docText, $phrase, $p_offset);
		}
	}
	
	//return $suggestions;
	return array($swords, $spositions);
}

function getPolaritiesFromPMI($mysqli, $aspect_suggestions, $translator, $language){
	$lpID = $_SESSION['cur_lpID'];
	$phrases = array();
	//Retrieving phrases for the current process
    $query = "SELECT phrase
					FROM tbl_pmi_phrases 
					WHERE pmi_lp = ?"; 
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	if($stmt->execute()){
		$result = $stmt->get_result();
		while($data = mysqli_fetch_row($result)){
			array_push($phrases,stripslashes($data[0]));
		}
	}else{
		setAlert("Error retrieving phrases for PMI-based algorithm");
	}
	$stmt->close();
	
	$aspects = $aspect_suggestions[0];
	$polarities = array();
	$sentences = array();
	
	//retrieving document text
	$docText = strtoupper(removeSpecialChars(getCurrDocText($mysqli)));
	$docText = utf8_encode($docText);
	/*$euro_pos = strpos($docText, '¬');
	$ndoc = "";
	$last = 0;
	while($euro_pos !== false){
		$ndoc = $ndoc.substr($docText, $last, $euro_pos-5-$last)."E";
		//echo "////substring: $ndoc////";
		$last = $euro_pos+1;
		$euro_pos = strpos($docText, '¬', $last);
	}
	$ndoc = $ndoc.substr($docText, $last, strlen($docText)-$last);
	$docText = $ndoc;
	//echo($ndoc);
	
	echo(strtr(utf8_encode($docText), array('¬'=>'E')));*/
	if($docText == "")
		return $polarities;
	
	$PMILang = 'en';
	if($language == 'pt' && $translator == 0){
		$PMILang = 'pt';
	}

	$pointseparated = explode(".", ($docText));
	$exclamationseparated = array();
	
	foreach($pointseparated as $sentence){
		foreach(explode("!", $sentence) as $word){
				array_push($exclamationseparated, $word);
		}
	}
	
	foreach($exclamationseparated as $sentence){
		foreach(explode("?", $sentence) as $word){
				array_push($sentences, $word);
		}
	}
		
	$sentence_polarities = array();
	foreach($sentences as $sentence){
		$polarity = 0;
		foreach($phrases as $phrase){
			//phpAlert("////phrase: $phrase, sentence: $sentence///");
			if(strpos($sentence, $phrase) !== false){
				$polarity += SO($phrase, $lpID, $mysqli);				
				//phpAlert("////POLARITY: $polarity///");
			}
		}
		$polarity = intval($polarity);
		if($polarity > 0)
			array_push($sentence_polarities, "POSITIVE");
		else if($polarity < 0)
			array_push($sentence_polarities, "NEGATIVE");
		else
			array_push($sentence_polarities, "NEUTRAL");
	}
	
	$keys = array_keys($aspect_suggestions[1]);
	$evaluated_aspects = array();
	$regex = "/^[\\.\\,\\b\\s\\n\\~\\!\\@\\#\\$\\%\\^\\&\\*\\(\\)\\_\\+\\=\\[\\]\\{\\}\\;\\:\\'\\\"\\\\\\/\\<\\>\\?]+$/";
	//phpAlert("count(evaluated_aspects): ".count($evaluated_aspects));
	foreach($keys as $i){
		$aspect = $aspects[$i];
		
		$index = array_search($aspect,$evaluated_aspects);
		$last_sentence = -1;
		$last_pos = -1;
		
		//echo("count(evaluated_aspects): ".count($evaluated_aspects)." aspect: $aspect, index: $index, search: ".array_search($aspect,$evaluated_aspects));
		if($index === false || $index % 2 != 0){
			$index = count($evaluated_aspects);
			array_push($evaluated_aspects, $aspect);
			array_push($evaluated_aspects, $last_sentence);
			array_push($evaluated_aspects, $last_pos);
		}else{
			$last_sentence = $evaluated_aspects[$index+1];
			$last_pos = $evaluated_aspects[$index+2];
		}
		
		for($j = 0; $j < count($sentences); $j++){
			$sentence = $sentences[$j];
			$polarity = $sentence_polarities[$j];
			$curpos = strpos($sentence, $aspect, 0);
			//echo "pos: ".($curpos+strlen($aspect))."aspect: $aspect, strlen:".strlen($sentence);
			//echo "*************************$sentence**********************";
			//echo("substr://".substr($sentence, $curpos+strlen($aspect), 1)."//".(!preg_match($regex, substr($sentence, $curpos+strlen($aspect)-1, 1))));
			//echo "///aspect: $aspect, first cond:".(($curpos > 0 && !preg_match($regex, substr($sentence, $curpos-1, 1))))."////";
			while($curpos !== false && (($curpos > 0 && !preg_match($regex, substr($sentence, $curpos-1, 1))) 
				|| ($curpos+strlen($aspect) < strlen($sentence) && !preg_match($regex, substr($sentence, $curpos+strlen($aspect), 1))))){
				$offset = $curpos+1;
				$curpos = strpos($sentence, $aspect, $offset);
			}
			
			if($curpos !== false && ($j > $last_sentence || $curpos != $last_pos)){
				//echo("aspect: $aspect, sentence: $j, last: $last_pos, substr: ".substr($sentence, $curpos));
				$evaluated_aspects[$index+1] = $j;
				array_push($polarities, $polarity);
				break;
			}
		}
	}
		
	return $polarities;
}

?>