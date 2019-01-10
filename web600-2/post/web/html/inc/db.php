<?php

class DB {
    private static $con;
    private static $init = false;

    private static function initialize() {
        DB::$con = sqlsrv_connect("db", array("pwd"=> "Foobar1!", "uid"=>"challenger", "Database"=>"challenge"));
        if (!DB::$con) DB::error();

        DB::$init = true;
    }

    private static function error() {
        die("db error");
    }

    private static function prepare_params($params) {
        return array_map(function($x){
            if (is_object($x) or is_array($x)) {
                return '$serializedobject$' . serialize($x);
            }

            if (preg_match('/^\$serializedobject\$/i', $x)) {
                die("invalid data");
                return "";
            }

            return $x;
        }, $params);
    }

    private static function retrieve_values($res) {
        $result = array();
        while ($row = sqlsrv_fetch_array($res)) {
            $result[] = array_map(function($x){
                return preg_match('/^\$serializedobject\$/i', $x) ?
                    unserialize(substr($x, 18)) : $x;
            }, $row);
        }
        return $result;
    }

    public static function query($sql, $values=array()) {
        if (!is_array($values)) $values = array($values);
        if (!DB::$init) DB::initialize();


        $res = sqlsrv_query(DB::$con, $sql, $values);
        if ($res === false) DB::error();

        return DB::retrieve_values($res);
    }

    public static function insert($sql, $values=array()) {
        if (!is_array($values)) $values = array($values);
        if (!DB::$init) DB::initialize();

        $values = DB::prepare_params($values);

        $x = sqlsrv_query(DB::$con, $sql, $values);
        if (!$x) throw new Exception;
    }
}
