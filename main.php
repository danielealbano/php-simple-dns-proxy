<?php

require_once('src/Config.php');
require_once('src/EventBus/Event.php');
require_once('src/EventBus/EventBus.php');
require_once('src/Socket/Socket.php');
require_once('src/Socket/UdpSocket.php');
require_once('src/Server/Server.php');
require_once('src/Server/UdpServer.php');
require_once('src/Rfcs/Rfc1035/Header.php');
require_once('src/Rfcs/Rfc1035/Question.php');
require_once('src/Rfcs/Rfc1035/Packet.php');
require_once('src/Rfcs/Rfc2136/Packet.php');
require_once('src/DnsProxy.php');

set_exception_handler(function($e) {
    echo "UNHANDLED EXCEPTION\n";
    echo "---";
    var_dump($e);

    die();
});

$config = new \SimpleDnsProxy\Config(__DIR__ . '/config.json');
$dnsProxy = new \SimpleDnsProxy\DnsProxy($config);
$dnsProxy->start();

do {
    $dnsProxy->poll(250000);
} while(true);
