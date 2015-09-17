<?php

require_once '../include/DbHandler.php';
require_once '../include/Videos.php';
require_once '../include/Nutritions.php';
require_once '../include/PassHash.php';
require_once '../include/BMI.php';
require_once '../include/HeartRate.php';
require_once '../include/HealthTest.php';
require '.././libs/Slim/Slim.php';


\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

// User id from db - Global Variable
$user_id = NULL;
$videosID = NULL;
/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {
    // Getting request headers
            $headers = apache_request_headers();
            $response = array();
            $app = \Slim\Slim::getInstance();

            // Verifying Authorization Header
            if (isset($headers['Authorization'])) {
                $db = new DbHandler();

                // get the api key
                $api_key = $headers['Authorization'];
                // validating api key
                if (!$db->isValidApiKey($api_key)) {
                    // api key is not present in users table
                    $response["errorcode"] = true;
                    $response["message"] = "Access Denied. Invalid Api key";
                    echoRespnse(401, $response);
                    $app->stop();
                } else {
                    global $user_id;
                    // get user primary key id
                    $user_id = $db->getUserId($api_key);
                }
            } else {
                // api key is missing in header
                $response["errorcode"] = true;
                $response["message"] = "Api key is misssing";
                echoRespnse(400, $response);
                $app->stop();
    }
}

/**
 * ------------------------------------------- METHODS Users ------------------------------------
 */
/**
 * User changepassword
 * url - /changepassword
 * method - POST
 * params - old_password, email, password
 */

$app->post('/changepassword', function() use ($app)
 {
                 verifyRequiredParams(array( 'old_password','password', 'apiKey'));

                 // reading post params
                 $old_password = $app->request()->post('old_password');
                 $password = $app->request()->post('password');
                 $apiKey = $app->request()->post('apiKey');
                 $response = array();
                 $db = new DbHandler();

                 if ($db->checkPass($apiKey,$old_password)) {
                     $res = $db->updatePassword($password, $apiKey);
                     if($res >0 ){
                         $response["status"] = "succeed";
                         $response['message'] = 'Update succesfully .';
                     }else{
                         $response['status'] = "failed";
                         $response['message'] = 'Update failed. Incorrect credentials';
                     }

                 } else {
                     // user credentials are wrong
                     $response['status'] = "failed";
                     $response['message'] = 'change failed. Incorrect credentials';
                     $response['errorcode']=3;
                 }
                 echoRespnse(200, $response);
 });

/**
 * forgetpassword
 * url - /forgetpassword
 * method - POST
 * params - email
 */

$app->post('/forgetpassword', function() use ($app)
{
                verifyRequiredParams(array('email'));
                require '../PHPMailer-master/PHPMailerAutoload.php';
                $email = $app->request->post('email');
                $db = new DbHandler();
                $response=array();
                // $a=$db->isUserExists($email);
                /*  print_r($a);
                 exit; */
                if(!$db->isUserExists($email))
                {
                     $response['status']="failed";
                     $response['errorcode']=9;

                    echoRespnse(200, $response);
                     $app->stop();
                }

                $res=$db->createPasswordToken($email);
                $mail = new PHPMailer;

                $mail->isSMTP();
                $mail->Host = 'mail.new-tech.vn';
                $mail->Port = 25;
                $mail->SMTPSecure = "ssl";
                $mail->SMTPDebug = 1;
                $mail->SMTPAuth= true;
                $mail->Username = 'health@new-tech.vn';
                $mail->Password ='123456';
                $mail->SMTPSecure = 'tls';
                $mail->From = 'health@new-tech.vn';
                $mail->FromName = 'Health';
                $mail->addAddress($email, 'Health');
                $mail->WordWrap = 50;
                $mail->isHTML(true);
                $mail->Subject = 'Forget password for Health App';
                $mail->Body = 'Please do not reply to this email. This email has been sent by a machine, replies will not be read. <br /> Hello,

                Someone (hopefully you) has requested to reset your password at the Health Support help desk. If you did not request this reset, please ignore this message <br/><br /><br/><br /><br/>'.'http://192.168.90.61:8080/task_manager/v1/forgetpassword.php?token='.$res.'<br /><br/> If asked, your password reset key is :

                When you visit the above page (which you must do within 24 hours)<br/>, you will be prompted to enter a new password.<br/> After you have submitted the form, you can log in normally using the new password you set.<br/><br/>
                ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

               Your question may already be answered in our knowledgebase:
                https://support.new-tech.com/kb_search.php?searchwords=

                As an alternate resource, please check Steam Discussions as other users may have resolved this issue:
                http://new-tech.com/';
//              if($mail->send()) echo json_encode("succeed");

            if(!$mail->send()) {
                    $response ['errorr']="Message could not be sent.";
                    $response['message errorr'] ="Mailer Error: " . $mail->ErrorInfo;
                    $app->stop();
                }
           else{
               $response['status']="succeed";

           }
//
            echoRespnse(200,$response);
    });



/**
 * register
 * url - /register
 * method - POST
 * params -'email', 'password','lastname', 'firstname', 'gender','phonenumber','address','country','birthday
 */


$app->post('/register', function() use ($app) {
            // check for required params
            verifyRequiredParams(array( 'email', 'password','lastname', 'firstname', 'gender','country','birthday'));
            $response = array();
            // reading post params
            $birthday = $app->request->post('birthday');
            $email = $app->request->post('email');
            $password = $app->request->post('password');
//          $username=$app->request->post('username');
			$lastname=$app->request->post('lastname');
            $firstname =$app->request->post('firstname');
            $gender=$app->request->post('gender');
            $phonenumber=$app->request->post('phonenumber');
            $address=$app->request->post('address');
            $country=$app->request->post('country');
            // validating email address
            validateEmail($email);
            if(empty($address)){
                $address="";
            }
            if(empty($phonenumber))
            {
                $phonenumber="";
            }
            $db = new DbHandler();
            $res = $db->createUser( $email, $password , $firstname, $lastname, $gender, $phonenumber, $address, $country  , $birthday);

            if ($res == USER_CREATED_SUCCESSFULLY) {
                $response["status"] ="succeed" ;
                $response["message"] = "You are successfully registered";
				/* $photo[]=array();
				$photo=explode("@",$email); */
				$photo=str_replace('@', '%40', $email);
	            mkdir("images/" .$photo."/avatar",0777,true);

            } else if ($res == USER_CREATE_FAILED) {
                $response["status"] = "failed";
                $response["message"] = "Oops! An error occurred while registereing";
                $response["errorcode"]=4;
            } else if ($res == USER_ALREADY_EXISTED) {
                $response["status"] = "failed";
                $response["message"] = "Sorry, this email already existed";
                $response["errorcode"]=1;
            }
            // echo json response
            echoRespnse(201, $response);
        });


/*----------------------------------------------Login-------------------------------------------------------*/
/**
 * User Login
 * url - /login
 * method - POST
 * params - email, password
 */

$app->post('/login', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('email', 'password'));

            // reading post params
            $email = $app->request()->post('email');
            $password = $app->request()->post('password');
            $response = array();
			
            $db = new DbHandler();
			
            // check for correct email and password
            if ($db->checkLogin($email, $password)) {
                // get the user by email
                $user = $db->getUserByEmail($email);

                if ($user != NULL) {
                    $response["status"] ="succeed";
//                    $response['name'] = $user['name'];
                    $response['email'] = $user['email'];
                    $response['apiKey'] = $user['api_key'];
                    $response['createdAt'] = $user['created_at'];
                    $response['birthday']=$user['birthday'];
                    $response['lastname']=$user['last_name'];
                    $response['firstname']=$user['first_name'];
                    $response['gender']=$user['gender'];
                    $response['phonenumber']=$user['phone_number'];
                    $response['address']=$user['address'];
                    $response['country']=$user['country'];
                    $response['avatar']=$user['avatar_url'];


                } else {
                    // unknown error occurred
                    $response['status'] = "failed";
                    $response['message'] = "An error occurred. Please try again";
//                    $response['errorcode']=4;
                }
            } else {
                // user credentials are wrong
                $response['status'] = "failed";
                $response['message'] = 'Login failed. Incorrect credentials';
                $response['errorcode']=3;
            }
            echoRespnse(200, $response);
        });


/**
 * User Login by FB
 * url - /login
 * method - POST
 * params - token
 */

