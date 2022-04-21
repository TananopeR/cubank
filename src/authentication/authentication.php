<?php namespace Operation;

require_once __DIR__.'./../serviceauthentication/DBConnection.php';

use DBConnection;
use AccountInformationException;
use Exception;
use Error;

class Authentication{
    private $acctNum,$pin;

    public function __construct(string $acctNum,string $pin){
        $this->acctNum = $acctNum;
        $this->pin = $pin;
    }

    public function login(){      
        $response = array("isError" => true);
        if(!preg_match('/^\d*$/',$this->acctNum) || !preg_match('/^\d*$/',$this->pin)){
          $response["message"] = "Account no. and PIN must be numeric!";
        }
        elseif(strlen($this->acctNum) != 10){
          $response["message"] = "Account no. must have 10 digit!";
        }
        elseif(strlen($this->pin) != 4){
          $response["message"] = "PIN must have 4 digit!";
        }
        else{
          try{
            DBConnection::accountInformationProvider($this->acctNum,$this->pin);
            $response["isError"] = false;
          }
          catch(AccountInformationException $e){
            $response["message"] = $e->getMessage();
          }
          catch(Exception $e){
            $response["message"] = "Unknown error occurs in Authentication";
          }
          catch(Error $e){
            $response["message"] = "Unknown error occurs in Authentication";
          }
        }
        return $response;
    }

}
