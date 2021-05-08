<?php

require_once(__DIR__."/../database_abstract/DatabaseCommunicator.php");
require_once("AnswersEvaluator.php");

class TestHandler extends DatabaseCommunicator
{
    public function __construct()
    {
        //ODKOMENTOVAT PRI POUZIVANI DATABAZY A ZAPISAT UDAJE O DATABAZE DO config.php
        parent::__construct();
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
        else if($question->type === 'pairQuestion')
        {
            $this->addPairQuestion($question,$testId);
        }
    }

    private function addMultiChoiceQuestion($question, $testId){

    }

    private function addPairQuestion($question, $testId)
    {
        $query = "INSERT INTO question(test_id, text, type, max_points) VALUES (:testId, :text, :type, :maxPoints);";
        $params = [":testId" => $testId, ":text" => $question->data->questionText, ":type" => "PAIR", ":maxPoints" => $question->data->questionPoints];

        $questionId = $this->pushToDatabaseAndReturnId($query, $params);

        $this->addAllOptionsForPairQuestion($question->data->questionPairs,$questionId);

    }

    private function addAllOptionsForPairQuestion($pairs,$questionId)
    {
        foreach ($pairs as $pair)
        {
            $this->addOptionForPairQuestion($pair,$questionId);
        }
    }

    private function addOptionForPairQuestion($pair, $questionId)
    {
        $query = "INSERT INTO question_option(question_id, type, value1, value2) VALUES (:questionId, :type, :value1, :value2);";
        $params = [":questionId" => $questionId, ":type" => 'PAIR', ":value1" => $pair->question, ":value2" => $pair->answer];

        $this->pushToDatabase($query,$params);
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