<?php

require_once(__DIR__."/../database_abstract/DatabaseCommunicator.php");

class LecturerAccessor extends DatabaseCommunicator
{

    public function __construct()
    {
        //ODKOMENTOVAT PRI POUZIVANI DATABAZY A ZAPISAT UDAJE O DATABAZE DO config.php
        //parent::__construct();
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
}