$app->post('/loginfacebook', function() use ($app) {


            $response1 = array();
            $db = new DbHandler();
            verifyRequiredParams(array('token'));
            $access_token =$app->request->post('token');
            $response= array();
            //    $fbUserId = $facebook->getUser();
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/me?access_token=$access_token");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $response = curl_exec($ch);
            curl_close($ch);
            $user = json_decode($response);
            session_start();
            $_SESSION['user_login'] = true;
            if(empty($user->birthday))
            {
                $user->birthday="1/1/2014";
            }
            if(empty($user->email))
            {

                $response2= array();

                if(empty($user->email)){
                    $response2['messageboxemail'] = "email missing";
                }
                $response2["status"] ="failed";
                $response2['message'] = "lack of information";
                $response2['errorcode']=11;
                echoRespnse(200, $response2);
                $app->stop();

            }
            $name= $_SESSION['user_name'] = $user->name;
            $email=$_SESSION['Email']= $user->email;
            $local= $_SESSION['local']= $user->locale;
            $gender=$_SESSION['gender']= $user->gender;
            $lastname=$_SESSION['last_name']= $user->last_name;
            $firstname=$_SESSION['first_name']= $user->first_name;
            $birthday=$_SESSION['birthday']= $user->birthday;
            $phonenumber="";
            $address="";
            $country1=strstr($local, "_");
            $country = str_replace('_', "", $country1 );

    if($db->isUserExists($email))
    {
        $user1 = $db->getUserByEmail($email);

        if ($user1 != NULL)
        {
            $response1["status"] ="succeed";
            $response1['email'] = $user1['email'];
            $response1['apiKey'] = $user1['api_key'];
            $response1['createdAt'] = $user1['created_at'];
            $response1['birthday']=$user1['birthday'];
            $response1['lastname']=$user1['last_name'];
            $response1['firstname']=$user1['first_name'];
            $response1['gender']=$user1['gender'];
            $response1['phonenumber']=$user1['phone_number'];
            $response1['address']=$user1['address'];
            $response1['country']=$user1['country'];
            $response1['avatar']=$user1['avatar_url'];
        }

    }
    else {
//
        $res = $db->createUserByFacebook( $email , $firstname, $lastname, $gender, $phonenumber, $address, $country , $birthday);
        $user1 = $db->getUserByEmail($email);
            $response1["status"] ="succeed";
            //$response['name'] = $user['name'];
            $response1['email'] =  $email;
            $response1['lastname']=$lastname;
            $response1['firstname']=$firstname;
            $response1['gender']=$gender;
            $response1['country']=$country;
            $response1['birthday']=$birthday;
            $response1['apiKey'] = $user1['api_key'];
            $response1['phonenumber']=$user1['phone_number'];
            $response1['address']=$user1['address'];
            $response1['avatar']=$user1['avatar_url'];
            $photo=str_replace('@', '%40', $email);
             mkdir("images/" .$photo.'/avatar',0777,true);
            if ($res == USER_CREATED_SUCCESSFULLY) {
                $response1["status"] ="succeed" ;
                $response1["message"] = "You are successfully registered";
            }

    }
    echoRespnse(200, $response1);

});
/*------------------------------------------------End Login-------------------------------------------------------*/




/*------------------------------------------------ Users Attack-------------------------------------------------------*/

/*
	get userInfo
	url - /userInfo
	method - POST
	params - api_key
	response - first_name,last_name,gender,phone_number,address,country,birthday,avatar_url
 */
$app->post('/userInfo', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('apikey'));
            // reading post params
            $apikey = $app->request()->post('apikey');
            $response = array();
            $db = new DbHandler();
            // check for api_key
            $user = $db->getUserInfoByApikey($apikey);

            if ($user != NULL) {

                $response["status"] ="succeed";

                $response['firstname'] = $user['first_name'];
                $response['lastname'] = $user['last_name'];
                $response['gender'] = $user['gender'];
                $response['phonenumber']=$user['phone_number'];
                $response['address']=$user['address'];
                $response['country']=$user['country'];
                $response['birthday']=$user['birthday'];
                $response['avatar']=$user['avatar_url'];

            } else {
                // unknown error occurred
                $response['status'] = "failed";
                $response['message'] = "An error occurred. Please try again";
            }
            echoRespnse(202, $response);
});


/**
 * update users
 * url - /update
 * method - POST
 * params - 'firstname', 'lastname', 'gender', 'phonenumber', 'address', 'country', 'birthday', 'apiKey'
 */		
$app->post('/updateaccount', function() use ($app) {
            // check for required params
            verifyRequiredParams(array( 'firstname', 'lastname', 'gender', 'country', 'birthday', 'apiKey'));

            // reading post params
			$firstname = $app->request()->post('firstname');
            $lastname = $app->request()->post('lastname');
            $gender = $app->request()->post('gender');
            $phonenumber = $app->request()->post('phonenumber');
            $address = $app->request()->post('address');
            $country = $app->request()->post('country');
            $birthday = $app->request()->post('birthday');
            $apiKey = $app->request()->post('apiKey');
            $response = array();
            $db = new DbHandler();
            //

            if(empty($address)){

                $address="";

            }
            if(empty($phonenumber))
            {
                $phonenumber="";
            }

			$res = $db->updateUser($firstname, $lastname, $gender, $phonenumber, $address, $country, $birthday, $apiKey);



			if($res >0 ){
				$response["status"] = "succeed";
				$response['message'] = 'Update succesfully .';
                $response['firstname'] =$firstname;
                $response['lastname'] =$lastname;
                $response['gender'] =$gender;
                $response['phonenumber']=$phonenumber;
                $response['address']=$address;
                $response['country']= $country;
                $response['birthday']=$birthday;
           	}else{
				$response['status'] = "failed";
                $response['message'] = 'Update failed. Incorrect credentials';
                $response['errorcode'] = "6";
			}
            echoRespnse(200, $response);
        });


/**
 * upload images
 * url - /upload
 * method - POST
 * params - aip_key
 * files - name: avata
 */		
$app->post('/upload' ,function() use ($app){
            verifyRequiredParams(array('apikey'));
            $api_key = $app->request->post('apikey');
            $response = array();
            $db = new DbHandler();
            $email = $db->getEmailByApikey($api_key);
            $app->response()->header("Content-Type","application/json");
            if($_FILES['avatar']['type'] == "image/jpeg"
            || $_FILES['avatar']['type'] == "image/png"
            || $_FILES['avatar']['type'] == "image/gif"){
                 if($_FILES['avatar']['size'] > 1048576){
                    $response["status"] = "failed";
                    $response["errorcode"] = "7";

                }else{
                    $img = $db->uploadImage($email);

                    $response["status"] = "succeed";
                    $response["avatar"] = $img;
                }
            }else{
               // không phải file ảnh
                $response["status"] = "failed";
                $response["errorcode"] = "8";
                $response['message'] = 'Not image';
            }
                    echoRespnse(202, $response);
});
/*------------------------------------------------End -------------------------------------------------------*/


/*------------------------------------------------Videos-------------------------------------------------------*/



/**
 * show video for categoryId
 * url - /getvideowihtcategoryId
 * method - POST
 * params - api_key, categoryID
*/
$app->post('/getvideobycategoryid',function() use($app){

        verifyRequiredParams(array('apiKey',"categoryID"));
        $api_key = $app->request->post('apiKey');
        $categoryID = $app->request->post('categoryID');
        $db = new DbHandler();
        $dbvideo= new GetVideo();
        $response=array();
        if (!$db->isValidApiKey($api_key)) {
            // api key is not present in users table
            $response["message"] = "Access Denied. Invalid Api key";
            $response["status"] = "failed";
            $response["errorcode"] = "12";
            echoRespnse(401, $response);
            $app->stop();
        }
        else{

        if($dbvideo->isCategoryExists($categoryID))
        {
            $response["data"] = array();
            $result = $dbvideo->getAllvideobyCategoryID($categoryID);
            $tmp = array();
            $response["status"] = "succeed";

            while ($task = $result->fetch_assoc()){

                $tmp["categoryID"] = $task["CategoryID"];
                $tmp["id"] = $task["VideosID"];
                $tmp["title"] = $task["Title"];
                $tmp["duration"] = $task["Duration"];
                $tmp["thumbnailurl"] = $task["ThumbnailURL"];
                $tmp["url"] = $task["URL"];
                $tmp['timeupdate']=$task["TimeUpDate"];
                $tmp["view"]=$task["View"];
                $tmp["originthumbnailurl"]=$task["OriginThumbnailURL"];
//                        print_r($tmp);die;
                array_push($response["data"],$tmp);
            }
        }
        else{
            $response["status"] = "failed";
            $response["message"] = "No ID category";
            $response["errorcode"] = "14";

        }
    }
    echoRespnse(200, $response);
});
/**
 * show video for categoryId
 * url - /getVideosByApiKey
 * method - POST
 * params - api_key
 */

$app->post('/getVideosByApiKey',function() use($app){

            verifyRequiredParams(array('apiKey'));
            $apiKey = $app->request->post('apiKey');
              $gv = new GetVideo();
            $response=array();
            $healthID = $gv->getHealthByApikey($apiKey);
            if (!$gv->isValidApiKey($apiKey)) {
                // api key is not present in users table
                $response["status"] = "failed";
                $response['errorcode'] = "12";
                $response["message"] = "Access Denied. Invalid Api key";
            }
            else{
                $response['status'] = "succeed";
                $result = $gv->getVideosIDByHealthID($healthID);
                $tmp = array();
                $response["data"] = array();

                while ($task = $result->fetch_assoc()){
                    $tmp["id"] = $task["VideosID"];
                    $tmp["title"] = $task["Title"];
                    $tmp["duration"] = $task["Duration"];
                    $tmp["thumbnailurl"] = $task["ThumbnaiURL"];
                    $tmp["url"] = $task["URL"];
                    $tmp["originthumbnailurl"]=$task["OriginThumbnailURL"];
                    $tmp['timeupdate']=$task["TimeUpDate"];
                    $tmp["view"]=$task["View"];
                    $tmp["categoryID"] = $task["CategoryID"];

                    array_push($response["data"],$tmp);
                }
            }
    echoRespnse(200, $response);
});

/**
 * show category
 * url - /getvideocategory
 * method - POST
 * params - api_key
 */
