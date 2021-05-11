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
        return $this->getPoints($answer, $correctOptions);
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

    private function getPoints($answer, $correctOptions){
        $totalPoints = 0;
        $maxPoints = $this->getQuestionPoints($answer->questionInfo->id);
        $optionPoints = $maxPoints / count($correctOptions);
        $allAnsweredOptions = $answer->answer;

        if(empty($correctOptions) && empty($allAnsweredOptions)){
            return $maxPoints;
        }

        foreach ($allAnsweredOptions as $answeredOption){
            if(in_array($answeredOption, $correctOptions, true)){
               $totalPoints += $optionPoints;
            }
            else{
                $totalPoints -= $optionPoints;
            }
        }

        if($totalPoints < 0){
            $totalPoints = 0;
        }
        return $totalPoints;
    }


    private function getQuestionPoints($questionId){
        $query = "SELECT max_points FROM question WHERE id=:questionId";
        $bindParameters = [":questionId" => $questionId];
        return $this->getFromDatabase($query, $bindParameters)[0]['max_points'];
    }



    public function evaluateOneAnswer($answer){
        //TODO: dorobit funkciu ktora skontroluje odpoved a vrati ziskane body
        $questionId = $answer->questionInfo->id;
        $query = "SELECT answer FROM question WHERE id=:questionId";
        $bindParameters = [":questionId" => $questionId];
        $correctAnswer = $this->getFromDatabase($query, $bindParameters)[0]['answer'];
        $studentAnswer = $answer->answer;

        if($correctAnswer === $studentAnswer){
            return $this->getQuestionPoints($questionId);
        }
        else{
            return 0;
        }
    }

    public function evaluatePairAnswer($answer){
        //TODO: dorobit funkciu ktora skontroluje odpoved a vrati ziskane body
        $maxPoints = $this->getQuestionPoints($answer->questionInfo->id);
        $countPair = $this->getCountPairOption($answer->questionInfo->id);
        $pointsPerPair = $maxPoints / $countPair;

        return $this->checkPairAnswerAndReturnPoints($answer->questionInfo->id,$pointsPerPair,$answer->answer->pairs);
    }

    private function getCountPairOption($questionId)
    {
        $query = "SELECT COUNT(*) as countPairOption FROM question_option WHERE question_option.question_id = :questionId;";
        $bindParameters = [":questionId" => $questionId];
        return $this->getFromDatabase($query,$bindParameters)[0]['countPairOption'];
    }

    private function checkPairAnswerAndReturnPoints($questionId, $pointsPerPair, $answerPairs)
    {
        $totalPoints = 0.0;

        foreach ($answerPairs as $answerPair)
        {
            if($this->isCorrectPair($questionId,$answerPair->question,$answerPair->answer))
            {
                $totalPoints += $pointsPerPair;
            }
        }

        return $totalPoints;
    }

    private function isCorrectPair($questionId,$question, $studentAnswer)
    {
        $correctPairAnswer = $this->getCorrectPairAnswerByPairQuestion($questionId,$question);

        return $correctPairAnswer === $studentAnswer;
    }

    private function getCorrectPairAnswerByPairQuestion($questionId,$question)
    {
        $query = "SELECT value2 FROM question_option WHERE question_option.question_id = :questionId AND question_option.value1 = :value1;";
        $bindParameters = [":questionId" => $questionId, ":value1" => $question];
        return $this->getFromDatabase($query,$bindParameters)[0]['value2'];
    }
}