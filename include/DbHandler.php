<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Ravi Tamada
 * @link URL Tutorial link
 */
class DbHandler {

    public $conn;

    function __construct() {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /* ------------- `users` table method ------------------ */

    /*
     * Creating new user
     * @param String $name User full name
     * @param String $email User login email id
     * @param String $password User login password
     */
    public function createUser($email, $password, $firstname, $lastname, $gender, $phonenumber, $address, $country , $birthday) {
        require_once 'PassHash.php';
        $response = array();

        // First check if user already existed in db
        if (!$this->isUserExists($email)) {
            // Generating password hash
            $password_hash = PassHash::hash($password);
//            $password_hash=$password;
            // Generating API key
            $api_key = $this->generateApiKey();

            $avatar_url="http://192.168.90.61:8080/task_manager/v1/images/default_avatar/default_avatar.jpg";
//            $video= "videos/" . $_FILES["file_up"]["name"];
//            $URL = 'http://'.$_SERVER['HTTP_HOST'].'/task_manager/v1/'.$video;

            // insert query
            $stmt = $this->conn->prepare("INSERT INTO users(email, password_hash, api_key, status,last_name, first_name, gender , phone_number , address , country, birthday,avatar_url) values(?, ?, ?, 1 , ? ,? ,? ,? ,? ,? ,?, ? )");
            $stmt->bind_param("sssssssssss", $email, $password_hash, $api_key , $lastname, $firstname, $gender, $phonenumber, $address, $country,$birthday,$avatar_url );

            $result = $stmt->execute();

            $stmt->close();

            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
        } else {
            // User with same email already existed in the db
            return USER_ALREADY_EXISTED;
        }

        return $response;
    }

    //function createUserbytokenfacebook

    public function createUserByFacebook($email, $firstname, $lastname, $gender, $phonenumber, $address, $country , $birthday) {
//        require_once 'PassHash.php';
        $response = array();

        // First check if user already existed in db
        if (!$this->isUserExists($email)) {
            // Generating password hash
//           $password_hash = PassHash::hash($password);
            // Generating API key
            $api_key = $this->generateApiKey();
            $password_hash = $this->generatePass();
            $avatar_url="http://192.168.90.61:8080/task_manager/v1/images/default_avatar/default_avatar.jpg";
//            $video= "videos/" . $_FILES["file_up"]["name"];
//            $URL = 'http://'.$_SERVER['HTTP_HOST'].'/task_manager/v1/'.$video;
            // insert query
            $stmt = $this->conn->prepare("INSERT INTO users(email, password_hash, api_key, status,last_name, first_name, gender , phone_number , address , country, birthday,avatar_url) values( ?, ?, ?, 1 , ? ,? ,? ,? ,? ,? ,? , ?)");
            $stmt->bind_param("sssssssssss", $email, $password_hash, $api_key , $lastname, $firstname, $gender, $phonenumber, $address, $country,$birthday, $avatar_url );

            $result = $stmt->execute();

            $stmt->close();

            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
        }
        else {
            // User login fb
            return USER_ALREADY_EXISTED;

        }

        return $response;
    }

    /**
     * Checking user login
     * @param String $email User login email id
     * @param String $password User login password
     * @return boolean User login status success/fail
     */
    public function checkLogin($email, $password) {
        // fetching user by email
        $stmt = $this->conn->prepare("SELECT password_hash FROM users WHERE email = ?");

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $stmt->bind_result($password_hash);

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password

            $stmt->fetch();

            $stmt->close();

            if (PassHash::check_password($password_hash, $password)) {
                // User password is correct
                return TRUE;
            } else {
                // user password is incorrect
                return FALSE;
            }
        } else {
            $stmt->close();

            // user not existed with the email
            return FALSE;
        }
    }


