<?php
// عدّل بيانات قاعدة البيانات من cPanel > MySQL Databases
const DB_HOST='localhost',DB_NAME='cpanelUser_elite',DB_USER='cpanelUser_elite',DB_PASS='CHANGE_ME';
ini_set('display_errors',0);
function db(){static $p;return $p??=new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);}