$app->post('/getvideocategory',function() use($app){

//            global $api_key;
            verifyRequiredParams(array('apiKey'));
            $api_key = $app->request->post('apiKey');

            $db = new DbHandler();
            $dbvideo= new GetVideo();
             $response=array();

            if (!$db->isValidApiKey($api_key)) {
                // api key is not present in users table
                $response["errorcode"] = 12;
                $response["status"] = "failed";
                $response["message"] = "Access Denied. Invalid Api key";
                echoRespnse(401, $response);
                $app->stop();
            }

            else{
                $response["status"] = "succeed";

                $healthID=$db->getUserId($api_key);

                $result = $dbvideo->getthumbnail($healthID);

                $response["myvideo"]=array();

                $tmp = array();

                while ($task = $result->fetch_assoc())
                {
                    $tmp["thumbnailurl"] = $task["ThumbnailURL"];
                    $tmp["id"]=$task["VideosID"];

                    $count= $dbvideo->geturlcount($healthID);

                        while ($task1 = $count->fetch_assoc())
                        {
                            $tmp["quantity"]=$task1["count"];
                            break;
                        }
                    array_push($response["myvideo"], $tmp);

                }
//                while($task1 = $count->fetch_assoc())
//                {
//
//                    $tmp["quantity"]=$task1;
//                    array_push($response["myvideo"], $tmp);
//                }


                $videoId=140;
                $response["video"] = array();
                $videos = $dbvideo->getvideodefault($videoId);

                while ($task2 = $videos->fetch_assoc())
                {
                    $tmp = array();
                    $tmp["id"]=$task2["VideosID"];
                    $tmp["title"]=$task2["Title"];
                    $tmp["duration"]=$task2["Duration"];
                    $tmp["thumbnailurl"]=$task2["ThumbnailURL"];
                    $tmp["url"]=$task2["URL"];
                    $tmp["categoryID"]=$task2["CategoryID"];
                    $tmp["timeupdate"]=$task2["TimeUpDate"];
                    $tmp["view"]=$task2["View"];
                    $tmp["originthumbnailurl"]=$task2["OriginThumbnailURL"];

                 array_push($response["video"],$tmp);

                }

               $result = $dbvideo->getAllcategory();
               $tmp = array();
               $response["data"] = array();

                   while ($task = $result->fetch_assoc())
                   {
                     //
                     $tmp["id"] = $task["CategoryID"];
                     $count= $dbvideo->geturlcategoryid($tmp["id"]);

                       while ($task1 = $count->fetch_assoc())
                       {
                           $tmp["quantity"]=$task1["count"];
                           break;
                       }
                      $tmp["title"] = $task["Title"];
                     $tmp["iconurl"] = $task["IconURL"];
                     $tmp["version"] = $task["Version"];
    //               $tmp["timeupdate"]=$task["TimeUpDate"];
                     array_push($response["data"],$tmp);

                      }
            }
            echoRespnse(200, $response);

//}
});
/**
 * show category
 * url - /getvideothumnail
 * method - POST
 * params - api_key
 */

$app->post('/getvideothumnail',function() use($app){

                verifyRequiredParams(array('apiKey'));
                $api_key = $app->request->post('apiKey');
                $db = new DbHandler();

                $dbvideo= new GetVideo();

                $response=array();
                if(!$db->isUserExit($api_key))
                {
                    $response['status'] = "failed";
                    $response['message'] = 'apiKey does not exist';
                    $response['errorcode'] = "12";
                    echoRespnse(401, $response);
                    $app->stop();
                }
                else{
                $response['status'] = "succeed";
                $healthID=$db->getUserId($api_key);

                $result = $dbvideo->getthumbnail($healthID);

                $response["myvideo"]=array();

                    $tmp = array();

                while ($task = $result->fetch_assoc())
                {
                     $tmp["thumbnailurl"] = $task["ThumbnailURL"];
                    $tmp["id"]=$task["VideosID"];

                    $count= $dbvideo->geturlcount($healthID);

                    while ($task1 = $count->fetch_assoc())
                    {
                        $tmp["quantity"]=$task1["count"];
                        break;
                    }
                    array_push($response["myvideo"], $tmp);

                }

                 echoRespnse(200, $response);
    }
});
/**
 * show category
 * url - /viewVideo
 * method - POST
 * params - api_key,videoID
 */


$app->post('/viewVideo', function() use ($app) {

                // check for required params
                verifyRequiredParams(array('apiKey', 'videoID'));

                // reading post params
                $api_key = $app->request()->post('apiKey');
                $VideosID = $app->request()->post('videoID');
                $response = array();
                $db = new DbHandler();
                $dbvideo=new GetVideo();
                // check for correct email and password
                $View = $dbvideo ->GetViewByCategoryID($VideosID)+1;
                if ($db ->isUserExit($api_key)) {
                    // get the user by email
                    $upView = $dbvideo ->updateView($View,$VideosID);
                    $response['status'] = "succeed";
                } else {
                    // unknown error occurred
                    $response['status'] = "failed";
                    $response['message'] = "An error occurred. Please try again";
                }
                echoRespnse(200, $response);
});

/**
 * deleteHealthVideos
 * url - /deleteHealthVideos
 * method - POST
 * params - apiKey, videosID
 */

$app->post('/deleteHealthVideos', function() use ($app) {
    // check for required params
                verifyRequiredParams(array('apiKey' , 'videoID'));
                $response = array();

                // reading post params
                $apiKey = $app->request->post('apiKey');
                $videosID = $app->request->post('videoID');
                $user = new DbHandler();
                $gv = new GetVideo();
                $healthID = $user->getHealthByApikey($apiKey);
                // check for correct email and password
                if ($user->isValidApiKey($apiKey) and $gv->isVideosExit($videosID)) {
                    if($gv->isVideosByHealthUser_videosExit($videosID,$healthID)){
                        $delete = $gv->deleteHealthVideos($healthID,$videosID);
                        $response['status'] = "succeed";
                    }else{
                        $response['status'] = "failed";
                        $response['errorcode'] = "";
                    }
                } else {
                    // unknown error occurred
                    $response['status'] = "failed";
                    $response['errorcode'] = "12";
                    $response["message"] = "Access Denied. Invalid Api key";
                }
                // echo json response
                echoRespnse(201, $response);
});

$app->post('/ViewVideos', function() use ($app) {
                // check for required params
                verifyRequiredParams(array('api_key', 'VideosID'));

                // reading post params
                $api_key = $app->request()->post('api_key');
                $VideosID = $app->request()->post('VideosID');
                $response = array();

                $gv = new GetVideo();
                // check for correct email and password
                $View = $gv->GetViewByCategoryID($VideosID)+1;
                if ($gv->isUserExit($api_key)) {
                    $upView = $gv->updateView($View,$VideosID);
                    $response['status'] = "succeed";
                } else {
                    // unknown error occurred
                    $response['status'] = "failed";
                    $response['errorcode'] = "12";
                    $response["message"] = "Access Denied. Invalid Api key";

                }
                echoRespnse(200, $response);
});

/**
 * insertHealthVideos
 * url - /insertHealthVideos
 * method - POST
 * params - apiKey, videosID
 */

$app->post('/insertHealthVideos', function() use ($app) {
                // check for required params
                verifyRequiredParams(array('apiKey', 'videoID'));
                $response = array();

                // reading post params
                $apiKey = $app->request->post('apiKey');
                $videosID = $app->request->post('videoID');

                $user = new DbHandler();
                $gv = new GetVideo();
                $healthID = $gv->getHealthByApikey($apiKey);
                // check for correct email and password
                if ($user->isValidApiKey($apiKey) and $gv->isVideosExit($videosID)) {

                    if(!$gv->isVideosByHealthUser_videosExit($videosID,$healthID)){

                        $insert = $gv->insertHealthVideos($healthID,$videosID);
                        $response['status'] = "succeed";
                    }
                    else{
                        $response['status'] = "failed";
                        $response['errorcode'] = "13";
                        $response["message"] = "Access Denied. Invalid videosID";
                    }
                } else {
                    // unknown error occurred
                    $response['status'] = "failed";
                    $response['errorcode'] = "12";
                    $response["message"] = "Access Denied. Invalid Api key";
                }
                // echo json response
                echoRespnse(201, $response);
});



/*------------------------------------------------End Videos-------------------------------------------------------*/
/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {

            $error = false;
            $error_fields = "";
            $request_params = array();
            $request_params = $_REQUEST;
            // Handling PUT request params
            if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
                $app = \Slim\Slim::getInstance();
                parse_str($app->request()->getBody(), $request_params);
            }
            foreach ($required_fields as $field) {
                if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
                    $error = true;
                    $error_fields .= $field . ', ';
                }
            }

            if ($error) {
                // Required field(s) are missing or empty
                // echo error json and stop the app
                $response = array();
                $app = \Slim\Slim::getInstance();
                $response["status"] = "failed";
                $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
                $response["errorcode"]=5;
                echoRespnse(400, $response);
                $app->stop();
            }

}



/*------------------------------------------------Api Nutritions-------------------------------------------------------*/
/**
 * insertHealthNutritions
 * url - /insertHealthNutritions
 * method - POST
 * params - apiKey, NutritionID
 */

$app->post('/insertHealthNutritions', function() use ($app) {
    // check for required params
            verifyRequiredParams(array('apiKey' , 'nutritionID'));
            $response = array();

            // reading post params
            $apiKey = $app->request->post('apiKey');
            $NutritionID = $app->request->post('nutritionID');
            $gn = new GetNutritions();
            $user = new DbHandler();
            $healthID = $user->getHealthByApikey($apiKey);
            // check for correct email and password
            if ($user->isValidApiKey($apiKey) and $gn->isNutritionsExit($NutritionID)) {
                if(!$gn->isNutritionsByNutritions_HealthUserExit($healthID,$NutritionID)){
                    $insert = $gn->insertHealthNutritions($healthID,$NutritionID);
                    $response['status'] = "succeed";
                }else{
                    $response['status'] = "failed";
                    $response['errorcode'] = "13";
//                    $response["message"] = "";
                }
            } else {
                // unknown error occurred
                $response['status'] = "failed";
                $response['errorcode'] = "12";
                $response["message"] = "Access Denied. Invalid Api key";
            }
            // echo json response
            echoRespnse(201, $response);
});

