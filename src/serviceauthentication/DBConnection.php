<?php

include_once 'ServiceType.php';
include_once 'AccountInformationException.php';
include_once 'BillingException.php';

$DB_HOST = getenv('MYSQL_HOST');
$DB_USER = getenv('MYSQL_USER');
$DB_PASS = getenv('MYSQL_PASS');
$DB_NAME = getenv('MYSQL_NAME');

class DBConnection {

    public static function accountInformationProvider(): array {
        $argument = func_get_args();

        if (count($argument) == 1) {
            return DBConnection::serviceAuthentication($argument[0]);
        }
        elseif(count($argument) == 2) {
            return DBConnection::userAuthentication(
                $argument[0],
                $argument[1]
            );
        }
    }

    public static function saveTransaction(string $accNo, int $updatedBalance): bool {
        $con = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
        $stmt = $con->prepare("UPDATE ACCOUNT SET balance = ? WHERE no = ?");
        $stmt->bind_param("is", $updatedBalance, $accNo);
        $result = $con->query($stmt);
        $con->close();
        return $result;
    }

    private static function serviceAuthentication(string $accNo): array {
        $con = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
        $stmt = $con->prepare("SELECT no as accNo, "
            . "name as accName, "
            . "balance as accBalance, "
            . "waterCharge as accWaterCharge, "
            . "electricCharge as accElectricCharge, "
            . "phoneCharge as accPhoneCharge "
            . "FROM ACCOUNT "
            . "WHERE no = ?");
        $stmt->bind_param("s", $accNo);
        $result = $con->query($stmt);
        $con->close();
        if ($result->num_rows == 0) {
            throw new AccountInformationException("Account number : {$accNo} not found.");
        }
        return $result->fetch_array(MYSQLI_ASSOC);
    }

    private static function userAuthentication(string $accNo, string $pin): array {
        $con = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
        $stmt = $con->prepare("SELECT no as accNo, "
            . "name as accName, "
            . "balance as accBalance "
            . "FROM ACCOUNT "
            . "WHERE no = ? AND pin = ?";
        $stmt->bind_param("ss", $accNo, $pin);
        $result = $con->query($stmt);
        $con->close();
 
        if ($result->num_rows == 0) {
            throw new AccountInformationException("Account number or PIN is invalid.");
        }
        return $result->fetch_array(MYSQLI_ASSOC);
    }
}
