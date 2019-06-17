<?php

namespace app\models;

use loader\db\Model;
use loader\db\Db;


class Log extends Model
{

    protected $table = 'log';


    public function search($keyword)
    {

        $sql = "select * from `$this->table` where `item_name` like :keyword";
        $sth = Db::pdo()->prepare($sql);
        $sth = $this->formatParam($sth, [':keyword' => "%$keyword%"]);
        $sth->execute();
        return $sth->fetchAll();
    }
}
