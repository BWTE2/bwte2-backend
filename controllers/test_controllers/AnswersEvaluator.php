<?php

require_once(__DIR__."/../database_abstract/DatabaseCommunicator.php");

class AnswersEvaluator extends DatabaseCommunicator
{
    public function __construct()
    {
        //ODKOMENTOVAT PRI POUZIVANI DATABAZY A ZAPISAT UDAJE O DATABAZE DO config.php
        parent::__construct();
    }


    public function evaluateMultiChoiceAnswer($answer){
        //TODO: dorobit funkciu ktora skontroluje odpoved a vrati ziskane body
        return 0;
    }


    public function evaluateOneAnswer($answer){
        //TODO: dorobit funkciu ktora skontroluje odpoved a vrati ziskane body
        return 0;
    }

    public function evaluatePairAnswer($answer){
        //TODO: dorobit funkciu ktora skontroluje odpoved a vrati ziskane body
        return 0;
    }
}