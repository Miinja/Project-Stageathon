<?php
class DatabaseLinker {
    private static $conn;

    public static function getConnexion() {
        if (self::$conn === null) {
            try {
                // Charger la configuration de la base de données
                $db_config = require 'db_config.php';

                $username = $db_config['username'];
                $password = $db_config['password'];
                $host = $db_config['host'];
                $dbname = $db_config['dbname'];

                $dsn = 'mysql:host='.$host.';dbname='.$dbname.'';
                self::$conn = new PDO($dsn, $username, $password);
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                echo 'Connection failed: ' . $e->getMessage();
            }
        }
        return self::$conn;
    }
}
DatabaseLinker::getConnexion();
?>