 <?php
        // $db = new mysqli('localhost', 'root', 'root', 'getflag');
        $t = file_get_contents('php://input');
        // $db->query("INSERT INTO `getflag` (`flag`) VALUES('{$t}')");
        
        echo $t;

        ?>