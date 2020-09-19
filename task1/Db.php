<?php


class Db
{
    private static $connection;

    /**
     * @throws Exception
     */
    public static function connection()
    {
        if (!isset(Db::$connection)) {
            $configDb = include_once 'config/configDb.php';
            self::connect($configDb);
        }

        return Db::$connection;
    }

    /**
     * @param array $db
     * @return  mysqli
     * @throws Exception
     */
    public static function connect(array $db)
    {
        $host = isset($db['host']) ? $db['host'] : null;
		$username = isset($db['username']) ? $db['username'] : null;
		$pass = isset($db['pass']) ? $db['pass'] : null;
		$dbname = isset($db['dbname']) ? $db['dbname'] : null;
		$port = isset($db['port']) ? $db['port'] : null;

        $mysqli = new mysqli($host, $username, $pass, $dbname, $port);

        if (!$mysqli->connect_error) {
            Db::$connection = $mysqli;
        } else throw new Exception('Error to connect db ' . $mysqli->connect_error);

        return $mysqli;
    }
}