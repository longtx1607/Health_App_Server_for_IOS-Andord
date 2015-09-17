<?php
/**
 * Created by PhpStorm.
 * User: LongTran
 * Date: 11/17/14
 * Time: 1:40 PM
 */
require_once 'DbHandler.php';

class getHealthTest extends DbHandler
{
    public function getTest($healthID)
    {
        $stmt = $this->conn->prepare("SELECT TestID ,HealthID ,Time, CategoryID FROM test where HealthID=? ");
        $stmt->bind_param('i',$healthID);
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;

    }

    public function getTestValue($testId)
    {
        $stmt = $this->conn->prepare(" SELECT ID,TestID,`Value`,TestIndexCode FROM testValues WHERE TestID = ?");
        $stmt->bind_param('i',$testId);
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;

    }


    // get gender by APIKey

    public function getGender($api_key) {
        $stmt = $this->conn->prepare("SELECT gender FROM users WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        if ($stmt->execute()) {
            $stmt->bind_result($gender);
            $stmt->fetch();
            // TODO
            $stmt->close();
            return $gender;
        } else {
            return NULL;
        }
    }

    public function getCodeIndex($TestIndexCode)
    {
        $stmt = $this->conn->prepare(" SELECT ID,TestID,`Value`,TestIndexCode FROM testValues WHERE  TestIndexCode= ?");
        $stmt->bind_param('s',$TestIndexCode);

        if($stmt->execute()){
            $stmt->bind_result( $TestID, $Value, $TestIndexCode,$ID);
            $stmt->fetch();
            $user = array();
            $user["ID"] = $ID;
            $user["TestID"]=$TestID;
            $user["Value"] = $Value;
            $user["TestIndexCode"] = $TestIndexCode;
              return $user;
        }
        else {
            return NULL;
        }

    }
    public function getName($TestIndexCode,$languageCode) {
        $stmt = $this->conn->prepare("SELECT Name as name FROM testindexs_language WHERE TestIndexCode = ? and LanguageCode=?");
        $stmt->bind_param('ss', $TestIndexCode,$languageCode);
        $stmt->execute();
        $name = $stmt->get_result();
        $stmt->close();

        return $name;
    }

