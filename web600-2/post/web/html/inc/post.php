<?php
class Attachment {
    private $url = NULL;
    private $za = NULL;
    private $mime = NULL;

    public function __construct($url) {
        $this->url = $url;
        $this->mime = (new finfo)->file("../".$url);
        if (substr($this->mime, 0, 11) == "Zip archive") {
            $this->mime = "Zip archive";
            $this->za = new ZipArchive;
        }
    }

    public function __toString() {
        $str = "<a href='{$this->url}'>".basename($this->url)."</a> ($this->mime ";
        if (!is_null($this->za)) {
            $this->za->open("../".$this->url);
            $str .= "with ".$this->za->numFiles . " Files.";
        }
        return $str. ")";
    }

}

class Post {
    private $title = NULL;
    private $content = NULL;
    private $attachment = NULL;
    private $ref = NULL;
    private $id = NULL;


    public function __construct($title, $content, $attachments="") {
        $this->title = $title;
        $this->content = $content;
        $this->attachment = $attachments;
    }

    public function save() {
        global $USER;
        if (is_null($this->id)) {
            DB::insert("INSERT INTO posts (userid, title, content, attachment) VALUES (?,?,?,?)", 
                array($USER->uid, $this->title, $this->content, $this->attachment));
        } else {
            DB::query("UPDATE posts SET title = ?, content = ?, attachment = ? WHERE userid = ? AND id = ?",
                array($this->title, $this->content, $this->attachment, $USER->uid, $this->id));
        }
    }

    public static function truncate() {
        global $USER;
        DB::query("DELETE FROM posts WHERE userid = ?", array($USER->uid));
    }

    public static function load($id) {
        global $USER;
        $res = DB::query("SELECT * FROM posts WHERE userid = ? AND id = ?",
            array($USER->uid, $id));
        if (!$res) die("db error");
        $res = $res[0];
        $post = new Post($res["title"], $res["content"], $res["attachment"]);
        $post->id = $id;
        return $post;
    }

    public static function loadall() {
        global $USER;
        $result = array();
        $posts = DB::query("SELECT id FROM posts WHERE userid = ? ORDER BY id DESC", array($USER->uid)) ;
        if (!$posts) return $result;
        foreach ($posts as $p) {
            $result[] = Post::load($p["id"]);
        }
        return $result;
    }

    public function __toString() {
        $str = "<h2>{$this->title}</h2>";
        $str .= $this->content;
        $str .= "<hr>Attachments:<br><il>";
        foreach ($this->attachment as $attach) {
            $str .= "<li>$attach</li>";
        }
        $str .= "</il>";
        return $str;
    }
}
