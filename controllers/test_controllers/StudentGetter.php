<?php

require_once(__DIR__ . "/../database_abstract/DatabaseCommunicator.php");

class StudentGetter extends DatabaseCommunicator
{
    public function __construct()
    {
        //ODKOMENTOVAT PRI POUZIVANI DATABAZY A ZAPISAT UDAJE O DATABAZE DO config.php
        parent::__construct();
    }

    public function getName($studentId){
        $query = "SELECT name, surname FROM student WHERE id=:studentId";
        return $this->getFromDatabase($query, [":studentId" => $studentId])[0];
    }

    public function getStudentsStates($key)
    {
        $query = "SELECT student.id, student.name, student.surname, student_action.action FROM `student` 
                    JOIN student_action ON student.id=student_action.student_id 
                        JOIN test ON student_action.test_id=test.id WHERE test.code=:key";
        $bindParameters = [":key" => $key];
        $students = $this->getFromDatabase($query, $bindParameters);
        return ["students" => $students];
    }

    public function getOneStudentAnswers($key, $studentId)
    {
        $query = "  SELECT question_id as id, qs.type as type, text, max_points as maxPoints, points, qs.answer as answer
                    FROM question
                             JOIN test t on t.id = question.test_id
                             JOIN question_student qs on question.id = qs.question_id
                             JOIN student s on s.id = qs.student_id
                    where code = :testCode and student_id = :studentId;";

        $bindParameters = [":testCode" => $key, ":studentId" => $studentId];
        $questions = $this->getFromDatabase($query, $bindParameters);
        $questionAnswers = $this->getStudentQuestionsAnswer($questions, $studentId);
        return ["questions" => $questionAnswers];
    }

    private function getStudentQuestionsAnswer($questions, $studentId)
    {
        $questionsAnswers = [];
        foreach ($questions as $question) {
            $answer = $this->getAnswerByType($question, $studentId);
            $questionAnswer = ["question" => ["id"=>$question['id'],"type" => $question['type'], "text" => $question['text'],
                "maxPoints" => $question['maxPoints'], "points" => $question['points']]];
            $questionAnswer['question']['answer'] = $answer;
            $questionsAnswers[] = ["studentQuestionAnswer" => $questionAnswer];
        }
        return $questionsAnswers;
    }


    private function getAnswerByType($question, $studentId)
    {
        $questionId = $question['id'];
        $type = $question['type'];
        if ($type === "CHOICE") {
            return ["answers" => $this->getMultiChoiceAnswer($questionId, $studentId)];
        }
        if ($type === "PAIR") {
            return ["answers" => $this->getPairAnswer($questionId,$studentId)];
        }

        if ($type === "SHORT_ANSWER") {
            return $this->getAnswer($question);
        }

        if ($type === "DRAW") {
            return $this->getAnswer($question);
        }

        if ($type === "MATH") {
            return $this->getAnswer($question);
        }
        return null;
    }

    private function getAnswer($question)
    {
        return $question['answer'];
    }


    private function getMultiChoiceAnswer($questionId, $studentId)
    {
        $allOptions = $this->getAllOptions($questionId);
        $allCorrectOptions = $this->getAllCorrectOptions($questionId);
        $allStudentOptions = $this->getAllStudentOptions($questionId, $studentId);

        return ["allOptions" => $allOptions, "correctOptions" => $allCorrectOptions, "studentOptions" => $allStudentOptions];
    }

    private function getAllOptions($questionId){
        $query = "SELECT id, value1 as text FROM question_option WHERE question_id=:questionId";
        $bindParameters = [":questionId" => $questionId];

        return $this->getFromDatabase($query, $bindParameters);
    }

    private function getAllCorrectOptions($questionId){
        $query = "SELECT question_option.id, value1 as text  FROM correct_question_option 
                    JOIN question_option ON question_option.id=correct_question_option.question_option_id 
                    WHERE question_option.question_id=:questionId";
        $bindParameters = [":questionId" => $questionId];

        return $this->getFromDatabase($query, $bindParameters);
    }

    private function getAllStudentOptions($questionId, $studentId){
        $query = "SELECT question_option.id, value1 FROM `question_student` 
                    JOIN question_student_choice_option ON question_student_choice_option.question_student_id=question_student.id 
                        JOIN question_option ON question_option_id=question_option.id 
                            WHERE question_student.question_id=:questionId AND student_id=:studentId";
        $bindParameters = [":questionId" => $questionId, ":studentId" => $studentId];

        return $this->getFromDatabase($query, $bindParameters);
    }

    private function getPairAnswer($questionId,$studentId)
    {

        $questionStudentId = $this->getQuestionStudentIdByQuestionIdAndStudentId($questionId,$studentId);
        $studentAnswerPairs = $this->getStudentAnswerPairs($questionStudentId);

        return $this->createArrayWithPairs($studentAnswerPairs,$questionId);
    }

    private function getQuestionStudentIdByQuestionIdAndStudentId($questionId,$studentId)
    {
        $query = "SELECT question_student.id as id FROM question_student WHERE question_student.student_id = :studentId AND question_student.question_id = :questionId;";
        $bindParameters = [":questionId" => $questionId, ":studentId" => $studentId];
        return $this->getFromDatabase($query, $bindParameters)[0]["id"];
    }

    private function getStudentAnswerPairs($questionStudentId)
    {
        $query = "SELECT value1, value2 FROM question_student_option WHERE question_student_option.question_student_id = :questionStudentId;";
        $bindParameters = [":questionStudentId" => $questionStudentId];
        return $this->getFromDatabase($query, $bindParameters);
    }

    private function createArrayWithPairs($studentAnswerPairs,$questionId)
    {
        $result = [];

        foreach ($studentAnswerPairs as $pair)
        {
            array_push($result,$this->createAnswerPair($pair,$questionId));
        }

        return $result;
    }

    private function getCorrectPairAnswerByPairQuestion($questionId,$question)
    {
        $query = "SELECT value2 FROM question_option WHERE question_option.question_id = :questionId AND question_option.value1 = :value1;";
        $bindParameters = [":questionId" => $questionId, ":value1" => $question];
        return $this->getFromDatabase($query,$bindParameters)[0]['value2'];
    }

    private function createAnswerPair($pair,$questionId)
    {
        return array(
            "question" => $pair["value1"],
            "correctAnswer" => $this->getCorrectPairAnswerByPairQuestion($questionId,$pair["value1"]),
            "studentAnswer" => $pair["value2"]
        );
    }

    public function getAllStudents($key)
    {
        /*
        $query = "SELECT * FROM student WHERE id IN 
                    (SELECT student_id FROM question_student WHERE question_id IN 
                        (SELECT id FROM `question` WHERE test_id IN 
                            (SELECT id FROM `test` WHERE code=:key)))";
        */
        $query = "SELECT student.id, student.name, student.surname FROM `student` 
                    JOIN student_action ON student.id=student_action.student_id 
                        JOIN test ON student_action.test_id=test.id WHERE test.code=:key";

        $bindParameters = [":key" => $key];
        $allStudents = $this->getFromDatabase($query, $bindParameters);

        return ["students" => $allStudents];
    }
}
