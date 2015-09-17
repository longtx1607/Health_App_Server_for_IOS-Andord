<?php
/**
 * Created by PhpStorm.
 * User: Longtran
 * Date: 10/28/14
 * Time: 9:01 AM
 */
require_once 'DbHandler.php';

class GetVideo extends DbHandler
{

    /*
     * Fetching all category
     * @param String $user_id  of the user
     */

    public function getvideocategory($user_id)
    {

//        $stmt = $this->conn->prepare("SELECT  Title,Quantity,IconURL,Version FROM category where  ");
        $stmt = $this->conn->prepare("SELECT category.Title,category.Quantity,category.IconURL,category.Version from category, videos,healthuser_video,users
            WHERE category.CategoryID = videos.CategoryID
            AND videos.VideosID=healthuser_video.VideosID
            AND healthuser_video.HealthID = users.HealthID
            AND users.HealthID= ? ");
//        var_dump(count($stmt));exit;
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($Title, $Quantity, $IconURL, $Version);
            $stmt->fetch();
            $user = array();
            $user["Title"] = $Title;
            $user["Quantity"] = $Quantity;
            $user["IconURL"] = $IconURL;
            $user["Version"] = $Version;
            $stmt->close();

            return $user;
        } else {
            return NULL;
        }

    }

    public function getAllcategoryforapikey()
    {

        $stmt = $this->conn->prepare("SELECT category.Title,category.Quantity,category.IconURL,category.Version from category ");

        if ($stmt->execute()) {

            $stmt->bind_result($Title, $Quantity, $IconURL, $Version);
            $stmt->fetch();
            $user = array();
            $user["Title"] = $Title;
            $user["Quantity"] = $Quantity;
            $user["IconURL"] = $IconURL;
            $user["Version"] = $Version;
            $stmt->close();
            return $user;
        }
    }

    public function getthumbnail($user_id)
    {
        $stmt = $this->conn->prepare("SELECT videos.ThumbnailURL, videos.VideosID AS VideosID, healthuser_video.VideosID, videos.CategoryID, videos.TimeUpDate FROM videos INNER JOIN healthuser_video ON healthuser_video.VideosID = videos.VideosID
                 INNER JOIN users ON users.healthID = healthuser_video.HealthID WHERE users.healthID = ?
                 GROUP BY videos.ThumbnailURL,healthuser_video.VideosID ORDER BY healthuser_video.id DESC LIMIT 1");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()){
        $stmt->fetch();
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;
        }
    }



    public function geturlcount($healthID)
    {
        $stmt = $this->conn->prepare("SELECT Count(HealthID) AS count FROM healthuser_video WHERE HealthID = ? ");
        $stmt->bind_param('i',$healthID);
        $stmt->execute();
        $count = $stmt->get_result();
        $stmt->close();
        return $count;

    }

