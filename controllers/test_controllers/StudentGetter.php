<?php

require_once(__DIR__ . "/../database_abstract/DatabaseCommunicator.php");

class StudentGetter extends DatabaseCommunicator
{
    public function __construct()
    {
        //ODKOMENTOVAT PRI POUZIVANI DATABAZY A ZAPISAT UDAJE O DATABAZE DO config.php
        parent::__construct();
    }

    public function getStudentsStates($key)
    {
        //mozno bude iny pristup
        //TODO: vratit zoznam studentov pri teste a ich aktualny stav
        return ["students" => []];
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
        $questionAnswers = $this->getStudentQuestionsAnswer($questions);
        return ["questions" => $questionAnswers];
    }

    private function getStudentQuestionsAnswer($questions)
    {
        $questionsAnswers = [];
        foreach ($questions as $question) {
            $answer = $this->getAnswerByType($question);
            $questionAnswer = ["question" => ["type" => $question['type'], "text" => $question['text'],
                "maxPoints" => $question['maxPoints'], "points" => $question['points']]];
            $questionAnswer['question']['answer'] = $answer;
            $questionsAnswers[] = ["studentQuestionAnswer" => $questionAnswer];
        }
        return $questionsAnswers;
    }


    private function getAnswerByType($question)
    {
        $questionId = $question['id'];
        $type = $question['type'];
        if ($type === "CHOICE") {
//            return $this->getMultiChoiceAnswer($questionId);
        }
        if ($type === "PAIR") {
//            return $this->getPairAnswer($questionId);
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


    private function getMultiChoiceAnswer($questionId)
    {
        $query = "select value1                                                                  as defualtOption,
                           if(qsco.question_option_id = cqo.question_option_id, 'true', 'false') as studentOption,
                           if(cqo.question_id = qo.question_id, 'true', 'false')                 as correctOption
                    from question
                             JOIN question_option qo on question.id = qo.question_id
                             JOIN correct_question_option cqo on question.id = cqo.question_id
                             JOIN question_student_choice_option qsco on qo.id = qsco.question_option_id
                    where question.id = ':questionId';";

        $bindParameters = [":questionId" => $questionId];
        return $this->getFromDatabase($query, $bindParameters);
    }

    private function getPairAnswer($questionId)
    {
        $query = " ";
        $bindParameters = [":questionId" => $questionId];
        return $this->getFromDatabase($query, $bindParameters);
    }

    public function getAllStudents($key)
    {
        $query = "SELECT * FROM student WHERE id IN 
                    (SELECT student_id FROM question_student WHERE question_id IN 
                        (SELECT id FROM `question` WHERE test_id IN 
                            (SELECT id FROM `test` WHERE code=:key)))";
        $bindParameters = [":key" => $key];
        $allStudents = $this->getFromDatabase($query, $bindParameters);

        return ["students" => $allStudents];
    }
}
