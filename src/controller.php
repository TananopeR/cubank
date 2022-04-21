<?php

define("PHP_ENV",false);
ini_set("display_errors",PHP_ENV?"ON":"Off");

require_once "authentication/authentication.php";
require_once "withdraw/Withdrawal.php";
require_once "deposit/DepositService.php";
require_once "transfer/transfer.php";
require_once "billpayment/billpayment.php";
require_once "serviceauthentication/serviceauthentication.php";

use Operation\Authentication;
use Operation\DepositService;
use Operation\Withdrawal;
use Operation\Transfer;
use Operation\BillPayment;

$logFile = "../errorlog.txt";
$service = $_POST["service"];
$session = isset($_COOKIE["authentication"])?$_COOKIE["authentication"]:null;

try{
  if ($service == "Authentication"){
    $transaction = $_POST["transaction"];
    $auth = new Authentication($transaction["acct_num"],$transaction["pin"]);
    echo json_encode($auth->login());
  }
  elseif($session)
  {
      if ($service == "Deposit"){
        $transaction = $_POST["transaction"];
        $deposit = new DepositService($session);
        echo json_encode($deposit->deposit($transaction["amount"]));
      }
      elseif ($service == "Withdraw"){
        $transaction = $_POST["transaction"];
        $withdrawal = new Withdrawal($session);
        echo json_encode($withdrawal->withdraw($transaction["amount"]));
      }
      elseif ($service == "Transfer"){
        $transaction = $_POST["transaction"];
        $transfer = new Transfer($transaction["srcNumber"],$transaction["srcName"]);
        echo json_encode($transfer->doTransfer($transaction["targetNumber"],$transaction["amount"]));
      }
      elseif ($service == "BillPayment"){
        $transaction = $_POST["transaction"];
        $billPayment = new BillPayment($session);
        echo json_encode($billPayment->pay($transaction["bill_type"]));
      }
      elseif ($service == "BillPaymentInq"){
        $transaction = $_POST["transaction"];
        $billPayment = new BillPayment($session);
        echo json_encode($billPayment->getBill($transaction["bill_type"]));
      }
      elseif ($service == "ServiceAuthentication"){
        $result["isError"] = true;
        try{
          $result = ServiceAuthentication::accountAuthenticationProvider($session);
          $result["isError"] = false;
        }
        catch(AccountInformationException $e){
          $result["message"] = $e->getMessage();
        }
        echo json_encode($result);
      }
      else{
        http_response_code(501);
        return;
      }
  }
  else{
    http_response_code(401);
    return;
  }

}catch(Error $e){
  date_default_timezone_set('Asia/Bangkok');
  $file = fopen($logFile,"a+");
  fwrite($file,"Log Time: ".date("d-m-Y H:i:sa") . "\n");
  fwrite($file,$e."\n\n");
  http_response_code(400);
  return;
}
