<?php
/*
 * Team 1 Dev Retreat Texting challenge Aug 2018
 * Docs URL https://www.twilio.com/docs/sms/send-messages
 *
 * Rules Create any app using Twilio's SMS or MMS messaging. The app must talk back to the person sending the text.
 *
 * The phone number that has been assigned to your team is +12248013075
 *
 * A database for you to use called team1 has been setup on the dev server.
 *
 * Folders have been created on the develop branch in wapi. Use wapi/team1.
 *
 * Local testing can be done using Postman.
 *
 * The contest will be judged by Brock and 2 other Calldrip employee's. The wining team will get a prize.
 *
 * Good luck!!!
 *
 */

require("/opt/lampp/htdocs/assets/php/mysql.php");

try {
    require_once '/opt/lampp/htdocs/vendor/autoload.php'; // Loads the library
} catch (Exception $e) {
    echo "Failure to load Twilio";
}

use Twilio\Rest\Client;
$client = new Client($twilio_account_sid, $twilio_auth_token);

class kaiju{
    private $id;
    private $name;
    private $health_points;
    private $rank_id;
    private $attack_list = array();
    private $defense_list = array();

    public function __construct($id = null, $name = null, $health_points = null, $rank_id = null, $attack_list = [], $defense_list = []){
        $this -> id = $id;
        $this -> name = $name;
        $this -> health_points = $health_points;
        $this -> rank_id = $rank_id;
        $this -> attack_list = $attack_list;
        $this -> defense_list = $defense_list;
    }

    public function __set($name, $value){
        if( property_exists($this, $name)){
            $this -> $name = $value;
        }
    }

    public function set_attack_list($attacks){
        $this -> attack_list['primary'] = $attacks['primary'];
        $this -> attack_list['secondary'] = $attacks['secondary'];
    }

    public function set_defense_list($defenses){
        $this -> defense_list['primary'] = $defenses['primary'];
        $this -> defense_list['secondary'] = $defenses['secondary'];
    }

    public function __get($name){
        if( property_exists( $this, $name ) ){
            return $this -> $name;
        }
    }

    public function attack($name){
        foreach($this -> attack_list as $attack){
            if($attack == $name){
                $attack_stats = $this->get_move_stats( $attack );
                return $this->roll($attack_stats[0]['min_effect'], $attack_stats[0]['max_effect']);
            }
        }
    }

    public function defend($name){
        foreach($this -> defense_list as $defense) {
            if ($defense == $name) {
                $defense_stats = $this->get_move_stats($defense);

                return $this->roll($defense_stats[0]['min_effect'], $defense_stats[0]['max_effect']);
            }
        }
    }

    private function get_move_stats( $attack_name ){
        $sql = "
            SELECT ML.min_effect, ML.max_effect
            FROM team1.kaiju_move_list ML
            WHERE ML.kaiju_id = ? AND ML.`name` = ?
        ";

        $statement = $GLOBALS['con'] -> prepare($sql);
        $statement -> bind_param('is', $this->id, $attack_name);
        $statement -> execute();
        $result = $statement -> get_result();
        return $info = $result -> fetch_assoc;
    }

    private function roll($min, $max){
        return rand($min, $max);
    }
}

function checkSignup($to) {
    $sql = "
        SELECT * 
        FROM team1.player
        WHERE phone_number = ?
    ";

    $statement = $GLOBALS['con'] -> prepare($sql);
    $statement -> bind_param("i", $to);
    $statement -> execute();
    $result = $statement -> get_result();

    return $result -> num_rows;
}

function sendSignupText($to) {
    try {
        $json_response_data = $GLOBALS['client'] -> messages -> create(
            $to,
            array("from" => "+12248013075",
                "body" => "Welcome to Kaiju Battle Arena! Sign up by replying with your player name!"
            ));
    }
    catch(Exception $e){
        echo 'Message: ' .$e->getMessage();
    }
}

function createNewPlayer($phoneNumber, $name = "") {
    $sql = "
        INSERT INTO team1.player (`name`, `phone_number`) VALUES (?, ?);
    ";

    $statement = $GLOBALS['con'] -> prepare($sql);
    $statement -> bind_param("si", $name, $phoneNumber);
    return $statement -> execute();
}

function updatePlayer($phoneNumber, $name = "") {
    $sql = "
        UPDATE team1.player SET `name` = ? WHERE phone_number = ?;
    ";

    $statement = $GLOBALS['con'] -> prepare($sql);
    $statement -> bind_param("si", $name, $phoneNumber);
    return $statement -> execute();
}

