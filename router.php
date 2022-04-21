<?php

    $requestURI = $_SERVER["REQUEST_URI"];
    $filePath = __DIR__  . $requestURI;

    if(file_exists($filePath) && is_file($filePath)){
      $fileInfo = pathinfo($filePath);
      if ($fileInfo["extension"] != "html"){
        return false;
      }
    }
    $session = isset($_COOKIE["authentication"])?true:false;
    if($session){
      if($requestURI == '/deposit') {
      	include 'view/deposit.html';
      }
      elseif($requestURI == '/pay') {
      	include 'view/pay.html';
      }
      elseif($requestURI == '/pay/electric') {
        include 'view/pay/electric.html';
      }
      elseif($requestURI == '/pay/phone') {
        include 'view/pay/phone.html';
      }
      elseif($requestURI == '/pay/water') {
        include 'view/pay/water.html';
      }
      elseif($requestURI == '/transfer') {
      	include 'view/transfer.html';
      }
      elseif($requestURI == '/withdrawal') {
      	include 'view/withdrawal.html';
      }

      else{
        if($requestURI != "/main"){
          header("Location: /main");
        }
        include 'view/main.html';
      }
    }
    else {
      if($requestURI != "/"){
        header("Location: /");
      }
      include 'view/login.html';
    }

?>
