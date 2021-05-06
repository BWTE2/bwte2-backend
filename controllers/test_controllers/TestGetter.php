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
        //TODO: vratit vsetky otazky z testu bez odpovedi
        return ["questions" => []];
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