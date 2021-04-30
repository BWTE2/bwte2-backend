<?php

require_once("test_controllers/TestHandler.php");
require_once("test_controllers/TestGetter.php");
require_once("test_controllers/StudentGetter.php");


class MainTestController
{
    public function getOneTestInfo($key){
        $testGetter = new TestGetter();
        return $testGetter->getOneTestInfo($key);
    }

    public function getAllTestsInfo(){
        $testGetter = new TestGetter();
        return $testGetter->getAllTestsInfo();
    }

    public function addTest($data, $key){
        $testHandler = new TestHandler();
        return $testHandler->addTest($data, $key);
    }

    public function activateTest($key){
        $testHandler = new TestHandler();
        return $testHandler->activateTest($key);
    }

    public function deactivateTest($key){
        $testHandler = new TestHandler();
        return $testHandler->deactivateTest($key);
    }

    public function getQuestions($key){
        $testGetter = new TestGetter();
        return $testGetter->getQuestions($key);
    }

    public function isValidKey($key){
        $testGetter = new TestGetter();
        return $testGetter->isValidKey($key);
    }

    public function getTestMaxTime($key){
        $testGetter = new TestGetter();
        return $testGetter->getTestMaxTime($key);
    }

    public function getStudentsStates($key){
        $studentGetter = new StudentGetter();
        return $studentGetter->getStudentsStates($key);
    }

    public function isTestRunning($key){
        $testGetter = new TestGetter();
        return $testGetter->isTestRunning($key);
    }

    public function getOneStudentAnswers($key, $studentId){
        $studentGetter = new StudentGetter();
        return $studentGetter->getOneStudentAnswers($key, $studentId);
    }

    public function getAllStudents($key){
        $studentGetter = new StudentGetter();
        return $studentGetter->getAllStudents($key);
    }

    public function sendAnswers($data, $key, $studentId){
        $testHandler = new TestHandler();
        return $testHandler->sendAnswers($data, $key, $studentId);
    }

    public function updateEvaluation($data, $key, $studentId){
        $testHandler = new TestHandler();
        return $testHandler->updateEvaluation($data, $key, $studentId);
    }

}