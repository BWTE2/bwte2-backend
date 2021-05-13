<?php

require_once(__DIR__."/../database_abstract/DatabaseCommunicator.php");

class LecturerAccessor extends DatabaseCommunicator
{

    public function __construct()
    {
        //ODKOMENTOVAT PRI POUZIVANI DATABAZY A ZAPISAT UDAJE O DATABAZE DO config.php
        parent::__construct();
    }

    public function handleRegistrationEvent($data){
        //TODO
        return [];
    }

    public function handleLoginEvent($data){
        //TODO
        return [];
    }

    public function isLogged($data){
        //TODO: ak bude treba
        return true;
    }

    public function isRegistered($data){
        //TODO: ak bude treba
        return true;
    }

    public function getLecturerInfo($id){
        $query = "SELECT id, name, surname, email FROM teacher WHERE id=:id";
        $bindParameters = [":id" => $id];
        $results = $this->getFromDatabase($query, $bindParameters);

        if(empty($results)){
            return ["id" => null, "name" => null, "surname" => null, "email" => null];
        }

        return $results[0];
    }
}