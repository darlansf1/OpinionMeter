<?php

include_once 'functions.php';
include_once 'stem/english.php';
include_once 'stem/portuguese.php';

function removeSpecialChars($text) {
	$text = strtr(
		$text,
		array (
		  'À' => 'A', 'Á' => 'A', 'Â¬' => '$', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
		  'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
		  'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ð' => 'D', 'Ñ' => 'N',
		  'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O',
		  'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Ŕ' => 'R',
		  'Þ' => 's', 'ß' => 'B', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a',
		  'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e',
		  'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
		  'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
		  'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y',
		  'þ' => 'b', 'ÿ' => 'y', 'ŕ' => 'r', '&' => 'E'
		)
	);   
	return $text;
}

function cleanText($text) {
    return strtolower(	//Passing to lower-case
        preg_replace(	
			array( '#[\\s-]+#', '#[^A-Za-z0-9 -]+#' ), //Allowing only alphanumeric chars
			array( ' ', '' ),
			removeSpecialChars($text)
        )
    );
}

function removeStopWords ($text, $idiom){
	$stopWordsFileName = __DIR__ . "/stop_words/";
	if($idiom == "pt") 		$stopWordsFileName = $stopWordsFileName . 'pt.txt';
	else if($idiom == "en")	$stopWordsFileName = $stopWordsFileName . 'en.txt';
	
	//Loading stopWords array
	$stopWords = file($stopWordsFileName, FILE_IGNORE_NEW_LINES);
	
	//Removing stopwords from $text
	return preg_replace('/\b('.implode('|',$stopWords).')\b/','',$text);
	
}

function doStem ($text, $idiom) {
	$text = preg_replace('/[[:punct:]]/', '', $text); //Removing punctuation 
	if($idiom == "pt")
		$stemming = new PorterStemmerPT ();
	else if($idiom == "en")
		$stemming = new PorterStemmerEN ();
	else	
		return $text;


	/*Getting and replacing each term*/
	$terms = array_unique ( explode (" ", $text) );
	foreach ($terms as $term) {
		if(strlen($term ) >  2){
			$stem = $stemming->Stem ($term) ;
			$text = preg_replace ("/\b($term)\b/i",$stem,$text);
		}	
	}
	
	return $text;
}

function preProcess ($text , $idiom) {
	$text = removeStopWords ($text, $idiom);
	$text = doStem ($text , $idiom);
	$text = cleanText( $text );
	return $text;
}


//Before this point: functions that will work on "new labeling process" page
//Below this point: functions that will work on the "labeling" page

/*Normalizing(probability) adjacent list by each row 
* Ex.:
* Before :
* index0 --> { subIndex0 , 4} { subIndex1 , 6}	(sum=10)
* index1 --> { subIndex0 , 1} { subIndex1 , 3} { subIndex2 , 1}	(sum=5)
*
* After:
* index0 --> { subIndex0 , 0.4} { subIndex1 , 0.6}
* index1 --> { subIndex0 , 0.2} { subIndex1 , 0.6} { subIndex2 , 0.2}
*
* @param  ($adjList) -- adjacent list (array)
*/
function normalize (&$adjList ) {
	foreach ($adjList as $adjListIndex => $list) {
		$sum = 0;
		foreach ($list as $index => $data)
			$sum = $sum + $data;
		if($sum == 0) continue;
		foreach ($list as $index => $data)
			$adjList[$adjListIndex][$index] = $data / $sum;
			
	}
}

/**
*	This function returns two adjacent lists.
*	First one is indexed by the documentID and the other
*	is indexed by the termID.
*/
function getAdjListsW ( $mysqli , $lpID ) {
	$listDoc 	= array ();
	$listTerm 	= array ();
	$query = "	SELECT *
					FROM tbl_document_term 
					WHERE term_document IN
						(SELECT document_id 
						 FROM tbl_document 
						 WHERE document_process = ? ) ";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	if(!$stmt->execute()){
		$mysqli->rollback();
		setAlert("Erro ao recuperar dados do processo de rotulação");
		$stmt->close();
		return ;
	}
	$result = $stmt->get_result();

	while ($row = $result->fetch_row()) {
		/* $row[0] = termID , $row[1] = DocID , $row[2] = Term Count
			Building an adj. list (W) like this:
				Doc 0 --> { Term 0 , 4 }  { Term 1 , 3 }  { Term 2 , 1 } { Term 3 , 5 }
				Doc 1 --> { Term 3 , 2 }  { Term 8 , 5 }
				Doc 2 --> { Term 1 , 3 }  { Term 3 , 3 } 
				...
			To check how many times a term appears on a document
		*/
		$listDoc[$row[1]][$row[0]] =  $row[2] ;
		/*	Second adj. list is like this:
				Term 0 --> { Doc 0 , 4 }  
				Term 1 --> { Doc 0 , 3 }  { Doc 2 , 3 }
				Term 2 --> { Doc 0 , 1 }  
				...
		*/
		$listTerm[$row[0]][$row[1]] =  $row[2];
	}
	$stmt->close();
	normalize($listDoc);
	normalize($listTerm);
	return array ($listDoc,$listTerm);
}

