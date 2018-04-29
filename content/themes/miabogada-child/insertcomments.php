<?php
include 'connect.php';
// clean user input
// it's escaped by default			$email = mysqli_real_escape_string ($link , $email);
//						$comments = mysqli_real_escape_string ($link , $comments);

// check if email exists in table
$sqlstr = "SELECT id,email from marketingconsumer WHERE email ='". $email ."'";
$result = mysqli_query($link,$sqlstr);

if ($row = mysqli_fetch_assoc($result)){ //email exists in table
	$sqlstr = "INSERT INTO comments (comment,id_marketingconsumer) values ('" .$comments ."','" .$row['id'] ."')";
} else { // email does not exist in table
	$sqlstr = "INSERT INTO marketingconsumer (email,source,acceptemail) values ('" .$email ."','" .$_SERVER['HTTP_HOST'] ."','" .isset($_POST['optin'])  ."')";
	$result = mysqli_query($link,$sqlstr); //add email and set up the comment query
	$sqlstr = "INSERT INTO comments (comment,id_marketingconsumer) values ('" .$comments ."','" .mysqli_insert_id($link) ."')";
}

// insert the comment			
$result = mysqli_query($link,$sqlstr);

//free the memory and close the connection
//mysqli_free_result($result);
mysqli_close($link);
?>
