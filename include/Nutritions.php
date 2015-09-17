<?php
/**
 * Created by PhpStorm.
 * User: Longtran
 * Date: 11/4/14
 * Time: 8:31 AM
 */
	require_once 'DbHandler.php';

	Class GetNutritions extends DbHandler{

		public function GetViewByNutritionID($NutritionID) {		
			$stmt = $this->conn->prepare("SELECT View FROM Nutritions WHERE NutritionID = ?");
			$stmt->bind_param("i", $NutritionID);
			if ($stmt->execute()) {
				$stmt->bind_result($View);
				$stmt->fetch();
				$nutritions = array();
				$nutritions["View"] = $View;
				$stmt->close();
				return $View;
			} else {
				return NULL;
			}
		}	

		public function updateView($View,$NutritionID) {
			$stmt = $this->conn->prepare("UPDATE Nutritions set View=?  WHERE NutritionID=?");
			$stmt->bind_param("ii", $View, $NutritionID);
			$stmt->execute();
			$num_affected_rows = $stmt->affected_rows;
			$stmt->close();
			return $num_affected_rows;
		}
		public function isNutritionsExit($NutritionID) {
			$stmt = $this->conn->prepare("SELECT NutritionID from Nutritions WHERE NutritionID = ?");
			$stmt->bind_param("i", $NutritionID);
			$stmt->execute();
			$stmt->store_result();
			$num_rows = $stmt->num_rows;
			$stmt->close();
			return $num_rows > 0;
		}
		public function isNutritionsByNutritions_HealthUserExit($healthId,$NutritionID) {
			$stmt = $this->conn->prepare("SELECT NutritionID from Nutritions_HealthUser WHERE healthID=? AND NutritionID = ?");
			$stmt->bind_param("ii",$healthId, $NutritionID);
			$stmt->execute();
			$stmt->store_result();
			$num_rows = $stmt->num_rows;
			$stmt->close();
			return $num_rows > 0;
		}

		
		public function insertHealthNutritions($healthID,$NutritionID) {
			$stmt = $this->conn->prepare("INSERT INTO Nutritions_HealthUser(healthID, NutritionID) values(?, ?)");
			$stmt->bind_param("ii", $healthID, $NutritionID);
			$result = $stmt->execute();

			if (false === $result) {
				die('execute() failed: ' . htmlspecialchars($stmt->error));
			}
			$stmt->close();
			return $result;
		}			
		public function deleteHealthNutritions($healthID,$NutritionID) {
			$stmt = $this->conn->prepare("DELETE FROM Nutritions_HealthUser WHERE healthID = ? AND NutritionID = ?");
			$stmt->bind_param("ii", $healthID, $NutritionID);
			$result = $stmt->execute();

			if (false === $result) {
				die('execute() failed: ' . htmlspecialchars($stmt->error));
			}
			$stmt->close();
			return $result;
		}	
	
		 public function getNutritionIDByHealthID($healthID) {

        $stmt = $this->conn->prepare("SELECT
										nutritions.NutritionID AS NutritionID,
										nutritions.Title AS Title,
										nutritions.ThumbnailURL AS ThumbnailURL,
										nutritions.CategoryID AS CategoryID,
										nutritions.URL AS URL,
										nutritions.TimeUpLoad AS TimeUpLoad,
										nutritions.View AS View,
										nutritions.OriginThumbnailURL AS OriginThumbnailURL
										FROM
										nutritions
										Inner Join nutritions_healthuser ON nutritions.NutritionID = nutritions_healthuser.NutritionID
										WHERE
										Nutritions_HealthUser.healthID =?");

		$stmt->bind_param("i", $healthID);
		$stmt->execute();
		
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;
	
		}

        public function getCategoryNutritions()
        {
            $stmt = $this->conn->prepare("SELECT CategoryID, Title, ThumbnailURL,Version,Quantity from NutritionCategorys ");

            $stmt->execute();
            $nutrition = $stmt->get_result();
            $stmt->close();
            return $nutrition;
        }
        /*
       * get all category by apikey
       * @param String $api_key api_key to check in db
       * @return boolean
       */


        public function isCategoryExists($categoryID)
        {
            $stmt = $this->conn->prepare("SELECT NutritionID from nutritions WHERE CategoryID = ?");
            $stmt->bind_param("i", $categoryID);
            $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;
            $stmt->close();
            return $num_rows > 0;
            //return 1;
        }


        public function getAllNutritionByCategoryID($categoryID)
        {
            $stmt = $this->conn->prepare("SELECT
            nutritions.NutritionID,
            nutritions.Title,
            nutritions.ThumbnailURL,
            nutritions.OriginThumbnailURL,
            nutritions.TimeUpLoad,
            nutritions.View,
            nutritions.CategoryID,
            nutritions.URL
            FROM
            nutritions
            INNER JOIN nutritioncategorys ON nutritions.CategoryID = nutritioncategorys.CategoryID
            WHERE
            nutritioncategorys.CategoryID = nutritions.CategoryID AND
            nutritions.CategoryID = ?");
            $stmt->bind_param("i", $categoryID);
            $stmt->execute();
            $tasks = $stmt->get_result();
            $stmt->close();
            return $tasks;

        }

        public function getQuantityNutriton($countid)
        {
            $stmt = $this->conn->prepare("SELECT COUNT(*) AS count FROM nutritions  WHERE nutritions.CategoryID =?");
            $stmt->bind_param("i", $countid);
            if ($stmt->execute()) {
                $count = $stmt->get_result();
                $stmt->close();
            }
            return $count;
        }


        public function getNutritionThumbnail($user_id)
        {
            $stmt = $this->conn->prepare("SELECT
            nutritions.NutritionID,
            nutritions.ThumbnailURL
            FROM
            nutritions_healthuser
            INNER JOIN nutritions ON nutritions_healthuser.NutritionID = nutritions.NutritionID
            INNER JOIN users ON nutritions_healthuser.HealthID = users.healthID
            WHERE
            users.healthID = ?
            GROUP BY
            nutritions.ThumbnailURL,
            nutritions.NutritionID LIMIT 1");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $tasks = $stmt->get_result();
            $stmt->close();
            return $tasks;

        }

        public function getcount($user_id)
        {
            $stmt = $this->conn->prepare("SELECT Count(HealthID) AS count FROM healthuser_video WHERE HealthID = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $count = $stmt->get_result();
            $stmt->close();

            return $count;
        }

        public function getcountNutritionUser($user_id)
        {
            $stmt = $this->conn->prepare("SELECT Count(HealthID) AS count FROM nutritions_healthuser  WHERE HealthID = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $count = $stmt->get_result();
            $stmt->close();

            return $count;
        }
        public function getNutritionDefault($nutritionId)
        {

            $stmt = $this->conn->prepare("SELECT NutritionID,Title,ThumbnailURL,CategoryID,URL,TimeUpLoad,View,OriginThumbnailURL FROM nutritions WHERE NutritionID = ? ");
            $stmt->bind_param("i", $nutritionId);
            $stmt->execute();
            $tasks = $stmt->get_result();
            $stmt->close();
            return $tasks;

        }

    }
?>