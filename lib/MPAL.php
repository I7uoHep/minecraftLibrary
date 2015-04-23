<?php

    class MPAL {

        
        
        public function __construct() {
            $this->mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);

                if (mysqli_connect_errno()) {
                        printf("Ошибка подключения базы: %s\n", mysqli_connect_error());
                        exit();
                }
            
                $this->mysqli->set_charset("utf8");
        }
        
        // Функция пополнения баланса
        
        public function updateBalance($username, $amount) {
            
            $this->username     = $this->mysqli->real_escape_string($username);
            $this->amount       = intval($amount);
            
            $this->Result       = $this->mysqli->query("UPDATE ".userTable." SET ".userBColumn." = ".userBColumn."+'".$this->amount."' WHERE ".userNColumn." = '".$this->username."'") or die($this->mysqli->error);
            
            if(!$this->Result) {
                return false;
            }
            return true;
            
        }
        
        // Функция списания средств с баланса
        
        public function cancelMoney($username, $amount) {
            
            $this->username     = $this->mysqli->real_escape_string($username);
            $this->amount       = intval($amount);
            
            $this->Result       = $this->mysqli->query("UPDATE ".userTable." SET ".userBColumn." = ".userBColumn."-'".$this->amount."' WHERE ".userNColumn." = '".$this->username."'") or die($this->mysqli->error);
            
            if(!$this->Result) {
                return false;
            }
            return true;
            
        }
        
        // Функция смены префикса
        
        public function prefixChange($username, $prefix) {
            
            $this->username     = $this->mysqli->real_escape_string($username);
            $this->prefix       = $this->mysqli->real_escape_string($prefix);
            
            $this->Result       = $this->mysqli->query("UPDATE ".permissionsTable." SET `prefix` = '".$this->prefix."' WHERE `name` = '".$this->username."'") or die($this->mysqli->error);
            
            if(!$this->Result) {
                return false;
            }
            return true;
            
        }
        
        // Функция смены суффикса
        
        public function suffixChange($username, $suffix) {
            
            $this->username     = $this->mysqli->real_escape_string($username);
            $this->suffix       = $this->mysqli->real_escape_string($suffix);
            
            $this->Result       = $this->mysqli->query("UPDATE ".permissionsTable." SET `suffix` = '".$this->suffix."' WHERE `name` = '".$this->username."'") or die($this->mysqli->error);
            
            if(!$this->Result) {
                return false;
            }
            return true;
            
        }
        
        // Функция смены группы
        
        public function groupChange($username, $group) {
            
            $this->username     = $this->mysqli->real_escape_string($username);
            $this->group        = intval($group);
            
            $this->Result       = $this->mysqli->query("UPDATE ".permissionsTable." SET `type` = '".$this->group."' WHERE `name` = '".$this->username."'") or die($this->mysqli->error);
            
            if(!$this->Result) {
                return false;
            }
            return true;
            
        }
        
        
        // Функция создания новой группы в Permissions
        
        public function addGroup($name, $type, $permission, $world, $value) {
            
            $this->name         = $this->mysqli->real_escape_string($name);
            $this->type         = intval($type);
            $this->permission   = $this->mysqli->real_escape_string($permission);
            $this->world        = $this->mysqli->real_escape_string($world);
            $this->value        = $this->mysqli->real_escape_string($value);
            
            $this->typeCheck    = $this->mysqli->query("SELECT * FROM ".permissions." WHERE `type` = '".$this->type."' OR `name` = '".$this->name."'") or die($this->mysqli->error);
            
            if($this->typeCheck->num_rows > 0) {
                return false;
            }
            
            $this->GroupCreate  = $this->mysqli->query("INSERT INTO ".permissions." (`name`, `type`, `permission`, `world`, `value`) 
                                                                                       VALUES (
                                                                                               '".$this->name."', 
                                                                                               '".$this->type."', 
                                                                                               '".$this->permission."', 
                                                                                               '".$this->world."', 
                                                                                               '".$this->value."'
                                                                                              )") or die($this->mysqli->error);
            
            if(!$this->GroupCreate) {
                return false;
            }
            
            return true;
        }
        
        // Функция удаления группы
        
        public function delGroup($name) {
            
            $this->name         = $this->mysqli->real_escape_string($name);
            
            $this->groupTypeQ   = $this->mysqli->query("SELECT `type` FROM ".permissions." WHERE `name` = '".$this->name."'");
            $this->groupType    = $this->groupTypeQ->fetch_array();
            
                
            $this->uu           = $this->mysqli->query("UPDATE ".permissionsTable." SET `type` = DEFAULT WHERE `type` = '".$this->groupType['type']."'");
                
            
            $this->Result       = $this->mysqli->query("DELETE FROM ".permissions." WHERE `name` = '".$this->name."'");
            
            if(!$this->Result || !$this->uu) {
                return false;
            }
            
            return true;
            
        }
        
        public function __destruct() {
            
            $this->mysqli->close();
            
        }
        
    }

?>