/**
* Shows labels (classes) for the current labelling process, which uses
* the transductive algorithm for classification of label suggestion.
* Current document ($_SESSION['curDocID']) must be set before calling this function	
*
* @param  ($adjList) 	- adjacent list ($listDoc or $listTerm) 	
* @param  ($matrix) 	- normal matrix (arrays of arrays)
* @return ($answer) 	- result of this matrix multiplication (adjList x matrix)
*/
function matrixMultiplication (&$adjList, &$matrix) {
	$answer = array();
	//var_dump($matrix);
	$matrixLines = array_keys($matrix);
	$matrixCols = array_keys($matrix[$matrixLines[0]]);
	
	foreach ($adjList as $lineIndex => $list) {
		foreach ($matrixCols as $colIndex) {
			$answer[$lineIndex][$colIndex]=0;
			foreach ($list as $index => $data){
				$answer[$lineIndex][$colIndex] += 
						$data * $matrix[$index][$colIndex];
			}
		}
	}
	return $answer;
	
}

function getTransductiveResetRate ( $mysqli , $lpID ){
	$rate = 1;
	$query = "SELECT transductive_reset_rate
				FROM tbl_labeling_process_transductive
				WHERE transductive_process = ?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	if($stmt->execute()){
		$stmt->bind_result($rate);
		$stmt->fetch();
	}else{
		setAlert("Houve erro ao recuperar dados do processo de rotulação");
	}
	$stmt->close();
	return $rate;
}

function setTransductiveData( $mysqli , $lpID ){
	$_SESSION['transductiveResetRate'] = getTransductiveResetRate ( $mysqli , $lpID );
	$lists = getAdjListsW ( $mysqli , $lpID );
	$_SESSION['listPDocTem'] 	= $lists[0];
	$_SESSION['listPTermDoc']	= $lists[1];
	setFdoc( $mysqli );
}

function unsetTransductiveData(){
	unset($_SESSION['listPDocTem']);
	unset($_SESSION['listPTermDoc']);
	unset($_SESSION['fDoc']);
	unset($_SESSION['transductiveResetRate']);
}

function setFdoc( $mysqli  ){
	$_SESSION['fDoc'] = getRankedLabelMatrix ( $mysqli , $_SESSION['cur_lpID']);
	normalize($_SESSION['fDoc']);
}

/**
* Steps one,two and three of the iterative algorithm (lpbhn)
*
* @param  ($mysqli) - mysqli object (MYSQL database connection) 
*/
function transductiveClassification ($mysqli) {
	
	$numberOfLabeledDocs =  $_SESSION['curDocID'] - $_SESSION['minDocID'];
	if($numberOfLabeledDocs % $_SESSION['transductiveResetRate'] == 0)
		setFdoc($mysqli);	//step three -- reseting first to consider user choices
	
	$fTerm  = matrixMultiplication ( $_SESSION['listPTermDoc'], $_SESSION['fDoc']);	//Step One
	$_SESSION['fDoc'] = matrixMultiplication ( $_SESSION['listPDocTem'], $fTerm);	//Step Two
	
}

/**
* Updating database(ranked labels) after an iteration of the transductive algorithm
*
* @param  ($mysqli) 		- mysqli object (MYSQL database connection) 
* @param  ($rankedLabels) 	- updated matrix of ranked labels ( doc x label --> accuracy)
*/
function updateRankedLabels ($mysqli, &$rankedLabels) {
	$query = "	UPDATE tbl_ranked_label 
				SET rLabel_accuracy = ? 
				WHERE rLabel_document = ? 
					AND rLabel_label = ?;";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('dsi',$accuracy,$docID,$label);
	foreach ($rankedLabels as $docID => $row){
		foreach ($row as $label => $accuracy){
			if(!$stmt->execute()){
				$stmt->close();
				$mysqli->rollback();
				setAlert("Erro ao atualizar dados do banco de dados");
				return;				
			}
		}
	}
	$stmt->close();
}

/**
* Given the current labelling process, recover the matrix of ranked labels
* (documents x classes). This matrix shows the probability (rank) of
* a label (class) for a given document. 
*
* @param  ($mysqli) 	- mysqli object (MYSQL database connection) 	
* @param  ($lpID) 		- current labelling process ID
* @return ($rankedLabels) - matrix of ranked labels
*/
function getRankedLabelMatrix ( $mysqli , $lpID ) {
	$rankedLabels = array();
	$query = "	SELECT *
					FROM tbl_ranked_label 
					WHERE rLabel_document IN
						(SELECT document_id 
						 FROM tbl_document 
						 WHERE document_process = ? ) ";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	if(!$stmt->execute()){
		$mysqli->rollback();
		setAlert("Erro ao recuperar dados do processo de rotulação");
		$stmt->close();
		return ;
	}
	$result = $stmt->get_result();

	while ($row = $result->fetch_row()) {
		//$row[0] --> documentID
		//$row[1] --> label(class)
		//$row[2] --> accuracy (probability or rank)
		$rankedLabels[$row[0]][$row[1]] = $row[2];
	}
	return $rankedLabels;
	
}

?>