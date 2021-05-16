<?php
require_once(__DIR__."/../database_abstract/DatabaseCommunicator.php");

class QrUpload extends DatabaseCommunicator
{
    public function __construct()
    {
        parent::__construct();
    }

    public function checkInput(){
        if (isset($_POST['codeTest']) &&  isset($_POST['studentId']) &&  isset($_POST['questionId']) &&  isset($_POST['token'])) {
            return true;
        }
        else {
            return false;
        }
    }

    public function isTestActive($code){
        $query = "SELECT is_active FROM test WHERE code=:code";
        $bindParameters = [":code" => $code];
        $results = $this->getFromDatabase($query, $bindParameters);

        if(empty($results)){
            return false;
        }

        return true;
    }

    public function isStudentFinished($test_code, $student_id){
        $query = "SELECT student_action.action, test.code FROM student_action LEFT JOIN test ON student_action.test_id = test.id WHERE student_action.student_id = :student_id AND test.code = :test_code";
        $bindParameters = [":student_id" => $student_id, ":test_code" => $test_code];
        $results = $this->getFromDatabase($query, $bindParameters);

        //var_dump($results);

        if(empty($results)){
            return true;
        }

        if($results[0]["action"] == "FINISHED")
            return true;

        return false;
    }


}

$cnt = new QrUpload();
if($cnt->checkInput()) {
    $codeTest = $_POST['codeTest'];
    $studentId = $_POST['studentId'];
    $questionId = $_POST['questionId'];
    $token = $_POST['token'];

    $directory = "./files/";
    $extension = pathinfo($_FILES["uploadfile"]["name"], PATHINFO_EXTENSION);
    $fullpath = $directory . $codeTest . "_" . $studentId . "_" . $questionId;
    $fullpathextension = $fullpath . "." . $extension;


    if ($cnt->isTestActive($codeTest))
    {
        if($cnt->isStudentFinished($codeTest, $studentId))
        {
            echo "Tento študent už test odovzdal!";
        }
        else
        {
            /* write to file */
            if (glob($fullpath . ".*"))
            {
                echo "Neúspešné nahratie súboru. Súbor uz existuje, možno aj s inou príponou!";
            }
            else
            {
                if (move_uploaded_file($_FILES["uploadfile"]["tmp_name"], $fullpathextension))
                {
                    echo "<p>Súbor " . htmlspecialchars(basename($_FILES["uploadfile"]["name"])) . " bol nahraný.</p>";
                    /* write to db */
                    /*
                    $query = "INSERT INTO question_student(question_id, student_id, type, answer_photo) VALUES (:question_id,:student_id,:type, :answer_photo)";
                    $bindParams = [":question_id" => question_id, ":student_id" => $studentId, ":type" => NULL, ":answer_photo" => $fullpathextension];
                    $this->pushToDatabase($query,$bindParams);
                    */
                }
                else {
                    echo "<p>Došlo k problému pri nahrávaní.</p>";
                }
            }
        }
    }
    else
    {
        echo "Tento test nie je aktivovaný!";
    }
}
else
{
    echo "Chyba vstupu používatela";
}
?>