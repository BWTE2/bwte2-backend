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
        $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $charactersLength = strlen($characters) - 1;
        $key = "";

        for ($i = 0; $i < 6; $i++) {
            $index = random_int(0, $charactersLength);
            $key .= $characters[$index];
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