/**
 * deleteHealthNutritions
 * url - /deleteHealthNutritions
 * method - POST
 * params - apiKey, NutritionID
 */

$app->post('/deleteHealthNutrition', function() use ($app) {
    // check for required params
            verifyRequiredParams(array('apiKey' ,'nutritionID'));
            $response = array();
            // reading post params
            $apiKey = $app->request->post('apiKey');
            $NutritionID = $app->request->post('nutritionID');
            $user = new DbHandler();
            $gn = new GetNutritions();
            $healthID = $user->getHealthByApikey($apiKey);
            // check for correct email and password
            if ($user->isValidApiKey($apiKey) and $gn->isNutritionsExit($NutritionID)) {
                if($gn->isNutritionsByNutritions_HealthUserExit($healthID,$NutritionID)){
                    $insert = $gn->deleteHealthNutritions($healthID,$NutritionID);
                    $response['status'] = "succeed";
                }else{
                    $response['status'] = "failed";
                    $response['errorcode'] = "";
                }
            } else {
                // unknown error occurred
                $response['status'] = "failed";
                $response['errorcode'] = "12";
                $response["message"] = "Access Denied. Invalid Api key";
            }
            // echo json response
            echoRespnse(201, $response);
});

/**
 * getNutritionIDByHealthID
 * url - /getNutritionIDByHealthID
 * method - POST
 * params - apiKey
 */

$app->post('/getNutritionIDByHealthID',function() use($app){

            verifyRequiredParams(array('apiKey'));
            $apiKey = $app->request->post('apiKey');
            $user = new DbHandler();
            $gn = new GetNutritions();
            $response=array();
            $healthID = $user->getHealthByApikey($apiKey);

            if (!$user->isValidApiKey($apiKey)) {
                // api key is not present in users table
                $response["status"] = "failed";
                $response['errorcode'] = "12";
                $response["message"] = "Access Denied. Invalid Api key";
            }
            else{
                $response['status'] = "succeed";
                $result = $gn->getNutritionIDByHealthID($healthID);
                $tmp = array();
                $response["data"] = array();

                while ($task = $result->fetch_assoc())
                {
                    $tmp["id"] = $task["NutritionID"];
                    $tmp["title"] = $task["Title"];
                    $tmp["thumbnailurl"] = $task["ThumbnailURL"];
                    $tmp["categoryID"] = $task["CategoryID"];
                    $tmp["url"] = $task["URL"];

                    $tmp["timeupload"] = $task["TimeUpLoad"];
                    $tmp["view"] = $task["View"];
                    $tmp["originthumbnailurl"] = $task["OriginThumbnailURL"];

                    array_push($response["data"],$tmp);
                }
            }
            echoRespnse(200, $response);
});

/**
 * get Nutrition With CategoryId
 * url - /getNutritionWithCategoryId
 * method - POST
 * params - apiKey,categoryID
 */

$app->post('/getNutritionWithCategoryId', function() use ($app){

            verifyRequiredParams(array('apiKey',"categoryID"));
            $api_key = $app->request->post('apiKey');
            $categoryID = $app->request->post('categoryID');
            $db = new DbHandler();
            $getDbNutrition= new GetNutritions();
            $response=array();
            $response['data']=array();

            if (!$db->isValidApiKey($api_key)) {
                // api key is not present in users table

                $response["message"] = "Access Denied. Invalid Api key";
                $response["status"] = "failed";
                $response["errorcode"] = "12";
                echoRespnse(401, $response);
                $app->stop();

            }
            else{
                $response['status'] = "succeed";
                if($getDbNutrition->isCategoryExists($categoryID)){
                    $tmp=array();
                    $getNutritionWithId=$getDbNutrition->getAllNutritionByCategoryID($categoryID);
                    while($category=$getNutritionWithId->fetch_assoc()){

                        $tmp["id"]=$category["NutritionID"];
                        $tmp["title"]=$category["Title"];
                        $tmp["thumbnailurl"]=$category["ThumbnailURL"];
                        $tmp["originthumbnailurl"]=$category["OriginThumbnailURL"];
                        $tmp["timeupload"]=$category["TimeUpLoad"];
                        $tmp["view"]=$category["View"];
                        $tmp["categoryID"]=$category["CategoryID"];
                        $tmp["url"]=$category["URL"];
                        array_push($response["data"], $tmp);
                    }
                }

                else{
                    $response["status"] = "failed";
                    $response["message"] = "No ID category";
                    $response["errorcode"] = "14";

                }
            }
            echoRespnse(200,$response);

});


/**
 * get Nutrition Thumnail
 * url - /getNutritionThumnail
 * method - POST
 * params - apiKey
 */

$app->post('/getNutritionThumnail',function() use($app){

            verifyRequiredParams(array('apiKey'));
            $api_key = $app->request->post('apiKey');
            $db = new DbHandler();

            $getDbNutrition = new GetNutritions();
            $response=array();
            if(!$db->isUserExit($api_key))
            {
                $response['status'] = "failed";
                $response['message'] = 'apiKey does not exist';
                $response['errorcode'] = "18";
                echoRespnse(401, $response);
                $app->stop();
            }
            else{
                $response['status'] = "succeed";
//
                $healthID=$db->getUserId($api_key);

                $result = $getDbNutrition->getNutritionThumbnail($healthID);

                $response["mynutrition"]=array();

                $tmp = array();

                while ($task = $result->fetch_assoc())
                {
                    $tmp["thumbnailurl"] = $task["ThumbnailURL"];
//                    $tmp["id"]=$task["VideosID"];

                    $count= $getDbNutrition->getcountNutritionUser($healthID);

                    while ($task1 = $count->fetch_assoc())
                    {
                        $tmp["quantity"]=$task1["count"];
                        break;
                    }
                    array_push($response["mynutrition"], $tmp);
//                }
                }

                echoRespnse(200, $response);
            }
});

/**
 * get Category Nutrition
 * url - /getCategoryNutrition
 * method - POST
 * params - apiKey
 */

$app->post('/getCategoryNutrition', function() use ($app) {

            verifyRequiredParams(array('apiKey'));
            $api_key = $app->request->post('apiKey');
            $db= new DbHandler();
            $response = array();

            $getDbNutrition = new GetNutritions();
            if(!$db->isUserExit($api_key))
            {
                $response['status'] = "failed";
                $response['message'] = 'apiKey does not exist';
                $response['errorcode'] = "18";
                echoRespnse(401, $response);
                $app->stop();
            }
            else{

               $response["status"]="succeed";

                $response["data"]=array();
                $tmp=array();
                $healthID=$db->getUserId($api_key);
//
                $result = $getDbNutrition->getNutritionThumbnail($healthID);

                $response["mynutrition"]=array();
                $nutritionID=1;
                $response["Nutrition"] = array();
                $nutrition = $getDbNutrition->getNutritionDefault(38);

                while ($task2 = $nutrition->fetch_assoc())
                {
                    $tmp2=array();
                    $tmp2["id"]=$task2["NutritionID"];
                    $tmp2["title"]=$task2["Title"];
                    $tmp2["thumbnailurl"]=$task2["ThumbnailURL"];
                    $tmp2["url"]=$task2["URL"];
                    $tmp2["categoryID"]=$task2["CategoryID"];
                    $tmp2["timeupload"]=$task2["TimeUpLoad"];
                    $tmp2["view"]=$task2["View"];
                    $tmp2["originthumbnailurl"]=$task2["OriginThumbnailURL"];
                    array_push($response["Nutrition"],$tmp2);

                }

                while ($task = $result->fetch_assoc())
                {
                    $tmp1=array();
                    $tmp1["thumbnailurl"] = $task["ThumbnailURL"];
//                    $tmp["id"]=$task["VideosID"];

                    $count= $getDbNutrition->getcountNutritionUser($healthID);

                    while ($task1 = $count->fetch_assoc())
                    {
                        $tmp1["quantity"]=$task1["count"];
                        break;
                    }
                    array_push($response["mynutrition"], $tmp1);
//                }
                }

                $getAllNutritionCategory= $getDbNutrition->getCategoryNutritions();


                while($category=$getAllNutritionCategory->fetch_assoc())
                {
                    $tmp["id"]=$category["CategoryID"];
                    $quantity=$getDbNutrition->getQuantityNutriton( $tmp["id"]);

                    while ($task = $quantity->fetch_assoc())
                    {
                        $tmp["quantity"]=$task["count"];
                        break;
                    }
                    $tmp["title"]=$category["Title"];
                    $tmp["thumbnailurl"]=$category["ThumbnailURL"];
                    $tmp["version"]=$category["Version"];



                    array_push($response["data"], $tmp);
                }
            }

            echoRespnse(200,$response);

});
/*
 * get view nutrition
 * method post
 * params apiKey ,NutritionID

*/

$app->post('/viewNutritions', function() use ($app) {
                // check for required params
                verifyRequiredParams(array('apiKey', 'nutritionID'));

                // reading post params
                $apiKey = $app->request()->post('apiKey');
                $NutritionID = $app->request()->post('nutritionID');
                $response = array();
                $user = new DbHandler();
                $gn = new GetNutritions();
                // check for correct email and password
                $View = $gn->GetViewByNutritionID($NutritionID)+1;
                if ($user->isValidApiKey($apiKey)) {
                    $upView = $gn->updateView($View,$NutritionID);
                    $response['status'] = "succeed";
                } else {
                    // unknown error occurred
                    $response['status'] = "failed";
                    $response['message'] = "12";
                    $response["message"] = "Access Denied. Invalid Api key";
                }
                echoRespnse(200, $response);
});


