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

        $questions = $this->testGetter->getQuestions($data->codeTest);
        $test = $this->testGetter->getOneTestInfo($data->codeTest);

        if($questions["exists"] == false)
            return $this->createResponseStudentData($data->codeTest, false, $questions,null);
        else if($questions["activated"] == false)
        {
            return $this->createResponseStudentData($data->codeTest, $this->isStudentWroteTest($data->studentId,$test["id"]),$questions,null);
        }


        $student = $this->studentExists($data->studentId);

        if($student == false)
        {
            $newStudent = $this->saveAndReturnStudent($data->studentId, $data->studentName, $data->studentSurname);
            return $this->createResponseStudentData($data->codeTest,false,$questions,$newStudent);
        }


        if($this->isStudentWroteTest($student["studentId"],$test["id"]) == true)
        {
            return $this->createResponseStudentData($data->codeTest,true,$questions,null);
        }

        return $this->createResponseStudentData($data->codeTest,false,$questions,$student);

    }

    private function studentExists($studentId){
        $query = "SELECT student.id as id, student.name as name, student.surname as surname FROM student WHERE student.id = :studentId;";
        $bindParams = [":studentId" => $studentId];

        $student = $this->getFromDatabase($query,$bindParams);

        if($student == false)
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

        if($action == false)
            return false;
        else if($action[0]["action"] !== "FINISHED")
            return false;

        return true;
    }

    private function createResponseStudentData($codeTest, $isWroteTest, $question, $student)
    {
        return array(
            "codeTest" => $codeTest,
            "isWroteTest" => $isWroteTest,
            "question" => $question,
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



}