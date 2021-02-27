<?php


namespace App\Model;


/**
 * Class MainModel
 * @package App\Model
 */
class MainModel
{

    /**
     * @param $statement
     * @return mixed
     */
    public function read($statement)
    {
        $req = ConnectDB::getPDO()->prepare($statement);
        $req->execute();
        return $req->fetch();
    }

    /**
     * @param $statement
     * @return array
     */
    public function readAll($statement)
    {
        $req = ConnectDB::getPDO()->prepare($statement);
        $req->execute();
        return $req->fetchAll();
    }

    /**
     * @param $statement
     * @return bool|\PDOStatement
     */
    public function exec($statement)
    {
        $req = ConnectDB::getPDO()->prepare($statement);
        $req->execute();
        return $req;
    }

    /**
     * @param $statement
     * @param array $array
     * @return bool|\PDOStatement
     */
    public function execArray($statement, $array = [])
    {
        $req = ConnectDB::getPDO()->prepare($statement);
        $req->execute($array);
        return $req;
    }



}