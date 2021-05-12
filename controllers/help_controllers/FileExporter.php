<?php

require_once(__DIR__ . "/../database_abstract/DatabaseCommunicator.php");

class FileExporter extends DatabaseCommunicator
{

    public function __construct()
    {
        //ODKOMENTOVAT PRI POUZIVANI DATABAZY A ZAPISAT UDAJE O DATABAZE DO config.php
        //parent::__construct();
    }

    public function createCsv($key)
    {
        $timestamp = new DateTime();
        $path = "bwte2-backend/exports/csv/";
        $format = ".csv";
        $filename = "csvExport" . $timestamp->getTimestamp();
        $fp = fopen($path . $filename . $format, "w");
        $header = array("studentId", "name", "surname", "points");

        $query = "SELECT student.id, student.name, surname, SUM(points) AS points
                    FROM student
                             JOIN question_student qs ON student.id = qs.student_id
                             JOIN question q ON q.id = qs.question_id
                             JOIN test t ON t.id = q.test_id
                    WHERE code = :code
                    GROUP BY student.id, student.name, student.surname";
        $bindParameters = [":code" => $key];
        $students = $this->getFromDatabase($query, $bindParameters);

        fputcsv($fp, $header);

        foreach ($students as $student) {
            fputcsv($fp, $student);
        }

        fclose($fp);

        return ["path" => $path . $filename . $format];
    }

    public function createStudentPdf($data, $key, $student_id)
    {
        //TODO: dorobit vytvaranie pdf suboru s vysledkami testu konkretneho studenta
        return ["path" => "path/to/file.pdf"];
    }

}