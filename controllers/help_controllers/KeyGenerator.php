<?php

require_once(__DIR__."/../database_abstract/DatabaseCommunicator.php");

class KeyGenerator extends DatabaseCommunicator
{

    public function __construct()
    {
        //ODKOMENTOVAT PRI POUZIVANI DATABAZY A ZAPISAT UDAJE O DATABAZE DO config.php
        //parent::__construct();
    }


    public function generateKey(){
        //TODO: generovat kluc ktory je pre databazu unikatny
        return "aa11";
    }





}