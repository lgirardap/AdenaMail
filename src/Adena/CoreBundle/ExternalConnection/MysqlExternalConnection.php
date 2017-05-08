<?php
/**
 * Created by PhpStorm.
 * User: Girard Lionel
 * Date: 5/2/2017
 * Time: 3:54 PM
 */

namespace Adena\CoreBundle\ExternalConnection;

use mysqli;

class MysqlExternalConnection
{
    /** @var  \mysqli */
    private $conn;
    private $isConnected = false;
    private $connectErrors = '';

    public function connect(array $params){
        if($this->isConnected){
            return false;
        }

         $this->conn = new mysqli(
            $params['servername'],
            $params['username'],
            $params['password'],
            $params['database'],
            $params['port'] ?? null,
            $params['socket'] ?? null
        );

        if( $this->conn->connect_error ){
            $this->connectErrors = $this->conn->connect_error;
            throw new \Exception( $this->conn->connect_error );
        }

        $this->isConnected = true;

        return true;
    }

    public function ping(array $params = null){
        if($this->isConnected){
            return true;
        }

        try {
            $this->connect($params);
        }catch(\Exception $e){
            $this->connectErrors = $e->getMessage();
            return false;
        }

        return true;
    }

    public function executeQuery($query, array $params = null){
        if(!$this->isConnected){
            $this->connect($params);
        }

        $result   = $this->conn->query($query);

        if (!$result) {
            throw new \Exception( $this->conn->error );
        }

        return $this->_resultToArray($result);
    }

    public function close(){
        $this->conn->close();
        $this->conn = null;
        $this->isConnected = false;
    }

    public function getConnectErrors(){
        return $this->connectErrors;
    }

    private function _resultToArray(\mysqli_result $result){
        $results = array();
        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
        }
        return $results;
    }
}