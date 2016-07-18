<?php
include_once 'functions.php';
include_once 'lpbhn.php'; 

sec_session_start();

function getAttributes($mysqli, $lpID){
	$query = "SELECT process_training_set 
					FROM tbl_labeling_process 
					WHERE process_id = ?" ;
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	$training_set;
	if($stmt->execute()){
		$stmt->bind_result($training_set);
		$stmt->fetch();
		$stmt->close();
	}else{
		$stmt->close();
		echo $mysqli->error;
		return;
	}
	@chdir("./includes");
	$filename = "./training_set.arff";
	file_put_contents($filename, $training_set);
	@chdir("./..");
	
	$training_set = utf8_decode($training_set);
	
	$start = 0;
	$tag = '@ATTRIBUTE ';
	$index = strpos($training_set, $tag, $start);
	$attributes = array();
	while($index !== false){
		$start = $index+strlen($tag);
		$end = strpos($training_set, ' ', $start);
		
		$word = substr($training_set, $start, $end-$start);
		
		if(strlen($word) >= 1)
			array_push($attributes, $word);
		
		$index = strpos($training_set, $tag, $start);
	}
	
	return array($attributes, $training_set);
}

function run_NB($attributes, $sentence_representation){
	@chdir("./includes");
	$filename = "./data.arff";
	
	$file_content = substr($attributes, 0, strpos($attributes, '@DATA'));
	
	$file_content.="@DATA\r\n";
	
	foreach($sentence_representation as $sentence){	
		for($i = 0; $i < count($sentence); $i++){
			$value = $sentence[$i];
			if($i < count($sentence)-1)
				$value .= ",";
			$file_content .= "$value";
		}
		$file_content .= " NEUTRAL\r\n";
	}
	//echo $file_content;
	$result = file_put_contents($filename, $file_content);
	
	$command = 'java -Djava.awt.headless=true -jar "./NaiveBayes/dist/NaiveBayes.jar"';
	$result = array();
	$ret = exec($command, $result);
	
	@chdir("./..");
	return $result;
}

function getPolaritiesFromNB($mysqli, $aspect_suggestions){
	$lpID = $_SESSION['cur_lpID'];
	
	$aspects = $aspect_suggestions[0];
	$polarities = array();
	$sentences = array();
	
	//retrieving document text
	$docText = strtoupper(removeSpecialChars(getCurrDocText($mysqli)));
	$docText = utf8_encode($docText);
	if($docText == "")
		return $polarities;
	
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
	
	$attributes = getAttributes($mysqli, $lpID);//get the attributes from the training set
	$ts_file = $attributes[1];
	$attributes = $attributes[0];
	
	$sentence_polarities = array();
	$sentence_representation = array();
	
	foreach($sentences as $sentence){
		$sentence = str_word_count($sentence,2);
		$polarity = 0;
		array_push($sentence_representation, array());
		$count = array_count_values($sentence);
		for($i = 0; $i < count($attributes)-1; $i++){
			$attribute = $attributes[$i];
			if(strlen($attribute) == 0 || !isset($attribute)) continue;
			$attribute = strtoupper($attribute);
			if(array_search($attribute, $sentence) !== false){
				array_push($sentence_representation[count($sentence_representation)-1], $count[$attribute]);
			}else{
				array_push($sentence_representation[count($sentence_representation)-1], 0);
			}
		}
	}
	
	$sentence_polarities = run_NB($ts_file, $sentence_representation);//creates the arff and run the java code
	
	$keys = array_keys($aspect_suggestions[1]);
	$evaluated_aspects = array();

	$regex = "/^[0-9.,\\b\\s\\n\\~\\!\\@\\#\\$\\%\\^\\&\\*\\(\\)\\_\\+\\=\\[\\]\\{\\}\\;\\:\\'\\\"\\\\\\/\\<\\>\\?]+$/";
	foreach($keys as $i){
		$aspect = $aspects[$i];
		$index = array_search($aspect,$evaluated_aspects);
		$last_sentence = -1;
		$last_pos = -1;
		
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
			
			while($curpos !== false && (($curpos > 0 && !preg_match($regex, substr($sentence, $curpos-1, 1))) 
				|| ($curpos+strlen($aspect) < strlen($sentence) && !preg_match($regex, substr($sentence, $curpos+strlen($aspect), 1))))){
				$offset = $curpos+1;
				$curpos = strpos($sentence, $aspect, $offset);
			}
			
			if($curpos !== false && ($j > $last_sentence || $curpos != $last_pos)){
				$evaluated_aspects[$index+1] = $j;
				array_push($polarities, $polarity);
				//phpAlert("Sentence: $sentence, Polarity: $polarity, Aspect: $aspect");
				break;
			}
		}
	}
		
	return $polarities;
}
?>