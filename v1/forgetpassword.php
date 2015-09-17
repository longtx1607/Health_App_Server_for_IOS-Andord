<html>
	<head>
	</head>
	<body style="background:url(bg.jpg)	no-repeat;">
		<?php 
		
			require_once '../include/PassHash.php';
			require_once 'connect.php';
			mysql_query("SET NAME 'UTF-8'");
			$token=$_GET['token'];
			
			$sql="select * from passwordtoken where token='$token'";
			$result=mysql_query($sql);
			
			if($row=mysql_num_rows($result)==0){
				echo "Not infomation ! Please update now !";
				//echo "<a href='updateinfo.php'>Update</a>";
				exit;
			}			
			$row=mysql_fetch_array($result);
			$healthID = $row['healthID'];
			
			if(isset($_POST['OK'])){
					
				$password = $_POST['password'];
				$password_set = $_POST['password_set'];
				if(!empty($password)){
					if(strlen($password)>=8){	
						if($password == $password_set){
							$password_hash = PassHash::hash($password);
							$sql = "Update users set password_hash='$password_hash' where healthID='$healthID'";			
							$result = mysql_query($sql);
							if($result){
								$sql = "Update passwordtoken set ExpiryTime='0'  where Token='$token'";
								$result = mysql_query($sql);
								//echo "ban da doi mat khau thanh cong";
								header("Location: abc.jpg");
							}
						}else{
							echo"enter a password reset invalid";
						}
					}else{
						echo "mat khau cua ban phai co 8 ki tu tro len";
					}		
				}else{
					echo "ban chua nhap mat khau";
				}	
			}
			$time=((date('y')*356*24*60)+(date('m')*30*24*60)+(date('d')*24*60)+(date('h')*60)+(date('i')));		
		?> 
		<?php if($row["ExpiryTime"]>=$time){ ?>
		<form action="" method="post">
			<table>
				<tr>
					<td>
						<span>password :</span><input  type="password" name="password" value="" />
					</td>
					<td>
						<span>enter a password reset :</span><input  type="password" name="password_set" value="" />
					</td>
					
				</tr>
				<tr>
					<td>
						<button type="submit" class="edit" name="OK">Save page</button>
						<button type="reset">Reset</button>  
					</td>
				</tr>
			</table>
		</form>
		<?php } 
		else{echo "token da het han";}
		?>
	</body>
</html>
