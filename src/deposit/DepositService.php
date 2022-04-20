<?php namespace Operation;

use AccountInformationException;
use ServiceAuthentication;
use DBConnection;

require_once __DIR__."./../serviceauthentication/serviceauthentication.php";
require_once __DIR__."./../serviceauthentication/AccountInformationException.php";
require_once __DIR__."./../serviceauthentication/DBConnection.php";
require_once __DIR__."./../commonConstant.php";

class DepositService
{
    private $acctNum;
    public function __construct($acctNum)
    {
        $this->acctNum = $acctNum;
    }

    public function deposit(string $amount):array
    {
        $response = array("isError" => true);
        if(!preg_match(REGEX_ALL_NUMBER,$this->acctNum)){
            $response["message"] = "Account no. must be numeric!";
        }
        elseif(strlen($this->acctNum) != 10){
            $response["message"] = "Account no. must have 10 digit!";
        }
        elseif(!preg_match(REGEX_ALL_NUMBER,$amount)){
            $response["message"] = "Amount must be numeric!";
        }
        elseif((int)$amount <= 0){
            $response["message"] = "จำนวนเงินฝากเข้าระบบต้องมากกว่า 0 บาท";
        }
        elseif((int)$amount > 100000){
            $response["message"] = "จำนวนเงินฝากเข้าระบบต้องไม่เกิน 100,000 บาทต่อครั้ง";
        }
        else{
            try
            {
                $account =  $this->accountAuthenticationProvider($this->acctNum);
                $accNo = $account['accNo'];
                $updatedBalance = $account['accBalance'] + (int)$amount;
                if($this->saveTransaction($accNo, $updatedBalance))
                {
                    $response = array(
                        "isError" => false,
                        "accNo" => $accNo,
                        "accBalance" => $updatedBalance,
                        "accName" => $account['accName'],
                    );       
                }
                else
                {
                    $response["message"] = "Invalid.";
                }
            }
            catch(AccountInformationException $e)
            {
                $response["message"] = $e->getMessage();
            }            
        }

        return $response;
    }   

    public function accountAuthenticationProvider(string $acctNum) : array
    {
        return  ServiceAuthentication::accountAuthenticationProvider($acctNum);
    }

    public function saveTransaction(string $accNo, int $updatedBalance) : bool
    { 
        return  DBConnection::saveTransaction($accNo, $updatedBalance);
    }
}