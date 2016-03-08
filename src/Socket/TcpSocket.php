<?php namespace SimpleDnsProxy\Socket;

use SimpleDnsProxy\Socket\Socket;

class TcpSocket extends Socket {
    private $eof;
    protected $clientsSocket = [ ];

    public function create() {
        $this->internalCreate(AF_INET, SOCK_STREAM, 0);
    }

    public function connect($address, $port = null) {
        if (@socket_connect($this->socket, $address, $port) == false) {
            throw new SocketUnableToConnectException($address, $port);
        }

        $this->trigger('connect', [
            'address'   => $address,
            'port'      => $port
        ]);

        return $this;
    }

    public function shutdown($type) {
        @socket_shutdown($this->socket, $type);

        $this->trigger('shutdown', [
            'type'  => $type
        ]);
    }

    public function read($length) {
        $buffer = null;

        $amount = @socket_recv($this->socket, $buffer, $length, 0);

        if ($amount === false) {
            throw new TcpSocketUnableToReadException($length);
        }

        if ($amount == 0) {
            $this->eof = true;

            $this->trigger('eof');

            return false;
        }

        $this->trigger('read', [
            'buffer'    => $buffer,
            'amount'    => $amount
        ]);

        return $buffer;
    }

    public function write($buffer) {
        $amount = @socket_send($this->socket, $buffer, strlen($buffer), 0);

        if ($amount === false) {
            throw new TcpSocketUnableToWriteException($buffer);
        }

        $this->trigger('write', [
            'amount'    => $amount
        ]);

        return $amount;
    }

    public function eof() {
        return $this->eof;
    }

    public static function fromResource($socket) {
        $tcpSocket = new TcpSocket();
        $tcpSocket->socket = $socket;

        return $tcpSocket;
    }

    protected function clientSocketOnError($event) {
        $clientSocket = $event['socket'];
        $key = $clientSocket->key();

        unset($this->clientsSocket[$key]);

        $this->trigger('clientError', [
            'clientSocket'  => $clientSocket ]);
    }

    protected function clientSocketOnClose($event) {
        $clientSocket = $event['socket'];
        $key = $clientSocket->key();

        unset($this->clientsSocket[$key]);

        $this->trigger('clientClose', [
            'clientSocket'  => $clientSocket ]);
    }
}

class TcpSocketException extends SocketException {

}

class TcpSocketUnableToReadException extends TcpSocketException {
    private $length;

    public function __construct($length) {
        parent::__construct();

        $this->length($length);
    }

    public function length($length = null) {
        if (func_num_args() > 0) {
            $this->length = $length;
        }

        return $this->length;
    }
}

class TcpSocketUnableToWriteException extends TcpSocketException {
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