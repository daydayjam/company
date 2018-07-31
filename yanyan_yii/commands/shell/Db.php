<?php

class Db {
    static private $_instance;
    private $_dbConfig = [
      'host'=>'127.0.0.1',
      'user'=>'root',
      'password'=>'',
      'databases'=>'yanyan'
    ];
    static private $_connectSource;
    private function __construct() {}

    static public function getInstance() {
      if(!(self::$_instance instanceof self)) {
        self::$_instance = new self();
      }
      return self::$_instance;
    }
    
    public function connect() {
      if(!self::$_connectSource) {
        @self::$_connectSource = new mysqli($this->_dbConfig['host'], $this->_dbConfig['user'], $this->_dbConfig['password'], $this->_dbConfig['databases']);
        if(self::$_connectSource->connect_error) {
          throw new Exception('mysql connect error' . self::$_connectSource->connect_error);
        }
        self::$_connectSource->set_charset('utf8');
      }
      return self::$_connectSource;
    }


}

