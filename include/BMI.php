<?php
/**
 * Created by PhpStorm.
 * User: Longtran
 * Date: 11/4/14
 * Time: 8:31 AM
 */

require_once 'DbHandler.php';

class getBMI extends DbHandler {
    public function getAllBmi($healthID)
    {
        $stmt = $this->conn->prepare("SELECT Time ,Height ,Weight, HeightUnit , WeightUnit, HealthID,ID FROM bmi where HealthID=? ");
        $stmt->bind_param('i',$healthID);
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;

    }
    public function insertBMI($time,$height,$weight,$heightUnit,$weightUnit,$healthId) {
        $stmt = $this->conn->prepare("INSERT INTO bmi (Time ,Height ,Weight,HeightUnit ,WeightUnit,HealthID) values(?,?,?,?,?,?)");
        $stmt->bind_param("sssssi",$time,$height,$weight,$heightUnit,$weightUnit,$healthId);
        $result = $stmt->execute();

        if (false === $result) {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
        }
        $stmt->close();
        return $result;
    }


    public function createBMI($user_id, $task) {
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

    public function deleteHealthId($healthID) {
        $stmt = $this->conn->prepare("DELETE FROM bmi WHERE HealthID = ?");
        $stmt->bind_param("i", $healthID);
        $result = $stmt->execute();

        if (false === $result) {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
        }
        $stmt->close();
        return $result;
    }

    public function insertBMIFormJson($data,$healthID) {

        $stmt = $this->conn->prepare("INSERT INTO bmi (Time, Value,HealthID) values(?, ?, ?)");
        $stmt->bind_param("si",$data,$healthID);
        $result = $stmt->execute();

        if (false === $result) {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
        }
        $stmt->close();
        return $result;
    }
} 