/* ----------------------------------------------------End API Nutrition----------------------------------------------------------------------------- */

//
//
/* ------------------------------------------------API HeartRate--------------------------------------------------------------------- */

/*
 * get view HeartRate
 * method post
 * params apiKey

*/
$app->post('/getHeartRate',function() use($app){

                verifyRequiredParams(array('apiKey'));
                $apiKey = $app->request->post('apiKey');
                $db = new DbHandler();
                $dbHeartRate = new getHeartRate();
                $response=array();
                $healthID=$db->getUserId($apiKey);
                if (!$db->isValidApiKey($apiKey)) {
                    // api key is not present in users table
                    $response['status'] = "failed";
                    $response['message'] = 'apiKey does not exist';
                    $response['errorcode'] = "18";
                    echoRespnse(401, $response);
                    $app->stop();
                }
                else{
                    $response['status'] = "succeed";
                    $result = $dbHeartRate->getAllHeartRate($healthID);
                    $tmp = array();
                    $response["data"] = array();

                    while ($task = $result->fetch_assoc())
                    {
                        $tmp["id"] = $task["ID"];
                        $tmp["time"] = $task["Time"];
                        $tmp["value"] = $task["Value"];
                        array_push($response["data"],$tmp);
                    }
                }
                echoRespnse(200, $response);
});

/*
 * update HeartRate
 * method post
 * params apiKey, array data

*/
$app->post('/updateHeartRate',function() use($app){

            verifyRequiredParams(array('apiKey','data'));
            $apiKey = $app->request->post('apiKey');
            $data = $app->request->post('data');
            $db = new DbHandler();
            $dbHeartRate = new getHeartRate();
            $response=array();
            $healthID=$db->getUserId($apiKey);
            $heartRateList = json_decode($data,true);


            if (!$db->isValidApiKey($apiKey)) {
                // api key is not present in users table
                $response['status'] = "failed";
                $response['message'] = 'apiKey does not exist';
                $response['errorcode'] = "18";
                echoRespnse(401, $response);
                $app->stop();
            }

            else {
                if($apiKey !=null){
                    $result = $dbHeartRate->deleteHealthId($healthID);
                    if(count($heartRateList)==0){
                        $response["status"] = 'succeed';
                        echoRespnse(200, $response);
                        $app->stop();
                    }
                    else{
                    for($i=0;$i<count($heartRateList);$i++){
                        $list= array(
                            $value=$heartRateList[$i]["value"],
                            $time=$heartRateList[$i]["time"]
                        );
                        $insertBMI=$dbHeartRate->insertBMI($time,$value,$healthID);
                    }
                    }
        //                        $insertBMI=$dbBMI->insertBMIFormJson();
                    if($result&&$insertBMI){
                        $response["status"] = 'succeed';
                        echoRespnse(200, $response);
                    }
                    else{
                        $response['status'] = "failed";
                        $response['message'] = 'can not insert to database';
                        $response['errorcode'] = "19";
                        echoRespnse(200, $response);
                    }
                }

                else{
        //                        $insertBMI=$dbBMI->insertBMIFormJson();
                    if(count($heartRateList)==0){
                        $response["status"] = 'succeed';
                        echoRespnse(200, $response);
                    }
                    else{
                    for($i=0;$i<count($heartRateList);$i++){

                        $list= array(
                            $value=$heartRateList[$i]["value"],
                            $time=$heartRateList[$i]["time"]
                        );
                        $insertBMI=$dbHeartRate->insertBMI($time,$value, $healthID);
                    }
                    }
                    if($insertBMI){

                        $response["status"] = 'succeed';
                        echoRespnse(200, $response);

                    }
                    else{
                        $response['status'] = "failed";
                        $response['message'] = 'you not insert to database';
                        $response['errorcode'] = "19";

                        echoRespnse(200, $response);
                    }
                }

    }
});

/* ------------------------------------------------End API HeartRate--------------------------------------------------------------------- */
//
//


/* ------------------------------------------------API BMI--------------------------------------------------------------------- */
/*
 * get view BMI
 * method post
 * params apiKey

*/

$app->post('/getBMI',function() use($app){

                verifyRequiredParams(array('apiKey'));
                $apiKey = $app->request->post('apiKey');
                $db = new DbHandler();
                $dbBMI = new getBMI();
                $response=array();
                $healthID=$db->getUserId($apiKey);

                if (!$db->isValidApiKey($apiKey)) {
                    // api key is not present in users table
                    $response['status'] = "failed";
                    $response['message'] = 'apiKey does not exist';
                    $response['errorcode'] = "18";
                    echoRespnse(401, $response);
                    $app->stop();
                }
                else{
                    $response['status'] = "succeed";
                    $result = $dbBMI->getAllBmi($healthID);
                    $tmp = array();
                    $response["data"] = array();

                    while ($task = $result->fetch_assoc())
                    {
                        $tmp["id"] = $task["ID"];
                        $tmp["time"] = $task["Time"];
                        $tmp["weight"] = $task["Weight"];
                        $tmp["height"] = $task["Height"];
                        $tmp["weightUnit"] = $task["WeightUnit"];
                        $tmp["heightUnit"] = $task["HeightUnit"];
//                      $tmp["TimeStamp"] = $task["TimeStamp"];
                        array_push($response["data"],$tmp);
                    }
                }
                echoRespnse(200, $response);
});

/*
 *  update BMI
 * method post
 * params apiKey, array data

*/

$app->post('/updateBMI',function() use($app){

                verifyRequiredParams(array('apiKey','data'));
                $apiKey = $app->request->post('apiKey');
                $data = $app->request->post('data');

                $db = new DbHandler();
                $dbBMI = new getBMI();

                $response=array();
                $healthID=$db->getUserId($apiKey);
                $bmiList = json_decode($data,true);

                if(!$bmiList){
                    $response['status'] = "failed";
                    $response['errorcode'] = "20";
                    $response['message'] = 'Incorrect data ';
                    echoRespnse(401, $response);
                    $app->stop();
                }

                 if (!$db->isValidApiKey($apiKey)) {
                    // api key is not present in users table
                    $response['status'] = "failed";
                    $response['message'] = 'apiKey does not exist';
                    $response['errorcode'] = "18";
                    echoRespnse(401, $response);
                    $app->stop();
                 }

                 else {
                    if($apiKey !=null){
                        $result = $dbBMI->deleteHealthId($healthID);
                        if(count($bmiList)==0){
                            $response["status"] = 'succeed';
                            echoRespnse(200, $response);
                        }
                        else{
                        for($i=0;$i<count($bmiList);$i++){
                            $list= array(
                                $time=$bmiList[$i]["time"],
                                $height=$bmiList[$i]["height"],
                                $weight=$bmiList[$i]["weight"],
                                $heightUnit=$bmiList[$i]["heightUnit"],
                                $weightUnit=$bmiList[$i]["weightUnit"],
                            );
                            $insertBMI=$dbBMI->insertBMI($time,$height,$weight,$heightUnit,$weightUnit,$healthID);
                        }
//                        $insertBMI=$dbBMI->insertBMIFormJson();
                            if($result&&$insertBMI){
                                $response["status"] = 'succeed';
                               echoRespnse(200, $response);
                            }
                            else{
                                $response['status'] = "failed";
                                $response['message'] = 'you not insert to database';
                                $response['errorcode'] = "19";
                                echoRespnse(200, $response);
                            }
                         }
                    }
                    else{
//                        $insertBMI=$dbBMI->insertBMIFormJson();
                        if(count($bmiList)==0){
                            $response["status"] = 'succeed';
                            echoRespnse(200, $response);
                        }
                        else{
                        for($i=0;$i<count($bmiList);$i++){
                            $list= array(
                                $time=$bmiList[$i]["time"],
                                $height=$bmiList[$i]["height"],
                                $weight=$bmiList[$i]["weight"],
                                $heightUnit=$bmiList[$i]["heightUnit"],
                                $weightUnit=$bmiList[$i]["weightUnit"],
                            );
                            $insertBMI=$dbBMI->insertBMI($time,$height,$weight,$heightUnit,$weightUnit,$healthID);
                        }

                        if($insertBMI){

                            $response["status"] = 'succeed';
                            echoRespnse(200, $response);

                        }
                        else{
                            $response['status'] = "failed";
                            $response['message'] = 'you not insert to database';
                            $response['errorcode'] = "19";

                            echoRespnse(200, $response);
                        }
                    }
                    }
                }
});




/* ------------------------------------------------End API BMI---------------------------------------------------------------------------------- */



/*------------------------------------------------Test-----------------------------------------------------------------------------------------*/


/*
 *  get test for apiKey
 * method post
 * params apiKey
*/