function checkName($to) {
    $sql = "
        SELECT * 
        FROM team1.player
        WHERE phone_number = ?
    ";

    $statement = $GLOBALS['con'] -> prepare($sql);
    $statement -> bind_param("i", $to);
    $statement -> execute();
    $result = $statement -> get_result();
    $info = $result -> fetch_assoc();

    return $info['name'] == '';
}

function sendSignupSuccessText($to, $name, $kaiju_id) {
    $kaiju_name = getKaijuName($kaiju_id);

    try {
        $json_response_data = $GLOBALS['client'] -> messages -> create(
            $to,
            array("from" => "+12248013075",
                "body" => "Welcome {$name}! Your first Kaiju is {$kaiju_name}!"
            ));
    }
    catch(Exception $e){
        echo 'Message: ' .$e->getMessage();
    }
}

function sendMenuOptions($to) {
    try {
        $json_response_data = $GLOBALS['client'] -> messages -> create(
            $to,
            array("from" => "+12248013075",
                "body" => "Menu Options: Battle Arena (Fight me bro!)"
            ));
    }
    catch(Exception $e){
        echo 'Message: ' .$e->getMessage();
    }
}

function fetchFirstKaiju() {

    $random_number = RNJesus(1, 5);

    $sql = "
        SELECT kaiju_id
        FROM team1.kaiju
        WHERE kaiju_id = ?
    ";

    $statement = $GLOBALS['con'] -> prepare($sql);
    $statement -> bind_param("i", $random_number);
    $statement -> execute();
    $result = $statement -> get_result();
    $info = $result -> fetch_assoc();

    return $info['kaiju_id'];
}

function getKaijuName($kaiju_id) {
    $sql = "
        SELECT `name`
        FROM team1.kaiju
        WHERE kaiju_id = ?
    ";

    $statement = $GLOBALS['con'] -> prepare($sql);
    $statement -> bind_param("i", $kaiju_id);
    $statement -> execute();
    $result = $statement -> get_result();
    $info = $result -> fetch_assoc();

    return $info['name'];
}

function assignPlayerKaiju($to, $kaiju) {
    $player_id = getPlayerID($to);

    $sql = "
        INSERT INTO team1.player_kaiju (`player_id`, `kaiju_id`) VALUES(?, ?)
    ";

    $statement = $GLOBALS['con'] -> prepare($sql);
    $statement -> bind_param("ii", $player_id, $kaiju);
    return $statement -> execute();
}

function assignBattleArena($to) {
    $sql = "
        SELECT battle_arena_id, p.phone_number, p.name 
        FROM team1.battle_arena ba
            LEFT JOIN  team1.player p ON p.player_id = ba.player_one_id
        WHERE player_one_id IS NOT NULL 
        AND player_two_id IS NULL
    ";

    $statement = $GLOBALS['con'] -> prepare($sql);
    $statement -> execute();
    $result = $statement -> get_result();
    $info = $result -> fetch_assoc();

    $battle_arena_id = $info['battle_arena_id'];
    $player_id = getPlayerID($to);
    $current_player_id = $player_id;

    if($result -> num_rows > 0) {
        $sqlTwo = "
            UPDATE team1.battle_arena SET player_two_id = ?, current_player_id = ? WHERE battle_arena_id = ?
        ";

        $statement = $GLOBALS['con'] -> prepare($sqlTwo);
        $statement -> bind_param("iii", $player_id, $current_player_id, $battle_arena_id);
        $statement -> execute();

        $player_one_name = $info['name'];
        $player_two_name = getPlayerName($to);

        try {
            $json_response_data = $GLOBALS['client'] -> messages -> create(
                $to,
                array("from" => "+12248013075",
                    "body" => "A match has been found! You are paired up against {$player_one_name}! Your move first."
                ));
        }
        catch(Exception $e){
            echo 'Message: ' .$e->getMessage();
        }

        try {
            $json_response_data = $GLOBALS['client'] -> messages -> create(
                $info['phone_number'],
                array("from" => "+12248013075",
                    "body" => "Match Found! Your opponent is {$player_two_name}! Their move first"
                ));
        }
        catch(Exception $e){
            echo 'Message: ' .$e->getMessage();
        }


        return "Match start";
    } else {
        $sqlTwo = "
            INSERT INTO team1.battle_arena (player_one_id) VALUES(?)
        ";

        $statement = $GLOBALS['con'] -> prepare($sqlTwo);
        $statement -> bind_param("i", $player_id);
        $statement -> execute();

        try {
            $json_response_data = $GLOBALS['client'] -> messages -> create(
                $to,
                array("from" => "+12248013075",
                    "body" => "Waiting for opponent...you will receive a text when a match is found."
                ));
        }
        catch(Exception $e){
            echo 'Message: ' .$e->getMessage();
        }

        return "Match wait";
    }
}

