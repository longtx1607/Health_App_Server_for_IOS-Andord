<html>
	<head>
		<title>bai thi nghiem</title>
		<link rel="stylesheet" href="css/bootstrap.min.css"/>	
		<link rel="stylesheet" href="css/bootstrap-theme.min.css"/>	
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<style>
			.container{
				
			}
			.row{
				

			}
			.col-xs-12{
				padding-right:0;
				padding-left:0;
			}
	
		
			.page{
				margin-top:10px;
				
				/* padding:10px; */
				text-align: justify;
			}
			.a{
				text-align:center;
			}
			.images{
				text-align:center;
				
			}
			.title{
				
				/* padding:5px 10px; */
				word-spacing: -1px;
			}
			img{
				padding:10px 0;
			}
		</style>
		<script type="text/javascript" src="js/jquery-1.9.0.min.js"></script>
		<script type="text/javascript" src="js/bootstrap.min.js"></script>
	</head>
	<body>
		<?php 
			require("../connect.php");
			$NutritionID = $_GET['NutritionID'];
			$sql="select * from page where NutritionID='$NutritionID'";
			$result=mysql_query($sql);
			if($row=mysql_num_rows($result)==0){
				echo "Not infomation ! Please update now !";
				echo "<a href='#'>Update</a>";
				exit;
			}			
			$row=mysql_fetch_array($result);
		?> 
		<div class="container">
			<div class="row">
				<div class="title">
						<h1><?php echo $row['Title'] ?></h1>
				</div>
			</div>
			<div class="row">	
				<div class="page col-xs-12">
                    <div class="col-xs-12 images"><img src="images/<?php echo $row['images'] ?>" class="img-responsive" alt="Responsive image"/></div>
					<div class="col-xs-12 post"><?php echo $row['Post'] ?></div>
					
				</div>	
			</div>
		</div>
	</body>
</html>
