<?php
class Database
{
    private $host = 'localhost';
    private $dbname = 'postgres';
    private $username = 'postgres';
    private $password = 'prueba1234';
    private $connection;

    public function connect()
    {
        $this->connection = null;

        try {
            $this->connection = new PDO(
                "pgsql:host=" . $this->host . ";dbname=" . $this->dbname,
                $this->username,
                $this->password
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }

        return $this->connection;
    }
}
