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
        $correctOptions = $this->getCorrectOptions($answer);
        $points = $this->getPositivePoints($answer, $correctOptions);

        return $points;
    }

    private function getPositivePoints($answer, $correctOptions){
        $totalPoints = 0;
        $maxPoints = $this->getQuestionPoints($answer->questionInfo->id);
        $optionPoints = $maxPoints / count($correctOptions);
        $allAnsweredOptions = $answer->answer;

        foreach ($allAnsweredOptions as $answeredOption){
            if(in_array($answeredOption, $correctOptions, true)){
               $totalPoints += $optionPoints;
            }
        }

        return $totalPoints;
    }

    private function getQuestionPoints($questionId){
        $query = "SELECT max_points FROM question WHERE id=:questionId";
        $bindParameters = [":questionId" => $questionId];
        return $this->getFromDatabase($query, $bindParameters)[0]['max_points'];
    }

    private function getCorrectOptions($answer){
        $questionId = $answer->questionInfo->id;
        $query = "SELECT question_option_id FROM correct_question_option WHERE question_id=:questionId";
        $bindParameters = [":questionId" => $questionId];

        $allResults = $this->getFromDatabase($query, $bindParameters);
        $allCorrectOptions = [];

        foreach ($allResults as $result){
            $allCorrectOptions[] = "" . $result['question_option_id'];
        }

        return $allCorrectOptions;
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