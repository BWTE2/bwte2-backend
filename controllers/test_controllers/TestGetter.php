<?php

require_once(__DIR__."/../database_abstract/DatabaseCommunicator.php");

class TestGetter extends DatabaseCommunicator
{
    public function __construct()
    {
        //ODKOMENTOVAT PRI POUZIVANI DATABAZY A ZAPISAT UDAJE O DATABAZE DO config.php
        parent::__construct();
    }

    public function getOneTestInfo($key){
        //TODO: ziskat zakladne info o jednom teste
        return ["key" => $key];
    }

    public function getAllTestsInfo(){
        $lecturerId = $_SESSION['lecturerId'];
        $query = "SELECT title, code, is_active, test_created FROM test WHERE teacher_id=:teacherId";
        $bindParameters = [":teacherId" => $lecturerId];
        $tests = $this->getFromDatabase($query, $bindParameters);

        return ["tests" => $tests];
    }

    public function getQuestions($key){
        $testInfo = $this->getTestInfo($key);
        $questions = $this->getRawQuestions($testInfo['id']);
        return ["testName" => $testInfo['title'],"questions" => $questions];
    }

    private function getTestInfo($key){
        $query = "SELECT id, title FROM test WHERE test.code=:key";
        $bindParameters = [":key" => $key];
        return $this->getFromDatabase($query, $bindParameters)[0];
    }

    private function getRawQuestions($testId){
        $allQuestions = [];
        $allEmptyQuestions = $this->getEmptyQuestions($testId);

        foreach ($allEmptyQuestions as $emptyQuestion){
            $question = $this->getQuestion($emptyQuestion);
            $allQuestions[] = $question;
        }

        return $allQuestions;
    }

    private function getEmptyQuestions($testId){
        $query = "SELECT * FROM question WHERE question.test_id=:testId";
        $bindParameters = [":testId" => $testId];

        return $this->getFromDatabase($query, $bindParameters);
    }

    private function getQuestion($emptyQuestion){
        $questionText = $emptyQuestion['text'];
        $points = $emptyQuestion['max_points'];
        $type = $emptyQuestion['type'];
        $otherInfo = $this->getOtherInfo($emptyQuestion);

        return ["questionText" => $questionText, "type" => $type, "points" => $points, "otherInfo" => $otherInfo];
    }

    private function getOtherInfo($emptyQuestion){
        $type = $emptyQuestion['type'];

        //TODO: kazdy si vytvori funkciu ktora bude vracat pripadne dodatocne info k otazke
        //pokial netreba nic viac vracat, nechajte return [];
        //napr. CHOICE potrebuje vratit moznosti na zakliknutie
        if($type === "CHOICE"){
            return [];
        }
        else if($type === "SHORT_ANSWER"){
            return [];
        }
        else if($type === "PAIR"){
            return [];
        }
        else if($type === "DRAW"){
            return [];
        }
        else if($type === "MATH"){
            return [];
        }

        return [];
    }



    public function isValidKey($key){
        //TODO: overenie ci je kluc od existujuceho testu
        return true;
    }

    public function getTestMaxTime($key){
        //TODO: vratit cas v milisekundach
        return 900;
    }

    public function isTestRunning($key){
        //TODO: vratit ci test bezi
        return true;
    }
}