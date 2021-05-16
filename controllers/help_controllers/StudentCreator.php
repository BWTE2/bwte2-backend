<?php

require_once(__DIR__."/../database_abstract/DatabaseCommunicator.php");
require_once(__DIR__."/../test_controllers/TestGetter.php");

class StudentCreator extends DatabaseCommunicator
{
    private TestGetter $testGetter;

    public function __construct()
    {
        //ODKOMENTOVAT PRI POUZIVANI DATABAZY A ZAPISAT UDAJE O DATABAZE DO config.php
        parent::__construct();
        $this->testGetter = new TestGetter();
    }

    public function createStudent($data){

        $test = $this->testGetter->getOneTestInfo($data->codeTest);


        if($test === null)
            return $this->createResponseStudentData($data->codeTest,false,false,false,null);
        else if($test["isActive"] === '0')
        {
            return $this->createResponseStudentData($data->codeTest, true, false, $this->isStudentWroteTest($data->studentId,$test["id"]),null);
        }


        $student = $this->studentExists($data->studentId);

        if($student === false)
        {
            $newStudent = $this->saveAndReturnStudent($data->studentId, $data->studentName, $data->studentSurname);
            $this->setStudentActionToWriting($newStudent["studentId"],$test["id"]);
            return $this->createResponseStudentData($data->codeTest,true,true,false,$newStudent);
        }


        if($this->isStudentWroteTest($student["studentId"],$test["id"]) === true)
        {
            return $this->createResponseStudentData($data->codeTest,true,true,true,null);
        }

        $this->setStudentActionToWriting($student["studentId"],$test["id"]);
        return $this->createResponseStudentData($data->codeTest,true,true,false,$student);

    }

    private function studentExists($studentId){
        $query = "SELECT student.id as id, student.name as name, student.surname as surname FROM student WHERE student.id = :studentId;";
        $bindParams = [":studentId" => $studentId];

        $student = $this->getFromDatabase($query,$bindParams);

        if(empty($student))
            return false;

        return $this->createStudentData($student);
    }

    private function createStudentData($studentDataFromDatabase)
    {
        return array(
            "studentId" => $studentDataFromDatabase[0]["id"],
            "studentName" => $studentDataFromDatabase[0]["name"],
            "studentSurname" => $studentDataFromDatabase[0]["surname"]
        );
    }

    private function isStudentWroteTest($studentId, $testId)
    {
        $query = "SELECT student_action.action as action FROM student_action WHERE student_action.student_id = :studentId AND student_action.test_id = :testId;";
        $bindParams = [":studentId" => $studentId, ":testId" => $testId];
        $action = $this->getFromDatabase($query,$bindParams);

        if(empty($action))
            return false;

        return true;
    }

    private function createResponseStudentData($codeTest,$isExistsTest, $isActivateTest,$isWroteTest, $student)
    {
        return array(
            "codeTest" => $codeTest,
            "isExistsTest" => $isExistsTest,
            "isActivateTest" => $isActivateTest,
            "isWroteTest" => $isWroteTest,
            "student" => $student
        );
    }

    private function saveAndReturnStudent($studentId, $studentName, $studentSurname)
    {
        $query = "INSERT INTO student(id, name, surname) VALUES (:id,:name,:surname);";
        $bindParams = [":id" => $studentId, ":name" => $studentName, ":surname" => $studentSurname];
        $this->pushToDatabase($query,$bindParams);
        return $this->studentExists($studentId);
    }

    private function setStudentActionToWriting($studentId, $testId)
    {
        $query = "INSERT INTO student_action(student_id, test_id, action) VALUES (:studentId, :testId, 'WRITING');";
        $bindParams = [":studentId" => $studentId, ":testId" => $testId];

        $this->pushToDatabase($query,$bindParams);
    }


    public function updateInTestStatus($key, $studentId){
        $testId = $this->getTestId($key);
        $query = "UPDATE student_action SET action='WRITING' WHERE student_id=:studentId AND test_id=:testId";
        $bindParameters = [":testId" => $testId, ":studentId" => $studentId];
        $this->pushToDatabase($query, $bindParameters);

        return ["updated" => "in", "key" => $key, "studentId" => $studentId];
    }

    public function updateOutTestStatus($key, $studentId){
        $testId = $this->getTestId($key);
        $query = "UPDATE student_action SET action='OUT_OF_TAB' WHERE student_id=:studentId AND test_id=:testId";
        $bindParameters = [":testId" => $testId, ":studentId" => $studentId];
        $this->pushToDatabase($query, $bindParameters);

        return ["updated" => "out", "key" => $key, "studentId" => $studentId];
    }

    private function getTestId($key){
        //sorry za tuto funkciu hocikomu kto sa necha znechutit ale som unaveny a v zhone aby som robil komplikovane joiny
        $query = "SELECT id FROM test WHERE code=:key";
        $bindParameters = [":key" => $key];
        $results = $this->getFromDatabase($query, $bindParameters);

        if(empty($results)) {
            return null;
        }

        return $results[0]['id'];
    }

    public function getActualStatus($key, $studentId){
        $query = "SELECT student_action.action FROM `student_action`
                    JOIN test on student_action.test_id=test.id WHERE test.code=:key AND student_id=:studentId";
        $bindParameters = [":key" => $key, ":studentId" => $studentId];
        $results = $this->getFromDatabase($query, $bindParameters);

        if(empty($results)){
            return "FINISHED";
        }

        return  $results[0]['action'];
    }
}