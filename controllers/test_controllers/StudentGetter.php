<?php

require_once(__DIR__."/../database_abstract/DatabaseCommunicator.php");

class StudentGetter extends DatabaseCommunicator
{
    public function __construct()
    {
        //ODKOMENTOVAT PRI POUZIVANI DATABAZY A ZAPISAT UDAJE O DATABAZE DO config.php
        //parent::__construct();
    }

    public function getStudentsStates($key){
        //mozno bude iny pristup
        //TODO: vratit zoznam studentov pri teste a ich aktualny stav
        return ["students" => []];
    }

    public function getOneStudentAnswers($key, $studentId){
        //TODO: vratit otazky s odpovedami studenta
        return ["quetions" => []];
    }

    public function getAllStudents($key){
        //TODO: vratit vsetkych studentov ktori robili na teste
        return ["students" => []];
    }
}