<?php namespace SimpleDnsProxy\Server;

use SimpleDnsProxy\Server\Server;
use SimpleDnsProxy\Server\ServerException;
use SimpleDnsProxy\Socket\UdpSocket;

class TcpServer extends Server {
    protected function createSocket() {
        $udpSocket =  new UdpSocket();
        $udpSocket->create();

        return $udpSocket;
    }

    public function poll($timeout) {
        $this->pollServerSockets($timeout);
        $this->pollClientsSocket($timeout);
    }

    protected function createServerSockets() {
        parent::createServerSockets();

        $this->listenOnServerSockets();
    }

    protected function listenOnServerSockets() {
        foreach($this->serverSockets as $serverSocket) {
            $serverSocket->listen($this->backlog);
        }
    }

    protected function pollServerSockets($timeout) {
        $reads = [ ]; $writes = [ ]; $errors = [ ];
        foreach($this->serverSockets as $serverSocket) {
            $reads[] = $serverSocket->socket(); $errors[] = $serverSocket->socket();
        }
        
        $result = @socket_select($reads, $writes, $errors, 0, $timeout);

        if ($result === false) {
            throw new TcpServerUnableToPollException();
        }

        if ($result == 0) {
            return;
        }

        foreach($reads as $socket) {
            $key = Socket::keyFromSocketResource($socket);
            $serverSocket = $this->findServerSocket($socket);
            $this->acceptIncomingConnection($serverSocket);
        }

        foreach($errors as $socket) {
            $key = Socket::keyFromSocketResource($socket);
            $serverSocket = $this->findServerSocket($socket);

            $this->trigger('serverError', [
                'serverSocket' => $serverSocket
            ]);

            $serverSocket->close();
            unset($this->serverSockets[$key]);
        }
    }

    protected function pollClientsSocket($timeout) {
        throw new Exception('TODO');
    }

    protected function acceptIncomingConnection($serverSocket) {
        $clientSocket = $serverSocket->accept();

        $clientSocket->on('close', function($event) {
            $this->clientSocketOnClose($event);
        });

        $clientSocket->on('error', function($event) {
            $this->clientSocketOnError($event);
        });

        $key = $clientSocket->key();

        $this->clientsSocket[$key] = $clientSocket;

        $this->trigger('newClient', [
            'socket'        => $clientSocket,
            'localPeer'     => $clientSocket->localPeer(),
            'remotePeer'    => $clientSocket->remotePeer() ]);
    }
}

class TcpServerException extends ServerException {

}

class TcpServerUnableToPollException extends ServerException {

}