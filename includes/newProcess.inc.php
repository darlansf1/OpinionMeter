<?php
include_once 'db_connect.php';
include_once 'functions.php';
include_once 'lpbhn.php';
include_once 'frequence.php';
include_once 'syntacticPatterns.php';

sec_session_start(); 

function isFormValid($mysqli) {
	//phpAlert("checking form-> login: ".(login_check($mysqli) == true).", user_id: ".isset($_SESSION['user_id']).", lpName: ".isset($_POST['lpName']));
	if((login_check($mysqli) == true) &&  isset($_SESSION['user_id']) && isset($_POST['lpName']))
		return true;
	return false;
}

function trainingSetContent(){
	if(!isset($_FILES['trainingSet']['tmp_name']))
		return false;
	$ts_tmpName  	= $_FILES['trainingSet']['tmp_name'];
	$ts_fp      	= fopen($ts_tmpName, 'r');
	$ts_content 	= addslashes(fread($ts_fp, filesize($ts_tmpName)));
	fclose($ts_fp);
	return $ts_content;
}

//Inserting a new Labeling Process on database
function insertLP ($mysqli) {		
	$lpName 					= $_POST['lpName'];	
	$uID 						= $_SESSION['user_id'];

	/*setting default values*/
	$trainingSet = trainingSetContent();
	
	$lpAspectSuggestionAlgorithm= 'none';
	$lpTranslatorUse = 0;
	$lpLanguage = "xx";
		
	$query = "";
	
	$lpAspectSuggestionAlgorithm = $_POST['lpAspectSuggestionAlgorithm'];
	$lpTranslatorUse = $_POST['translator'] == 'true' ? 1 : 0;
	$lpLanguage = $_POST['language'];
	
	$lpName =($lpName);
	
	if($trainingSet === false){
		$query = "INSERT INTO tbl_labeling_process
		(`process_name`, `process_admin`,`process_aspect_suggestion_algorithm`, `process_translator`, `process_language`) 
		VALUES ( ? , ? , ? , ? , ?)";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('sisis',$lpName,$uID,$lpAspectSuggestionAlgorithm, $lpTranslatorUse, $lpLanguage);
	}else{
		$trainingSet = utf8_encode($trainingSet);
		$query = "INSERT INTO tbl_labeling_process
		(`process_name`, `process_admin`,`process_aspect_suggestion_algorithm`, `process_translator`, `process_language`, `process_training_set`) 
		VALUES ( ? , ? , ? , ? , ?, ?)";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('sisiss',$lpName,$uID,$lpAspectSuggestionAlgorithm, $lpTranslatorUse, $lpLanguage, $trainingSet);
	}
		
	if(!$stmt->execute()){
		echo $mysqli->error;
		$stmt->close();
		$mysqli->rollback();
		setAlert("Error inserting process' data into the database");
		return;
	}
	
	$lpID = $stmt->insert_id;
	$_SESSION['cur_lpName'] = $lpName;
	$_SESSION['cur_lpID'] = $lpID;
	$stmt->close();
	
	return $lpID;
}

function insertClassifiers($mysqli, $lpID){
	$classifiers = $_POST['polarityClassifier'];
	$query = "INSERT INTO tbl_used_algorithm (`ua_lp`, `ua_algorithm`) VALUES ( ? , ?)";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('is',$lpID,$classifierName);
	$update_hits = false;
	
	foreach($classifiers as $classifier){
		$classifierName = $classifier;
		
		if($classifier == "PMIBased"){
			$update_hits = true;
		}
		if(!$stmt->execute()){
			echo $mysqli->error;
			$stmt->close();
			$mysqli->rollback();
			setAlert("Error setting up the data");
			return;
		}
	}
	
	$stmt->close();
	
	if($update_hits){
		update_hits($mysqli);
	}
}

//Inserting documents on Database
function insertDocuments ( $mysqli , $lpID ) {
	
	$query = 	"INSERT INTO `tbl_document`(`document_process`, `document_text`, 
						`document_name`,`document_size`) VALUES (?,?,?,?)";
	$stmt = $mysqli->prepare($query);	
	$stmt->bind_param("issi",$lpID,$file_content,$file_name,$file_size);
	$counts = array();
	
	//Uploading documents
	foreach($_FILES['lpDocs']['tmp_name'] as $index => $tmp_name){
		$file_name 		= 	($_FILES['lpDocs']['name'][$index]);
		$file_tmpName 	=	$_FILES['lpDocs']['tmp_name'][$index];
		$file_size 		= 	$_FILES['lpDocs']['size'][$index];
		$file_fp      	= 	fopen($file_tmpName, 'r');
		$file_text		=	fread($file_fp, filesize($file_tmpName)+1);
		$file_content	=	utf8_encode(addslashes($file_text));
		fclose($file_fp);
		if(!get_magic_quotes_gpc()) {  $file_name = utf8_encode(addslashes($file_name)); }
		if(!$stmt->execute()){
			echo $mysqli->error;
			$mysqli->rollback();
			setAlert("Error adding document to the database: " .$file_name);
			break;
		}
		
		$docID = $stmt->insert_id;
	}
	
	$stmt->close();	
}

if ( isFormValid($mysqli) ) {
	$mysqli->autocommit(FALSE);
	$mysqli->commit();
	
	$lpID = insertLP ($mysqli);
	
	if(isAlertEmpty()){
		insertDocuments ( $mysqli , $lpID );
		insertClassifiers($mysqli , $lpID);
	}
	
	$mysqli->commit();
	
	//If this process uses the frequence-based algorithm to predict aspects, calculate word frequence
	if($_POST['lpAspectSuggestionAlgorithm'] == 'frequenceBased'){
		//phpAlert("RotuLabic is going to calculate word frequences. This might take a little while.");
		$result = calculateFrequency($_POST['language'], 'maxent', $_POST['translator'], $lpID, $mysqli, $_POST['min_frequency']);

		$classifiers = $_POST['polarityClassifier'];
		
		//If this process uses the PMI algorithm to predict polarity, calculate PMIs
		if(in_array('PMIBased', $classifiers)){	
			findPatterns($_POST['language'],  $_POST['translator'], $result,$lpID, $mysqli);
		}
	}
	
	$mysqli->commit();
	$mysqli->autocommit(TRUE);
	
	if (isAlertEmpty()) {
		header('Location: ./applyAlgorithms.php');
		exit();
	}
	
} 
?>