function getPlayerName($to) {
    $sql = "
        SELECT `name`
        FROM team1.player
        WHERE phone_number = ?
    ";

    $statement = $GLOBALS['con'] -> prepare($sql);
    $statement -> bind_param("i", $to);
    $statement -> execute();
    $result = $statement -> get_result();
    $info = $result -> fetch_assoc();

    return $info['name'];
}

function getPlayerID($to) {
    $sql = "
        SELECT player_id
        FROM team1.player
        WHERE phone_number = ?
    ";

    $statement = $GLOBALS['con'] -> prepare($sql);
    $statement -> bind_param("i", $to);
    $statement -> execute();
    $result = $statement -> get_result();
    $info = $result -> fetch_assoc();

    return $info['player_id'];
}

function RNJesus($min, $max) {
    return rand( $min, $max );
}

function sendInvalidResponseText($to) {
    try {
        $json_response_data = $GLOBALS['client'] -> messages -> create(
            $to,
            array("from" => "+12248013075",
                "body" => "Sorry, invalid response."
            ));
    }
    catch(Exception $e){
        echo 'Message: ' .$e->getMessage();
    }
}

function sendPlayerOptions($to) {
    $player_id = getPlayerID($to);

    $sql = "
        SELECT 
          kml.name as MoveName,
          mt.name as MoveType,
          kml.min_effect as MinEffect,
          kml.max_effect as MaxEffect 
        FROM team1.kaiju k
          INNER JOIN team1.player_kaiju pk ON pk.kaiju_id = k.kaiju_id
          INNER JOIN team1.player p ON p.player_id = pk.player_id
          INNER JOIN team1.kaiju_move_list kml ON kml.kaiju_id = k.kaiju_id
          INNER JOIN team1.move_type mt ON mt.move_type_id = kml.move_type_id
        WHERE p.player_id = ?
    ";

    $statement = $GLOBALS['con'] -> prepare($sql);
    $statement -> bind_param("i", $player_id);
    $statement -> execute();
    $result = $statement -> get_result();

    $message = "Menu options: <br>";

    while($row = $result -> fetch_assoc()) {
        try {
            $json_response_data = $GLOBALS['client'] -> messages -> create(
                $to,
                array("from" => "+12248013075",
                    "body" => "Name: {$row['MoveName']} Type: {$row['MoveType']}"
                ));
        }
        catch(Exception $e){
            echo 'Message: ' .$e->getMessage();
        }
    }
}

function checkIfInGame($to) {
    $player_id = getPlayerID($to);
    $player_id_again = $player_id;

    $sql = "
        SELECT *
        FROM team1.battle_arena 
        WHERE (player_one_id = ? OR player_two_id = ?)
        AND winning_player_id IS NULL
    ";

    $statement = $GLOBALS['con'] -> prepare($sql);
    $statement -> bind_param("ii", $player_id, $player_id_again);
    $statement -> execute();
    $result = $statement -> get_result();

    return $result -> num_rows > 0;
}

