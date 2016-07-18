<?php
include_once 'db_connect.php';
include_once 'functions.php';
include_once 'lpbhn.php';

sec_session_start();
unsetLabelingProcessData();

function printLPInfo ( $mysqli, $lpInfo, $algorithms) {
	$lpID = $lpInfo['process_id'];
	
	$alg = $lpInfo['process_aspect_suggestion_algorithm'];
	$lpInfo['process_name'] =($lpInfo['process_name']);
	
	
	
	echo "	<div class='row' align='center'  >
				<div class='col-md-6' style= 'border:1px solid #ddd;padding:20px;'>
					<h2>Process Information</h2>
					<div class='panel panel-primary'>
						<div class='panel-heading text-center'>
							<h1 class='panel-title'>Process Name: ".$lpInfo['process_name']."</h1>
						</div>
					
						<table class='table table-hover table-bordered  table-condensed' >";
						echo "<tr>
								<td>Number of documents</td>
								<td>". $lpInfo['numberOfDocs'] ."</td>
							</tr>";
					echo 	"<tr>
								<td>Aspect Identification Algorithm</td>
								<td>". getEnglishAlgorithm($lpInfo['process_aspect_suggestion_algorithm'])."</td>
							</tr>";
					for($i = 0; $i < count($algorithms); $i++){
						$aux = $i+1;
						echo "<tr>
								<td>Sentiment Classification Algorithm ".$aux."</td>
								<td>". getEnglishAlgorithm($algorithms[$i])."</td>
							</tr>";
					}
					echo	"<tr>
								<td>Using Automated Translator</td>
								<td>".(($lpInfo['process_translator'] == 1)? "Yes":"No")."</td>
							</tr>
							<tr>
								<td>Language of the documents</td>
								<td>".getEnglishIdiom($lpInfo['process_language'])."</td>
							</tr>";	
	
	echo	"			</table>";
	
	echo 				"<form action='includes/download.php' class=\"form-horizontal\" method='POST'>
							<h2>Download results</h2>
							<a href=\'http://alt.qcri.org/semeval2016/\'>SemEval</a>-like XML files
							<input type=\"hidden\" name=\"lpID\" value='$lpID'>;
							<div class=\"form-group\">
								<input type=\"Submit\" class='btn btn-default ' value=\"Download\"/>
							</div>
						</form>";
	
	echo	"		</div>
				</div>
			</div>";
	
	
}

function getOrderedAspects($mysqli, $lpID){
	$query = "SELECT aspect_aspect, count(*) as frequence FROM tbl_aspect 
				WHERE aspect_lp = ? GROUP BY aspect_aspect ORDER BY frequence DESC,aspect_aspect ASC";
				
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i', $lpID);
	$aspects = array();
	if($stmt->execute()){
		$result = $stmt->get_result();
		while($data = mysqli_fetch_row($result)){
			array_push($aspects, $data[0]);
		}
	}else{
		phpAlert("Error retrieving process information");
	}
	$stmt->close();
	return $aspects;
}

function getAspects($mysqli, $lpID, $toEcho){
	$allaspects = array();
	$query = "SELECT aspect_aspect, aspect_polarity FROM `tbl_aspect` 
				WHERE aspect_polarity_alg = ? AND aspect_lp = ? ORDER BY aspect_aspect ASC";
				
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('si', $algorithm, $lpID);
	
	$algorithms = getAlgorithms($mysqli, $lpID);
	$i = 0;
	
	foreach($algorithms as $algorithm){
		array_push($allaspects, array());
		if($stmt->execute()){
			$result = $stmt->get_result();
			while($data = mysqli_fetch_row($result)){
				array_push($allaspects[$i], $data[0]);
				array_push($allaspects[$i], $data[1]);
			}
		}else{
			setAlert("Error retrieving process information");
		}
		$i++;
	}
	$stmt->close();
	
	$counts = array();
	$negatives = array();
	$positives = array();
	$neutrals = array();
	$aspects = array();
	
	for($i = 0; $i < count($algorithms); $i++){
		array_push($counts, array());
		array_push($negatives, array());
		array_push($positives, array());
		array_push($neutrals, array());
		array_push($aspects, array());
		
		$last = '';
		for($j = 0; $j < count($allaspects[$i]); $j+=2){
			if($last != $allaspects[$i][$j]){
				array_push($counts[$i], 0);
				array_push($negatives[$i], 0);
				array_push($positives[$i], 0);
				array_push($neutrals[$i], 0);
				array_push($aspects[$i], $allaspects[$i][$j]);
			}
			$last = $allaspects[$i][$j];
			$polarity = $allaspects[$i][$j+1];
			$index = count($counts[$i])-1;
			$counts[$i][$index]++;
			if($polarity == 'NEGATIVE')
				$negatives[$i][$index]++;
			else if ($polarity == 'POSITIVE')
				$positives[$i][$index]++;
			else
				$neutrals[$i][$index]++;
		}
	}
	
	$ret_aspects = array();
	$str = '[';
	
	if(count($aspects) > 0){
		arsort($counts[0]);
		$keys = array_keys($counts[0]);
	}
	
	for($i = 0; $i < count($aspects); $i++){
		$str .= '[ ';
		foreach($keys as $j){
			if($i == 0)
				array_push($ret_aspects, $aspects[$i][$j]);
			$str .= '"'.$aspects[$i][$j].'"'.',';
			$str .= $counts[$i][$j].',';
			$str .= $negatives[$i][$j].',';
			$str .= $positives[$i][$j].',';
			$str .= $neutrals[$i][$j].',';
		}
		$str = substr($str, 0, strlen($str)-1).']';
		if($i+1 < count($aspects))
			$str.=',';
	}
	$str .= ']';
	
	if($toEcho)
		echo $str;
	else
		return $ret_aspects;
}

function getLPInfo($mysqli,$lpID) {
	$query = "	SELECT *
					FROM tbl_labeling_process 
					WHERE process_id = ? LIMIT 1" ;
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	if($stmt->execute()){
		$result = $stmt->get_result();
		$lpInfo = $result->fetch_assoc();
	}else{
		setAlert("Error retrieving data from database");
	}
	$stmt->close();
	
	$query = "	SELECT COUNT(*)
					FROM tbl_document 
					WHERE document_process = ?" ;
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	if($stmt->execute()){
		$stmt->bind_result($result);
		$stmt->fetch();
		$lpInfo['numberOfDocs'] = $result;
	}else{
		setAlert("Error retrieving data from database");
	}
	$stmt->close();
	
	$algorithms = array();
	$algorithms = getAlgorithms($mysqli, $lpID);
	
	if (isAlertEmpty()) {
		printLPInfo ( $mysqli, $lpInfo, $algorithms);
	}
}

if ( !empty($_GET['lpID'])){
	$lpID = $_GET['lpID'];
}else{
	header('Location: ./index.php');
	exit();
}