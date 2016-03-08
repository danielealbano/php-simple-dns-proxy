<?php namespace SimpleDnsProxy\Server;

use SimpleDnsProxy\EventBus\EventBus;

use SimpleDnsProxy\Socket\SocketException;
use SimpleDnsProxy\Socket\SocketUnabletToGetLocalPeerException;
use SimpleDnsProxy\Socket\SocketUnabletToGetRemotePeerException;

use Exception;

abstract class Server extends EventBus {
    protected $async;
    protected $backlog;
    protected $bindings = [ ];
    protected $serverSockets = [ ];

    public function __construct($config) {
        if (isset($config['bindings'])) {
            foreach($config['bindings'] as $binding) {
                $this->binding($binding['ip'], $binding['port']);
            }
        }

        $this->backlog(isset($config['backlog'])
            ? $config['backlog']
            : 10);

        $this->async(isset($config['async'])
            ? $config['async']
            : false);
    }

    public function binding($ip, $port) {
        if ($ip == null) {
            throw new ServerNullIpException();
        }

        if ($port == null) {
            throw new ServerNullPortException();
        }

        $key = $ip . ':' . $port;
        $this->bindings[$key] = [
            'ip'    => $ip,
            'port'  => $port
        ];
    }

    public function backlog($backlog = null) {
        if (func_num_args() > 0) {
            $this->backlog = $backlog;
        }

        return $this->backlog;
    }

    public function async($async = null) {
        if (func_num_args() > 0) {
            $this->async = $async;
        }

        return $this->async;
    }

    public function start() {
        $this->trigger('starting');

        $this->createServerSockets();

        $this->trigger('started');
    }

    public function stop() {
        $this->trigger('stopping');

        $this->destroyServerSockets();

        $this->trigger('stopped');
    }

    public function poll($timeout) {
        throw new Exception('Not implemented');
    }

    protected function findServerSocket($socket) {
        foreach($this->serverSockets as $serverSocket) {
            if ($serverSocket->socket() == $socket) {
                return $serverSocket;
            }
        }

        return null;
    }

    protected function createServerSockets() {
        foreach($this->bindings as $key => $binding) {
            $socket = $this->createSocket();
            $socket->bind($binding['ip'], $binding['port']);
            $socket->async($this->async());

            $this->serverSockets[$key] = $socket;
        }
    }

    protected function destroyServerSockets() {
        foreach($this->serverSockets as $socket) {
            $socket->async(false);
            $socket->close();
        }
    }

    protected function createSocket() {
        throw new Exception('Not implemented');
    }

    public function keyFromSocketResourceRemotePeer($socket) {
        $ip = null; $port = null;
        
        if (@socket_getpeername($socket, $ip, $port) == false) {
            throw new SocketUnabletToGetRemotePeerException();
        }

        return $ip . ':' . $port;
    }

    public function keyFromSocketResourceLocalPeer($socket) {
        $ip = null; $port = null;
        
        if (@socket_getsockname($socket, $ip, $port) == false) {
            throw new SocketUnabletToGetLocalPeerException();
        }

        return $ip . ':' . $port;
    }
}

class ServerException extends SocketException {

}

class ServerNullIpException extends ServerException {

}

class ServerNullPortException extends ServerException {

}
