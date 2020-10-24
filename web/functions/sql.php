<?php
    //require_once __DIR__ . '/main.php';
    function establishSQL(){
        $conn = mysqli_connect("localhost","putting","","puttingv2");
        if(!$conn){
            exit('Connect Error (' . mysqli_connect_errno() . ') '. mysqli_connect_error());
        }
        return $conn;
    }
    function queryHandler($statement){ //NOT TO BE USED BY ANYTHING OTHER THAN FUNCTIONS INSIDE safeQueryHandler()!!!
        session_regenerate_id(true);                            //regenerates session id and throws away previous id
        return mysqli_query(establishSQL(),$statement);
    }
    function safeQueryHandler($statement){
        timeoutTracker();
        $result = NULL;
        if(secureCheck()){
            if(!isset($_SESSION['league_selected']) || checkLeaguePermission($_SESSION['league_selected'])){
                $result = queryHandler($statement);
            }
            else{
                $_SESSION['page_error'] = "You do not have permission to modify this league!";
            }
        }
        else{
            //$_SESSION['login_status'] = "error";
            //header('Location: index.php');
        }
        $result = queryHandler($statement); //REMOVE AFTER LEAGUE IMPLEMENTATION
        return $result;
    }
    /*newer broken version of function safeQueryHandler($statement){
        timeoutTracker();
        $result = NULL;
        if(secureCheck()){
            $temp = checkLeaguePermission($_SESSION['league_id']);
            if($temp == NULL || $temp >= getDefaultPermission($_SESSION['league_id'])){
                $result = queryHandler($statement);
            }
            else{
                $_SESSION['page_error'] = "You do not have permission to modify this league!";
            }
        }
        else{
            //$_SESSION['login_status'] = "error";
            //header('Location: index.php');
        }
        //$result = queryHandler($statement); //REMOVE AFTER LEAGUE IMPLEMENTATION
        return $result;
    }*/
    function getDefaultPermission($league_id){
        $result = queryHandler("SELECT * FROM leagues WHERE league_id == " . $league_id);
        $row = $result->fetch_array();
        return $row['default_permission'];
    }
    function checkLeaguePermission($league_id){
        $result = queryHandler("SELECT * FROM " . $league_id . "-permissions");
        while($row = $result->fetch_array()){
            if($row['user_id'] == $_SESSION['user_id']){
                return $row['permission_level'];
            }
        }
        return NULL;
    }
    function scoreInputHelper(){
		$sql;
		$sql_week;
		$sql_round;
		switch($_POST['sixth']){
			case 1:
				$sql_week = "weekOne";
			break;
			case 2:
				$sql_week = "weekTwo";
			break;
			case 3:
				$sql_week = "weekThree";
			break;
			case 4:
				$sql_week = "weekFour";
			break;
			case 5:
				$sql_week = "weekFive";
			break;
			case 6:
				$sql_week = "weekSix";
			break;
		};
		switch($_POST['seventh']){
			case 1:
				$sql_round = "r1";
			break;
			case 2:
				$sql_round = "r2";
			break;
			case 3:
				$sql_round = "r3";
			break;
		};
		return "UPDATE " . $sql_week . " SET " . $sql_round . "20r=" . $_POST['first'] . "," . $sql_round . "25r=" . $_POST['second'] . "," . $sql_round . "33r=" . $_POST['third'] . "," . $sql_round . "25m=" . $_POST['fourth'] . "," . $sql_round . "33m=" . $_POST['fifth'] . "," . $sql_round . "total=" . $_POST['eight'] . " WHERE name='" . $_SESSION['name'] . "'";
	}
    function tableSwitch($in){
	    $output;
		switch($in){
			case 1:
				$output = "weekOne";
			break;
			case 2:
				$output = "weekTwo";
			break;
			case 3:
				$output = "weekThree";
			break;
			case 4:
				$output = "weekFour";
			break;
			case 5:
				$output = "weekFive";
			break;
			case 6:
				$output = "weekSix";
			break;
		};
		return $output;
    }
    function overWriteCheckArray(){
		$result = queryHandler("SELECT * FROM grandTotal WHERE name='" . $_SESSION['name'] . "'");
		$row = $result->fetch_array();
		echo "var currentWeekTotals = [" . $row[weekOne] . "," . $row[weekTwo] . "," . $row[weekThree] . "," . $row[weekFour] . "," . $row[weekFive] . "," . $row[weekSix] . "];\n";
	}
    function loadTables(){
		for($i = 1; $i < 7; $i++){

			$current = tableSwitch($i);
			$result = queryHandler("SELECT * from " . $current);
			if($result->num_rows < 1 && $i == 1){
				echo "<p>There is no data to be displayed.</p>";
				return;
			}
			echo "<h3 style='display:none' id='". tableSwitch($i) . "'>Week " . $i . ":<br></h3>";
			echo "<table class='sortable' class='tables' style='display:none' border='1' id='". tableSwitch($i) . "Table'> <tr><th>Name</th><th>Round 1 20ft Recruit</th><th>Round 1 25ft Recruit</th><th>Round 1 33ft Recruit</th><th>Round 1 25ft Marksmen</th><th>Round 1 33ft Marksmen</th><th>Round 1 <span id='invisible'>aa</span>Total<span id='invisible'>aa</span></th><th>Round 2 20ft <span id='invisible'>a</span>Recruit<span id='invisible'>a</span></th><th>Round 2 25ft <span id='invisible'>a</span>Recruit<span id='invisible'>a</span></th><th>Round 2 33ft <span id='invisible'>a</span>Recruit<span id='invisible'>a</span></th><th>Round 2 25ft Marksmen</th><th>Round 2 33ft Marksmen</th><th>Round 2 <span id='invisible'>aa</span>Total<span id='invisible'>aa</span></th><th>Round 3 20ft Recruit</th><th>Round 3 25ft Recruit</th><th>Round 3 33ft Recruit</th><th>Round 3 25ft Marksmen</th><th>Round 3 33ft Marksmen</th><th>Round 3 <span id='invisible'>aa</span>Total<span id='invisible'>aa</span></th><th>Final Total</th> </tr>";
			while($row = $result->fetch_array()){
				if($row['ftotal'] > 0){
					$resultTotal = queryHandler("SELECT total FROM grandTotal WHERE name = '" . $row['name'] . "'");
					$rowTotal = $resultTotal->fetch_array();
					echo "<tr><td onclick='toggleTable(\"" . $row['name'] . "\")'>" . $row['name'] . ": " . $rowTotal['total'] . "</td><td>" . $row['r120r'] . "</td><td>" . $row['r125r'] . "</td><td>" . $row['r133r'] . "</td><td>" . $row['r125m'] . "</td><td>" . $row['r133m'] . "</td><td>" . $row['r1total'] . "</td><td>" . $row['r220r'] . "</td><td>" . $row['r225r'] . "</td><td>" . $row['r233r'] . "</td><td>" . $row['r225m'] . "</td><td>" . $row['r233m'] . "</td><td>" . $row['r2total'] . "</td><td>" . $row['r320r'] . "</td><td>" . $row['r325r'] . "</td><td>" . $row['r333r'] . "</td><td>" . $row['r325m'] . "</td><td>" . $row['r333m'] . "</td><td>" . $row['r3total'] . "</td><td>" . $row['ftotal'] . "</td></tr>";
				}
			}
			echo "</table>";
		}
		echo "<h3 id = 'total' style='display:none'>Overall Totals:<br></h3>";
		echo "<table class='sortable' class='tables' border='1' id='totalTable' style='display:none'> <tr><th>Name</th><th>Week 1</th><th>Week 2</th><th>Week 3</th><th>Week 4</th><th>Week 5</th><th>Week 6</th><th>Total</th></tr>";
		$sql_readOver = "SELECT * from grandTotal";
		$result = queryHandler($sql_readOver);
		while($row = $result->fetch_array()){
			if($row['total'] > 0){
				echo "<tr><td onclick='toggleTable(\"" . $row['name'] . "\")'>" . $row['name'] . "</td><td>" . $row['weekOne'] . "</td><td>" . $row['weekTwo'] . "</td><td>" . $row['weekThree'] . "</td><td>" . $row['weekFour'] . "</td><td>" . $row['weekFive'] . "</td><td>" . $row['weekSix'] . "</td><td>" . $row['total'] . "</td></tr>";
			}
		}
		echo "</table>";

		$result = queryHandler("SELECT * from grandTotal");
		while($row = $result->fetch_array()){
			loadPlayerTables($row['name'],$row['total']);
		}
	}
	function loadPlayerTables($name,$total){
		echo "<h3 style='display:none' id='" . $name . "' >" . $name . ": " . $total . "</h3>";
		echo "<table class='tables'style='display:none' border='1' id='" . $name . "Table' ><tr><th>Week</th><th>Round 1 20ft Recruit</th><th>Round 1 25ft Recruit</th><th>Round 1 33ft Recruit</th><th>Round 1 25ft Marksmen</th><th>Round 1 33ft Marksmen</th><th>Round 1 Total</th><th>Round 2 20ft Recruit</th><th>Round 2 25ft Recruit</th><th>Round 2 33ft Recruit</th><th>Round 2 25ft Marksmen</th><th>Round 2 33ft Marksmen</th><th>Round 2 <span id='invisible'>aa</span>Total<span id='invisible'>aa</span></th><th>Round 3 20ft Recruit</th><th>Round 3 25ft Recruit</th><th>Round 3 33ft Recruit</th><th>Round 3 25ft Marksmen</th><th>Round 3 33ft Marksmen</th><th>Round 3 <span id='invisible'>aaa</span>Total<span id='invisible'>aaa</span></th><th>Final Total</th> </tr>";
		for($i = 1; $i < 7; $i++){
			$result = queryHandler("SELECT * from " . tableSwitch($i) . " WHERE name='" . $name . "'");
			$row = $result->fetch_array();
			echo "<tr><td>" . $i . "</td><td>" . $row['r120r'] . "</td><td>" . $row['r125r'] . "</td><td>" . $row['r133r'] . "</td><td>" . $row['r125m'] . "</td><td>" . $row['r133m'] . "</td><td>" . $row['r1total'] . "</td><td>" . $row['r220r'] . "</td><td>" . $row['r225r'] . "</td><td>" . $row['r233r'] . "</td><td>" . $row['r225m'] . "</td><td>" . $row['r233m'] . "</td><td>" . $row['r2total'] . "</td><td>" . $row['r320r'] . "</td><td>" . $row['r325r'] . "</td><td>" . $row['r333r'] . "</td><td>" . $row['r325m'] . "</td><td>" . $row['r333m'] . "</td><td>" . $row['r3total'] . "</td><td>" . $row['ftotal'] . "</td></tr>";
		}
		echo "</table>";
	}
	function updateTotals(){
		for($i = 1; $i < 7; $i++){
			$current = tableSwitch($i);
			$sql_read = "SELECT * from " . $current;
			$result = queryHandler($sql_read);
			while($row = $result->fetch_array()){
				$total = $row['r1total'] + $row['r2total'] + $row['r3total'];
				$sql_write = "UPDATE " . $current . " SET ftotal=" . $total . " WHERE name='" . $row['name'] . "'";
				queryHandler($sql_write);
				$sql_otherTable = "UPDATE grandTotal SET " . $current . "=" . $total . " WHERE name='" . $row['name'] . "'";
				queryHandler($sql_otherTable);
			}
		}
		$sql_readTotals = "SELECT * from grandTotal";
		$result = queryHandler($sql_readTotals);
		while($row = $result->fetch_array()){
			$currentTotal = $row['weekOne'] + $row['weekTwo'] + $row['weekThree'] + $row['weekFour'] + $row['weekFive'] + $row['weekSix'];
			#echo $currentTotal;
			$sql_writeTotals = "UPDATE grandTotal SET total= " . $currentTotal . " WHERE name='" . $row['name'] . "'";
			queryHandler($sql_writeTotals);
		}
	}
    function checkOverwrite(){

    }
 ?>