$app->post('/getTest',function() use($app){

                verifyRequiredParams(array('apiKey'));
                $apiKey = $app->request->post('apiKey');
                $db = new DbHandler();
                $dbTest = new getHealthTest();
                $response=array();
                $healthID=$db->getUserId($apiKey);

                if (!$db->isValidApiKey($apiKey)) {
                    // api key is not present in users table
                    $response['status'] = "failed";
                    $response['message'] = 'apiKey does not exist';
                    $response['errorcode'] = "18";
                    echoRespnse(401, $response);
                    $app->stop();
                }
                else{
                    $response['status'] = "succeed";
                    $result = $dbTest->getTest($healthID);
                    $tmp = array();
                    $response["data"] = array();


                    while ($task = $result->fetch_assoc())
                    {

                        $tmp["id"] = $task["TestID"];
                        $resultValue= $dbTest->getAllTestValue($task["TestID"]);
                        $tmp["time"] = $task["Time"];
                        $tmp["categoryId"] = $task["CategoryID"];
                        array_push($response["data"],$tmp);
                    }
                }
                echoRespnse(200, $response);
});
/*
 *  get test for apiKey
 * method post
 * url /getTestValuesByTestId
 * params apiKey, testID
*/

$app->post('/getTestValuesByTestId',function() use($app){

                verifyRequiredParams(array('apiKey','testId','languageCode'));
                $apiKey = $app->request->post('apiKey');
                $testId = $app->request->post('testId');
                $language = $app->request->post('languageCode');
                $languageCode=strtolower($language);
                $db = new DbHandler();
                $dbTest = new getHealthTest();
                $response=array();

                if (!$db->isValidApiKey($apiKey)) {
                    // api key is not present in users table
                    $response['status'] = "failed";
                    $response['message'] = 'apiKey does not exist';
                    $response['errorcode'] = "18";
                    echoRespnse(401, $response);
                    $app->stop();

                 }
                else{

                    $response['status'] = "succeed";
                    $result = $dbTest->getTestValue($testId);
                    $tmp = array();
                    $response["data"] = array();

                    while ($task = $result->fetch_assoc())
                    {
                        $genderCode= $dbTest->getGender($apiKey);
                       $gender= strtolower($genderCode);

                        if($gender=='female'){

                            $tmp["value"] = (double)$task["Value"];
                            $tmp["id"] = $task["ID"];
                            $tmp["testID"] = $task["TestID"];
                            $tmp["testIndexCode"] = $task["TestIndexCode"];
                            $name= $dbTest->getvalueIndexs($task["TestIndexCode"]);
                            while ($task1 = $name->fetch_assoc())
                            {
                                $tmpRisk["lowValueOfFemale"]=(double)$task1["LowValueOfFemale"];
                                $tmpRisk["highValueOfFemale"]=(double)$task1["HighValueOfFemale"];

                               
                            }
                            $rickValue=$dbTest->getRick($task["TestIndexCode"],$testId,$languageCode);

                            if($languageCode=='vi')
                            {
                                while ($getRiskValue = $rickValue->fetch_assoc()){
                                    if((double)$task["Value"]< (double)$tmpRisk["lowValueOfFemale"]){

                                        $tmp["risk"] = $getRiskValue["RiskLowOfFemale"];

                                    }
                                    elseif((double)$task["Value"] > (double)$tmpRisk["highValueOfFemale"]){
                                        $tmp["risk"] = $getRiskValue["RiskHighOfFemale"];
                                    }
                                    if((double)$task["Value"] <= (double)$tmpRisk["highValueOfFemale"] && (double)$task["Value"]>= (double)$tmpRisk["lowValueOfFemale"])
                                    {

                                        $tmp["risk"] = "Bạn hoàn toàn bình thường";
                                    }
                                }
                            }
                            elseif($languageCode=='en')
                            {
                                while ($getRiskValue = $rickValue->fetch_assoc()){
                                    if((double)$task["Value"]< (double)$tmpRisk["lowValueOfFemale"]){

                                        $tmp["risk"] = $getRiskValue["RiskLowOfFemale"];

                                    }
                                    elseif((double)$task["Value"] > (double)$tmpRisk["highValueOfFemale"]){
                                        $tmp["risk"] = $getRiskValue["RiskHighOfFemale"];
                                    }
                                    if((double)$task["Value"] <= (double)$tmpRisk["highValueOfFemale"] && (double)$task["Value"]>= (double)$tmpRisk["lowValueOfFemale"])
                                    {

                                        $tmp["risk"] = "You perfectly healthy";
                                    }
                                }
                            }
                        }

                        elseif($gender=='male'){

                            $tmp["value"] = (double)$task["Value"];
                            $tmp["id"] = $task["ID"];
                            $tmp["testID"] = $task["TestID"];
                            $tmp["testIndexCode"] = $task["TestIndexCode"];
                            $name= $dbTest->getvalueIndexs($task["TestIndexCode"]);
                            while ($task1 = $name->fetch_assoc())
                            {
                                $tmpRisk["lowValueOfMale"]=(double)$task1["LowValueOfMale"];
                                $tmpRisk["highValueOfMale"]=(double)$task1["HighValueOfMale"];
                            }
                            $rickValueMale=$dbTest->getRick($task["TestIndexCode"],$testId,$languageCode);
                            if($languageCode=='vi'){

                                while ($task3 = $rickValueMale->fetch_assoc()){


                                    if((double)$task["Value"]> (double)$tmpRisk["highValueOfMale"]){

                                        $tmp["risk"] = $task3["RiskHighOfMale"];


                                    }
                                    elseif((double)$task["Value"] < (double)$tmpRisk["lowValueOfMale"]){

                                        $tmp["risk"] = $task3["RiskLowOfMale"];
                                    }
                                    if((double)$task["Value"] <= (double)$tmpRisk["highValueOfMale"] && (double)$task["Value"]>= (double)$tmpRisk["lowValueOfMale"])
                                    {
                                        $tmp["risk"] = "Bạn hoàn toàn bình thường";
                                    }
                                }

                            }
                            elseif($languageCode=='en'){
                                while ($task3 = $rickValueMale->fetch_assoc()){


                                    if((double)$task["Value"]> (double)$tmpRisk["highValueOfMale"]){

                                        $tmp["risk"] = $task3["RiskHighOfMale"];
                                    }
                                    elseif((double)$task["Value"] < (double)$tmpRisk["lowValueOfMale"]){

                                        $tmp["risk"] = $task3["RiskLowOfMale"];
                                    }
                                    if((double)$task["Value"] <= (double)$tmpRisk["highValueOfMale"] && (double)$task["Value"]>= (double)$tmpRisk["lowValueOfMale"])
                                    {
                                        $tmp["risk"] = "You perfectly healthy";
                                    }
                                }

                            }
                        }
                        array_push($response["data"],$tmp);
                    }
                }
    echoRespnse(200, $response);

});

/*
 *  get test for apiKey
 * method post
 * url /getTestValues
 * params apiKey, testID
*/

$app->post('/getTestData',function() use($app){

                verifyRequiredParams(array('apiKey','languageCode'));
                $apiKey = $app->request->post('apiKey');
                $language = $app->request->post('languageCode');
                $languageCode=strtolower($language);
                $db = new DbHandler();
                $dbTest = new getHealthTest();
                $response=array();

                if (!$db->isValidApiKey($apiKey)) {
                    // api key is not present in users table
                    $response['status'] = "failed";
                    $response['message'] = 'apiKey does not exist';
                    $response['errorcode'] = "18";
                    echoRespnse(401, $response);
                    $app->stop();

                }
                else{
                    $response['status'] = "succeed";
                    $healthID=$db->getUserId($apiKey);
                    $tmp = array();
                    $tmpRisk=array();
                    $responseValue=array();
                    $response["data"] = array();
                    $resultTest = $dbTest->getTest($healthID);

                    while($taskRisk = $resultTest->fetch_assoc()){

                        $responseValue["testValue"]=array();
                        $responseValue["value"]=array();
                        $responseValue["testValue"]=array();

                        $tmp["time"] = $taskRisk["Time"];
                        $tmp["categoryId"] = $taskRisk["CategoryID"];
                        $tmp["testValue"] = array();

                        $result = $dbTest->getAllTestValue($taskRisk["TestID"]);

                        while ($task = $result->fetch_assoc())
                        {
                            $genderCode= $dbTest->getGender($apiKey);
                            $gender= strtolower($genderCode);
                            if($gender=='female'){
                                $tmpValue["value"] = (double)$task["Value"];
                                $tmpValue["testIndexCode"] = $task["TestIndexCode"];
                                $name= $dbTest->getvalueIndexs($task["TestIndexCode"]);

                                while ($riskHigh= $name->fetch_assoc())
                                {
                                    $tmpRisk["lowValueOfFemale"]=(double)$riskHigh["LowValueOfFemale"];
                                    $tmpRisk["highValueOfFemale"]=(double)$riskHigh["HighValueOfFemale"];
                                }
                                $rickValue=$dbTest->getAllRick($task["TestIndexCode"],$languageCode);
                                if($languageCode=='vi'){

                                    while ($getRisk = $rickValue->fetch_assoc()){

                                        if((double)$task["Value"]< (double)$tmpRisk["lowValueOfFemale"]){
                                            $tmpValue["risk"] = $getRisk["RiskLowOfFemale"];
                                        }
                                        elseif((double)$task["Value"] > (double)$tmpRisk["highValueOfFemale"]){
                                            $tmpValue["risk"] = $getRisk["RiskHighOfFemale"];
                                        }
                                        if((double)$task["Value"] <= (double)$tmpRisk["highValueOfFemale"] && (double)$task["Value"]>= (double)$tmpRisk["lowValueOfFemale"])
                                        {
                                            $tmpValue["risk"] = "Bạn hoàn toàn bình thường";
                                        }
                                    }
                                }
                                if($languageCode=='en')
                                {
                                    while ($getRisk = $rickValue->fetch_assoc()){

                                        if((double)$task["Value"]< (double)$tmpRisk["lowValueOfFemale"]){
                                            $tmpValue["risk"] = $getRisk["RiskLowOfFemale"];
                                        }
                                        elseif((double)$task["Value"] > (double)$tmpRisk["highValueOfFemale"]){
                                            $tmpValue["risk"] = $getRisk["RiskHighOfFemale"];
                                        }
                                        if((double)$task["Value"] <= (double)$tmpRisk["highValueOfFemale"] && (double)$task["Value"]>= (double)$tmpRisk["lowValueOfFemale"])
                                        {
                                            $tmpValue["risk"] = "You perfectly healthy";
                                            }
                                        }
                                    }
                                }

                            elseif($gender=='male'){
                                $tmpValue["value"] = (double)$task["Value"];

                                $tmpValue["testIndexCode"] = $task["TestIndexCode"];
                                $name= $dbTest->getvalueIndexs($task["TestIndexCode"]);

                                while ($riskHigh = $name->fetch_assoc())
                                {
                                    $tmpRisk["lowValueOfMale"]=(double)$riskHigh["LowValueOfMale"];
                                    $tmpRisk["highValueOfMale"]=(double)$riskHigh["HighValueOfMale"];
                                }
                                $rickValueMale=$dbTest->getAllRick($task["TestIndexCode"],$languageCode);
                                if($languageCode=='vi')
                                {
                                    while ($rick = $rickValueMale->fetch_assoc()){

                                        if((double)$task["Value"]> (double)$tmpRisk["highValueOfMale"]){

                                            $tmpValue["risk"] = $rick["RiskHighOfMale"];

                                        }
                                        elseif((double)$task["Value"] < (double)$tmpRisk["lowValueOfMale"]){

                                            $tmpValue["risk"] = $rick["RiskLowOfMale"];
                                        }
                                        if((double)$task["Value"] <= (double)$tmpRisk["highValueOfMale"] && (double)$task["Value"]>= (double)$tmpRisk["lowValueOfMale"])
                                        {
                                            $tmpValue["risk"] = "Bạn hoàn toàn bình thường";
                                        }
                                    }
                                }
                                if($languageCode=='en')
                                {
                                    while ($rick = $rickValueMale->fetch_assoc()){

                                        if((double)$task["Value"]> (double)$tmpRisk["highValueOfMale"]){

                                            $tmpValue["risk"] = $rick["RiskHighOfMale"];

                                        }
                                        elseif((double)$task["Value"] < (double)$tmpRisk["lowValueOfMale"]){

                                            $tmpValue["risk"] = $rick["RiskLowOfMale"];
                                        }
                                        if((double)$task["Value"] <= (double)$tmpRisk["highValueOfMale"] && (double)$task["Value"]>= (double)$tmpRisk["lowValueOfMale"])
                                        {
                                            $tmpValue["risk"] = "You perfectly healthy";
                                        }
                                    }
                                }
                            }
                                array_push($tmp['testValue'],$tmpValue);
                        }

                        array_push($response["data"],$tmp);
                    }
                }
                echoRespnse(200, $response);

});
/*
 *  get test for apiKey
 * method post
 * url /saveTestValue
 * params apiKey, data
*/

