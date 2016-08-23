<?php

// http://drops.wooDyun.org/tips/4483




if (isset($_GET['a'])) {  
    if (strcmp($_GET['a'], $flag) == 0)  
        die('Flag: '.$flag);  
    else  
        print '离成功更近一步了';  
}



?>



