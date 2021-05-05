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
        return ["result" => "created"];
    }

    private function addEmptyTest($data, $key){
        /*
         *  KYM NIE JE UROBENE PRIHLASOVANIE UCITELA, VYTVORTE SI V DATABAZE NEJAKEHO UCITELA S ID 1
         */
        $_SESSION["lecturerId"] = 1;


        $teacherId = $_SESSION["lecturerId"];
        $query = "INSERT INTO test(teacher_id, title, code, is_active, duration) VALUES (?,?,?,?,?)";
        $bindParameters = [$teacherId, $data->name, $key, 0, $data->timeLimit];
        $this->pushToDatabase($query, $bindParameters);
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