$app->post('/saveTestData',function() use($app){

                    verifyRequiredParams(array('apiKey','data'));
                    $apiKey = $app->request->post('apiKey');
                    $data = $app->request->post('data');
                    $db = new DbHandler();
                    $dbTest = new getHealthTest();
                    $response=array();
                    $healthID=$db->getUserId($apiKey);
                    $TestValuesList = json_decode($data,true);

                    if(!$TestValuesList){
                        $response['status'] = "failed";
                        $response['errorcode'] = "20";
                        $response['message'] = 'Incorrect data ';
                        echoRespnse(401, $response);
                        $app->stop();
                    }

                    if (!$db->isValidApiKey($apiKey)) {
                        // api key is not present in users table
                        $response['status'] = "failed";
                        $response['message'] = 'apiKey does not exist';
                        $response['errorcode'] = "18";
                        echoRespnse(401, $response);
                        $app->stop();
                    }

                    else {
                        if($apiKey !=null){
                            $deleteTest=$dbTest->deleteTestHealthId($healthID);

                            if(count($TestValuesList)==0){
                                $response["status"] = 'succeed';
                                echoRespnse(200, $response);
                                $app->stop();
                            }
                            else{
                                for($i=0;$i<count($TestValuesList);$i++){
                                    $Test = json_decode(($TestValuesList[$i]["testValue"]),true);
                                    $list= array(
                                        $testValue= ($TestValuesList[$i]["testValue"]),
                                        $time= $TestValuesList[$i]["time"],
                                        $categoryId= $TestValuesList[$i]["categoryId"]
                                    );
//                                    if($testValue==null||$time==null||$categoryId=null){
//                                        print_r($testValue);die;
//
//                                    }
                                    $insertTest=$dbTest->insertTest($healthID,$time,$categoryId);
                                    $testId=$dbTest->getTestId();


                                    for($j=0;$j<count($Test);$j++){
                                        $listArray=array(
                                        $Value=$Test[$j]['value'],
                                        $testIndexCode=$Test[$j]['testIndexCode']
                                      );
                                        $insertTestValue=$dbTest->insertTestValue($Test[$j]['value'],$testId['TestID'],$Test[$j]['testIndexCode']);
                                    }
                                }

                            }
                            if($insertTest && $deleteTest && $insertTestValue){

                                $response["status"] = 'succeed';
                                echoRespnse(200, $response);

                            }
                            else{

                                $response['status'] = "failed";
                                $response['message'] = 'can not insert to database';
                                $response['errorcode'] = "19";
                                echoRespnse(200, $response);
                            }
                        }
                    }

});

/*
 *  get test for apiKey
 * method post
 * url :/getTestIndexs
 * params apiKey
*/

$app->post('/getTestIndexs',function() use($app){

                verifyRequiredParams(array('apiKey','languageCode'));
                $apiKey = $app->request->post('apiKey');
                $language = $app->request->post('languageCode');
                $languageCode=strtolower($language);
                $db = new DbHandler();
                $dbTest = new getHealthTest();
                $response=array();


                if (!$db->isValidApiKey($apiKey)) {
                    // api key is not present in users table
                    $response['status'] = "failed";
                    $response['message'] = 'apiKey does not exist';
                    $response['errorcode'] = "18";
                    echoRespnse(401, $response);
                    $app->stop();
                }
                else{
                    $response['status'] = "succeed";
                    $result = $dbTest->getTestIndexs();
                    $tmp = array();
                    $response["data"] = array();

                    while ($task = $result->fetch_assoc())
                    {
                        if($languageCode=='vi'){

                            $tmp["testIndexCode"] = $task["TestIndexCode"];
                            $name= $dbTest->getName($task["TestIndexCode"],$languageCode);
                            while ($getTest = $name->fetch_assoc())
                            {
                                $tmp["name"]=$getTest["name"];
                                break;
                            }
                            $tmp["lowValueOfMale"] = $task["LowValueOfMale"];
                            $tmp["lowValueOfFemale"] = $task["LowValueOfFemale"];
                            $tmp["highValueOfFemale"] = $task["HighValueOfFemale"];
                            $tmp["highValueOfMale"] = $task["HighValueOfMale"];
                            $tmp["unit"] = $task["Unit"];
                            $tmp["highValueOfMale"] = $task["HighValueOfMale"];
                            $tmp["categoryID"] = $task["CategoryID"];

                        }
                        elseif($languageCode=='en')
                        {
                            $tmp["testIndexCode"] = $task["TestIndexCode"];

                            $name= $dbTest->getName($task["TestIndexCode"],$languageCode);

                            while ($getTest = $name->fetch_assoc())
                            {
                                $tmp["name"]=$getTest["name"];
                                break;
                            }
                            $tmp["lowValueOfMale"] = $task["LowValueOfMale"];
                            $tmp["lowValueOfFemale"] = $task["LowValueOfFemale"];
                            $tmp["highValueOfFemale"] = $task["HighValueOfFemale"];
                            $tmp["highValueOfMale"] = $task["HighValueOfMale"];
                            $tmp["unit"] = $task["Unit"];
                            $tmp["highValueOfMale"] = $task["HighValueOfMale"];
                            $tmp["categoryID"] = $task["CategoryID"];
                        }

                        array_push($response["data"],$tmp);
                    }
                }
                echoRespnse(200, $response);

});

/*
 *  get Test By HealthId CategoryId
 * url /getTestByHealthIdCategoryId
 * method post
 * params apiKey,categoryId
*/

$app->post('/getTestByHealthIdCategoryId',function() use($app){

                verifyRequiredParams(array('apiKey','categoryId'));
                $apiKey = $app->request->post('apiKey');
                $categoryId = $app->request->post('categoryId');
                $db = new DbHandler();
                $dbTest = new getHealthTest();
                $response=array();
                $healthID=$db->getUserId($apiKey);

                if (!$db->isValidApiKey($apiKey)) {
                    // api key is not present in users table
                    $response['status'] = "failed";
                    $response['message'] = 'apiKey does not exist';
                    $response['errorcode'] = "18";
                    echoRespnse(401, $response);
                    $app->stop();
                }
                else{
                    $response['status'] = "succeed";
                    $result = $dbTest->getTestByHealthIdCategoryID($healthID,$categoryId);
                    $tmp = array();
                    $response["data"] = array();

                    while ($task = $result->fetch_assoc())
                    {

                        $tmp["id"] = $task["TestID"];
                        $tmp["healthId"] = $task["HealthID"];
                        $tmp["time"] = $task["Time"];
                        $tmp["categoryId"] = $task["CategoryID"];
                        array_push($response["data"],$tmp);
                    }
                }
                echoRespnse(200, $response);
});


