<?php
 	require "db.php";
 	require "config.php";
 
 	if($_SERVER['REQUEST_METHOD']=='POST'){
 		// Always return JSON format
 		header('Content-Type: application/json');

 		// Sign up
 		if($_POST['isSignup']==1){
	 		$username = $_POST['username'];
	 		$email = strtolower($_POST['email']);
	 		$return=[];
	 		$isError = 0;

	 		// check if username or email has been already in the database
	 		// prepare and bind parameters
	 		$username_sql = $conn->prepare("SELECT * FROM userdata WHERE username=?");
	 		$email_sql = $conn->prepare("SELECT * FROM userdata WHERE email=?");
	 		$username_sql->bind_param("s",$username);
	 		$email_sql->bind_param("s",$email);

	 		// execute query 
	 		$username_sql->execute();
	 		$email_sql->execute();

	 		$username_sql->store_result();
	 		$email_sql->store_result();
	 		
	 		// duplicate username
	 		if($username_sql->num_rows>0){
	 			$return['error_msg'] = "Username has been used !";
	 			$return['active'] = 1;
	 			echo json_encode($return,JSON_PRETTY_PRINT);
	 			exit;
	 		}

	 		// duplicate email
	 		if($email_sql->num_rows>0){
	 			$return['error_msg'] = "Email has been used !";
	 			$return['active'] = 1;
	 			echo json_encode($return,JSON_PRETTY_PRINT);
	 			exit;
	 		}

	 		// creates a new password hash using a strong one-way hashing algorithm.
	 		// a random salt will be generated by password_hash() for each password hashed
	 		$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

	 		//沒有重複 存資料
	 		$sql = $conn->prepare("INSERT INTO userdata (username, password, email) VALUES (?, ?, ?)");
			$sql->bind_param("sss",$username, $password, $email);
			$sql->execute();

			$return['error_msg'] ="";
	 		$return['active'] = 0;
	 		$return['method'] = "signup";
			$return['redirect'] = 'dashboard.php';
			$_SESSION["user_name"] = $username;

	 		echo json_encode($return,JSON_PRETTY_PRINT);

	 		$conn->close();
	 		exit();
	 	}

	 	// Log in
	 	else{
	 		$username = $_POST['username'];
	 		$password = $_POST['password'];
	 		$return=[];
	 		$isError = 0;

	 		//check if username or email has been already in the database
	 		$username_sql = $conn->prepare("SELECT password FROM userdata WHERE username=?");
	 		$username_sql->bind_param("s",$username);
	 		$username_sql->execute();
	 		$username_sql->store_result();

	 		// has account
	 		if($username_sql->num_rows>0){
	 			
	 			$username_sql->bind_result($temp);
	 			$username_sql->fetch();
	 			$hash = (string) $temp;
	 			//check if the password is correct
	 			if(password_verify($password, $hash)){
	 				$return['error_msg'] = "";
	 				$return['active'] = 0;
	 				$return['method'] = "login";
	 				$return['redirect'] = "dashboard.php";
	 				$_SESSION["user_name"] = $username;
	 				echo json_encode($return,JSON_PRETTY_PRINT);
	 				exit;
	 			}
	 			else{
	 				$return['error_msg'] = "Wrong password !";
	 				$return['active'] = 1;
	 				echo json_encode($return,JSON_PRETTY_PRINT);
	 				exit;
	 			}
	 		}
	 		//cannot find the identical username
	 		else{
	 			$return['error_msg'] = "You don't have an account !";
	 			$return['active'] = 1;
	 			echo json_encode($return,JSON_PRETTY_PRINT);
	 			exit;
	 		}
	 	}
 	}
 	else
 		// Die. Kill the script.
		exit('Invalid URL');
?>