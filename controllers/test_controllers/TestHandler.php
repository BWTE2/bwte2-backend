<?php

require_once(__DIR__ . "/../database_abstract/DatabaseCommunicator.php");
require_once("AnswersEvaluator.php");

class TestHandler extends DatabaseCommunicator
{
    private $evaluator;

    public function __construct()
    {
        //ODKOMENTOVAT PRI POUZIVANI DATABAZY A ZAPISAT UDAJE O DATABAZE DO config.php
        parent::__construct();
        $this->evaluator = new AnswersEvaluator();
    }

    public function addTest($data, $key)
    {
        $this->addEmptyTest($data, $key);
        $this->addAllQuestionsToTest($data->questions, $key);
        return ["result" => "created"];
    }

    private function addEmptyTest($data, $key)
    {
        $teacherId = $_SESSION["lecturerId"];
        $query = "INSERT INTO test(teacher_id, title, code, is_active, duration) VALUES (?,?,?,?,?)";
        $bindParameters = [$teacherId, $data->name, $key, 0, $data->timeLimit];
        $this->pushToDatabase($query, $bindParameters);
    }

    private function addAllQuestionsToTest($allQuestions, $key)
    {
        $testId = $this->getTestId($key);
        foreach ($allQuestions as $question) {
            $this->addQuestion($question, $testId);
        }
    }

    private function addQuestion($question, $testId)
    {
        if ($question->type === 'multiChoice') {
            $this->addMultiChoiceQuestion($question, $testId);
        } else if ($question->type === 'pairQuestion') {
            $this->addPairQuestion($question, $testId);
        } else if ($question->type === 'draw') {
            $this->addDrawQuestion($question, $testId);
        } else if ($question->type === 'oneAnswerQuestion') {
            $this->addOneAnswerQuestion($question, $testId);
        } else if ($question->type === 'math') {
            $this->addMathQuestion($question, $testId);
        }
    }

    private function addMultiChoiceQuestion($question, $testId)
    {
        $query = "INSERT INTO question(test_id, text, type, max_points) VALUES (?, ?, ?, ?)";
        $bindParameters = [$testId, $question->data->question, "CHOICE", $question->data->points];

        $questionId = $this->pushToDatabaseAndReturnId($query, $bindParameters);
        $allAnswers = $question->data->answers;

        $this->addAllOptionsForMultiChoiceQuestion($allAnswers, $questionId);
    }

    private function addAllOptionsForMultiChoiceQuestion($allAnswers, $questionId)
    {
        foreach ($allAnswers as $answer) {
            $this->addOptionForMultiChoiceQuestion($answer, $questionId);
        }
    }


    private function addOptionForMultiChoiceQuestion($answer, $questionId)
    {
        $query = "INSERT INTO question_option(question_id, type, value1) VALUES (?, ?, ?);";
        $params = [$questionId, 'CHOICE', $answer->answerText];

        $this->pushToDatabase($query, $params);

        if ($answer->checked) {
            $this->addCorrectAnswerForMultiChoiceQuestion($answer, $questionId);
        }
    }

    private function addCorrectAnswerForMultiChoiceQuestion($answer, $questionId)
    {
        $query = "INSERT INTO correct_answer(question_id, answer) VALUES (?, ?);";
        $params = [$questionId, $answer->answerText];
        $this->pushToDatabase($query, $params);
    }


    private function addPairQuestion($question, $testId)
    {
        $query = "INSERT INTO question(test_id, text, type, max_points) VALUES (:testId, :text, :type, :maxPoints);";
        $params = [":testId" => $testId, ":text" => $question->data->questionText, ":type" => "PAIR", ":maxPoints" => $question->data->questionPoints];

        $questionId = $this->pushToDatabaseAndReturnId($query, $params);

        $this->addAllOptionsForPairQuestion($question->data->questionPairs, $questionId);

    }