/*
 *  get Test By HealthId CategoryId
 * url /getRick
 * method post
 * params apiKey,array: data, languageCode
*/

$app->post('/getRisk',function() use($app){

                verifyRequiredParams(array('apiKey','data','languageCode'));
                $apiKey = $app->request->post('apiKey');
                $data = $app->request->post('data');
                $languageCode = $app->request->post('languageCode');
                $db = new DbHandler();
                $dbTest = new getHealthTest();
                $response=array();

                $TestValuesList = json_decode($data,true);
                    if(!$TestValuesList){
                        $response['status'] = "failed";
                        $response['errorcode'] = "20";
                        $response['message'] = 'Incorrect data ';
                        echoRespnse(401, $response);
                        $app->stop();
                    }
                    if (!$db->isValidApiKey($apiKey)) {
                    // api key is not present in users table
                    $response['status'] = "failed";
                    $response['message'] = 'apiKey does not exist';
                    $response['errorcode'] = "18";
                    echoRespnse(401, $response);
                    $app->stop();
                    }

                else {
                    if($apiKey !=null){

                        if(count($TestValuesList)==0){

                            $response["status"] = 'succeed';

                            echoRespnse(200, $response);
                            $app->stop();
                        }

                        else{
                            $response['status'] = "succeed";
                            $response['data']=array();

                            for($i=0;$i<count($TestValuesList);$i++){
                                $list= array(
                                    $Value= ($TestValuesList[$i]["value"]),
                                    $TestIndexCode= $TestValuesList[$i]["testIndexCode"],

                                );
                                $genderCode= $dbTest->getGender($apiKey);
                                $gender= strtolower($genderCode);

                                $getRisk=$dbTest->getRiskValue($TestValuesList[$i]["testIndexCode"],strtolower($languageCode));

                                $getTestIndex=$dbTest->getTestIndex($TestValuesList[$i]["testIndexCode"]);
                                $result["HighValueOfMale"]= (double)$getTestIndex["HighValueOfMale"];
                                $result["LowValueOfMale"]= (double)$getTestIndex["LowValueOfMale"];
                                $result["HighValueOfFemale"]= (double)$getTestIndex["HighValueOfFemale"];
                                $result["LowValueOfFemale"]= (double)$getTestIndex["LowValueOfFemale"];


                                $tmp=array();
                                if($gender=='male'){

                                    if(strtolower($languageCode)=='vi'){

                                        if((double)($TestValuesList[$i]["value"]) <=(double) $getTestIndex["HighValueOfMale"] &&(double) ($TestValuesList[$i]["value"]) >= (double)$getTestIndex["LowValueOfMale"]){
                                            $tmp[$i]['risk'] ="Bạn hoàn toàn bình thường";
                                            $tmp[$i]['testIndexCode'] = $TestValuesList[$i]["testIndexCode"];
                                            $tmp[$i]['value']=  (double)($TestValuesList[$i]["value"]);
                                            $tmp[$i]['name'] = $getRisk['Name'];

                                            }
                                            if((double)($TestValuesList[$i]["value"]) < (double)$getTestIndex["LowValueOfMale"]){

                                            $tmp[$i]['testIndexCode'] = $TestValuesList[$i]["testIndexCode"];
                                            $tmp[$i]['value']=  (double)($TestValuesList[$i]["value"]);
                                            $tmp[$i]['risk'] = $getRisk['RiskLowOfMale'];
                                            $tmp[$i]['name'] = $getRisk['Name'];

                                        }
                                        elseif(($TestValuesList[$i]["value"]) > $getTestIndex["HighValueOfMale"]){

                                            $tmp[$i]['testIndexCode'] = $TestValuesList[$i]["testIndexCode"];
                                            $tmp[$i]['value'] = (double)($TestValuesList[$i]["value"]);
                                            $tmp[$i]['risk'] = $getRisk['RiskHighOfMale'];
                                            $tmp[$i]['name'] = $getRisk['Name'];
                                        }

                                    }
                                        elseif(strtolower($languageCode)=='en'){

                                            if((double)($TestValuesList[$i]["value"]) <= (double)$getTestIndex["HighValueOfMale"] &&(double) ($TestValuesList[$i]["value"]) >=(double) $getTestIndex["LowValueOfMale"]){
                                                $tmp[$i]['risk'] = "Bạn hoàn toàn bình thường";
                                                $tmp[$i]['testIndexCode'] = $TestValuesList[$i]["testIndexCode"];
                                                $tmp[$i]['value'] = (double)($TestValuesList[$i]["value"]);
                                                $tmp[$i]['name'] = $getRisk['Name'];

                                           }

                                            if((double)($TestValuesList[$i]["value"]) < (double)$getTestIndex["LowValueOfMale"]){

                                                $tmp[$i]['testIndexCode'] = $TestValuesList[$i]["testIndexCode"];
                                                $tmp[$i]['value'] = (double)($TestValuesList[$i]["value"]);
                                                $tmp[$i]['risk'] = $getRisk['RiskLowOfMale'];
                                                $tmp[$i]['name'] = $getRisk['Name'];

                                            }

                                            elseif((double)($TestValuesList[$i]["value"]) >(double) $getTestIndex["HighValueOfMale"]){

                                                $tmp[$i]['testIndexCode'] = $TestValuesList[$i]["testIndexCode"];
                                                $tmp[$i]['value'] = (double)($TestValuesList[$i]["value"]);
                                                $tmp[$i]['risk'] = $getRisk['RiskHighOfMale'];
                                                $tmp[$i]['name'] = $getRisk['Name'];
                                            }

                                        }
                                    }

                                if($gender=='female'){

                                    if(strtolower($languageCode)=='vi'){
                                        if((double)(($TestValuesList[$i]["value"])) <= (double)($getTestIndex["HighValueOfFemale"]) && (double)($TestValuesList[$i]["value"]) >= (double)($getTestIndex["LowValueOfFemale"])){
                                            $tmp[$i]['risk'] ="Bạn hoàn toàn bình thường";
                                            $tmp[$i]['testIndexCode'] = $TestValuesList[$i]["testIndexCode"];
                                            $tmp[$i]['value'] = (double)(($TestValuesList[$i]["value"]));
                                            $tmp[$i]['name'] = $getRisk['Name'];

                                        }
                                        if((double)(($TestValuesList[$i]["value"])) <(double) ($getTestIndex["LowValueOfFemale"])){

                                            $tmp[$i]['testIndexCode'] = $TestValuesList[$i]["testIndexCode"];
                                            $tmp[$i]['value'] = (double)(($TestValuesList[$i]["value"]));
                                            $tmp[$i]['risk'] = $getRisk['RiskLowOfMale'];
                                            $tmp[$i]['name'] = $getRisk['Name'];
                                        }

                                        elseif((double)(($TestValuesList[$i]["value"])) > (double)($getTestIndex["HighValueOfFemale"])){

                                            $tmp[$i]['testIndexCode'] = $TestValuesList[$i]["testIndexCode"];
                                            $tmp[$i]['value'] =(double) ($TestValuesList[$i]["value"]);
                                            $tmp[$i]['risk'] = $getRisk['RiskHighOfFemale'];
                                            $tmp[$i]['name'] = $getRisk['Name'];
                                        }


                                    }
                                    elseif(strtolower($languageCode)=='en'){

                                        if((double)(($TestValuesList[$i]["value"])) <= (double)($getTestIndex["HighValueOfFemale"])&& (double)(($TestValuesList[$i]["value"])) >= (double)($getTestIndex["LowValueOfFemale"])){
                                            $tmp[$i]['risk'] ="You perfectly healthy";
                                            $tmp[$i]['testIndexCode'] = $TestValuesList[$i]["testIndexCode"];
                                            $tmp[$i]['value'] = (double)($TestValuesList[$i]["value"]);
                                            $tmp[$i]['name'] = $getRisk['Name'];

                                        }
                                        if((double)(($TestValuesList[$i]["value"])) < (double)($getTestIndex["LowValueOfFemale"])){

                                            $tmp[$i]['testIndexCode'] = $TestValuesList[$i]["testIndexCode"];
                                            $tmp[$i]['value'] = (double)($TestValuesList[$i]["value"]);
                                            $tmp[$i]['risk'] = $getRisk['RiskLowOfMale'];
                                            $tmp[$i]['name'] = $getRisk['Name'];
                                        }

                                        elseif((double)($TestValuesList[$i]["value"]) > (double)($getTestIndex["HighValueOfFemale"])){

                                            $tmp[$i]['testIndexCode'] = $TestValuesList[$i]["testIndexCode"];
                                            $tmp[$i]['value'] = (double)($TestValuesList[$i]["value"]);
                                            $tmp[$i]['risk'] = $getRisk['RiskHighOfFemale'];
                                            $tmp[$i]['name'] = $getRisk['Name'];

                                        }

                                    }
                                }
                                array_push( $response['data'],$tmp[$i]);
                            }

                        }
                        echoRespnse(200, $response);

                    }
                }

});


/*------------------------------------------------End Test-------------------------------------------------------*/



/**
 * Validating email address
*/

function validateEmail($email) {
            $app = \Slim\Slim::getInstance();
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response["error"] = true;
                $response["message"] = 'Email address is not valid';
                echoRespnse(400, $response);
                $app->stop();
            }

}

/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response) {
            $app = \Slim\Slim::getInstance();
            // Http response code
            $app->status($status_code);

            // setting response content type to json
            $app->contentType('application/json');

            echo json_encode($response);
        }

        $app->run();
?>