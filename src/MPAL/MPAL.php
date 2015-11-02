<?php

    namespace MPAL;

    use Dotenv\Dotenv;
    use \PDO;

    class MPAL {

        private $db;

        private $usertable;
        private $usercolumn;
        private $permissionstable;
        private $pexgrouptable;

        public function __construct() {
            $dotenv = new Dotenv(__DIR__);
            $dotenv->load();

            $this->usertable = getenv('USERS_TABLE');
            $this->usercolumn = getenv('USERNAME_COLUMN');
            $this->permissionstable = getenv('PERMISSIONS_TABLE');
            $this->pexgrouptable = getenv('PEXGROUPS_TABLE');

            $this->db = new PDO(
                    "mysql:host=".getenv('DB_HOST').";
                     dbname=".getenv('DB_NAME').";
                     charset=utf8",
                     getenv('DB_USER'),
                     getenv('DB_PASS')
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

        public function addFunds($username, $amount) {
            $query = $this->db->prepare("UPDATE ".$this->usertable." SET money = money + ? WHERE ".$this->usercolumn." = ?");

            if(!$query->execute([$amount, $username])) {
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

        public function removeFunds($username, $amount) {

            $query = $this->db->prepare("UPDATE ".$this->usertable." SET money = money - ? WHERE ".$this->usercolumn." = ?");

            if(!$query->execute([$amount, $username])) {
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

        public function prefixChange($username, $prefix) {

            $query = $this->db->prepare("UPDATE ".$this->permissionstable." SET `prefix` = ? WHERE `name` = ?");

            if(!$query->execute([$prefix, $username])) {
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

        public function suffixChange($username, $suffix) {

            $query = $this->db->prepare("UPDATE ".$this->permissionstable." SET `suffix` = ? WHERE `name` = ?");

            if(!$query->execute([$suffix, $username])) {
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

        public function groupChange($username, $group) {

            $query = $this->db->prepare("UPDATE ".$this->permissionstable." SET `type` = ? WHERE `name` = ?");

            if(!$query->execute([$group, $username])) {
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

        public function groupCreate($name, $type, $permission, $world, $value) {

            $query = $this->db->prepare(
            "INSERT INTO ".$this->pexgrouptable." (name, type, permission, world, value)
             SELECT * FROM (SELECT :name, :type, :permission, :world, :value) AS tmp
             WHERE NOT EXISTS (
                 SELECT name FROM ".$this->pexgrouptable." WHERE name = :name
             ) LIMIT 1"
        );

            if(!$query->execute([
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

        public function groupDelete($name) {

            $query = $this->db->prepare("
            update
            	".$this->permissionstable."
            set
            	".$this->permissionstable.".type = 0
            where
            	".$this->permissionstable.".type = (select ".$this->pexgrouptable.".type from ".$this->pexgrouptable." where ".$this->pexgrouptable.".name = :name);
            delete from
            	".$this->pexgrouptable."
            where
            	".$this->pexgrouptable.".name = :name
                ");

            if(!$query->execute(['name' => $name])) {
                return false;
            }

            return true;

        }
/*
        public function __destruct() {

            $this->mysqli->close();

        }
*/
    }
