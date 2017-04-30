<?php
include_once 'functions.php';
include_once 'lpbhn.php';
include_once 'HTTPTranslator.php';
include_once 'PMI.php';

function isNoun($tag){
	if(strcmp($tag, 'NN') == 0 || strcmp($tag, 'NNP') == 0 || strcmp($tag, 'NNS') == 0 || strcmp($tag, 'NNPS') == 0 || strcmp($tag, 'N') == 0 || strcmp($tag, 'PROP') == 0)
		return true;
	return false;
}

function isAdverb($tag){
	if(strcmp($tag, 'ADV') == 0 || strcmp($tag, 'RB') == 0 || strcmp($tag, 'RBR') == 0 || strcmp($tag, 'RBS') == 0)
		return true;
	return false;
}

function isVerb($tag){
	if(strcmp($tag, 'VB') == 0 || strcmp($tag, 'VBD') == 0 || strcmp($tag, 'VBN') == 0 || strcmp($tag, 'VBG') == 0
		|| strcmp($tag, 'V-FIN') == 0 || strcmp($tag, 'V-INF') == 0 || strcmp($tag, 'V-PCG') == 0 || strcmp($tag, 'V-GER') == 0)
		return true;
	return false;
}

function isAdjective($tag){
	if(strcmp($tag, 'ADJ') == 0 || strcmp($tag, 'JJ') == 0)
		return true;
	return false;
}

function addPhrase($word1, $word2, $translated1, $translated2, &$phrases, &$counts, $language){
	$phrase = $word1." ".$word2;
	$en_phrase = $translated1." ".$translated2;
	if(strlen(array_search($phrase, $phrases)) == 0){
		$countIndex = count($phrases);
		array_push($phrases, $phrase);
		array_push($counts, array());
		array_push($counts[$countIndex], search($phrase));
		if($language == 'en'){
			array_push($counts[$countIndex], english_hits($en_phrase, 0));
			array_push($counts[$countIndex], english_hits($en_phrase, 1));
		}else{
			array_push($counts[$countIndex], portuguese_hits($phrase, 0));
			array_push($counts[$countIndex], portuguese_hits($phrase, 1));
		}
		array_push($counts[$countIndex], $word1);
		array_push($counts[$countIndex], $word2);
	}
}

function addNoun($word, $tag, &$nouns, &$noun_counts){
	if($tag == 'NNP' || $tag == 'NNPS' || $tag == 'PROP') return;
	
	$word = processWord(strtoupper($word));
	if(strlen($word) == 0) return;
	$index = array_search($word, $nouns);
	if($index === false){
		array_push($nouns, $word);
		array_push($noun_counts, 1);
	}else{
		$noun_counts[$index]++;
	}
}

