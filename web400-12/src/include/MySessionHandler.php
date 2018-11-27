<?php
class MySessionHandler implements SessionHandlerInterface{
   private $savepath;
   private $dbsession ;
   
   public  function open($savepath,$sessionName){
       $this->dbsession = New DbSession();
       $this->gc(ini_get('session.gc_maxlifetime')); 
       return true;
   }
   public function close(){
       return true;
   }
   
   public function read($session_id){
      $res = $this->dbsession->query("SELECT * FROM `{$this->dbsession->table_name}` where `sessionid` = '{$session_id}'");
      if(empty($res)){
          return false;
      }else{
          return (string)@$res[0]['data'];
      }
   }
   public function write($session_id,$data){

      $time = time();
      $res = $this->dbsession->query("SELECT * FROM `{$this->dbsession->table_name}` where `sessionid` = '{$session_id}' ");

      if($res){
        $this->dbsession->execute("UPDATE `{$this->dbsession->table_name}` SET `data` = '{$data}',`lastvisit` = '{$time}' where `sessionid` = '{$session_id}'");
      }else{
        
        $res = $this->dbsession->create(
            ["data"=>$data,
            "sessionid"=>$session_id,
            "lastvisit"=>$time]);
      }
      return true;
   } 
   
   public function destroy($session_id){
        $res  = $this->dbsession->execute("DELETE FROM `{$this->dbsession->table_name}` where `sessionid`='{$session_id}'");   
        return $res;
    }

    public function gc($maxlifetime){
      $timeNow = time();
      $res = $this->dbsession->execute("DELETE FROM `{$this->dbsession->table_name}` where ($maxlifetime+`lastvisit`)<$timeNow");
      if($res) 
          return true;
    return false; 
    }
    
}