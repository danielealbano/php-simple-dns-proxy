<?php namespace SimpleDnsProxy\Server;

use SimpleDnsProxy\Server\Server;
use SimpleDnsProxy\Server\ServerException;
use SimpleDnsProxy\Socket\UdpSocket;

class UdpServer extends Server {
    protected function createSocket() {
        $udpSocket =  new UdpSocket();
        $udpSocket->create();

        return $udpSocket;
    }

    public function poll($timeout) {
        $this->pollServerSockets($timeout);
    }

    protected function pollServerSockets($timeout) {
        $reads = [ ]; $writes = [ ]; $errors = [ ];
        foreach($this->serverSockets as $serverSocket) {
            $reads[] = $serverSocket->socket(); $errors[] = $serverSocket->socket();
        }
        
        $result = @socket_select($reads, $writes, $errors, 0, $timeout);

        if ($result === false) {
            throw new UdpServerUnableToPollException();
        }

        foreach($reads as $socket) {
            $key = self::keyFromSocketResourceLocalPeer($socket);
            $socket = $this->findServerSocket($socket);

            $this->receiveFromServerSocket($key, $socket);
        }

        foreach($errors as $socket) {
            $key = self::keyFromSocketResourceLocalPeer($socket);
            $socket = $this->findServerSocket($socket);

            $this->handleServerSocketError($socket);
        }
    }

    protected function receiveFromServerSocket($key, $socket) {
        $data = $socket->read(1024);

        $this->trigger('clientData', [
            'data' => $data,
            'socket' => $socket
        ]);
    }

    protected function handleServerSocketError($key, $socket) {
        $this->trigger('serverError', [
            'socket' => $socket
        ]);

        $socket->close();
        unset($this->serverSockets[$key]);
    }
}

class UdpServerException extends ServerException {

}

class UdpServerUnableToPollException extends ServerException {

}