<?php

require '../vendor/autoload.php';

use MessagePack\Packer;
use MessagePack\Type\Map;

$packer = new Packer();

// mandar un register
//$data = array(
//    "Version" => 1,
//    "OpCode"  => 0x00,
//    "Args"    => ["pablo", "rust_splitd-0.0.1"]
//);
//$res = $packer->pack(new Map($data));
//echo $res;

// mandar un treatment
$data2 = array(
    "Version" => 1,
    "OpCode"  => 0x11,
    "Args"    => ["pablo", "split1", null, null]
);
$res = $packer->pack(new Map($data2));
echo $res;
