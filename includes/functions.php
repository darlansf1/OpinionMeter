<?php
include_once 'psl-config.php';
include_once 'lpbhn.php';
 
function sec_session_start() {
    $session_name = 'sec_session_id';   // Set a custom session name
    $secure = SECURE;
    // This stops JavaScript being able to access the session id.
    $httponly = true;
    // Forces sessions to only use cookies.
    if (ini_set('session.use_only_cookies', 1) === FALSE) {
        header("Location: ../error.php?err=Could not initiate a safe session (ini_set)");
        exit();
    }
    // Gets current cookies params.
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params($cookieParams["lifetime"],
        $cookieParams["path"], 
        $cookieParams["domain"], 
        $secure,
        $httponly);
    // Sets the session name to the one set above.
    session_name($session_name);
    session_start();            // Start the PHP session	
    session_regenerate_id(true);    // regenerated the session, delete the old one. 
}

function login($email, $password, $mysqli) {
    // Using prepared statements means that SQL injection is not possible. 
    if ($stmt = $mysqli->prepare("SELECT user_id, user_name, user_password, user_salt , user_role
        FROM tbl_user
        WHERE user_email = ?
        LIMIT 1")) {
        $stmt->bind_param('s', $email);  // Bind "$email" to parameter.
        $stmt->execute();    // Execute the prepared query.
        $stmt->store_result();
 
        // get variables from result.
        $stmt->bind_result($user_id, $username, $db_password, $salt, $user_role);
        $stmt->fetch();
 
        // hash the password with the unique salt.
        $password = hash('sha512', $password . $salt);
        if ($stmt->num_rows == 1) {
            // If the user exists we check if the account is locked
            // from too many login attempts 
 
            if (checkbrute($user_id, $mysqli) == true) {
                // Account is locked 
                // Send an email to user saying their account is locked
                return false;
            } else {
                // Check if the password in the database matches
                // the password the user submitted.
                if ($db_password == $password) {
					setAlert("");
					
                    // Password is correct!
                    // Get the user-agent string of the user.
                    $user_browser = $_SERVER['HTTP_USER_AGENT'];
                    
					// XSS protection as we might print this value
                    $user_id = preg_replace("/[^0-9]+/", "", $user_id);
                    $_SESSION['user_id'] = $user_id;
                    
					// XSS protection as we might print this value
                    $username = preg_replace("/[^a-zA-Z0-9_\-]+/","",$username);
                    $_SESSION['username'] = $username;

					// XSS protection as we might print this value
                    $user_role = preg_replace("/[^a-zA-Z0-9_\-]+/","",$user_role);
                    $_SESSION['user_role'] = $user_role;
					
                    $_SESSION['login_string'] = hash('sha512',$password . $user_browser);
                    // Login successful.
                    return true;
                } else {
                    // Password is not correct
                    // We record this attempt in the database
                    $now = time();
                    $mysqli->query("INSERT INTO tbl_login_attempts(la_user, la_time)
                                    VALUES ('$user_id', '$now')");
                    return false;
                }
            }
        } else {
            // No user exists.
            return false;
        }
    }
}

function checkbrute($user_id, $mysqli) {
    // Get timestamp of current time 
    $now = time();
 
    // All login attempts are counted from the past 2 hours. 
    $valid_attempts = $now - (2 * 60 * 60);
 
    if ($stmt = $mysqli->prepare("SELECT la_time 
                            FROM tbl_login_attempts 
                            WHERE la_user = ? 
                            AND time > '$valid_attempts'")) {
        $stmt->bind_param('i', $user_id);
 
        // Execute the prepared query. 
        $stmt->execute();
        $stmt->store_result();
 
        // If there have been more than 5 failed logins 
        if ($stmt->num_rows > 5) {
            return true;
        } else {
            return false;
        }
    }
}

function login_check($mysqli) {
    // Check if all session variables are set 
    if (isset($_SESSION['user_id'], 
                        $_SESSION['username'], 
                        $_SESSION['login_string'])) {
 
        $user_id = $_SESSION['user_id'];
        $login_string = $_SESSION['login_string'];
        $username = $_SESSION['username'];
 
        // Get the user-agent string of the user.
        $user_browser = $_SERVER['HTTP_USER_AGENT'];
 
        if ($stmt = $mysqli->prepare("SELECT user_password 
                                      FROM tbl_user 
                                      WHERE user_id = ? LIMIT 1")) {
            // Bind "$user_id" to parameter. 
            $stmt->bind_param('i', $user_id);
            $stmt->execute();   // Execute the prepared query.
            $stmt->store_result();
 
            if ($stmt->num_rows == 1) {
                // If the user exists get variables from result.
                $stmt->bind_result($password);
                $stmt->fetch();
                $login_check = hash('sha512', $password . $user_browser);
 
                if ($login_check == $login_string) {
                    // Logged In!!!! 
                    return true;
                } else {
                    // Not logged in 
                    return false;
                }
            } else {
                // Not logged in 
                return false;
            }
        } else {
            // Not logged in 
            return false;
        }
    } else {
        // Not logged in 
        return false;
    }
}

function esc_url($url) {
 
    if ('' == $url) {
        return $url;
    }
 
    $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);
 
    $strip = array('%0d', '%0a', '%0D', '%0A');
    $url = (string) $url;
 
    $count = 1;
    while ($count) {
        $url = str_replace($strip, '', $url, $count);
    }
 
    $url = str_replace(';//', '://', $url);
 
    $url = htmlentities($url);
 
    $url = str_replace('&amp;', '&#038;', $url);
    $url = str_replace("'", '&#039;', $url);
 
    if ($url[0] !== '/') {
        // We're only interested in relative links from $_SERVER['PHP_SELF']
        return '';
    } else {
        return $url;
    }
}

function phpAlert($msg) {
	echo '<script type="text/javascript">alert("' . $msg . '")</script>';
}

function setAlert($msg) {
	$_SESSION['alert_msg'] = $msg;
}

function isAlertEmpty() {
	if(empty($_SESSION['alert_msg']))	return true;
	return false;
}

function showAlert() {
	if (!isAlertEmpty()) { 
		phpAlert( $_SESSION['alert_msg'] );
		setAlert("");
	}		
}

function getPortugueseStatus ($status) {
	switch ($status) {
		case "concluded" 		:return "Concluído";
		case "draft" 			:return "Esboço";
		case "in_analysis" 		:return "Em análise";
		case "in_progress" 		:return "Em andamento";
		case "waiting" 			:return "Aguardando";
		case "labeled" 			:return "Rotulado";
		case "skipped" 			:return "Ignorado";
		case "finalized" 		:return "Finalizado";
	}
	return "Indefinido";
}

function getEnglishStatus ($status) {
	switch ($status) {
		case "concluded" 		:return "Concluded";
		case "draft" 			:return "Draft";
		case "in_analysis" 		:return "In Analysis";
		case "in_progress" 		:return "In Progress";
		case "waiting" 			:return "Waiting";
		case "labeled" 			:return "Labeled";
		case "skipped" 			:return "Skipped";
		case "finalized" 		:return "Finalized";
	}
	return "Undefined";
}

function getPortugueseIdiom($idiom) {
	switch ($idiom) {
		case "pt" 		:return "Português";
		case "en" 		:return "Inglês";
	}
	return "Indefinido";
}

function getEnglishIdiom($idiom) {
	switch ($idiom) {
		case "pt" 		:return "Portuguese";
		case "en" 		:return "English";
		case "es"		:return "Spanish";
		case "de"		:return "German";
		case "fr" 		:return "French";
		case "it" 		:return "Italian";
		case "bs-Latn" 	:return "Bosnian (Latin)";
		case "ca" 		:return "Catalan";
		case "hr" 		:return "Croatian";
		case "cs" 		:return "Czech";
		case "da" 		:return "Danish";
		case "nl" 		:return "Dutch";
		case "et" 		:return "Estonian";
		case "fi" 		:return "Finnish";
		case "ht" 		:return "Haitian Creole";
		case "hu" 		:return "Hungarian";
		case "id" 		:return "Indonesian";
		case "lv"		:return "Latvian";
		case "lt"		:return "Lithuanian";
		case "ms" 		:return "Malay";
		case "mt" 		:return "Maltese";
		case "no" 		:return "Norwegian";
		case "pl" 		:return "Polish";
		case "ro" 		:return "Romanian";
		case "sr-Latn" 	:return "Serbian (Latin)";
		case "sk" 		:return "Slovak";
		case "sv" 		:return "Swedish";
		case "tr" 		:return "Turkish";
		case "vi" 		:return "Vietnamese";
		case "cy" 		:return "Welsh";
	}
	return "Undefined";
}

function getPortugueseAlgorithm($alg) {
	switch ($alg) {
		case "mostVoted" 			:return "Mais Votado";
		case "random" 				:return "Aleatório";
		case "testMode" 			:return "Modo de teste";
		case "transductive" 		:return "Transdutivo";
		case "none"					:return "Nenhum";
		case "previouslyIdentified" :return "Previamente identificados";
	}
	return "Indefinido";
}

function processWord($text){

	$i = 0;
	$regex = "/^[0-9.,\\b\\s\\n\\~\\!\\@\\#\\$\\%\\^\\&\\*\\(\\)\\_\\+\\=\\-\\[\\]\\{\\}\\;\\:\\'\\\"\\\\\\/\\<\\>\\?]+$/";
	while(preg_match($regex, substr($text, $i, 1))){
		$i++;
		if($i == strlen($text))
			break;
	}
				
	$j = strlen($text);
	
	while(preg_match($regex, substr($text, $j-1, 1))){
		$j--;
		if($j == 0)
			break;
	}
	
	return substr($text, $i, $j-$i);
}

function getCurrDocText($mysqli){
	$docID = $_SESSION['curDocID'];
	
	$docText = "";
	$query = "	SELECT document_text 
					FROM tbl_document 
					WHERE document_id = ? LIMIT 1" ;
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$docID);
	if($stmt->execute()){
		$stmt->bind_result($docText);
		$stmt->fetch();
		$docText = strtoupper((utf8_decode(stripslashes($docText))));
	}else{
		dbError($mysqli);
	}
	
	$stmt->close();
	return $docText;
}

function getAlgorithms($mysqli,$lpID){
	$query = "SELECT ua_algorithm
				FROM tbl_used_algorithm
				WHERE ua_lp = ?";
				
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	$algorithms = array();
	if($stmt->execute()){
		$result = $stmt->get_result();
		while($data = mysqli_fetch_row($result)){
			array_push($algorithms, $data[0]);
		}
	}else{
		setAlert("Error retrieving process information");
	}
	$stmt->close();
	return $algorithms;
}

function getEnglishAlgorithm($alg) {
	switch ($alg) {
		case "mostVoted" 			:return "Most Voted";
		case "random" 				:return "Random";
		case "testMode" 			:return "Test Mode";
		case "transductive" 		:return "Transductive";
		case "none"					:return "None";
		case "frequenceBased"		:return "Frequence-based";
		case "lexiconBased"			:return "Lexicon-based";
		case "PMIBased"				:return "PMI-based";
	}
	return "Undefined";
}

/**
* Unset the data of a labeling process that is stored in session 
*/
function unsetLabelingProcessData(){
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

?>