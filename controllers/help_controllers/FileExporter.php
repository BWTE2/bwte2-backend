<?php

require_once(__DIR__."/../database_abstract/DatabaseCommunicator.php");

class FileExporter extends DatabaseCommunicator
{

    public function __construct()
    {
        //ODKOMENTOVAT PRI POUZIVANI DATABAZY A ZAPISAT UDAJE O DATABAZE DO config.php
        //parent::__construct();
    }

    public function createCsv($data, $key){
        //TODO: dorobit vytvaranie csv suboru s vysledkami z testu vsetkych studentov
        return ["path" => "path/to/file.csv"];
    }

    public function createStudentPdf($data, $key, $student_id){
        //TODO: dorobit vytvaranie pdf suboru s vysledkami testu konkretneho studenta
        return ["path" => "path/to/file.pdf"];
    }

}