<?php namespace SimpleDnsProxy\Socket;

use SimpleDnsProxy\Socket\Socket;

class UdpSocket extends Socket {
    public function create() {
        $this->internalCreate(AF_INET, SOCK_DGRAM, 0);
    }

    public function read($length) {
        $buffer = null; $ip = null; $port = null;

        $amount = @socket_recvfrom($this->socket, $buffer, $length, 0, $ip, $port);

        if ($amount === false) {
            throw new UdpSocketUnableToReadException($length, $ip, $port);
        }

        $this->trigger('read', [
            'buffer'    => $buffer,
            'amount'    => $amount,
            'ip'        => $ip,
            'port'      => $port
        ]);

        return [
            'ip'        => $ip,
            'port'      => $port,
            'buffer'    => $buffer
        ];
    }

    public function write($buffer, $address, $port) {
        $amount = @socket_sendto($this->socket, $buffer, strlen($buffer), 0, $address, $port);

        if ($amount === false) {
            throw new UdpSocketUnableToWriteException($buffer);
        }

        $this->trigger('write', [
            'amount'    => $amount
            ]);

        return $amount;
    }

    public static function fromResource($socket) {
        $udpSocket = new UdpSocket();
        $udpSocket->socket = $socket;

        return $udpSocket;
    }
}

class UdpSocketException extends SocketException {

}

class UdpSocketUnableToReadException extends UdpSocketException {
    private $length;
    private $address;
    private $port;

    public function __construct($length, $address, $port) {
        parent::__construct();

        $this->length($length);
        $this->address($address);
        $this->port($port);
    }

    public function length($length = null) {
        if (func_num_args() > 0) {
            $this->length = $length;
        }

        return $this->length;
    }

    public function address($address = null) {
        if (func_num_args() > 0) {
            $this->address = $address;
        }

        return $this->address;
    }

    public function port($port = null) {
        if (func_num_args() > 0) {
            $this->port = $port;
        }

        return $this->port;
    }
}

class UdpSocketUnableToWriteException extends UdpSocketException {
    private $buffer;

    public function __construct($buffer) {
        parent::__construct();

        $this->buffer($buffer);
    }

    public function buffer($buffer = null) {
        if (func_num_args() > 0) {
            $this->buffer = $buffer;
        }

        return $this->buffer;
    }
}