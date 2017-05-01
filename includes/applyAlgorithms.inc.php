<?php
include_once 'db_connect.php';
include_once 'functions.php';
include_once 'lpbhn.php'; 
include_once 'suggestion.php';

sec_session_start();

/**
* If there is an error during the rotulation process,
* then the system rollbacks the database, takes user to index page and
* presents an error message
*
* @param  ($mysqli) - mysqli object (MYSQL database connection) 
*/
function dbError($mysqli) {
	$mysqli->rollback();
	$mysqli->autocommit(TRUE);
	echo mysqli_error($mysqli);
	setAlert("Fatal error when trying to retrieve information");
	header('Location: ./index.php');
	exit();
}


/**
* Outputs a progress bar that indicates how much of the labeling
* process the tagger has completed
*/
function showProgressBar(){
	if((isset($_SESSION['curDocID'])) 
	&& (isset($_SESSION['minDocID'])) 
	&& (isset($_SESSION['maxDocID']))){
		
	$totalDocs 	= $_SESSION['maxDocID'] - $_SESSION['minDocID']+1;
	$completed 	= $_SESSION['curDocID'] - $_SESSION['minDocID'];
	$progress	= (int) ($completed * 100 / $totalDocs) ;
	
	echo 	"<div class='container' style='padding:0'>
				<div class = 'row'>
					<div class='progress col-xs-6 col-sm-4 col-centered' style='padding:0'>".
						"<div class='progress-bar' role='progressbar'".
							" aria-valuenow='" . $_SESSION['curDocID'] . "' " . 
							" aria-valuemin='" . $_SESSION['minDocID'] . "' " . 
							" aria-valuemax='" . $_SESSION['maxDocID'] . "' " . 
							" style='width:" .$progress. "%'>".
								$progress. "% complete". 
						'</div>'.
					'</div>'.
				'</div>'.
			'</div>';
	}	
}

/**
* Each document has an ID, which is created in a sequential manner (3,4,5...)
* Then, each labeling process has n documents and each one of this
* Has an ID inside a range, which is discovered
* and saved as minDocID and maxDocID (in session)
* Obs.: maxDocID  - minDocID = n
*
* @param ($mysqli) 	- mysqli object (MYSQL database connection) 	
* @param ($lpID) 	- current labeling process id
*/
function getLPDocRange ($mysqli, $lpID) {
	$query = "	SELECT MIN(document_id), MAX(document_id) 
					FROM tbl_document 
					WHERE document_process = ?" ;
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	if($stmt->execute()){
		$stmt->bind_result($_SESSION['minDocID'],$_SESSION['maxDocID']);
		$stmt->fetch();
		$stmt->close();
	}else{
		$stmt->close();
		dbError($mysqli);
	}
}

/**
* Discovers which document should be the first one
* to be presented (that is, the first with status 'waiting')
* and stores it in session as curDocID. 
* If there is no document with this status, then the curDocID
* is set to the first document of this labeling process
*
* @param ($mysqli) 	- mysqli object (MYSQL database connection) 	
*/
function getFirstDocument ($mysqli) {
	$query = "	SELECT MIN(document_id) 
					FROM tbl_document 
					WHERE document_process = ?" ;
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$_SESSION['cur_lpID']);
	if($stmt->execute()){
		$stmt->bind_result($_SESSION['curDocID']);
		$stmt->fetch();
		$stmt->close();
	}else{
		$stmt->close();
		dbError($mysqli);
	}

	if( empty ( $_SESSION['curDocID'] ) ) $_SESSION['curDocID'] = $_SESSION['minDocID'];
}

/**
* Gets current document name and text
*
* @param  ($mysqli) 	- mysqli object (MYSQL database connection)
* @return - array with the desired data 	
*/
function getDocumentInfo ($mysqli) {
	$docName = $docText = "";
	$query = "	SELECT document_name, document_text 
					FROM tbl_document 
					WHERE document_id = ? LIMIT 1" ;
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$_SESSION['curDocID']);
	if($stmt->execute()){
		$stmt->bind_result($docName,$docText);
		$stmt->fetch();
		$stmt->close();
		$docName = utf8_decode($docName);
		$docText = utf8_decode($docText);
	}else{
		$stmt->close();
		dbError($mysqli);
		return;
	}
	$docName = substr ($docName,0,-4); //Removing ".txt"

	return array ($docName, $docText);
	
}

function applyAlgorithms($mysqli){
	$lpID = $_SESSION['cur_lpID'];
	$docID = $_SESSION['curDocID'];
	$aspects = getAspectSuggestions($mysqli);
	$polarities = getAspectPolarities($mysqli, $aspects);
	$query = "INSERT INTO tbl_aspect (`aspect_doc`, `aspect_lp`, `aspect_aspect`,
										`aspect_polarity`, `aspect_polarity_alg`, `aspect_start`, `aspect_end`) 
										VALUES (? , ?, ?, ?, ?, ?, ?)";
	$stmt = $mysqli->prepare($query);
	#echo var_dump($query);
	#echo var_dump($stmt);
	$stmt->bind_param('iisssii',$docID,$lpID,$aspect,$polarity,$algorithm, $start,$end);
	
	for($i = 0; $i < count($aspects[0]); $i++){
		$aspect = $aspects[0][$i];
		$start = $aspects[1][$i];
		$end = $start+strlen($aspect);
		
		for($j = 0; $j < count($polarities); $j++){
			if(count($polarities[$j][1]) == 0 || count($polarities[$j][1]) != count($aspects[0]))
				continue;
			$algorithm = $polarities[$j][0];
			$polarity = $polarities[$j][1][$i];
			
			if(!$stmt->execute()){
				echo $mysqli->error;
				$stmt->close();
				$mysqli->rollback();
				setAlert("Error saving results.");
				$stmt->close();
				return;
			}
		}
	}
	
	$stmt->close();
}

//Rollback point
$mysqli->autocommit(FALSE);$mysqli->commit();

if (isset($_SESSION['cur_lpID'])){
	$lpID = $_SESSION['cur_lpID'];
	if(!(isset($_SESSION['minDocID']) && isset($_SESSION['maxDocID']))){
		getLPDocRange($mysqli, $lpID);	//Setting minDocId and maxDocId
	}

	if(!isset($_SESSION['curDocID'])){
		getFirstDocument ($mysqli);		//Setting current document ID
		applyAlgorithms($mysqli);
	}else if($_SESSION['curDocID'] < $_SESSION['maxDocID']){
		$_SESSION['curDocID'] = $_SESSION['curDocID'] + 1;
		applyAlgorithms($mysqli);
	}else{
		$mysqli->commit();
		$mysqli->autocommit(TRUE);
		header('Location: ./processInfo.php');
		exit();
	}	
	$mysqli->commit();
	$mysqli->autocommit(TRUE);
	//Finally, Getting current document name and text
	$docName = $docText = "";
	list ($docName, $docText) = getDocumentInfo ($mysqli);
	
	//$mysqli->commit();$mysqli->autocommit(TRUE);
}else {
	$mysqli->commit();$mysqli->autocommit(TRUE);
	header('Location: ./processInfo.php');
	exit();
}