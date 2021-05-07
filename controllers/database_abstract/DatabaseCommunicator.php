<?php

require_once("config.php");

abstract class DatabaseCommunicator
{
    protected $connection;

    public function __construct()
    {
        $this->connection = new PDO("mysql:host=".DB_HOST."; dbname=".DB_NAME, DB_USER, DB_PASSWORD);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    //$query = "SELECT * FROM table WHERE table.id=:id AND table.name=:name"
    //$bindParameters = [":id" => 5, ":name" => "Rumburak"]
    protected function getFromDatabase($query, $bindParameters){
        $statement = $this->connection->prepare($query);
        $statement->execute($bindParameters);

        $statement->setFetchMode(PDO::FETCH_ASSOC);
        return $statement->fetchAll();
    }

    //$query = "INSERT INTO table (name, work) VALUES (:name, :work)"
    //$bindParameters = [":name" => "Miro Pele", ":work" => "rapper"]
    protected function pushToDatabase($query, $bindParameters){
        $statement = $this->connection->prepare($query);
        $statement->execute($bindParameters);
    }

    //$query = "INSERT INTO table (name, work) VALUES (:name, :work)"
    //$bindParameters = [":name" => "Miro Pele", ":work" => "rapper"]
    protected function pushToDatabaseAndReturnId($query, $bindParameters){
        $statement = $this->connection->prepare($query);
        $statement->execute($bindParameters);
        return $this->connection->lastInsertId();
    }

}