    public function getvalueIndexs($TestIndexCode)
    {
        $stmt = $this->conn->prepare("SELECT testindexs.LowValueOfFemale as LowValueOfFemale,testindexs.LowValueOfMale as LowValueOfMale ,testindexs.HighValueOfMale as HighValueOfMale ,testindexs.HighValueOfFemale as HighValueOfFemale
        FROM testvalues INNER JOIN testindexs ON testvalues.TestIndexCode = testindexs.TestIndexCode
        WHERE testvalues.TestIndexCode = ? AND testvalues.TestIndexCode = testindexs.TestIndexCode");
        $stmt->bind_param('s', $TestIndexCode);
        $stmt->execute();
        $count = $stmt->get_result();
        $stmt->close();

        return $count;
    }
    public function getRick($TestIndexCode,$TestID,$LanguageCode) {
        $stmt = $this->conn->prepare("SELECT testindexs_language.RiskLowOfMale as RiskLowOfMale,testindexs_language.RiskHighOfMale as RiskHighOfMale,testindexs_language.RiskHighOfFemale as RiskHighOfFemale,testindexs_language.RiskLowOfFemale as RiskLowOfFemale
        FROM testvalues INNER JOIN testindexs ON testvalues.TestIndexCode = testindexs.TestIndexCode INNER JOIN testindexs_language ON testindexs_language.TestIndexCode = testindexs.TestIndexCode
        WHERE testvalues.TestIndexCode = testindexs.TestIndexCode AND testindexs.TestIndexCode = testindexs_language.TestIndexCode AND testvalues.TestIndexCode = ? and testvalues.TestID = ? AND  testindexs_language.LanguageCode=?");
        $stmt->bind_param('sis', $TestIndexCode,$TestID,$LanguageCode);
        $stmt->execute();
        $name = $stmt->get_result();
        $stmt->close();

        return $name;
    }

    public function getTestByHealthIdCategoryID($healthID,$CategoryID)
    {
        $stmt = $this->conn->prepare("SELECT TestID ,HealthID ,Time, CategoryID FROM Test where HealthID=? and CategoryID=? ");
        $stmt->bind_param('ii',$healthID,$CategoryID);
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;

    }

    public function getHealthId($HealthID) {
        $stmt = $this->conn->prepare("SELECT test.TestID FROM users INNER JOIN test ON test.HealthID = users.healthID WHERE users.healthID = test.HealthID AND test.HealthID = ?");
        $stmt->bind_param('i', $HealthID);
        $stmt->execute();
        $name = $stmt->get_result();
        $stmt->close();

        return $name;
    }


    public function getAllTestValue($TestID)
    {
        $stmt = $this->conn->prepare(" SELECT ID,TestID,`Value`,TestIndexCode FROM testValues where TestID=?");
        $stmt->bind_param('s', $TestID);
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;

    }


    public function getAllRick($TestIndexCode,$LanguageCode) {
        $stmt = $this->conn->prepare("SELECT testindexs_language.RiskLowOfMale as RiskLowOfMale,testindexs_language.RiskHighOfMale as RiskHighOfMale,testindexs_language.RiskHighOfFemale as RiskHighOfFemale,testindexs_language.RiskLowOfFemale as RiskLowOfFemale
        FROM testvalues INNER JOIN testindexs ON testvalues.TestIndexCode = testindexs.TestIndexCode INNER JOIN testindexs_language ON testindexs_language.TestIndexCode = testindexs.TestIndexCode
        WHERE testvalues.TestIndexCode = testindexs.TestIndexCode AND testindexs.TestIndexCode = testindexs_language.TestIndexCode AND testvalues.TestIndexCode = ? AND testindexs_language.LanguageCode=?");
        $stmt->bind_param('ss', $TestIndexCode,$LanguageCode);
        $stmt->execute();
        $name = $stmt->get_result();
        $stmt->close();

        return $name;
    }
    public function insertTest($HealthID,$Time,$CategoryID) {
        $stmt = $this->conn->prepare("INSERT INTO Test ( HealthID ,Time,CategoryID) values(?,?,?)");
        $stmt->bind_param("isi",$HealthID,$Time,$CategoryID);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function insertTestValue($Value,$TestID,$TestIndexCode) {
        $stmt = $this->conn->prepare("INSERT INTO testvalues (Value ,TestID ,TestIndexCode) values(?,?,?)");
        $stmt->bind_param("sis",$Value,$TestID,$TestIndexCode);
        $result = $stmt->execute();

        $stmt->close();
        return $result;
    }

    public function getRiskValue($TestIndexCode,$LanguageCode) {

        $stmt = $this->conn->prepare("SELECT TestIndexCode, LanguageCode,RiskLowOfMale,RiskHighOfMale,RiskHighOfFemale,RiskLowOfFemale,Name FROM testindexs_language WHERE TestIndexCode = ? and LanguageCode=?");
        $stmt->bind_param("ss", $TestIndexCode,$LanguageCode);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result( $TestIndexCode,$LanguageCode,$RiskLowOfMale,$RiskHighOfMale,$RiskHighOfFemale,$RiskLowOfFemale,$Name);
            $stmt->fetch();
            $user = array();
            $user["TestIndexCode"] = $TestIndexCode;
            $user["LanguageCode"]=$LanguageCode;
            $user["RiskLowOfMale"] = $RiskLowOfMale;
            $user["RiskHighOfMale"] = $RiskHighOfMale;
            $user["RiskHighOfFemale"] = $RiskHighOfFemale;
            $user["RiskLowOfFemale"] = $RiskLowOfFemale;
            $user["Name"]=$Name;
            $stmt->close();
            return $user;
        }
        else {
            return NULL;
        }
    }
    public function getRiskAllValue($TestIndexCode,$LanguageCode) {

        $stmt = $this->conn->prepare(" SELECT TestIndexCode, LanguageCode,RiskLowOfMale,RiskHighOfMale,RiskHighOfFemale,RiskLowOfFemale,Name FROM testindexs_language WHERE TestIndexCode = ? AND LanguageCode=?");
        $stmt->bind_param('ss', $TestIndexCode,$LanguageCode);
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;
    }

    public function getTestIndex($TestIndexCode) {

        $stmt = $this->conn->prepare("SELECT TestIndexCode,LowValueOfMale,LowValueOfFemale,HighValueOfFemale,HighValueOfMale FROM testindexs WHERE TestIndexCode=?");
        $stmt->bind_param("s", $TestIndexCode);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result( $TestIndexCode,$LowValueOfMale,$LowValueOfFemale,$HighValueOfFemale,$HighValueOfMale);
            $stmt->fetch();
            $user = array();
            $user["TestIndexCode"]=$TestIndexCode;
            $user["HighValueOfMale"] = $HighValueOfMale;
            $user["LowValueOfMale"] = $LowValueOfMale;
            $user["LowValueOfFemale"] = $LowValueOfFemale;
            $user["HighValueOfFemale"] = $HighValueOfFemale;

            $stmt->close();
            return $user;
        }
        else {
            return NULL;
        }
    }


    public function getTestIndexs()
    {
        $stmt = $this->conn->prepare("SELECT HighValueOfMale,TestIndexCode,LowValueOfMale,LowValueOfFemale,HighValueOfFemale,HighValueOfMale,Unit,CategoryID FROM testindexs");
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;

    }
    public function getTestId()
    {
        $stmt = $this->conn->prepare("SELECT Max(test.TestID)FROM test");
           if ($stmt->execute()) {
            $stmt->bind_result( $TestID);
            $stmt->fetch();
            $user = array();
            $user["TestID"]=$TestID;
            $stmt->close();
            return $user;
        }
        else {
            return NULL;
        }
    }

    public function deleteTestHealthId($healthID) {
        $stmt = $this->conn->prepare("DELETE FROM test WHERE HealthID = ?");
        $stmt->bind_param("i", $healthID);
        $result = $stmt->execute();
        if (false === $result) {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
        }
        $stmt->close();
        return $result;
    }

}