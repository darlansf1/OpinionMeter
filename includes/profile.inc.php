<?php
include_once 'psl-config.php';
include_once 'lpbhn.php';	//Using functions 'setTransductiveData' and 'unsetTransductiveData'

/**
* Connects to database and retrieves the number of
* documents that were labeled by the tagger (current user)
*
* @param ($mysqli) 	- mysqli object (MYSQL database connection) 	
* @return ($answer) - Number of labeling processes
*/ 
function getNumberOfLabeledDocument($mysqli) {
	$answer = 0;
    $query = "SELECT COUNT(*) FROM tbl_document_labeling 
				WHERE labeling_status = 'labeled' 
					AND labeling_tagger = ?" ;
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$_SESSION['user_id']);
	if($stmt->execute()){
		$stmt->bind_result($answer);
		$stmt->fetch();
	}
	$stmt->close();
	return $answer;
}

/**
* Connects to database and retrieves the number of
* labeling processes that were assigned to the user
*
* @param ($mysqli) 	- mysqli object (MYSQL database connection) 	
* @return ($answer) - Number of labeling processes
*/
function getNumberOfLabelingProcess($mysqli) {
	$answer = 0;
    $query = "SELECT COUNT(*) FROM tbl_labeling_process_tagger 
				WHERE process_tagger_tagger = ?" ;
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$_SESSION['user_id']);
	if($stmt->execute()){
		$stmt->bind_result($answer);
		$stmt->fetch();
	}
	$stmt->close();
	return $answer;
}

/**
* Connects to database and retrieves the number of
* labeling processes that were concluded by the current user
*
* @param ($mysqli) 	- mysqli object (MYSQL database connection) 	
* @return ($answer) - Number of concluded labeling processes
*/
function getNumberOfConcludedLabelingProcess($mysqli) {
	$answer = 0;
    $query = "SELECT COUNT(*) FROM tbl_labeling_process_tagger 
				WHERE process_tagger_tagger = ? 
					AND process_tagger_status = 'concluded'";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$_SESSION['user_id']);
	if($stmt->execute()){
		$stmt->bind_result($answer);
		$stmt->fetch();
	}
	$stmt->close();
	return $answer;
}

/**
* Connects to database and retrieves the email of the
* current user
*
* @param ($mysqli) 	- mysqli object (MYSQL database connection) 	
* @return ($email) 	- Email of the current user
*/
function getUserEmail($mysqli) {
	$email = "";
    $query = "SELECT user_email FROM tbl_user
				WHERE user_id = ?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$_SESSION['user_id']);
	if($stmt->execute()){
		$stmt->bind_result($email);
		$stmt->fetch();
	}
	$stmt->close();
	return $email;
}

?>