    private function addAllOptionsForPairQuestion($pairs, $questionId)
    {
        foreach ($pairs as $pair) {
            $this->addOptionForPairQuestion($pair, $questionId);
        }
    }

    private function addOptionForPairQuestion($pair, $questionId)
    {
        $query = "INSERT INTO question_option(question_id, type, value1, value2) VALUES (:questionId, :type, :value1, :value2);";
        $params = [":questionId" => $questionId, ":type" => 'PAIR', ":value1" => $pair->question, ":value2" => $pair->answer];

        $this->pushToDatabase($query, $params);
    }

    private function addDrawQuestion($question, $testId)
    {
        //TODO pridanie otázky
    }

    private function addOneAnswerQuestion($question, $testId)
    {
        //TODO pridanie otázky
    }

    private function addMathQuestion($question, $testId)
    {
        //TODO pridanie otázky
    }

    private function getTestId($key)
    {
        $teacherId = $_SESSION["lecturerId"];
        $query = "SELECT id FROM test WHERE test.teacher_id=:teacherId AND test.code=:code";
        $bindParameters = [":teacherId" => $teacherId, ":code" => $key];
        return $this->getFromDatabase($query, $bindParameters)[0]['id'];
    }


    public function activateTest($key)
    {
        //TODO: aktivovat test
        return ["result" => "activated"];
    }

    public function deactivateTest($key)
    {
        //TODO: deaktivovat test
        return ["result" => "deactivated"];
    }

    public function sendAnswers($data, $key, $studentId)
    {
        foreach ($data->answers as $answer) {
            $this->saveAnswer($answer, $studentId);
        }

        return ["result" => "sent"];
    }

    private function saveAnswer($answer, $studentId)
    {
        $type = $answer->questionInfo->type;
        if ($type === "CHOICE") {
            $this->saveMultiChoiceAnswer($answer, $studentId);
        } else if ($type === "SHORT_ANSWER") {
            $this->saveOneAnswer($answer, $studentId);
        } else if ($type === "PAIR") {
            $this->savePairAnswer($answer, $studentId);
        } else if ($type === "DRAW") {
            $this->saveDrawAnswer($answer, $studentId);
        } else if ($type === "MATH") {
            $this->saveMathAnswer($answer, $studentId);
        }
    }

    private function saveMultiChoiceAnswer($answer, $studentId)
    {
        $questionId = $answer->questionInfo->id;
        $type = $answer->questionInfo->type;
        $points = $this->evaluator->evaluateMultiChoiceAnswer($answer);

        //TODO: dorobit ulozenie do tabulky question_student, pripadne aj na ine tabulky ktore treba
    }

    private function saveOneAnswer($answer, $studentId)
    {
        $questionId = $answer->questionInfo->id;
        $type = $answer->questionInfo->type;
        $points = $this->evaluator->evaluateOneAnswer($answer);

        //TODO: dorobit ulozenie do tabulky question_student, pripadne aj na ine tabulky ktore treba
    }

    private function savePairAnswer($answer, $studentId)
    {
        $questionId = $answer->questionInfo->id;
        $type = $answer->questionInfo->type;
        $points = $this->evaluator->evaluatePairAnswer($answer);

        //TODO: dorobit ulozenie do tabulky question_student, pripadne aj na ine tabulky ktore treba
    }

    private function saveDrawAnswer($answer, $studentId)
    {
        $questionId = $answer->questionInfo->id;
        $type = $answer->questionInfo->type;

        //TODO: dorobit ulozenie do tabulky question_student, pripadne aj na ine tabulky ktore treba
    }

    private function saveMathAnswer($answer, $studentId)
    {
        $questionId = $answer->questionInfo->id;
        $type = $answer->questionInfo->type;

        //TODO: dorobit ulozenie do tabulky question_student, pripadne aj na ine tabulky ktore treba
    }


    public function updateEvaluation($data, $key, $studentId)
    {
        //TODO: aktualizovat bodove hodnotenie odovzdanych odpovedi
        return ["result" => "updated"];
    }

}
