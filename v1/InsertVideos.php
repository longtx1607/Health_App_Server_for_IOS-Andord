<html>
	<head>
		<title>Form upload videos</title>
		<meta charset="UTF-8">
	<head/>
	<body>
		<form action="" method="post" enctype="multipart/form-data">
			Filename:
			<input type="file" name="file_up" accept="file_extension|audio/*|video/*|image/*|media_type"/>
			<br />
			<input type="submit" name="submit" value="Submit" />
		</form>
	</body>
</html>
<?php
	$connect = mysql_connect('localhost', 'root','');

	if (!$connect) {
		die('Could not connect: ' . mysql_error());
	}
	mysql_select_db('task_manager');
	mysql_query("SET NAME 'UTF-8'");
	if(isset($_POST["submit"])){
		if ($_FILES["file_up"]["error"] > 0)
		{
			echo "Return Code: " . $_FILES["file_up"]["error"] . "<br />";
		}
		else
		{
			if (file_exists("videos/" . $_FILES["file_up"]["name"]))
			{
				echo $_FILES["file_up"]["name"] . " da ton tai file tren server. ";
			}
			else
			{  
				move_uploaded_file($_FILES["file_up"]["tmp_name"],
				"videos/" . $_FILES["file_up"]["name"]);	
			}
		}
		$link = "videos/" . $_FILES["file_up"]["name"];
		//$nameVideo = $_FILES["file_up"]["name"];
/* 		echo "Duong link cua file la: $link <br />";	
		echo "Ten File: " . $_FILES["file_up"]["name"] . "<br />";
		echo "Type: " . $_FILES["file_up"]["type"] . "<br />";
		echo "Size: " . ($_FILES["file_up"]["size"] / 1024) . " Kb<br />";
		echo "Temp file: " . $_FILES["file_up"]["tmp_name"] . "<br />"; */

		$ffmpeg = 'C:\\ffmpeg\\bin\\ffmpeg';

		//video dir
		$video = $link;
		$ext = strtolower(pathinfo($_FILES["file_up"]["name"], PATHINFO_EXTENSION));
		$m_arr_filter = array(',',';',"'",'"',$ext,' ');

		//loai bo ký tự đặc biệt
		$nameVideo = strtolower(str_replace($m_arr_filter,'',$_FILES["file_up"]["name"]));
        //random name image

        $nameImages=rand(10,100000);
		//where to save the image

		$image = 'images/'.$nameVideo.'jpg';

		$image1 = 'images/'.$nameImages.'.jpg';

        $milliseconds = round(microtime(true) * 1000);
		//time to take screenshot at
		$interval = 2;
		//screenshot size

        extension_loaded('ffmpeg') or die('Error in loading ffmpeg');

        // Determine the full path for our video
        $vid = realpath($link);

        // Create the ffmpeg instance and then display the information about the video clip.
        $ffmpegInstance = new ffmpeg_movie($vid);

        $videorate =( $ffmpegInstance->getFrameHeight()/$ffmpegInstance->getFrameWidth());

        $videoratewith=(int)(360*$videorate);

        $size='360x'.$videoratewith;
//        print_r($videoratewith);exit;
         $sizeOrigin=$ffmpegInstance->getFrameWidth().'x'.$ffmpegInstance->getFrameHeight();

        //ffmpeg command
		$cmd = "$ffmpeg -i $video -deinterlace -an -ss $interval -f mjpeg -t 1 -r 1 -y -s $size $image 2>&1";
        shell_exec($cmd);

        $cmdOrigin = "$ffmpeg -i $video -deinterlace -an -ss $interval -f mjpeg -t 1 -r 1 -y -s $sizeOrigin $image1 2>&1";

        shell_exec($cmdOrigin);

		$time = ((int)$ffmpegInstance->getDuration())*1000;
        $milliseconds = round(microtime(true) * 1000);
		$Duration = $time;
		//$ThumbnailURL = 'http://'.$_SERVER['HTTP_HOST'].'/task_manager1/v1/'.$image;
		$ThumbnailURL = 'http://192.168.90.61:8080/task_manager/v1/'.$image;
        $OriginThumbnailURL= 'http://192.168.90.61:8080/task_manager/v1/'.$image1;
		//$URL = 'http://'.$_SERVER['HTTP_HOST'].'/task_manager1/v1/'.$video;
		$URL = 'http://192.168.90.61:8080/task_manager/v1/'.$video;

		
		$sql = "SELECT MAX(videos.VideosID) FROM Videos";
		$result=mysql_query($sql);
		$VideosID = mysql_result($result,0);
		$sql = "update Videos set Duration='$Duration',ThumbnailURL='$ThumbnailURL',URL='$URL', TimeUpDate='$milliseconds', OriginThumbnailURL ='$OriginThumbnailURL' where VideosID =" . $VideosID ;
        $result = mysql_query($sql);
        //$ThumbnailURL

        $sql1 = "SELECT CategoryID FROM videos where VideosID =" . $VideosID;
        $result1 = mysql_query($sql1);
        $CategoryID = mysql_result($result1,0);

        $sql2 = "update category set IconURL='$ThumbnailURL' where CategoryID =" . $CategoryID;
        $result=mysql_query($sql2);

//            $sql= "SELECT COUNT(*) FROM category WHERE CategoryID =" . $CategoryID;
//            $result=mysql_query($sql);

    	header("Location: UploadVideos.php");

	}

?>