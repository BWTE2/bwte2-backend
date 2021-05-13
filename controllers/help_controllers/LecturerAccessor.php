<?php

require_once(__DIR__."/../database_abstract/DatabaseCommunicator.php");

class LecturerAccessor extends DatabaseCommunicator
{

    public function __construct()
    {
        //ODKOMENTOVAT PRI POUZIVANI DATABAZY A ZAPISAT UDAJE O DATABAZE DO config.php
        parent::__construct();
    }

    public function handleRegistrationEvent($data){

        $lecturer = $this->getLecturerByEmail($data->lecturerEmail);
        if($lecturer !== null)
        {
            return $this->createRegistrationResponseData(true,false, null);
        }

        $newLecturerId = $this->addNewLecturer($data->lecturerName, $data->lecturerSurname, $data->lecturerEmail,$data->lecturerPassword);
        $newLecturer = $this->getLecturerById($newLecturerId);

        if($newLecturer === null)
        {
            return $this->createRegistrationResponseData(false, false, null);
        }

        return $this->createRegistrationResponseData(false,true,$newLecturer);
    }

    private function addNewLecturer($name, $surname, $email, $password)
    {
        $query = "INSERT INTO teacher(name, surname, email, password) VALUES (:name, :surname, :email, :password);";
        $bindParams = [":name" => $name, ":surname" => $surname, ":email" => $email, ":password" => $this->getHashPassword($password)];

        return $this->pushToDatabaseAndReturnId($query, $bindParams);
    }

    public function handleLoginEvent($data){

        $lecturer = $this->getLecturerByEmail($data->email);

        if($lecturer === null)
            return $this->createLoginResponseData(false,false, null);

        if(!password_verify($data->password,$lecturer["lecturerPassword"]))
        {
            return $this->createLoginResponseData(true,false, null);
        }


        return $this->createLoginResponseData(true,true,$this->getLecturerById($lecturer["lecturerId"]));
    }

    public function isLogged($data){
        //TODO: ak bude treba
        return true;
    }

    public function isRegistered($data){
        //TODO: ak bude treba
        return true;
    }


    function getHashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    private function getLecturerById($id)
    {
        $query = "SELECT teacher.id as id, teacher.name as name, teacher.surname as surname,teacher.email as email FROM teacher WHERE teacher.id = :teacherId;";
        $bindParams = [":teacherId" => $id];
        $lecturer = $this->getFromDatabase($query,$bindParams);

        if(empty($lecturer))
            return null;

        return $this->createLecturerData($lecturer);

    }

    private function createLecturerData($dataFromDatabase)
    {
        return array(
            "lecturerId" => $dataFromDatabase[0]["id"],
            "lecturerName" => $dataFromDatabase[0]["name"],
            "lecturerSurname" => $dataFromDatabase[0]["surname"],
            "lecturerEmail" => $dataFromDatabase[0]["email"]
        );
    }

    private function getLecturerByEmail($email)
    {
        $query = "SELECT teacher.id as id, teacher.email as email, teacher.password as password FROM teacher WHERE teacher.email = :email;";
        $bindParams = [":email" => $email];
        $lecturer = $this->getFromDatabase($query,$bindParams);

        if(empty($lecturer))
            return null;

        return array(
            "lecturerId" => $lecturer[0]["id"],
            "lecturerEmail" => $lecturer[0]["email"],
            "lecturerPassword" => $lecturer[0]["password"]
        );
    }


    private function createRegistrationResponseData($lecturerAlreadyExists, $correctRegistration, $lecturer)
    {
        return array(
            "lecturerAlreadyExists" => $lecturerAlreadyExists,
            "correctRegistration" => $correctRegistration,
            "lecturer" => $lecturer
        );
    }


    private function createLoginResponseData($accountExists, $correctPassword, $lecturer)
    {
        return array(
            "accountExists" => $accountExists,
            "correctPassword" => $correctPassword,
            "lecturer" => $lecturer
        );
    }


    public function getLecturerInfo($id){
        $query = "SELECT id, name, surname, email FROM teacher WHERE id=:id";
        $bindParameters = [":id" => $id];
        $results = $this->getFromDatabase($query, $bindParameters);

        if(empty($results)){
            return ["id" => null, "name" => null, "surname" => null, "email" => null];
        }

        return $results[0];
    }
}