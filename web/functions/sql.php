<?php
    function establishSQL(){
        $conn = mysqli_connect("localhost","<username>","<p>","databasename");
        if(!$conn){
            exit('Connect Error (' . mysqli_connect_errno() . ') '. mysqli_connect_error());
        }
        return $conn;
    }
    function queryHandler($statement){ 
        session_regenerate_id(true);
        return mysqli_query(establishSQL(),$statement);
    }

 ?>
