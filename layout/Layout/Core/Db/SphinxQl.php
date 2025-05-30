<?php
/**
 * Created by PhpStorm.
 * User: serkan
 * Date: 08.09.2016
 * Time: 22:35
 */

namespace Layout\Core\Db;

class SphinxQl
{
    private $_conn;
    private $total;
    private $total_found;
    private $time;
    private $error;

    /**
     * SphinxQl constructor.
     * @param $con
     */
    public function __construct($host, $port)
    {
        $this->total        = 0;
        $this->total_found  = 0;
        $this->time         = 0;
        $this->error        = "";
        try{
            $this->_conn = mysqli_connect($host,'','','',$port);
        }catch (\Exception $e){
            $this->_conn = false;
            $this->error = "Couldn't connect to the Sphinx server on '".$host." : ".$port."' ";
        }
    }

    public function query($sql){
        $this->total        = 0;
        $this->total_found  = 0;
        $this->time         = 0;

        if(!$this->_conn){
            return false;
        }

        $result         = $this->_conn->query($sql);

        $result_meta    = $this->_conn->query("SHOW META");
        while($row_meta = $result_meta->fetch_object()) {
            $name  = $row_meta->Variable_name;
            $value =$row_meta->Value;
            $this->$name = $value;
        }

        return $result;
    }

    public function getTotal(){
        return $this->total;
    }

    public function getTotalFound(){
        return $this->total_found;
    }

    public function getTime(){
        return $this->time;
    }

}