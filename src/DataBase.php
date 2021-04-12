<?php
namespace App;

use Exception;
use InvalidArgumentException;
use PDO;
use PDOException;
use Dotenv\Dotenv;

class DataBase
{
    const CONFIG_FILE = '.database';

    private ?PDO $connection;

    public function __construct()
    {
        try {
            self::initConfig();
            $this->connection = new PDO($_ENV['DSN'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            throw new InvalidArgumentException('Database connection error: ' . $e->getMessage());
        } catch (Exception $e) {
            print '<pre>';
            print_r($e->getMessage());
            die;
        }
    }

    /**
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }


    /**
     * @throws Exception
     */
    private static function initConfig()
    {
        if(!(is_dir(CONFIG_DIR) && file_exists(CONFIG_DIR . DIRECTORY_SEPARATOR . self::CONFIG_FILE))) {
            throw new Exception( 'configuration file >> '.CONFIG_DIR . DIRECTORY_SEPARATOR . self::CONFIG_FILE.' << is not found');
        }
        Dotenv::createImmutable(CONFIG_DIR, self::CONFIG_FILE)->load();
    }
}