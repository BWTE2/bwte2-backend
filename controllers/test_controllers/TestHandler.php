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
        //TODO: pridat test
        return ["result" => "created"];
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