<?php
/**
 * Created by PhpStorm.
 * User: Longtran
 * Date: 11/4/14
 * Time: 1:42 PM
 */
require_once 'DbHandler.php';
class getHeartRate extends DbHandler {

    public function getAllHeartRate($healthID)
    {
        $stmt = $this->conn->prepare("SELECT HealthID, Time, Value, TimeStamp, ID  FROM heartrate where HealthID=?");
        $stmt->bind_param('i',$healthID);
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;

    }
    public function insertBMI($time,$value,$healthId) {
        $stmt = $this->conn->prepare("INSERT INTO heartrate (Time ,Value,HealthID) values(?,?,?)");
        $stmt->bind_param("ssi",$time,$value,$healthId);
        $result = $stmt->execute();

        if (false === $result) {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
        }
        $stmt->close();
        return $result;
    }
    public function deleteHealthId($healthID) {
        $stmt = $this->conn->prepare("DELETE FROM heartrate WHERE HealthID = ?");
        $stmt->bind_param("i", $healthID);
        $result = $stmt->execute();

        if (false === $result) {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
        }
        $stmt->close();
        return $result;
    }
} 