<?php
namespace App\RequestHandler;

use App\RequestHandler\handleInterface;





use App\RequestHandler\Response\ActionResponse;



abstract class generateRequest implements handleInterface
{
    public static function handle()
    {

        $actionResponse = new ActionResponse();
        $actionResponse->checkRequest();
    }



}