<?php

namespace App\RequestHandler\Response;


use app\models\Log;


use loader\db\Db;
use PDO;


class ActionResponse
{


    protected $config = [];



    private $ip;


    public function __construct()
    {
        $this->ip = $this->getUserIP();
    }


    function getUserIP()
    {
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];
        if(filter_var($client, FILTER_VALIDATE_IP))
        {
            $ip = $client;
        }
        elseif(filter_var($forward, FILTER_VALIDATE_IP))
        {
            $ip = $forward;
        }
        else
        {
            $ip = $remote;
        }
        return $ip;
    }


    public function checkRequest()
    {

        if ((count($this->getListRequest()) > 4) && $this->getBannedIp() == 0) {
            $this->banIp();
            $this->badRequest();
        }
        elseif ($this->getBannedIp() == 1){
            $this->badRequest();
        }
        elseif($this->getBannedIp() == 0) {
            $this->goodRequest();
        }

    }

    private function goodRequest()
    {
        $this->insertLog();
        $this->getRequest('200','Hello world');

    }


    private function badRequest()
    {
        $this->getRequest('400','Time Out');
    }


    private function getListRequest()
    {
        $startTime = time() - 60;
        $sql = "select * from log WHERE ip = (:ip) AND `time` > $startTime";
        $stmt = Db::pdo()->prepare($sql);

        $stmt->execute(array(':ip'=>$this->ip));

        return $stmt->fetchAll();


    }


    private function getBannedIp()
    {

        $sql = "select * from ban WHERE ip = (:ip) AND `startBan` <=(:currentTime) AND `endBan` >=(:currentTime) ";
        $stmt = Db::pdo()->prepare($sql);
        $stmt->execute(array(':ip'=>$this->ip , ':currentTime' => time()));
        $stmt->fetchColumn();

        return count($stmt->fetchAll());
    }

    private function banIp()
    {
        if ($this->getBannedIp() == 0){
            $sql = "INSERT INTO ban (ip, startBan, endBan) VALUES ((:ip),(:startBan),(:endBan))";
            $stmt = Db::pdo()->prepare($sql);
            $stmt->execute(array(':ip'=>$this->ip , ':startBan' => time(), ':endBan' => time()+600));
        }
    }



    private function insertLog()
    {

        $sql = "INSERT INTO log (ip, `time` ) VALUES ((:ip),(:timeLog))";
        $stmt = Db::pdo()->prepare($sql);
        $stmt->execute(array(':ip'=>$this->ip , ':timeLog' => time()));
    }


    private function getTimeTimeout(){
        $time = 0;
        $endTimeOut = "select endBan from ban WHERE ip = (:ip) AND `startBan` <=(:currentTime) AND `endBan` >=(:currentTime) ";
        $stmt = Db::pdo()->prepare($endTimeOut);
        $stmt->execute(array(':ip'=>$this->ip , ':currentTime' => time()));

        $timeOut  = $stmt->fetchAll();



        if (isset($timeOut[0]['endBan']))
        {
            $time = intval($timeOut[0]['endBan']) - time();
        }
        return $time;
    }


    public function getRequest ($status,$message)
    {
        echo ($message);
        $response_length = ob_get_length();
        if (is_callable('fastcgi_finish_request'))
        {
            session_write_close();
            fastcgi_finish_request();

            return;
        }
        ignore_user_abort(true);
        ob_start();
        if ($this->getTimeTimeout() == null )
        {
            header('HTTP/1.1 200 OK');
        }
        else{
            header('HTTP/1.1 400 Time Out'.$this->getTimeTimeout().' second');
        }
        header('Content-Encoding: none');
        header('Content-Length: ' . $response_length);
        ob_end_flush();
        ob_flush();
        flush();

    }


}