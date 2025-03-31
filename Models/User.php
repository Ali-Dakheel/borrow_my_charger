<?php

use Core\Database;

class User{
    protected $db;
    public function __construct(Database $db){
        $this->db = $db;
    }
    public function getAllUsers(){
        return $this->db->query('SELECT * from users;')->findAll();
    }
}
