<?php

namespace models;

class BaseModel{

    public function connection($dbname){
        $servername = "localhost";
        $username = "root";
        $password = "";

        if(!isset($dbname)||empty($dbname)){
            die("Connection couldn't establish");
        }

        // Create connection
        $conn = new \mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $this->conn=$conn;
    }
    
    function insert($table,$field,$value){
        $sql="INSERT INTO $table (".$field.") VALUES('".$value."');";
        if ($this->conn->query($sql) === TRUE) {
            echo "New record created successfully";
          } else {
            echo "Error: " . $sql . "<br>" . $this->conn->error;
          }
    }
}

?>