    public function geturlcount1($user_id)
    {
        $stmt = $this->conn->prepare("SELECT Count(*) AS count FROM healthuser_video WHERE  HealthID = ?");
        $stmt->bind_param('i', $user_id);

        if ($stmt->execute()) {
            $stmt->bind_result($View);
            $stmt->fetch();
            $videos = array();
            $videos["View"] = $View;
            $stmt->close();
            return $View;
        } else {
            return NULL;
        }




    }
    public function geturlcategoryid($countid)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS count FROM videos  WHERE videos.CategoryID =?");
        $stmt->bind_param("i", $countid);
        if ($stmt->execute()) {
            $count = $stmt->get_result();
            $stmt->close();
        }
        return $count;
    }

    public function getvideodefault($videosID)
    {

        $stmt = $this->conn->prepare("SELECT VideosID, Title, Duration, ThumbnailURL, URL, CategoryID, TimeUpDate,View ,OriginThumbnailURL FROM videos WHERE VideosID = ? ");
        $stmt->bind_param("i", $videosID);
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;

    }


    public function getAllvideobyCategoryID($categoryID)
    {

        $stmt = $this->conn->prepare("SELECT videos.VideosID,videos.Title,videos.Duration,videos.ThumbnailURL,videos.URL, videos.CategoryID, videos.TimeUpDate , videos.View,OriginThumbnailURL from videos,category
         where videos.CategoryID=category.CategoryID and  videos.CategoryID= ?");
        $stmt->bind_param("i", $categoryID);
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;

    }


    public function isCategoryExists($categoryID)
    {
        $stmt = $this->conn->prepare("SELECT VideosID from videos WHERE CategoryID = ?");
        $stmt->bind_param("i", $categoryID);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
        //return 1;
    }



    public function getAllcategory() {

        $stmt = $this->conn->prepare("SELECT category.Title,category.Quantity,category.IconURL,category.Version,category.CategoryID from category");
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;

    }


    public function GetViewByCategoryID($VideosID) {
        $stmt = $this->conn->prepare("SELECT View FROM Videos WHERE VideosID = ?");
        $stmt->bind_param("i", $VideosID);
        if ($stmt->execute()) {
            $stmt->bind_result($View);
            $stmt->fetch();
            $videos = array();
            $videos["View"] = $View;
            $stmt->close();
            return $View;
        } else {
            return NULL;
        }
    }
    /**
     * Checking for duplicate user by api_key address
     * @param String $api_key api_key to check in db
     * @return boolean
     */
    /* 		public function isUserExit($api_key) {
                $stmt = $this->conn->prepare("SELECT healthID from users WHERE api_key = ?");
                $stmt->bind_param("s", $api_key);
                $stmt->execute();
                $stmt->store_result();
                $num_rows = $stmt->num_rows;
                $stmt->close();
                return $num_rows > 0;
            }
         */
    public function updateView($View,$VideosID) {
        $stmt = $this->conn->prepare("UPDATE videos set View=?  WHERE VideosID=?");
        $stmt->bind_param("ii", $View, $VideosID);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows;
    }

    public function isVideosExit($VideosID) {
        $stmt = $this->conn->prepare("SELECT VideosID from Videos WHERE VideosID = ?");
        $stmt->bind_param("i", $VideosID);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }



    public function isVideosByHealthUser_videosExit($VideosID,$healthID) {
        $stmt = $this->conn->prepare("SELECT VideosID from healthuser_video WHERE VideosID = ? and  healthID = ?");
        $stmt->bind_param("ii", $VideosID, $healthID);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function insertHealthVideos($healthID,$videosID) {
        $stmt = $this->conn->prepare("INSERT INTO HealthUser_Video(healthID, VideosID) values(?, ?)");
        $stmt->bind_param("ii", $healthID, $videosID);
        $result = $stmt->execute();

        if (false === $result) {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
        }
        $stmt->close();
        return $result;
    }

    public function deleteHealthVideos($healthID,$videosID) {
        $stmt = $this->conn->prepare("DELETE FROM HealthUser_Video WHERE healthID = ? AND VideosID = ?");
        $stmt->bind_param("ii", $healthID, $videosID);
        $result = $stmt->execute();

        if (false === $result) {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
        }
        $stmt->close();
        return $result;
    }
    public function getVideosIDByHealthID($healthID) {

        $stmt = $this->conn->prepare("SELECT
										videos.VideosID AS VideosID,
										videos.Title AS Title,
										videos.Duration AS Duration,
										videos.ThumbnailURL AS ThumbnaiURL,
										videos.URL AS URL,
										Videos.OriginThumbnailURL AS OriginThumbnailURL,
										Videos.TimeUpDate AS TimeUpDate,
										Videos.View AS View,
										Videos.CategoryID AS CategoryID
										FROM
										videos
										Inner Join healthuser_video ON healthuser_video.VideosID = videos.VideosID
										WHERE
										healthuser_video.healthID =?");
        $stmt->bind_param("i", $healthID);
        $stmt->execute();

        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;

    }

    // select healthID ứng với ApiKey 
    public function getHealthByApikey($api_key){
                $stmt = $this->conn->prepare("SELECT healthID FROM users WHERE api_key = ?");
                $stmt->bind_param("s", $api_key);
                if ($stmt->execute()) {
                    $stmt->bind_result($healthID);
                    $stmt->fetch();
                    $user = array();
                    $user["healthID"] = $healthID;
                    $stmt->close();
                    return $healthID;
                } else {
                    return NULL;
                }
    }


public function getAllBmi()
    {
        $stmt = $this->conn->prepare("SELECT HealthID, Time, Value, TimeStamp  FROM bmi ");
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;

    }
}