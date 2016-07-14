<?php
include_once 'db_connect.php';
include_once 'functions.php';
include_once 'lpbhn.php';

sec_session_start();

function adjustFileName($fileName){
	$fileName = iconv("UTF-8","CP860//IGNORE", $fileName); //Char-set problem -- converting to Portuguese language
	$fileName = stripslashes($fileName);	//Removing slashes, which causes problem when we create the file
	return $fileName;
}

function download($zipFileName){
	$zipped_size = filesize($zipFileName);
	header("Content-Description: File Transfer");
	header("Content-type: application/zip"); 
	header("Content-Type: application/force-download");
	header("Content-Disposition: attachment; filename=$zipFileName");
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header("Content-Length:". " $zipped_size");
	ob_clean();
	flush();
	readfile("$zipFileName");			
	array_map('unlink', glob("*.xml")); //Deleting documents on server
	unlink("$zipFileName"); 			//Deleting zip file on server
}

function getAspectsPerDoc($mysqli, $algorithm){
	$documents = array();
	$query = "";

	$info = "";
	
	$word = array(); $start = array(); $polarity = array(); $document = array(); $type = array(); $id = array();
	
	$query = "SELECT aspect_aspect, aspect_start, aspect_polarity, aspect_doc, aspect_number
					FROM tbl_aspect 
					WHERE aspect_lp = ? AND aspect_polarity_alg = ? ORDER BY aspect_doc"; 
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('is',$_POST['lpID'], $algorithm);
	
	if($stmt->execute()){
		$result = $stmt->get_result();
		while($data = mysqli_fetch_row($result)){
			array_push($word, $data[0]);
			array_push($start, $data[1]);
			array_push($polarity, $data[2]);
			array_push($document, $data[3]);
			array_push($id, $data[4]);
		}
	}else{
		setAlert("Erro ao acessar banco de dados");
	}
	$stmt->close();
	
	//Grouping by document
	$docAspects = array(); $docStarts = array(); $docPolarities = array(); $docIds = array(); $k = -1; $docAspectIds = array();
	for($i = 0; $i < count($word); $i++){
		if($i == 0 || ($i != 0 && $document[$i-1] != $document[$i])){
			array_push($docAspects, array());
			array_push($docStarts, array());
			array_push($docPolarities, array());
			array_push($docAspectIds, array());
			array_push($docIds, $document[$i]);
			
			$k++;
		}
		
		array_push($docAspects[$k], $word[$i]);
		array_push($docAspectIds[$k], $id[$i]);
		array_push($docStarts[$k], $start[$i]);
		array_push($docPolarities[$k], $polarity[$i]);
	}
	
	$docsWithNoAspects = array();
	$query = "SELECT document_id, document_name
					FROM tbl_document
					WHERE document_process = ?"; 
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$_POST['lpID']);
		
	if($stmt->execute()){
		$result = $stmt->get_result();
		while($data = mysqli_fetch_row($result)){
			if((array_search($data[0], $docIds)) === false)
				array_push($docsWithNoAspects, $data[1]);
		}
	}else{
		setAlert("Error retrieving data from database");
	}
	$stmt->close();
	
	for($i = 0; $i < count($docIds); $i++){

		$query = "SELECT document_name
					FROM tbl_document
					WHERE document_id = ?"; 
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('i',$docIds[$i] );
		
		if($stmt->execute()){
			$stmt->bind_result($docName);
			$stmt->fetch();
			$docName =utf8_decode($docName);
		}else{
			setAlert("Error retrieving data from database");
		}
		$stmt->close();
	
		array_push($documents, array());
		array_push($documents[$i], $docName);
		array_push($documents[$i], "");
		
		for($j = 0; $j < count($docAspects[$i]); $j++){
			$k = $j;
			
			$currentWord = $docAspects[$i][$k];
			$currentStart = $docStarts[$i][$k];
			$currentPolarity = $docPolarities[$i][$k];
			$currentEnd = $currentStart+strlen($currentWord);
			
			$documents[$i][1] = $documents[$i][1]."\r\n\t<aspectTerm term=\"".$currentWord."\" polarity=\"".$currentPolarity."\" from=\"".$currentStart."\" to=\"".$currentEnd."\"/>";
		}

		//phpAlert($documents[$i][1]);
		$documents[$i][1] = "<aspectTerms>".$documents[$i][1]."\r\n</aspectTerms>";
	}
	
	foreach($docsWithNoAspects as $doc){
		$index = count($documents);
		array_push($documents, array());
		array_push($documents[$index], $doc);
		array_push($documents[$index], "<aspectTerms>\r\n</aspectTerms>");
	}

	return $documents;
}

function downloadResults($mysqli, $lpID){
    $zip = new ZipArchive();
	$zipFileName = "results.zip";
	
    if ($zip->open($zipFileName, ZIPARCHIVE::CREATE )!== TRUE) {
		setAlert("Error creating zip file");
		return;
    }
	
	$algs = getAlgorithms($mysqli, $lpID);
	
	foreach($algs as $algorithm){
		$docs = getAspectsPerDoc($mysqli, $algorithm);
		echo($algorithm);
		foreach($docs as $doc) {
			//Adding text files(labeled documents) to zip archive
			$fileName = adjustFileName($doc[0]);
			$fileName = $algorithm.'_'.substr($fileName, 0, strlen($fileName)-4).".xml";
			//echo($fileName.": ".$doc[1]);
			file_put_contents($fileName, $doc[1]);	//Creating and adding content to the file
			$zip->addFile($fileName);				//Inserting file on zip archive
		}
	}
	
    $zip->close();
	download($zipFileName);		//Adjust header and download zip file
    exit;

}

if(	(login_check($mysqli)) && (!empty($_POST['lpID']))){
	$lpID = $_POST['lpID'];
	downloadResults($mysqli, $lpID);
}else {
	setAlert("Error downloading results");
	header('Location: ../index.php');
	exit();
}