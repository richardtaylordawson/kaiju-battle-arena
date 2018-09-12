# Dump of schema kaiju
# ------------------------------------------------------------
DROP SCHEMA IF EXISTS `kaiju`;
CREATE SCHEMA IF NOT EXISTS `kaiju` DEFAULT CHARACTER SET utf8;



# Dump of table battle_arena
# ------------------------------------------------------------

DROP TABLE IF EXISTS `battle_arena`;

CREATE TABLE `battle_arena` (
  `battle_arena_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `player_one_id` int(11) unsigned DEFAULT NULL,
  `player_two_id` int(11) unsigned DEFAULT NULL,
  `winning_player_id` int(11) unsigned DEFAULT NULL,
  `current_player_id` int(11) unsigned DEFAULT NULL,
  `start_date` varchar(45) NOT NULL DEFAULT 'CURDATE()',
  `start_time` varchar(45) NOT NULL DEFAULT 'CURTIME()',
  `end_date` varchar(45) NOT NULL DEFAULT 'CURDATE()',
  `end_time` varchar(45) NOT NULL DEFAULT 'CURTIME()',
  PRIMARY KEY (`battle_arena_id`),
  KEY `player_one_id` (`player_one_id`),
  KEY `player_two_id` (`player_two_id`),
  KEY `winning_player_id` (`winning_player_id`),
  KEY `current_player_id` (`current_player_id`),
  CONSTRAINT `battle_arena_ibfk_1` FOREIGN KEY (`player_one_id`) REFERENCES `player` (`player_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `battle_arena_ibfk_2` FOREIGN KEY (`player_two_id`) REFERENCES `player` (`player_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `battle_arena_ibfk_3` FOREIGN KEY (`winning_player_id`) REFERENCES `player` (`player_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `battle_arena_ibfk_4` FOREIGN KEY (`current_player_id`) REFERENCES `player` (`player_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table kaiju
# ------------------------------------------------------------

DROP TABLE IF EXISTS `kaiju`;

CREATE TABLE `kaiju` (
  `kaiju_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `health_points` int(11) DEFAULT NULL,
  `kaiju_rank_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`kaiju_id`),
  KEY `kaiju_rank_id` (`kaiju_rank_id`),
  CONSTRAINT `kaiju_ibfk_1` FOREIGN KEY (`kaiju_rank_id`) REFERENCES `kaiju_rank` (`kaiju_rank_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `kaiju` (`kaiju_id`, `name`, `health_points`, `kaiju_rank_id`)
VALUES
	(1,'Godzilla',1000,4),
	(2,'Mothra',1000,4),
	(3,'Senpai',500,2),
	(4,'Kappa',250,1),
	(5,'Oni',750,3);



# Dump of table kaiju_move_list
# ------------------------------------------------------------

DROP TABLE IF EXISTS `kaiju_move_list`;

CREATE TABLE `kaiju_move_list` (
  `kaiju_move_list_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `kaiju_id` int(11) unsigned DEFAULT NULL,
  `name` varchar(45) DEFAULT NULL,
  `move_type_id` int(11) unsigned DEFAULT NULL,
  `min_effect` int(11) DEFAULT NULL,
  `max_effect` int(11) DEFAULT NULL,
  PRIMARY KEY (`kaiju_move_list_id`),
  KEY `kaiju_id` (`kaiju_id`),
  KEY `move_type_id` (`move_type_id`),
  CONSTRAINT `kaiju_move_list_ibfk_1` FOREIGN KEY (`kaiju_id`) REFERENCES `kaiju` (`kaiju_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `kaiju_move_list_ibfk_2` FOREIGN KEY (`move_type_id`) REFERENCES `move_type` (`move_type_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table kaiju_rank
# ------------------------------------------------------------

DROP TABLE IF EXISTS `kaiju_rank`;

CREATE TABLE `kaiju_rank` (
  `kaiju_rank_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`kaiju_rank_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `kaiju_rank` (`kaiju_rank_id`, `name`)
VALUES
	(1,'Common'),
	(2,'Uncommon'),
	(3,'Rare'),
	(4,'Legendary');



# Dump of table move_type
# ------------------------------------------------------------

DROP TABLE IF EXISTS `move_type`;

CREATE TABLE `move_type` (
  `move_type_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`move_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `move_type` (`move_type_id`, `name`)
VALUES
	(1,'Attack'),
	(2,'Defense');



# Dump of table player
# ------------------------------------------------------------

DROP TABLE IF EXISTS `player`;

CREATE TABLE `player` (
  `player_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `phone_number` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table player_kaiju
# ------------------------------------------------------------

DROP TABLE IF EXISTS `player_kaiju`;

CREATE TABLE `player_kaiju` (
  `player_kaiju_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` int(11) unsigned DEFAULT NULL,
  `kaiju_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`player_kaiju_id`),
  KEY `player_id` (`player_id`),
  KEY `kaiju_id` (`kaiju_id`),
  CONSTRAINT `player_kaiju_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `player` (`player_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `player_kaiju_ibfk_2` FOREIGN KEY (`kaiju_id`) REFERENCES `kaiju` (`kaiju_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table player_turn
# ------------------------------------------------------------

DROP TABLE IF EXISTS `player_turn`;

CREATE TABLE `player_turn` (
  `player_turn_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `battle_arena_id` int(11) unsigned NOT NULL,
  `kaiju_id` int(11) unsigned NOT NULL,
  `move_id` int(11) unsigned NOT NULL,
  `effect` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`player_turn_id`),
  KEY `battle_arena_id_fk` (`battle_arena_id`),
  KEY `kaiju_move_list_id` (`move_id`),
  KEY `kaiju_id_fk` (`kaiju_id`),
  CONSTRAINT `battle_arena_id_fk` FOREIGN KEY (`battle_arena_id`) REFERENCES `battle_arena` (`battle_arena_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `kaiju_id_fk` FOREIGN KEY (`kaiju_id`) REFERENCES `kaiju` (`kaiju_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `kaiju_move_list_id` FOREIGN KEY (`move_id`) REFERENCES `kaiju_move_list` (`kaiju_move_list_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