/*function executePMITagger($command, &$phrases, &$counts, $explodedText, $useTranslator, $language, &$nouns, &$noun_counts){
	@chdir("./includes");
	
	exec($command, $result);
	//phpAlert($command);
	
	if($result == 0){
		phpAlert("Failed to run POSTagger");
	}else{
		$count = count($result);
	//	phpAlert("count: $count");
		for($i = 0; $i < count($result); $i+=2){
			/* POS Tags Patterns:
			*
			* PURE ENGLISH & TRANSLATED ENGLISH
			*	JJ ->NN or NNS -> any 
			*	RB or RBR or RBS -> JJ -> neither NN nor NNS
			*	JJ -> JJ -> neither NN nor NNS
			*	NN or NNS -> JJ -> neither NN nor NNS
			*	RB or RBR or RBS -> VB or VBD or VBN or VBG -> any
			*
			* TRANSLATED ENGLISH ONLY
			*	JJ -> RB or RBR or RBS -> neither NN nor NNS
			*	VB or VBD or VBN or VBG -> RB or RBR or RBS -> any
			*
			* PORTUGUESE
			* 	ADJ -> N or PROP -> any
			*	ADV -> ADJ -> neither N nor PROP
			*	ADJ -> ADJ -> neither N nor PROP
			*	N or PROP -> ADJ -> neither N nor PROP
			*	ADV -> V-FIN or V-INF or V-PCG or V-GER -> any
			*//*
			//echo("$i<br>");
			$result[$i+1] = strtoupper($result[$i+1]);
			$first = $result[$i+1];
			
			$index = floor($i/2);
			$countIndex = count($counts);
			
			if(isNoun($first)){
				addNoun($explodedText[$index], $first, $nouns, $noun_counts);	
			}
			
			$second = '';
			$third = '';
			//phpAlert("word: ".$result[$i].", tag: ".$result[$i+1]);
			if($count > $i+3){
				$second = strtoupper($result[$i+3]);
			}
			if($count > $i+5){
				$third = strtoupper($result[$i+5]);
			}
			
			//padroes usados exclusivamento com traducao
			if($useTranslator == 'true'){
				if(isAdjective($first) && isAdverb($second) && !isNoun($third)){
					$word1 = processWord(strtoupper($explodedText[$index]));
					$word2 = processWord(strtoupper($explodedText[$index+1]));
					addPhrase($word1, $word2, $result[$i], $result[$i+2], $phrases, $counts, $countIndex, $language);
					$i = $i+2;
				}else if(isVerb($first) && isAdverb($second)){
					$word1 = processWord(strtoupper($explodedText[$index]));
					$word2 = processWord(strtoupper($explodedText[$index+1]));
					addPhrase($word1, $word2, $result[$i], $result[$i+2],$phrases, $counts, $countIndex, $language);
					$i = $i+2;
				}
			}
			
			if(isAdjective($first) && isNoun($second)){
				$word1 = processWord(strtoupper($explodedText[$index]));
				$word2 = processWord(strtoupper($explodedText[$index+1]));
				addPhrase($word1, $word2, $result[$i], $result[$i+2], $phrases, $counts, $countIndex, $language);
				addNoun($explodedText[$index+1], $second, $nouns, $noun_counts);
				$i = $i+2;
			}else if(isAdverb($first) && isAdjective($second) && !isNoun($third)){
				$word1 = processWord(strtoupper($explodedText[$index]));
				$word2 = processWord(strtoupper($explodedText[$index+1]));
				addPhrase($word1, $word2, $result[$i], $result[$i+2], $phrases, $counts, $countIndex, $language);
				$i = $i+2;
			}else if(isAdjective($first) && isAdjective($second) && !isNoun($third)){
				$word1 = processWord(strtoupper($explodedText[$index]));
				$word2 = processWord(strtoupper($explodedText[$index+1]));
				addPhrase($word1, $word2, $result[$i], $result[$i+2], $phrases, $counts, $countIndex, $language);
				$i = $i+2;
			}else if(isNoun($first) && isAdjective($second) && !isNoun($third)){
				$word1 = processWord(strtoupper($explodedText[$index]));
				$word2 = processWord(strtoupper($explodedText[$index+1]));
				addPhrase($word1, $word2, $result[$i], $result[$i+2], $phrases, $counts, $countIndex, $language);
				$i = $i+2;
			}else if(isAdverb($first) && isVerb($second)){
				$word1 = processWord(strtoupper($explodedText[$index]));
				$word2 = processWord(strtoupper($explodedText[$index+1]));
				addPhrase($word1, $word2, $result[$i], $result[$i+2], $phrases, $counts, $countIndex, $language);
				$i = $i+2;
			}
		}
	}
}*/

