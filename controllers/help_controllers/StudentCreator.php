<?php

require_once(__DIR__."/../database_abstract/DatabaseCommunicator.php");

class StudentCreator extends DatabaseCommunicator
{

    public function __construct()
    {
        //ODKOMENTOVAT PRI POUZIVANI DATABAZY A ZAPISAT UDAJE O DATABAZE DO config.php
        //parent::__construct();
    }

    public function createStudent($data){
        //TODO
        return [];
    }

    private function studentExists($studentId){
        return true;
    }

    

}