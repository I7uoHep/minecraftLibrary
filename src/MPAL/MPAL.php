<?php

    namespace MPAL;

    use \PDO;

    class MPAL {

        private $db;

        private $usertable;
        private $usercolumn;
        private $permissionstable = 'permissions_entity';
        private $pexgrouptable = 'permissions';

        public function __construct($configs) {

            $this->usertable = $configs['USERS_TABLE'];
            $this->usercolumn = $configs['USERNAME_COLUMN'];

            $this->db = new PDO(
                    "mysql:host=".$configs['DB_HOST'].";
                     dbname=".$configs['DB_NAME'].";
                     charset=utf8",
                     $configs['DB_USER'],
                     $configs['DB_PASS'],
                     [
                         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                     ]
            );
            if(!$this->db) echo 'Database connection error';
        }

        /**
         * addFunds
         * Increases quantity of funds on Val
         *
         * @param (string $username) Name of the user in database
         * @param (int $amount) amount which adds on balance
         * @return (bool)
         */

        public function addFunds($username, $amount, $null = null) {
            $this->nullCheck($null);
            $query = $this->db->prepare("UPDATE ".$this->usertable." SET money = money + ? WHERE ".$this->usercolumn." = ?");

            if(!$this->ExecuteQuery($query, [$amount, $username])) {
                return false;
            }
            return true;

        }

        /**
         * removeFunds
         * Decreases quantity of funds on Val
         *
         * @param (string $username) Name of the user in database
         * @param (int $amount) amount which removes from balance
         * @return (bool)
         */

        public function removeFunds($username, $amount, $null = null) {
            $this->nullCheck($null);
            $query = $this->db->prepare("UPDATE ".$this->usertable." SET money = money - ? WHERE ".$this->usercolumn." = ?");

            if(!$this->ExecuteQuery($query, [$amount, $username])) {
                return false;
            }
            return true;

        }

        /**
         * prefixChange
         * Changes user prefix
         *
         * @param (string $username) Name of the user in database
         * @param (string $prefix) new user prefix
         * @return (bool)
         */

        public function prefixChange($username, $prefix, $null = null) {
            $this->nullCheck($null);
            $query = $this->db->prepare("UPDATE ".$this->permissionstable." SET `prefix` = ? WHERE `name` = ?");

            if(!$this->ExecuteQuery($query, [$prefix, $username])) {
                return false;
            }
            return true;

        }

        /**
         * suffixChange
         * Changes user suffix
         *
         * @param (string $username) Name of the user in database
         * @param (string $suffix) new user suffix
         * @return (bool)
         */

        public function suffixChange($username, $suffix, $null = null) {
            $this->nullCheck($null);
            $query = $this->db->prepare("UPDATE ".$this->permissionstable." SET `suffix` = ? WHERE `name` = ?");

            if(!$this->ExecuteQuery($query, [$suffix, $username])) {
                return false;
            }
            return true;

        }


        /**
         * groupChange
         * Changes user group
         *
         * @param (string $username) Name of the user in database
         * @param (int $group) id of group in which user moves
         * @return (bool)
         */

        public function groupChange($username, $group, $null = null) {
            $this->nullCheck($null);
            $query = $this->db->prepare("UPDATE ".$this->permissionstable." SET `type` = ? WHERE `name` = ?");

            if(!$this->ExecuteQuery($query, [$group, $username])) {
                return false;
            }
            return true;

        }


        /**
         * groupCreate
         * Creates a new group for users
         *
         * @param (string $name) Name of the user in database
         * @param (int $type) number of new group
         * @param (string $permission) permission for this group
         * @param (string $group) world in which permissions will affect
         * @param (string $value) ...
         * @return (bool)
         */

        public function groupCreate($name, $type, $permission, $world, $value, $null = null) {
            $this->nullCheck($null);
            $query = $this->db->prepare(
            "INSERT INTO ".$this->pexgrouptable." (name, type, permission, world, value)
             VALUES (:name, :type, :permission, :world, :value)
             ON DUPLICATE KEY UPDATE name = :name"
        );

            if(!$this->ExecuteQuery($query, [
                'name' => $name,
                'type' => $type,
                'permission' => $permission,
                'world' => $world,
                'value' => $value,
                ])) {
                    print_r($query->errorInfo());
                return false;
            }

            return true;
        }

        /**
         * groupDelete
         * Delete group, all users moves in 0 group
         *
         * @param (string $name) Name of the group
         * @return (bool)
         */

        public function groupDelete($name, $null = null) {
            $this->nullCheck($null);
            $query = $this->db->prepare("
            update
            	".$this->permissionstable."
            set
            	".$this->permissionstable.".type = 0
            where
            	".$this->permissionstable.".type = (select ".$this->pexgrouptable.".type from ".$this->pexgrouptable." where ".$this->pexgrouptable.".name = :name limit 1);
            delete from
            	".$this->pexgrouptable."
            where
            	".$this->pexgrouptable.".name = :name
                ");

            if(!$this->ExecuteQuery($query, ['name' => $name])) {
                return false;
            }

            return true;

        }

        /**
         * ExecuteQuery
         * Runs query
         *
         * @param (PDO $query) query object
         * @param (array $params) array with params for query
         * @return (bool) or (exception)
         */

        private function ExecuteQuery($query, $params) {
            try {
                $query->execute($params);
                return true;

            } catch(PDOException $e) {
                return 'Error: ' . $e->getMessage();
            }
        }

        /**
         * nullCheck
         * Check is the method overloaded?
         *
         * @param (null $data) null
         */

        private function nullCheck($data) {
            if($data !== null) {
                echo 'Wrong parametrs';
                die();
            }
        }

        public function __destruct() {

            $this->db = null;

        }

    }
