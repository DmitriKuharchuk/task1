<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 17.06.2019
 * Time: 18:01
 */

namespace app\controllers;


use App\RequestHandler\generateRequest;
use loader\base\controller;

class RequestController extends  Controller
{

    public function execute(){
        generateRequest::handle();
    }



}