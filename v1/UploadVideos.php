<html>
	<head>

	</head>
	<body>
		<?php 
			$connect = mysql_connect('localhost', 'root','');
			if (!$connect) {
				die('Could not connect: ' . mysql_error());
			}
			mysql_select_db('task_manager');
			mysql_query("SET NAME 'UTF-8'");
			if(isset($_POST['OK'])){	
				$Title=$_POST['Title'];
				$CategoryID=$_POST['CategoryID'];	
				$sql="INSERT INTO Videos(Title,CategoryID) VALUES ('$Title','$CategoryID')";			
				$row=mysql_query($sql);				
				header('Location: InsertVideos.php');
			} 							
		?>
		<form action="" method="POST">						
			Title :<input name="Title"/>
			CategoryID :<input name="CategoryID"/>
			<button type="submit" name="OK" >OK and Insert Videos</button><button type="reset">Reset</button> 
		</form>
	</body>
</html>