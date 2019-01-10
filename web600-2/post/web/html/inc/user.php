<?php
class User {
    public $uid;

    public function __construct($username, $password) {
        $res = DB::query("SELECT uid FROM [user] WHERE username = ? AND password = ?", array($username, $password));
        if (!$res) throw new Exception("Invalid  username / password");
        $this->uid = $res[0]["uid"];
    }

    public static function create($username, $password) {
        DB::insert("INSERT INTO [user] (username, password) VALUES (?, ?)", array($username, $password));
    }
}

