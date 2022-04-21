<?php namespace Operation;

use AccountInformationException;
use ServiceAuthentication;
use DBConnection;
use Error;

require_once __DIR__.'./../serviceauthentication/serviceauthentication.php';
require_once __DIR__.'./../serviceauthentication/AccountInformationException.php';
require_once __DIR__.'./../serviceauthentication/DBConnection.php';

class BillPayment {

    private $accNo = '';

    public function __construct( string $accNo ) {
        $this->accNo = $accNo;
    }

    public function getAccountDetail( string $accNo ) {
        if ( strlen( $accNo ) != 10 ) throw new Error('Invalid Account No');
        return ServiceAuthentication::accountAuthenticationProvider( $accNo );
    }

    public function saveTransaction( string $accNo, string $updatedBalance ) : bool {
        return DBConnection::saveTransaction( $accNo, $updatedBalance );
    }

    public function saveChargeTransaction( string $accNo, string $bill_type ) : bool {
        if ( $bill_type == 'waterCharge' ) {
            return DBConnection::saveTransactionWaterCharge( $accNo, 0 );
        } else  if ( $bill_type == 'electricCharge' ) {
            return DBConnection::saveTransactionElectricCharge( $accNo, 0 );
        } else {
            return DBConnection::saveTransactionPhoneCharge( $accNo, 0 );
        }
    }

    public function getBill() {

        try {
            $dataAccount = $this->getAccountDetail( $this->accNo );

            if($dataAccount == 'ERROR'){
                $response['message'] = 'Invalid Account No';
                $response['isError'] = true ;
              return $response;
            }
            $response = $dataAccount;
            $response['message'] = '';
            $response['isError'] = false;
        } catch( Error $e ) {
            $response['message'] = 'Cannot get bill';
            $response['isError'] = true;
        }

        return $response;

    }

    public function pay( string $bill_type ) {
        try {
            if ( $bill_type == null || $bill_type == '' ) throw new Error('Invalid bill type');
            
            $arrayAccount = $this->getAccountDetail( $this->accNo );

            $accChargeType = 'accPhoneCharge';
            if ( $bill_type == 'waterCharge' ) $accChargeType = 'accWaterCharge';
            else if ( $bill_type == 'electricCharge' ) $accChargeType = 'accElectricCharge';

            if ( $arrayAccount['accBalance'] < $arrayAccount[$accChargeType] ) throw new Error('ยอดเงินในบัญชีไม่เพียงพอ');
            else {
                $updatedBalance = $arrayAccount['accBalance'] - $arrayAccount[$accChargeType];
                $this->saveTransaction( $this->accNo, $updatedBalance );
                $response = $this->getAccountDetail( $this->accNo );
                $response['isError'] = false;
                $response['message'] = '';
            }

        } catch (Error $e) {
            $response['isError'] = true;
            $response['message'] = $e->getMessage();
        } finally {
            return $response
        }
    }
}