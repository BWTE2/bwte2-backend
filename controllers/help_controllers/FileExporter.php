<?php

require_once(__DIR__ . "/../database_abstract/DatabaseCommunicator.php");
require_once(__DIR__ . "/../test_controllers/StudentGetter.php");
require_once(__DIR__ . "/../test_controllers/TestGetter.php");
require_once(__DIR__ . "/./PDFController.php");

class FileExporter extends DatabaseCommunicator
{
    private $pdfController;

    public function __construct()
    {
        //ODKOMENTOVAT PRI POUZIVANI DATABAZY A ZAPISAT UDAJE O DATABAZE DO config.php
        parent::__construct();
        $this->pdfController = new  PDFController();
    }

    public function createCsv($key)
    {

        $query = "SELECT student.id, student.name, surname, SUM(points) AS points
                    FROM student
                             JOIN question_student qs ON student.id = qs.student_id
                             JOIN question q ON q.id = qs.question_id
                             JOIN test t ON t.id = q.test_id
                    WHERE code = :code
                    GROUP BY student.id, student.name, student.surname";
        $bindParameters = [":code" => $key];
        $students = $this->getFromDatabase($query, $bindParameters);
        $out = fopen('php://output', 'wb');
        $header = array('studentId', 'name', 'surname', 'points');
        fputcsv($out, $header);
        foreach ($students as $student) {
            fputcsv($out, $student);
        }
        fclose($out);
    }

    public function createStudentPdf($key)
    {
        $testController = new TestGetter();
        $testTitle = $testController->getOneTestInfo($key)['title'];
        $this->pdfController->newPage();
        $this->pdfController->createHeader($testTitle . ' #' . $key);
        $this->cratePDFAllStudentAnswers($key);
        echo base64_encode($this->pdfController->export());

    }

    private function cratePDFAllStudentAnswers($key)
    {
        $studentController = new StudentGetter();

        $students = $studentController->getAllStudents($key);
        foreach ($students as $student) {
            $studentAnswerResponse = $studentController->getOneStudentAnswers($key, $student[0]['id']);
            $studentAnswer = $studentAnswerResponse['questions'];
            $this->appendAnswerToPDF($studentAnswer);
        }
    }

    private function appendAnswerToPDF($answers)
    {
        $questionNumber = 1;
        foreach ($answers as $answer) {
            $answer = $answer['studentQuestionAnswer']['question'];
            $this->appendToPDFByType($questionNumber, $answer);
            $questionNumber++;
            $this->pdfController->breakPage();
        }

    }

    private function appendToPDFByType($questionNumber, $answer)
    {

        $type = $answer['type'];
        $this->appendQuestionWording($questionNumber, $answer);
        if ($type === "CHOICE") {
            $this->appendChoiceAnswer($answer);
        } else if ($type === "SHORT_ANSWER") {
            $this->appendShortAnswer($answer);
        } else if ($type === "PAIR") {
            $this->pdfController->newPage();
            $this->appendPairAnswer($answer);

        } else if ($type === "DRAW") {
            $this->appendDrawAnswer($answer);
        } else if ($type === "MATH") {
            $this->appendMathAnswer($answer);
        }
        $this->pdfController->SetX($this->pdfController->getWidth() - 50);
        $questionWording = $this->getQuestionWording($questionNumber, $answer);
        $this->pdfController->appendText($questionWording['points'], 'subTitle');
    }

    private function appendShortAnswer($answer)
    {

        $this->pdfController->appendText($answer['answer'], 'text');
    }

    private function appendChoiceAnswer($answer)
    {

        $index = 0;
        $answers = $answer['answer']['answers'];
        foreach ($answers['allOptions'] as $option) {
            $default = $option['text'];
            $chosen = $this->isOptionInOptions($option, $answers['studentOptions']);
            $correct = $this->isOptionInOptions($option, $answers['correctOptions']);
            $this->pdfController->appendCheckBox($default, $chosen, $correct);
        }

    }

    public function isOptionInOptions($searchOption, $options)
    {
        foreach ($options as $option) {
            if ($searchOption['id'] === $option['id']) {
                return true;
            }
        }
        return false;
    }

    private function appendPairAnswer($answer)
    {
        $this->pdfController->setColumn(10);
        $answers = $answer['answer']['answers'];
        foreach ($answers as $option) {
            $default = $option['question'];
            $correct = $option['correctAnswer'];
            $student = $option['studentAnswer'];
            $chosenCorrect = ($student === $correct);
            $lastRow = $this->pdfController->getRow();
            $this->pdfController->appendPair($default, null);
            $this->pdfController->setRow($lastRow);
            $this->pdfController->setColumn(60);
            $this->pdfController->appendPair($student, $chosenCorrect);
            $this->pdfController->setColumn(10);
        }

    }

    private function appendDrawAnswer($answer)
    {

        $this->pdfController->appendText('Obrázok', 'text');
        $this->pdfController->appendImage($answer['answer']);

    }

    private function appendMathAnswer($answer)
    {

        $this->pdfController->appendText('Odpoveď', 'text');
        $this->pdfController->appendLatex($answer['answer']);


    }

    private function appendQuestionWording($questionNumber, $answer)
    {
        $questionWording = $this->getQuestionWording($questionNumber, $answer);
        $attributes = explode('\MATH', $questionWording['text']);
        $quote = $attributes[1];
        $this->pdfController->appendText('Otázka', 'text');
        $this->pdfController->appendText($attributes[0], 'title');
        if (count($attributes) > 1) {
            $this->pdfController->appendLatex($quote);
        }


    }


    private function getQuestionWording($questionNumber, $answer)
    {
        $questionWording = $questionNumber . '. ' . $answer['text'];
        if ($answer['points']) {
            $points = 'Body: ' . $answer['points'] . ' / ' . $answer['maxPoints'];
        } else {
            $points = 'Body: ' . $answer['maxPoints'];
        }
        return ['text' => $questionWording, 'points' => $points];
    }


    private function getOptions($options, int $index)
    {
        if (!$this->isOverflow($options, $index)) {
            return $options[$index];
        }
        return null;
    }


    private function isOverflow($array, $index)
    {
        $length = count($array);
        return $length <= $index;
    }
}
