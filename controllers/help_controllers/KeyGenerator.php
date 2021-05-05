<?php

require_once(__DIR__."/../database_abstract/DatabaseCommunicator.php");

class KeyGenerator extends DatabaseCommunicator
{

    public function generateKey(){
        do{
            $key = $this->getNewKey();
        } while(!$this->isKeyUnique($key));

        return $key;
    }

    public function getNewKey(){
        $key = "a0a0a0";
        try {
            $key = bin2hex(random_bytes(3));
        } catch (Exception $e) {
        }

        return $key;
    }

    public function isKeyUnique($key){
        $query = "SELECT * FROM test WHERE test.code=:key";
        $bindParameters = [":key" => $key];
        $result = $this->getFromDatabase($query, $bindParameters);

        return !(count($result) > 0);
    }


}