    /**
     * Checking for duplicate user by email address
     * @param String $email email to check in db
     * @return boolean
     */
    public function isUserExists($email) {
        $stmt = $this->conn->prepare("SELECT healthID from users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
        //return 1;
    }

    /*
     * Fetching user by email
     * @param String $email User email id
     */
    public function getUserByEmail($email) {

        $stmt = $this->conn->prepare("SELECT  healthID,email, api_key, status, created_at  ,first_name, last_name, gender,phone_number,address, country ,birthday ,avatar_url FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result( $heathID, $email, $api_key, $status, $created_at, $fistname , $lastname, $gender , $phonenumber, $address, $country, $birthday, $avatar_url);
            $stmt->fetch();
            $user = array();
            $user["avatar_url"] = $avatar_url;
            $user["healthID"]=$heathID;
            $user["email"] = $email;
            $user["api_key"] = $api_key;
            $user["status"] = $status;
            $user["created_at"] = $created_at;
            $user["birthday"]=$birthday;
            $user["first_name"]=$fistname;
            $user["last_name"]=$lastname;
            $user["gender"]=$gender;
            $user["phone_number"]=$phonenumber;
            $user["address"]=$address;
            $user["country"]=$country;
            $stmt->close();
            return $user;
        }
        else {
            return NULL;
        }
    }


    /*
     * Fetching user api key
     * @param String $user_id user id primary key in user table
     */
    public function getApiKeyById($user_id) {
        $stmt = $this->conn->prepare("SELECT api_key FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            // $api_key = $stmt->get_result()->fetch_assoc();
            // TODO
            $stmt->bind_result($api_key);
            $stmt->close();
            return $api_key;
        } else {
            return NULL;
        }
    }





    /*
     * Fetching user id by api key
     * @param String $api_key user api key
     */
    public function getUserId($api_key) {
        $stmt = $this->conn->prepare("SELECT healthID FROM users WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        if ($stmt->execute()) {
            $stmt->bind_result($user_id);
            $stmt->fetch();
            // TODO
            // $user_id = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $user_id;
        } else {
            return NULL;
        }
    }

    /*
     * Validating user api key
     * If the api key is there in db, it is a valid key
     * @param String $api_key user api key
     * @return boolean
     */
    public function isValidApiKey($api_key) {
        $stmt = $this->conn->prepare("SELECT healthID from users WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

	/*
     * Validating user api key
     * If the api key is there in db, it is a valid key
     * @param String $api_key user api key
     * @return boolean
     */
	public function updateUser($first_name, $last_name, $gender, $phone_number, $address, $country, $birthday, $apiKey) {

        $stmt = $this->conn->prepare("UPDATE users set first_name=?,last_name=?, gender=?, phone_number=?, address=?, country=?, birthday=?  WHERE api_Key=?");
        $stmt->bind_param("ssssssss", $first_name, $last_name, $gender, $phone_number, $address, $country, $birthday, $apiKey);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows;

    }

    public function createPasswordToken($email){

        $user = $this->getUserByEmail($email);
        $healthID = $user["healthID"];
        //$healthID = 24;
        $Token = $this->rand_string(20);
        $ExpiryTime=((date('y')*356*24*60)+(date('m')*30*24*60)+(date('d')*24*60)+(date('h')*60)+(date('i')))+30;

        $stmt = $this->conn->prepare("INSERT INTO passwordtoken(healthID, Token, ExpiryTime) values(? , ?, ?)");
        $stmt->bind_param("isi", $healthID,$Token,$ExpiryTime);

        $result = $stmt->execute();

        $stmt->close();

        return $Token;
    }



    public function checkPass($apikey,$old_password) {
        $stmt = $this->conn->prepare("SELECT password_hash FROM users WHERE api_key = ?");

        $stmt->bind_param("s", $apikey);

        $stmt->execute();

        $stmt->bind_result($password_hash);

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password

            $stmt->fetch();

            $stmt->close();

            if (PassHash::check_password($password_hash, $old_password)) {
                // User password is correct
                return TRUE;
            } else {
                // user password is incorrect
                return FALSE;
            }
        } else {
            $stmt->close();

            // user not existed with the email
            return FALSE;
        }

    }
    public function updatePassword($password,$api_key) {
        $password_hash = PassHash::hash($password);
        $stmt = $this->conn->prepare("UPDATE users set password_hash=?  WHERE api_key=?");

        $stmt->bind_param("ss", $password_hash, $api_key);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows;
    }



    /**
     * Fetching all category
     * @param String $user_id  of the user
     */

    public function getvideocategory($user_id) {

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
            $stmt->bind_result( $Title, $Quantity , $IconURL, $Version);
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

      /*  $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();*/
        //var_dump($stmt);die;
//        return $stmt;
//        var_dump($tasks);die;

    }


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

    public function getAllcategoryforapikey(){

        $stmt = $this->conn->prepare("SELECT category.Title,category.Quantity,category.IconURL,category.Version from category ");
////        $stmt->bind_param("s", $api_key);
//        $stmt->execute();
//        $tasks = $stmt->get_result();
//        $stmt->close();
//        return $tasks;
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result( $Title, $Quantity , $IconURL, $Version);
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
    public function isUserExit($api_key) {
        $stmt = $this->conn->prepare("SELECT healthID from users WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }


    /**
     * Generating random Unique MD5 String for user Api key
     */
    private function generateApiKey() {
        return md5(uniqid(rand(), true));
    }
    private function generatePass() {
        return md5(uniqid(rand(), true));
    }



    /*
        function lay du lieu cua user bang apikey
     */
    public function getUserInfoByApikey($api_key){
        $stmt = $this->conn->prepare("SELECT first_name,last_name,gender,phone_number,address,country,birthday,avatar_url FROM users WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        if ($stmt->execute()) {
            $stmt->bind_result($first_name,$last_name,$gender,$phone_number,$address,$country,$birthday,$avatar_url);
            $stmt->fetch();
            $user = array();
            $user["first_name"] = $first_name;
            $user["last_name"] = $last_name;
            $user["gender"] = $gender;
            $user["phone_number"] = $phone_number;
            $user["address"]=$address;
            $user["country"]=$country;
            $user["birthday"]=$birthday;
            $user["avatar_url"]=$avatar_url;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }




public function getvideodefault1($videosID) {
//
//    $stmt = $this->conn->prepare("SELECT VideosID, Title,Duration,ThumbnailURL,URL,CategoryID,TimeUpDate FROM Videos WHERE VideosID = ?");
//
//    $stmt->bind_param("i", $videosID);
//    if ($stmt->execute()) {
//        $stmt->bind_result($videosID, $Title, $Duration, $ThumbnailURL, $URL, $CategoryID, $TimeUpDate);
//        $stmt->fetch();
//        $videos1 = array();
//        $videos1["VideosID"]=$VideosID;
//        $videos1["Title"]=$Title;
//        $videos1["Duration"]=$Duration;
//        $videos1["ThumbnailURL"]=$ThumbnailURL;
//        $videos1["URL"]=$URL;
//        $videos1["CategoryID"]=$CategoryID;
//        $videos1["TimeUpDate"]=$TimeUpDate;
//
//        $stmt->close();
//        return $videos1;
//    } else {
//        return NULL;
//
//    }
    $stmt = $this->conn->prepare("SELECT VideosID, Title,Duration,ThumbnailURL,URL,CategoryID,TimeUpDate FROM Videos WHERE VideosID = ?");
    $stmt->bind_param("i", $VideosID);
    if ($stmt->execute()) {
        $stmt->bind_result($VideosID, $Title, $Duration, $ThumbnailURL, $URL, $TimeUpDate, $CategoryID);
        $stmt->fetch();
        $user = array();
        $user["VideosID"] = $VideosID;
        $user["Title"] = $Title;
        $user["Duration"] = $Duration;
        $user["ThumbnailURL"] = $ThumbnailURL;
        $user["URL"]=$URL;
        $user["TimeUpDate"]=$TimeUpDate;
        $user["CategoryID"]=$CategoryID;

        $stmt->close();
        return $user;
    } else {
        return NULL;
    }
}



    /* ------------- `tasks` table method ------------------ */

    /*
     * Creating new task
     * @param String $user_id user id to whom task belongs to
     * @param String $task task text
     */
    public function createTask($user_id, $task) {
        $stmt = $this->conn->prepare("INSERT INTO tasks(task) VALUES(?)");
        $stmt->bind_param("s", $task);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            // task row created
            // now assign the task to user
            $new_task_id = $this->conn->insert_id;
            $res = $this->createUserTask($user_id, $new_task_id);
            if ($res) {
                // task created successfully
                return $new_task_id;
            } else {
                // task failed to create
                return NULL;
            }
        } else {
            // task failed to create
            return NULL;
        }

}
    /*
     * Fetching single task
     * @param String $task_id id of the task
     */
    public function getTask($task_id, $user_id) {
        $stmt = $this->conn->prepare("SELECT t.id, t.task, t.status, t.created_at from tasks t, user_tasks ut WHERE t.id = ? AND ut.task_id = t.id AND ut.user_id = ?");
        $stmt->bind_param("ii", $task_id, $user_id);
        if ($stmt->execute()) {
            $res = array();
            $stmt->bind_result($id, $task, $status, $created_at);
            // TODO
            // $task = $stmt->get_result()->fetch_assoc();
            $stmt->fetch();
            $res["id"] = $id;
            $res["task"] = $task;
            $res["status"] = $status;
            $res["created_at"] = $created_at;
            $stmt->close();
            return $res;

        } else {
            return NULL;
        }
    }



public function getvideocategory1($user_id) {

//        $stmt = $this->conn->prepare("SELECT  Title,Quantity,IconURL,Version FROM category where  ");
    $stmt = $this->conn->prepare("SELECT category.Title,category.Quantity,category.IconURL,category.Version, category.CategoryID from category, videos,healthuser_video,users
        WHERE category.CategoryID = videos.CategoryID
        AND videos.VideosID=healthuser_video.VideosID
        AND healthuser_video.HealthID = users.HealthID
        AND users.HealthID= ? ");
//        var_dump(count($stmt));exit;
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $tasks = $stmt->get_result();
    $stmt->close();
    return $tasks;

}


    public function geturlcategoryid($countid) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS count FROM videos  WHERE videos.CategoryID =?");
        $stmt->bind_param("i", $countid);
        if ($stmt->execute()){
        $count= $stmt->get_result();
        $stmt->close();
         }
        return $count;
    }




    /**
     * Fetching all user tasks
     * @param String $user_id id of the user
     */
    public function getAllUserTasks($user_id) {

        $stmt = $this->conn->prepare("SELECT t.* FROM tasks t, user_tasks ut WHERE t.id = ut.task_id AND ut.user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();

        return $tasks;
    }

    /*
     * Updating task
     * @param String $task_id id of the task
     * @param String $task task text
     * @param String $status task status
     */
    public function updateTask($user_id, $task_id, $task, $status) {
        $stmt = $this->conn->prepare("UPDATE tasks t, user_tasks ut set t.task = ?, t.status = ? WHERE t.id = ? AND t.id = ut.task_id AND ut.user_id = ?");
        $stmt->bind_param("siii", $task, $status, $task_id, $user_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /*
     * Deleting a task
     * @param String $task_id id of the task to delete
     */
    public function deleteTask($user_id, $task_id) {
        $stmt = $this->conn->prepare("DELETE t FROM tasks t, user_tasks ut WHERE t.id = ? AND ut.task_id = t.id AND ut.user_id = ?");
        $stmt->bind_param("ii", $task_id, $user_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }


    /* ------------- `user_tasks` table method ------------------ */

    /**
     * Function to assign a task to user
     * @param String $user_id id of the user
     * @param String $task_id id of the task
     */
    public function createUserTask($user_id, $task_id) {
        $stmt = $this->conn->prepare("INSERT INTO user_tasks(user_id, task_id) values(?, ?)");
        $stmt->bind_param("ii", $user_id, $task_id);
        $result = $stmt->execute();

        if (false === $result) {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
        }
        $stmt->close();
        return $result;
    }


    public function upload($user_id, $task_id) {

        $stmt = $this->conn->prepare("INSERT INTO user_tasks(user_id, task_id) values(?, ?)");
        $stmt->bind_param("ii", $user_id, $task_id);
        $result = $stmt->execute();

        if (false === $result) {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
        }
        $stmt->close();
        return $result;
    }


	/* 
		function uploadImage
	 */
	public function getEmailByApikey($api_key){
		$stmt = $this->conn->prepare("SELECT email FROM users WHERE api_key = ?");
		$stmt->bind_param("s", $api_key);
		if ($stmt->execute()) {
			$stmt->bind_result($email);
			$stmt->fetch();
			$user = array();
			$user["email"] = $email;
			$email1=str_replace('@','%40', $email);
			//$email = $email[0];
			$stmt->close();
			return $email1;
		} else {
			return NULL;

		}
	}
    function rand_string($length) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $size = strlen( $chars );
        $str="";
        for( $i = 0; $i < $length; $i++ ) {
            $str .= $chars[ rand( 0, $size - 1 ) ];
        }
        return $str;
    }

    public function uploadImage($email){

        /* print_r($email);
        exit; */
        if ($handle = opendir("images/$email/avatar")) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (is_dir("images/$email/avatar/$item")) {
                        remove_directory("images/$email/avatar/$item");
                    } else {
                        unlink("images/$email/avatar/$item");
                    }
                }
            }
            closedir($handle);
        }
        // file hợp lệ, tiến hành upload
        $path = "images/".$email."/avatar/"; // file sẽ lưu vào thư mục
        $tmp_name = $_FILES['avatar']['tmp_name'];
        $name = $_FILES['avatar']['name'];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $my_string =$this-> rand_string(6);

        $name = $my_string .".".$ext;
        //$name = "avatar.".$ext;
        $type = $_FILES['avatar']['type'];
        $size = $_FILES['avatar']['size'];
        move_uploaded_file($tmp_name,$path.$name);
        //return $_SERVER['HTTP_HOST']."/task_manager/v1/".$path.$name;
//        return "http://192.168.90.61/task_manager/v1/".$path.$name;
        $avatar_url="http://192.168.90.61:8080/task_manager/v1/".$path.$name;
        $email1=str_replace('%40','@', $email);
        $stmt = $this->conn->prepare("UPDATE users set avatar_url=? WHERE email=?");
        $stmt->bind_param("ss", $avatar_url,$email1);
        $stmt->execute();
        $stmt->close();
        return $avatar_url;
    }
}
?>
