<?php
	require_once 'global.php';
    function establishSQL(){
        $conn = mysqli_connect("localhost","netvote","","netvote");
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