if(checkSignup($_POST['From'])) {
    if(checkName($_POST['From'])) {
        updatePlayer($_POST['From'], $_POST['Body']);
        $kaiju_id = fetchFirstKaiju();
        assignPlayerKaiju($_POST['From'], $kaiju_id);
        sendSignupSuccessText($_POST['From'], $_POST['Body'], $kaiju_id);
        sendMenuOptions($_POST['From']);
    } else {
        $bool = checkIfInGame($_POST['From']);

        if($_POST['Body'] == "Fight me bro!") {
            $status = assignBattleArena($_POST['From']);

            if($status == 'Match start') {
                sendPlayerOptions($_POST['From']);
            }
        } else if(checkIfInGame($_POST['From'])) {
            $player_id = getPlayerID($_POST['From']);

            $sql = "
                SELECT k.*
                FROM team1.kaiju k
                  INNER JOIN team1.player_kaiju pk ON pk.kaiju_id = k.kaiju_id
                  INNER JOIN team1.player p ON p.player_id = pk.player_id 
                WHERE p.player_id = ?
            ";

            $statement = $GLOBALS['con'] -> prepare($sql);
            $statement -> bind_param("i", $player_id);
            $statement -> execute();
            $result = $statement -> get_result();
            $info = $result -> fetch_assoc();

            $sqlTwo = "
                SELECT kml.*
                FROM team1.kaiju k
                  INNER JOIN team1.player_kaiju pk ON pk.kaiju_id = k.kaiju_id
                  INNER JOIN team1.player p ON p.player_id = pk.player_id 
                  INNER JOIN team1.kaiju_move_list kml ON kml.kaiju_id = k.kaiju_id
                WHERE p.player_id = ?
            ";

            $statement = $GLOBALS['con'] -> prepare($sqlTwo);
            $statement -> bind_param("i", $player_id);
            $statement -> execute();
            $result = $statement -> get_result();

            $attack = 0;
            $defense = 0;
            $attackArray = array();
            $defenseArray = array();

            while($moveInfo = $result -> fetch_assoc()) {
                if($moveInfo['move_type_id'] == '1') {
                    if($attack == 0) {
                        $attackArray['primary'] = $moveInfo['name'];
                    } else {
                        $attackArray['secondary'] = $moveInfo['name'];
                    }
                } else {
                    if($defense == 0) {
                        $defenseArray['primary'] = $moveInfo['name'];
                    } else {
                        $defenseArray['secondary'] = $moveInfo['name'];
                    }
                }
            }

            $moveName = $_POST['Body'];

            $sqlThree = "
                SELECT *
                FROM team1.kaiju_move_list kml
                WHERE `name` = ?
            ";

            $statement = $GLOBALS['con'] -> prepare($sqlThree);
            $statement -> bind_param("s", $moveName);
            $statement -> execute();
            $result = $statement -> get_result();
            $moveTestInfo = $result -> fetch_assoc();

            $kaijuClass = new kaiju($info['kaiju_id'], $info['name'], $info['health_points'], $info['rank_id'], $attackArray, $defenseArray);


            if($moveTestInfo['move_type_id'] == '1') {
                $value = $kaijuClass -> attack($moveName);
                $message = "Your attack did {$value} damage! Opponents turn.";
                $secondMessage = "Your opponent attacked you for {$value} damage! Your turn.";
            } else {
                $value = $kaijuClass -> defend($moveName);
                $message = "Your defense increased your health by {$value}! Opponents turn.";
                $secondMessage = "Your opponent increased their health by {$value}! Your turn.";
            }

            try {
                $json_response_data = $GLOBALS['client'] -> messages -> create(
                    $_POST['From'],
                    array("from" => "+12248013075",
                        "body" => $message
                    ));
            }
            catch(Exception $e){
                echo 'Message: ' .$e->getMessage();
            }

            $sqlFour = "
                SELECT *
                FROM team1.battle_arena 
                WHERE player_one_id = ? OR player_two_id = ?
            ";

            $statement = $GLOBALS['con'] -> prepare($sqlFour);
            $statement -> bind_param("ii", $player_id, $player_id);
            $statement -> execute();
            $result = $statement -> get_result();
            $test = $result -> fetch_assoc();

            if($test['player_one_id'] == $player_id) {
                $testPlayer = $test['player_two_id'];
            } else {
                $testPlayer = $test['player_one_id'];
            }

            $sqlFive = "
                SELECT phone_number
                FROM team1.player
                WHERE player_id = ?
            ";

            $statement = $GLOBALS['con'] -> prepare($sqlFive);
            $statement -> bind_param("i", $testPlayer);
            $statement -> execute();
            $result = $statement -> get_result();
            $testTwo = $result -> fetch_assoc();

            try {
                $json_response_data = $GLOBALS['client'] -> messages -> create(
                    $testTwo['phone_number'],
                    array("from" => "+12248013075",
                        "body" => $secondMessage
                    ));
            }
            catch(Exception $e){
                echo 'Message: ' .$e->getMessage();
            }


        } else {
            sendInvalidResponseText($_POST['From']);
        }
    }
} else {
    if($_POST['Body'] != 'Sign me up') {
        sendInvalidResponseText($_POST['From']);
    } else {
        sendSignupText($_POST['From']);
        createNewPlayer($_POST['From']);
    }
}
