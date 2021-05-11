<?php

require_once(__DIR__."/../database_abstract/DatabaseCommunicator.php");

class StudentGetter extends DatabaseCommunicator
{
    public function __construct()
    {
        //ODKOMENTOVAT PRI POUZIVANI DATABAZY A ZAPISAT UDAJE O DATABAZE DO config.php
        parent::__construct();
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
        $query = "SELECT * FROM student WHERE id IN 
                    (SELECT student_id FROM question_student WHERE question_id IN 
                        (SELECT id FROM `question` WHERE test_id IN 
                            (SELECT id FROM `test` WHERE code=:key)))";
        $bindParameters = [":key" => $key];
        $allStudents = $this->getFromDatabase($query, $bindParameters);

        return ["students" => $allStudents];
    }
}