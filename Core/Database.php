<?php

namespace Core;

use PDO;
class Database {
    public $connection;
    public $statement;
    public function __construct($config = null, $username = null, $password = null) {
        if ($config === null) {
            $config = [
                'host'    => DB_HOST,
                'dbname'  => DB_NAME,
                'charset' => 'utf8mb4'
            ];
        }
        if ($username === null) {
            $username = DB_USER;
        }
        if ($password === null) {
            $password = DB_PASSWORD;
        }
       $dsn = 'mysql:'.http_build_query($config,'', ';');

        $this->connection = new PDO($dsn,$username,$password,[
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
    public function query($query, $params=[]){

        $this->statement =$this->connection->prepare($query);
        $this->statement->execute($params); 

        return $this;
    }
    public function find(){
            return $this->statement->fetch();
    }
    public function findAll(){
        return $this->statement->fetchAll();
    }

}
