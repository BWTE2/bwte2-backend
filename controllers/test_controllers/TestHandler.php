<?php

require_once(__DIR__."/../database_abstract/DatabaseCommunicator.php");
require_once("AnswersEvaluator.php");

class TestHandler extends DatabaseCommunicator
{
    public function __construct()
    {
        //ODKOMENTOVAT PRI POUZIVANI DATABAZY A ZAPISAT UDAJE O DATABAZE DO config.php
        //parent::__construct();
    }

    public function addTest($data, $key){
        $this->addEmptyTest($data, $key);
        $this->addAllQuestionsToTest($data->questions, $key);
        return ["result" => "created"];
    }

    private function addEmptyTest($data, $key){
        $teacherId = $_SESSION["lecturerId"];
        $query = "INSERT INTO test(teacher_id, title, code, is_active, duration) VALUES (?,?,?,?,?)";
        $bindParameters = [$teacherId, $data->name, $key, 0, $data->timeLimit];
        $this->pushToDatabase($query, $bindParameters);
    }

    private function addAllQuestionsToTest($allQuestions, $key){
        $testId = $this->getTestId($key);
        foreach ($allQuestions as $question){
            $this->addQuestion($question, $testId);
        }
    }

    private function addQuestion($question, $testId){
        //TODO: doplnte si kazdy svoju funkciu a if ktorym rozlisite o aky typ otazky ide
        if($question->type === 'multiChoice'){
            $this->addMultiChoiceQuestion($question, $testId);
        }
    }

    private function addMultiChoiceQuestion($question, $testId){

    }

    private function getTestId($key){
        $teacherId = $_SESSION["lecturerId"];
        $query = "SELECT id FROM test WHERE test.teacher_id=:teacherId AND test.code=:code";
        $bindParameters = [":teacherId" => $teacherId, ":code" => $key];
        return $this->getFromDatabase($query, $bindParameters)[0]['id'];
    }






    public function activateTest($key){
        //TODO: aktivovat test
        return ["result" => "activated"];
    }

    public function deactivateTest($key){
        //TODO: deaktivovat test
        return ["result" => "deactivated"];
    }

    public function sendAnswers($data, $key, $studentId){
        //TODO: zapisat odpovede studenta
        $answersEvaluator = new AnswersEvaluator();
        return ["result" => "sent"];
    }

    public function updateEvaluation($data, $key, $studentId){
        //TODO: aktualizovat bodove hodnotenie odovzdanych odpovedi
        return ["result" => "updated"];
    }

}