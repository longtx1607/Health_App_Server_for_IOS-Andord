<html>
	<head>
		<script type="text/javascript" src="js/jquery-1.9.0.min.js"></script>
		<script type="text/javascript" src="js/jquery.ui.widget.js"></script>
		<script type="text/javascript" src="js/jquery.iframe-transport.js"></script>
		<script type="text/javascript" src="js/jquery.fileupload.js"></script>
	</head>
	<body>
		<?php


require("../connect.php");
//        $NutritionID=$_GET['NutritionID'];
        if(isset($_POST['OK'])){


                $milliseconds = round(microtime(true) * 1000);
				$Title=$_POST['Title'];

				$CategoryID=$_POST['CategoryID'];
				$Post=$_POST['Post'];
				$images=$_POST['images'];

				$sql = "SELECT Max(nutritions.NutritionID) FROM nutritions";
				$row = mysql_query($sql);
				$NutritionID = mysql_result($row,0)+1;
//				$ThumbnailURL = 'http://'.$_SERVER['HTTP_HOST'].'/task_manager/v1/insertpage/images/'.$images;
				$ThumbnailURL = 'http://192.168.90.61:8080/task_manager/v1/insertpage/images/'.$images;
//				$URL = 'http://'.$_SERVER['HTTP_HOST'].'/task_manager/v1/insertpage/viewPage.php?NutritionID='.$NutritionID;
				$URL = 'http://192.168.90.61:8080/task_manager/v1/insertpage/viewPage.php?NutritionID='.$NutritionID;

				$sql="INSERT INTO Nutritions(Title,ThumbnailURL,CategoryID,URL,TimeUpLoad,OriginThumbnailURL) VALUES ('$Title','$ThumbnailURL','$CategoryID','$URL','$milliseconds','$ThumbnailURL')";
				$result = mysql_query($sql);
	
				$sql="INSERT INTO Page(NutritionID,Title,Post,images) VALUES ('$NutritionID','$Title','$Post','$images')";			
				$result = mysql_query($sql);

                $sql = "update nutritioncategorys set ThumbnailURL='$ThumbnailURL' where CategoryID =" .$CategoryID;
                $result=mysql_query($sql);

				header('Location: insertPage.php');
			} 							
		?>
		
		<form action="" method="post">
			<table>
			<tr>
				<td>
					<div class="element">	
						
						<span class="sp red">Title :</span><input id="name" name="Title"/>
					</div>
				</td>
				<td>
					<div class="element">				
						<span class="sp red">CategoryID :</span><input id="name" name="CategoryID"/>

					</div>
				</td>
			</tr>
			<tr>
				<td>
                     <div class="element">
                        <span class="sp">Post :</span><textarea name="Post" rows="20" style="width:1000px;"></textarea>
                        <input type="text" name="abc" value="<div class='col-xs-12 images'><img src='' class='img-responsive' alt='Responsive image' </div><br/>"/>
                        <input type="text" name="abc" value="<div class='col-xs-12 images'><b>bai</b><img src='' class='img-responsive' alt='Responsive image' </div><br/>"/>
                    </div>
				</td>
			</tr>
			<tr>
				<td><label>Thumbnai</label></td>
				<td>
					<span class="btn btn-success fileinput-button">
						<input id="fileupload" type="file" name="files[]"/>
					</span>
					<p>Files uploaded:</p>
					<img src="" id="files1" width="100" height="100"/>
					
					<input type="hidden" id="images" name="images" value=""/>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="element">
						<div class="entry">
							<button type="submit" class="add" name="OK" >Save page</button><button type="reset">Reset</button> 
						</div>
					</div>
				</td>
			</tr>
			</table>
		</form>
	</body>
</html>
<script>
	$(document).ready(function() {
		// Define the url to send the image data to
		var url = 'files.php';
		
		// Call the fileupload widget and set some parameters
		$('#fileupload').fileupload({
			url: url,
			dataType: 'json',
			done: function (e, data) {
				// Add each uploaded file name to the #files list
				$.each(data.result.files, function (index, file) {
					var src = "http://192.168.90.61:8080/task_manager/v1/insertpage/images/" +file.name;
					$("#files1").attr("src",src);
					$("#images").attr("value",file.name);
					$("#name").attr("value",'test');
				});
			},
	
		});
	});
</script>	