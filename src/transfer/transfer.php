<?php namespace Operation;

require_once __DIR__."./../../src/deposit/DepositService.php";
require_once __DIR__."./../../src/withdraw/Withdrawal.php";
require_once __DIR__."./../serviceauthentication/serviceauthentication.php";
require_once __DIR__."./../serviceauthentication/AccountInformationException.php";
require_once __DIR__."./../serviceauthentication/DBConnection.php";
require_once __DIR__."./../commonConstant.php";

use DBConnection;
use ServiceAuthentication;
use Operation\DepositService;
use Operation\Withdrawal;
use AccountInformationException;

class Transfer{
    private $srcNumber,$srcName;

    public function __construct(string $srcNumber,string $srcName){
        $this->srcNumber = $srcNumber;
        $this->srcName = $srcName;
    }

    public function doTransfer(string $targetNumber, string $amount){
        $response["accBalance"] = 0;
        $response["isError"] = true;
        $response["message"] = transferVerification($targetNumber, $amount);

        if($response["message"] ==null){
            try{
                $srcAccount = $this->accountAuthenticationProvider($this->srcNumber);
                $desAccount = $this->accountAuthenticationProvider($targetNumber);

                if ($srcAccount['accBalance'] - (int)$amount < 0) {
                    $response["message"] = "คุณมียอดเงินในบัญชีไม่เพียงพอ";
                }else{
                    $withdrawResult = $this->withdraw($srcAccount['accNo'], $amount);
                    $depositResult = $this->deposit($desAccount['accNo'], $amount);
        
                    if ($depositResult['isError'] || $withdrawResult['isError']) {
                        $response['message'] = "ดำเนินการไม่สำเร็จ";
                    } else {
                        $response['isError'] = false;
                        $response['accBalance'] = $withdrawResult['accBalance'];
                        $response['message'] = "";
                    }
                }   
            } catch(AccountInformationException $e){
                $response["message"] = $e->getMessage();
            }
        }

        return $response;
    }

    private function transferVerification(string $targetNumber, string $amount){
        $message=null;
        if (!preg_match(REGEX_ALL_NUMBER,$this->srcNumber) || !preg_match(REGEX_ALL_NUMBER,$targetNumber)) {
            $message = "หมายเลขบัญชีต้องเป็นตัวเลขเท่านั้น";
        } elseif (!preg_match(REGEX_ALL_NUMBER,$amount)) {
            $message = "จำนวนเงินต้องเป็นตัวเลขเท่านั้น";
        } elseif (strlen($this->srcNumber) != 10 || strlen($targetNumber) != 10) {
            $message = "หมายเลขบัญชีต้องมีจำนวน 10 หลัก";
        } elseif ((int)$amount <=0) {
            $message = "ยอดการโอนต้องมากกว่า 0 บาท";
        } elseif ((int)$amount > 9999999) {
            $message = "ยอดการโอนต้องไม่มากกว่า 9,999,999 บาท";
        } elseif ($this->srcNumber == $targetNumber) {
            $message = "ไม่สามารถโอนไปบัญชีตัวเองได้";
        }
        return $message;
    }

    public function accountAuthenticationProvider(string $acctNum) : array
    {
        return  ServiceAuthentication::accountAuthenticationProvider($acctNum);
    }

    public function withdraw(string $acctNum, string $amount) : array
    {
        $service = new Withdrawal($acctNum);
        return $service->withdraw($amount);
    }

    public function deposit(string $acctNum, string $amount) : array
    {
        $service = new DepositService($acctNum);
        return $service->deposit($amount);
    }
}