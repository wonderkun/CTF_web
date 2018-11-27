<?php 

class Session{
   private $ip ; 
   private $userAgent; 
   private $userId;
   private $loginTime ; 
   public static $timeFormat = "H:i:s";
   function __construct($userId,$loginTime,$ip="0.0.0.0",$userAgent=""){
       $this->userId = $userId;
       $this->ip = $ip;
       $this->loginTime = $loginTime;
       $this->userAgent = $userAgent;
   }
   public function getUserInfo(){
       return array($this->userId,date(self::$timeFormat,$this->loginTime));
   }
   public function  isAccountSec($ip="0.0.0.0",$userAgent=""){
       return ($this->ip === $ip && $this->userAgent === $userAgent);
   }
   
   static function  getTime($timestamp){
       return date(self::$timeFormat,$timestamp);
   }
}
