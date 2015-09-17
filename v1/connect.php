<?php
	$connect = mysql_connect('localhost', 'root','');
	if (!$connect) {
		die('Could not connect: ' . mysql_error());
	}
	mysql_select_db('task_manager');
	mysql_query("SET NAME 'UTF-8'"); 
	
	/* $con = new mysqli('localhost', 'root', '', 'username');

	if($con->connect_errno > 0){
    die('Unable to connect to database [' . $con->connect_error . ']');
	mysqli_query("SET NAME 'UTF-8'"); 
	}*/
?>