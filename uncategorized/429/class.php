<?php

highlight_string(file_get_contents(basename($_SERVER['PHP_SELF'])));
//show_source(__FILE__);

class foo1{
        public $varr;
        function __construct(){
                $this->varr = "index.php";
                // var_dump(file_exists("index.php"));
                
        }
        function __destruct(){
           
                if(file_exists($this->varr)){
                        echo "<br>文件".$this->varr."存在<br>";
                }
                                  
                echo "<br>这是foo1的析构函数<br>";
        }
}

class foo2{
        public $varr;
        public $obj;
        function __construct(){
                $this->varr = '1234567890';
                $this->obj = null;
        }
        
        function __toString(){
                echo "I am runing!!";              
                $this->obj->execute();
                return $this->varr;
                       
        }
        function __desctuct(){
                echo "<br>这是foo2的析构函数<br>";
        }
}


class foo3{
        public $varr;
        function execute(){
                eval($this->varr);
        }
        function __desctuct(){
                echo "<br>这是foo3的析构函数<br>";
        }
}

?>



