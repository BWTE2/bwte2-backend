<?php

require_once(__DIR__ . "/../database_abstract/DatabaseCommunicator.php");
require_once("AnswersEvaluator.php");
require_once("TestGetter.php");

class TestHandler extends DatabaseCommunicator
{
    private $evaluator;
    private $testGetter;

    public function __construct()
    {
        //ODKOMENTOVAT PRI POUZIVANI DATABAZY A ZAPISAT UDAJE O DATABAZE DO config.php
        parent::__construct();
        $this->evaluator = new AnswersEvaluator();
        $this->testGetter = new TestGetter();
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


        $optionId = $this->pushToDatabaseAndReturnId($query, $params);

        if ($answer->checked) {
            $this->addCorrectAnswerForMultiChoiceQuestion($questionId, $optionId);
        }
    }

    private function addCorrectAnswerForMultiChoiceQuestion($questionId, $optionId)
    {
        $query = "INSERT INTO correct_question_option(question_id, question_option_id) VALUES (?, ?);";
        $params = [$questionId, $optionId];
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
        $query = "INSERT INTO question(test_id, text, type, max_points) VALUES (?,?,?,?);";
        $params = [$testId, $question->data->question, 'DRAW', $question->data->points];
        $this->pushToDatabase($query, $params);
    }

    private function addOneAnswerQuestion($question, $testId)
    {
        $query = "INSERT INTO question(test_id, text, type, max_points, answer) VALUES (?, ?, ?, ?, ?)";
        $bindParameters = [$testId, $question->data->question, "SHORT_ANSWER", $question->data->points, $question->data->correctAnswer];
        $this->pushToDatabase($query, $bindParameters);
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
        $query = "UPDATE test SET is_active=1 WHERE code=:key";
        $bindParameters = [":key" => $key];
        $this->pushToDatabase($query, $bindParameters);
        return ["result" => "activated"];
    }

    public function deactivateTest($key)
    {
        $query = "UPDATE test SET is_active=0 WHERE code=:key";
        $bindParameters = [":key" => $key];
        $this->pushToDatabase($query, $bindParameters);//TODO: deaktivovat test
        return ["result" => "deactivated"];
    }

    public function sendAnswers($data, $key, $studentId)
    {
        foreach ($data->answers as $answer) {
            $this->saveAnswer($answer, $studentId);
        }

        $this->setStudentActionToFinished($studentId,$this->testGetter->getOneTestInfo($key)["id"]);
        session_start();
        unset($_SESSION["studentId"]);
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

        $query = "INSERT INTO question_student(question_id, student_id, type, points) VALUES (?,?,?,?)";
        $bindParameters = [$questionId, $studentId, $type, $points];
        $questionStudentId = $this->pushToDatabaseAndReturnId($query, $bindParameters);

        $allAnsweredOptions = $answer->answer;
        $this->saveAllAnsweredOptions($allAnsweredOptions, $questionStudentId);
    }

    private function saveAllAnsweredOptions($allAnsweredOptions, $questionStudentId)
    {
        foreach ($allAnsweredOptions as $answeredOption) {
            $this->saveAnsweredOption($answeredOption, $questionStudentId);
        }
    }

    private function saveAnsweredOption($answeredOption, $questionStudentId)
    {
        $query = "INSERT INTO question_student_choice_option(question_student_id, question_option_id) VALUES (?,?)";
        $bindParameters = [$questionStudentId, $answeredOption];
        $this->pushToDatabase($query, $bindParameters);
    }

    private function saveOneAnswer($answer, $studentId)
    {
        $questionId = $answer->questionInfo->id;
        $type = $answer->questionInfo->type;
        $points = $this->evaluator->evaluateOneAnswer($answer);


        $query = "INSERT INTO question_student(question_id, student_id, type, points, answer) VALUES (?,?,?,?,?)";
        $bindParameters = [$questionId, $studentId, $type, $points, $answer->answer];
        $this->pushToDatabase($query, $bindParameters);
    }

    private function savePairAnswer($answer, $studentId)
    {
        $questionId = $answer->questionInfo->id;
        $type = $answer->questionInfo->type;
        $points = $this->evaluator->evaluatePairAnswer($answer);

        $questionStudentId = $this->saveQuestionStudentToDatabase($questionId, $studentId, $points);

        $this->saveAllQuestionStudentPairOption($questionStudentId, $answer->answer->pairs);

    }

    private function saveQuestionStudentToDatabase($questionId, $studentId, $points)
    {
        $query = "INSERT INTO question_student(question_id, student_id, type, points) VALUES (:questionId,:studentId,'PAIR',:points);";
        $bindParameters = [":questionId" => $questionId, ":studentId" => $studentId, ":points" => $points];

        return $this->pushToDatabaseAndReturnId($query, $bindParameters);
    }

    private function saveAllQuestionStudentPairOption($questionStudentId, $pairs)
    {
        foreach ($pairs as $pair) {
            $this->saveQuestionStudentPairOptionToDatabase($questionStudentId, $pair);
        }
    }

    private function saveQuestionStudentPairOptionToDatabase($questionStudentId, $pair)
    {
        $query = "INSERT INTO question_student_option(question_student_id, value1, value2) VALUES (:questionStudentId,:value1,:value2);";
        $bindParameters = [":questionStudentId" => $questionStudentId, ":value1" => $pair->question, ":value2" => $pair->answer];

        $this->pushToDatabase($query, $bindParameters);
    }


    private function saveDrawAnswer($answer, $studentId)
    {
        $questionId = $answer->questionInfo->id;
        $type = $answer->questionInfo->type;
        $query = "INSERT INTO question_student(question_id, student_id, type,answer) 
                  VALUES (?,?,?,?)";
        $bindParameters = [$questionId, $studentId, $type, $answer->answer];
        $this->pushToDatabase($query, $bindParameters);
    }

    private function saveMathAnswer($answer, $studentId)
    {
        // predpokladam ze sa to uklada jak string :) ale to asi nikdy nezistime
        $questionId = $answer->questionInfo->id;
        $type = $answer->questionInfo->type;
        $query = "INSERT INTO question_student(question_id, student_id, type,answer) 
                  VALUES (?,?,?,?)";
        $bindParameters = [$questionId, $studentId, $type, $answer->answer];
        $this->pushToDatabase($query, $bindParameters);
    }


    public function updateEvaluation($data, $key, $studentId)
    {
        //TODO: aktualizovat bodove hodnotenie odovzdanych odpovedi
        return ["result" => "updated"];

    }


    private function setStudentActionToFinished($studentId, $testId)
    {
        $query = "UPDATE student_action SET action = :action WHERE student_action.student_id = :studentId AND student_action.test_id = :testId;";
        $bindParams = [":action" => "FINISHED", ":studentId" => $studentId, ":testId" => $testId];

        $this->pushToDatabase($query,$bindParams);
    }

}
