<?php
class DatabaseLinker {
    private static $conn;

    public static function getConnexion() {
        if (self::$conn === null) {
            try {
                
                $username = 'root';
                $password = '';
                $host = 'localhost';
                $dbname = 'stageathon';

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