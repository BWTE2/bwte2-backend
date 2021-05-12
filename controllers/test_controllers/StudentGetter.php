<?php

require_once(__DIR__ . "/../database_abstract/DatabaseCommunicator.php");

class StudentGetter extends DatabaseCommunicator
{
    public function __construct()
    {
        //ODKOMENTOVAT PRI POUZIVANI DATABAZY A ZAPISAT UDAJE O DATABAZE DO config.php
        parent::__construct();
    }

    public function getStudentsStates($key)
    {
        //mozno bude iny pristup
        //TODO: vratit zoznam studentov pri teste a ich aktualny stav
        return ["students" => []];
    }

    public function getOneStudentAnswers($key, $studentId)
    {
        $query = "  SELECT q.text                        AS text,
                           q.type                        AS type,
                           q.answer                      AS questionAnswer,
                           q.max_points                  AS maxPoints,
                           question_student.answer       AS studentAnswer,
                           question_student.answer_photo AS questionPhoto
                    FROM question_student
                             JOIN question q ON q.id = question_student.question_id
                             JOIN test t ON t.id = q.test_id
                    WHERE code = :testCode && student_id = :studentId";
        $bindParameters = [":testCode" => $key, ":studentId" => $studentId];

        $questions = $this->getFromDatabase($query, $bindParameters);

        return ["questions" => [$questions]];
    }

    public function getAllStudents($key)
    {
        $query = "SELECT * FROM student WHERE id IN 
                    (SELECT student_id FROM question_student WHERE question_id IN 
                        (SELECT id FROM `question` WHERE test_id IN 
                            (SELECT id FROM `test` WHERE code=:key)))";
        $bindParameters = [":key" => $key];
        $allStudents = $this->getFromDatabase($query, $bindParameters);

        return ["students" => $allStudents];
    }
}
