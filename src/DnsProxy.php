<?php namespace SimpleDnsProxy;

use SimpleDnsProxy\Server\UdpServer;
use SimpleDnsProxy\Rfcs\Rfc1035\Packet as Rfc1035Packet;

use Exception;

class DnsProxy {
    private $config;
    private $udpServer;

    public function __construct($config) {
        if ($config == null) {
            throw new DnsProxyNullConfigException();
        }

        $bindings = isset($config['bindings'])
            ? $config['bindings']
            : [ [ 'ip' => '0.0.0.0', 'port' => 53 ] ];

        $udpServer = new UdpServer([
            'bindings'  => $bindings,
            'backlog'   => 10,
            'async'     => false
        ]);
        
        $udpServer->on('clientData', function($event) {
            $this->onClientData($event);
        });

        $udpServer->on('clientError', function($event) {
            $this->onClientError($event);
        });

        $this->udpServer = $udpServer;
    }

    public function start() {
        $this->udpServer->start();
    }

    public function stop() {
        $this->udpServer->stop();
    }

    public function poll($timeout) {
        $this->udpServer->poll($timeout);
    }

    private function onClientData($event) {
        $data = $event->data();
        $socket = $data['socket'];
        $data = $data['data'];

        $rfc1035 = Rfc1035Packet::parse($data['buffer']);

        echo "DNS REQUEST FROM " . $data['ip'] . "\n";
        var_dump($rfc1035);
    }

    private function onClientError($event) {
        echo "ERROR!!!";
        var_dump($event);
    }
}

class DnsProxyException extends Exception {

}

class DnsProxyNullConfigException extends Exception {

}