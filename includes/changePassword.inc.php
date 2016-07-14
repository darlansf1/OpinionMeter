<?php
include_once 'db_connect.php';
include_once 'includes/functions.php';
sec_session_start();
unsetLabelingProcessData();

setAlert("");
$user_name = $user_email = "";

//Form was submitted 
if (isset($_POST['username'], $_POST['email'], $_POST['p'])) {
    // Sanitize and validate the data passed in
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Not a valid email
		setAlert("O email inserido não é válido.");
    }
 
    $password = filter_input(INPUT_POST, 'p', FILTER_SANITIZE_STRING);
    if (strlen($password) != 128) {
        // The hashed pwd should be 128 characters long.
        // If it's not, something really odd has happened
		setAlert("Configuração de senha inválida");
    }
 
    if (isAlertEmpty()) {
        // Create a random salt
        //$random_salt = hash('sha512', uniqid(openssl_random_pseudo_bytes(16), TRUE)); // Did not work
        $random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
 
        // Create salted password 
        $password = hash('sha512', $password . $random_salt);
	
        // Insert the new user into the database 
		$query = "	UPDATE tbl_user 
							SET user_password = ?, user_salt = ? 
							WHERE user_id = ?";
        if ($stmt = $mysqli->prepare($query)) {
            $stmt->bind_param('ssi',$password, $random_salt, $_SESSION['user_id']);
            // Execute the prepared query.
            if (!$stmt->execute()) {
                header('Location: ../error.php?err=Password change - failure: UPDATE');
            }
        }
		$stmt->close();	
        header('Location: ./index.php');
    }
}else {
	//Getting username and user's email
	$query = "SELECT user_name,user_email FROM tbl_user WHERE user_id = ? LIMIT 1";
    $stmt = $mysqli->prepare($query);	
	$stmt->bind_param('s', $_SESSION['user_id']);
	if($stmt->execute()){
		$stmt->bind_result($user_name , $user_email );
		$stmt->fetch();
	} else {
		setAlert ("Erro ao recuperar o email do usuário");
	}
	$stmt->close();	
}

