<?php
include_once 'db_connect.php';
include_once 'functions.php';
include_once 'lpbhn.php';	//Using functions 'setTransductiveData' and 'unsetTransductiveData'
sec_session_start();

/**
*	Removing data stored in session
*/
function unsetData(){
	if( !empty($_SESSION['cur_lpAlgorithm']) && 
		($_SESSION['cur_lpAlgorithm'] == 'transductive' || $_SESSION['cur_lpAlgorithm'] == 'testMode'))
			unsetTransductiveData();
	
	unset($_SESSION['cur_lpID']);
	unset($_SESSION['cur_lpName']);
	unset($_SESSION['cur_lpMinAccRate']);
	unset($_SESSION['cur_lpMinFinalAccRate']);
	unset($_SESSION['cur_lpMultilabel']);
	unset($_SESSION['cur_lpType']);
	unset($_SESSION['cur_lpAlgorithm']);
	unset($_SESSION['curDocID']);
	unset($_SESSION['minDocID']);
	unset($_SESSION['maxDocID']);
	unset($_SESSION['lblOptionRank']);
	unset($_SESSION['cur_lpLabelingType']);
}

function getPostSetData($mysqli,$lpID){
	$_SESSION['cur_lpMinAccRate'] = 0;
	$query = "SELECT postset_suggestion_acceptance_rate 
				FROM tbl_labeling_process_postset
				WHERE postset_process = ?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	if($stmt->execute()){
		$stmt->bind_result($_SESSION['cur_lpMinAccRate']);
		$stmt->fetch();
	}else{
		setAlert("Houve erro ao recuperar dados do processo de rotulação");
	}
	$stmt->close();
}

/**
* Connects to database and updates the labeling process status
* to in_progress (as it was 'waiting' before)
*
* @param ($mysqli) 	- mysqli object (MYSQL database connection) 	
*/
function updateLPStatus($mysqli){
	//Updating process labeling status --> in_progress
	$query = "	UPDATE tbl_labeling_process_tagger 
					SET process_tagger_status = 'in_progress' 
					WHERE process_tagger_process = ? AND process_tagger_tagger = ?;";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('ii',$_SESSION['cur_lpID'],$_SESSION['user_id']);
	if(!$stmt->execute()){
		setAlert("Erro ao atualizar o status do processo de rotulação");
	}
	$stmt->close();
}

/**
* Connects to database and retrieves the labeling process' data
* Its instructions is printed and the rest is stored in session
*
* @param ($mysqli) 	- mysqli object (MYSQL database connection) 	
* @param ($lpID) 	- Labeling process ID 	
*/
function getInstructions($mysqli,$lpID) {
	
	$_SESSION['cur_lpName']  = $_SESSION['cur_lpAlgorithm'] = $_SESSION['cur_lpType']  =  $lpInst = "";
	$_SESSION['cur_lpMinFinalAccRate'] = $lpMultilabel  = 0;
	
	$query = "SELECT process_name, process_instructions, process_label_acceptance_rate, 
				process_multilabel, process_type, process_suggestion_algorithm, process_labeling_type
				FROM tbl_labeling_process 
				WHERE process_id = ? LIMIT 1" ;
				
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	if($stmt->execute()){
		$stmt->bind_result(	$_SESSION['cur_lpName'],
							$lpInst,
							$_SESSION['cur_lpMinFinalAccRate'],
							$lpMultilabel,
							$_SESSION['cur_lpType'],
							$_SESSION['cur_lpAlgorithm'],
							$_SESSION['cur_lpLabelingType']);
		$stmt->fetch();
		$_SESSION['cur_lpName'] =($_SESSION['cur_lpName']);
	}else{
		setAlert("Erro ao recuperar dados do banco de dados");
	}
	$stmt->close();
	
	if (isAlertEmpty()) {
		echo '<h3 align="center">Instruções para o processo de rotulação: ' . $_SESSION['cur_lpName'] . '</h3>';
		echo '<div align="center"><textarea readonly rows="18" cols="100" >'.utf8_decode($lpInst).'</textarea></div>';
		
		$_SESSION['cur_lpMultilabel'] 		= $lpMultilabel == 1 ? true : false;
		
		if( $_SESSION['cur_lpType'] == "postSet" ) getPostSetData($mysqli , $lpID);
		
		if($_SESSION['cur_lpAlgorithm'] 	== 'transductive' 
			|| $_SESSION['cur_lpAlgorithm'] == 'testMode'){
				setTransductiveData( $mysqli , $lpID );
		}
	}
}

/**
* Connects to database and check if the current labeling process
* was already concluded
*
* @param ($mysqli) 	- mysqli object (MYSQL database connection) 	
* @param ($lpID) 	- Labeling process ID 	
* @return 			- True if the status is 'concluded', false otherwise. 	
*/
function isLabelingProcessConcluded ($mysqli, $lpID){
	//Concluded process should not be presented
	$status = "";
	$query = "	SELECT process_tagger_status 
						FROM tbl_labeling_process_tagger 
						WHERE process_tagger_process = ? AND process_tagger_tagger = ?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('ii',$lpID,$_SESSION['user_id']);
	if($stmt->execute()){
		$stmt->bind_result($status);
		$stmt->fetch();
	}else{
		setAlert("Erro ao recuperar o status do processo de rotulação");
		header('Location: ./index.php');
		exit();
	}
	$stmt->close();
	return $status == 'concluded';
}

if ( isset($_GET['lpID'])){
	//Loading page
	if(isLabelingProcessConcluded ($mysqli, $_GET['lpID'])){
		header('Location: ./index.php');
		exit();		
	}else{
		unsetData();
		$_SESSION['cur_lpID'] = $lpID = $_GET['lpID'];
		if ( (isset($_GET['status'])) && ($_GET['status'] === 'waiting') ) updateLPStatus($mysqli);
	}
}else if(!empty($_POST["btnChangePage"])) {	
	//Form was submitted (loading next page)
	$nextPage = $_POST["btnChangePage"];
	if($nextPage == 'next'){
		//Initializes labeling process
		if($_SESSION['cur_lpLabelingType'] == 'normal')
			header('Location: ./labeling.php');
		else
			header('Location: ./ABLabeling.php');
	}else{
		//Navigation button was clicked
		unsetData();
		if($nextPage == 'home') header('Location: ./index.php');
		else if($nextPage == 'profile') header('Location: ./profile.php');
		else if($nextPage == 'manual') header('Location: ./help.php');
		else if($nextPage == 'logout') header('Location: ./include/logout.php');
	}
	exit();
}else{
	header('Location: ./index.php');
	exit();
}