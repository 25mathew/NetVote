<?php
	session_start();
	function checkLoginStatus($destination){
		if(!isset($_SESSION['logged_in'])){
			session_destroy();
			header("Location: " . $destination);
		}
	}
?>