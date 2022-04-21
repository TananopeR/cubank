<?php namespace Operation;

require_once __DIR__."./../commonConstant.php";
require_once __DIR__.'./../serviceauthentication/DBConnection.php';
require_once __DIR__.'./../WithdrawalException.php';

use DBConnection;
use AccountInformationException;
use WithdrawalException;
use Exception;

final class Withdrawal {
    private $accNo;

    public function __construct(string $accNo){
        $this->accNo = $accNo;
    }

    public function withdraw(string $amount) {
        $response['isError'] = false;
        $response['message'] = '';
        try {
            if(!preg_match(REGEX_ALL_NUMBER,$this->accNo)) {
                throw new WithdrawalException('หมายเลขบัญชีต้องเป็นตัวเลขเท่านั้น');
            } elseif(!preg_match('/^[0-9\-\.]*$/',$amount)) {
                throw new WithdrawalException('จำนวนเงินถอนต้องเป็นตัวเลขเท่านั้น');
            } elseif(preg_match(REGEX_NO_DECIMAL,$amount)) {
                throw new WithdrawalException('จำนวนเงินถอนต้องเป็นจำนวนเต็มเท่านั้น');
            } elseif($amount <= 0) {
                throw new WithdrawalException('จำนวนเงินถอนต้องมากกว่า 0 บาท');
            } elseif(strlen($this->accNo) != 10) {
                throw new WithdrawalException('หมายเลขบัญชีต้องมีครบทั้ง 10 หลัก');
            } elseif($amount > 50000) {
                throw new WithdrawalException('ยอดเงินที่ต้องการถอนต้องไม่เกิน 50,000 บาทต่อรายการ');
            } else {
                $response = $this->doWithdraw($this->accNo, $amount);
                $this->saveTransaction($this->accNo, $response["accBalance"]);
            }
        } catch (Exception $e) {
            $response['isError'] = true;
            $response['message'] = $e->getMesage();
        }
        return $response;
    }
    private function doWithdraw($accNo, $amount) {
        $auth = DBConnection::accountInformationProvider($this->accNo);
        if ($auth["accBalance"] - $amount >= 0) {
            return array(
                "accNo" => $accNo,
                "accName" => $auth["accName"],
                "accBalance" => $auth["accBalance"] - $amount,
                "isError" => false
            );
        } else {
            throw new WithdrawalException('ยอดเงินในบัญชีไม่เพียงพอ');
        }
    }
    private function saveTransaction( string $accNo, string $updatedBalance ) : bool {
        try {
            return DBConnection::saveTransaction( $accNo, $updatedBalance );
        } catch (Exception $e) {
            throw new WithdrawalException('ไม่สามารถปรับปรุงยอดเงินได้');
        }
    }
}
