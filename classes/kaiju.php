<?php

class kaiju {
    private $kaiju_id;
    private $name;
    private $health_points;
    private $rank_id;
    private $attack_list = array();
    private $defense_list = array();

    public function __construct($kaiju_id) {
        if(!isset($kaiju_id)) {
            return false;
        }

        $this -> kaiju_id = $kaiju_id;

        //TODO Query database for other information
    }

    public function __set($name, $value) {
        if(property_exists($this, $name)){
            $this -> $name = $value;
        }
    }

    public function __get($name){
        if(property_exists($this, $name)) {
            return $this -> $name;
        }
    }

    public function attack($name) {
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
            FROM kaiju.kaiju_move_list ML
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