function detectPatterns(&$counts, &$phrases, $tags, $explodedText, $language, $useTranslator){
	$count = count($tags);
	for($i = 0; $i < count($tags); $i+=2){
			/* POS Tags Patterns:
			*
			* PURE ENGLISH & TRANSLATED ENGLISH
			*	JJ ->NN or NNS -> any 
			*	RB or RBR or RBS -> JJ -> neither NN nor NNS
			*	JJ -> JJ -> neither NN nor NNS
			*	NN or NNS -> JJ -> neither NN nor NNS
			*	RB or RBR or RBS -> VB or VBD or VBN or VBG -> any
			*
			* TRANSLATED ENGLISH ONLY
			*	JJ -> RB or RBR or RBS -> neither NN nor NNS
			*	VB or VBD or VBN or VBG -> RB or RBR or RBS -> any
			*
			* PORTUGUESE
			* 	ADJ -> N or PROP -> any
			*	ADV -> ADJ -> neither N nor PROP
			*	ADJ -> ADJ -> neither N nor PROP
			*	N or PROP -> ADJ -> neither N nor PROP
			*	ADV -> V-FIN or V-INF or V-PCG or V-GER -> any
			*/
			//echo("$i<br>");
			$tags[$i+1] = strtoupper($tags[$i+1]);
			$first = $tags[$i+1];
			
			$index = floor($i/2);
			$countIndex = count($counts);
			
			$second = '';
			$third = '';
			//phpAlert("word: ".$tags[$i].", tag: ".$tags[$i+1]);
			if($count > $i+3){
				$second = strtoupper($tags[$i+3]);
			}
			if($count > $i+5){
				$third = strtoupper($tags[$i+5]);
			}
			
			//padroes usados exclusivamento com traducao
			if($useTranslator == 'true'){
				if(isAdjective($first) && isAdverb($second) && !isNoun($third)){
					$word1 = processWord(strtoupper($explodedText[$index]));
					$word2 = processWord(strtoupper($explodedText[$index+1]));
					addPhrase($word1, $word2, $tags[$i], $tags[$i+2], $phrases, $counts, $language);
					$i = $i+2;
				}else if(isVerb($first) && isAdverb($second)){
					$word1 = processWord(strtoupper($explodedText[$index]));
					$word2 = processWord(strtoupper($explodedText[$index+1]));
					addPhrase($word1, $word2, $tags[$i], $tags[$i+2], $phrases, $counts, $language);
					$i = $i+2;
				}
			}
			
			if(isAdjective($first) && isNoun($second)){
				$word1 = processWord(strtoupper($explodedText[$index]));
				$word2 = processWord(strtoupper($explodedText[$index+1]));
				addPhrase($word1, $word2, $tags[$i], $tags[$i+2], $phrases, $counts, $language);
				$i = $i+2;
			}else if(isAdverb($first) && isAdjective($second) && !isNoun($third)){
				$word1 = processWord(strtoupper($explodedText[$index]));
				$word2 = processWord(strtoupper($explodedText[$index+1]));
				addPhrase($word1, $word2, $tags[$i], $tags[$i+2], $phrases, $counts, $language);
				$i = $i+2;
			}else if(isAdjective($first) && isAdjective($second) && !isNoun($third)){
				$word1 = processWord(strtoupper($explodedText[$index]));
				$word2 = processWord(strtoupper($explodedText[$index+1]));
				addPhrase($word1, $word2, $tags[$i], $tags[$i+2], $phrases, $counts,$language);
				$i = $i+2;
			}else if(isNoun($first) && isAdjective($second) && !isNoun($third)){
				$word1 = processWord(strtoupper($explodedText[$index]));
				$word2 = processWord(strtoupper($explodedText[$index+1]));
				addPhrase($word1, $word2, $tags[$i], $tags[$i+2], $phrases, $counts, $language);
				$i = $i+2;
			}else if(isAdverb($first) && isVerb($second)){
				$word1 = processWord(strtoupper($explodedText[$index]));
				$word2 = processWord(strtoupper($explodedText[$index+1]));
				addPhrase($word1, $word2, $tags[$i], $tags[$i+2], $phrases, $counts, $language);
				$i = $i+2;
			}
		}
}

function findPatterns($originalLanguage, $useTranslator, $tagdata, $lpId, $mysqli){
	update_hits($mysqli);
	
	$used_language = 'en';
	if($originalLanguage == 'pt' && $useTranslator == 'false'){
		$used_language = 'pt';
	}
	
	$counts = array();
	$phrases = array();
	foreach($tagdata as $docdata){
		$explodedText = $docdata["exploded"];
		
		detectPatterns($counts, $phrases, $docdata["tags"], $explodedText, $used_language, $useTranslator);
	}
	savePhrases($counts, $lpId, $mysqli);
	
}


// counts[i][0]: hits for phrase i
// counts[i][1]: negative hits for phrase i
// counts[i][2]: positive hits for phrase i
// counts[i][3]: word1 in phrase i
// counts[i][4]: word2 in phrase i
function savePhrases($counts, $lpID, /*$nouns, $noun_counts,*/ $mysqli/*, $minFreq*/){
	$query = 	"INSERT INTO `tbl_pmi_phrases`(`pmi_lp`, `phrase`,
					`phrase_count`, `negative_count`, `positive_count`) VALUES (?,?,?,?,?)";
	#var_dump($counts);
	for($i = 0; $i < count($counts); $i++){
		if(!isset($counts[$i]) or is_null($counts[$i])) {
			continue;
		}
		$stmt = $mysqli->prepare($query);	
		$phrase = $counts[$i][3]." ".$counts[$i][4];
		
		$phrasecount = min($counts[$i][0], 9999999999999999);
		
		$stmt->bind_param("isiii",$lpID,$phrase,$phrasecount,$counts[$i][1],$counts[$i][2]);
		if(!$stmt->execute()){	
			echo $mysqli->error;
			$mysqli->rollback();
			setAlert("Failed to insert data into the database");
		}
		$stmt->close();
	}
	
	/*$query = 	"INSERT INTO `tbl_word_frequence`(`wf_process`, `wf_frequence`, `wf_word`) VALUES (?,?,?)";
	for($i = 0; $i < count($nouns); $i++){
		if($noun_counts[$i] >= $minFreq){
			$stmt = $mysqli->prepare($query);	
			$nouns[$i] = utf8_encode($nouns[$i]);
			$stmt->bind_param("iis",$lpID,$noun_counts[$i],$nouns[$i]);
			if(!$stmt->execute()){	
				echo $mysqli->error;
				$mysqli->rollback();
				setAlert("Failed to insert data into the database");
			}
			$stmt->close();
		}
	}*/
}
?>