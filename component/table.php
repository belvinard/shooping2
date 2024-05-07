<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $host = "localhost";
    $login = "root";
    $pass = "";
    $dbname = "class_shop";
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try{
        $conn = new PDO($dsn, $login, $pass, $options);
        $requestTable = "CREATE TABLE `admins` (
            id int(10) UNSIGNED NOT NULL,
            name varchar(255) NOT NULL,
            email Varchar(255) NOT NULL,
            password varchar(255) NOT NULL,
            token varchar(255) NOT NULL
        )";

        $conn->exec($requestTable);

        echo "table admins updated succesfully";
    }catch(\PDOException $e){

        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    
    }
    
?>
