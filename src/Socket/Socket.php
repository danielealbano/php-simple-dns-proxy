<?php namespace SimpleDnsProxy\Socket;

use SimpleDnsProxy\EventBus\EventBus;

use Exception;

abstract class Socket extends EventBus {
    protected $socket;
    protected $localPeer;
    protected $remotePeer;

    const SHUTDOWN_READ = 0;
    const SHUTDOWN_WRITE = 1;
    const SHUTDOWN_BOTH = 2;

    const POLL_NONE = 0;
    const POLL_READ = 1;
    const POLL_WRITE = 2;
    const POLL_ERROR = 3;

    public function __construct() {
    }

    public function option($level, $name, $value = null) {
        if (func_num_args() == 2) {
            $option = socket_get_option($this->socket, $level, $name);

            if ($option === false) {
                throw new SocketUnableToGetOptionException($level, $name);
            }

            return $option;
        } else {
            if (@socket_set_option($this->socket, $level, $name, $value) == false) {
                throw new SocketUnableToSetOptionException($level, $name, $value);
            }

            return $this;
        }
    }

    public function bind($address, $port) {
        if (@socket_bind($this->socket, $address, $port) == false) {
            throw new SocketUnableToBindException($address, $port);
        }

        $this->trigger('bind', [
            'address'   => $address,
            'port'      => $port
        ]);

        return $this;
    }

    public function listen($backlog = 0) {
        if (@socket_listen($this->socket, $backlog) == false) {
            throw new SocketUnableToListenException($backlog);
        }

        $this->trigger('listen', [
            'backlog'   => $backlog
        ]);

        return $this;
    }

    public function close() {
        $this->trigger('close');

        @socket_close($this->socket);
    }

    public function poll($timeout) {
        $reads = [ $this->socket ]; $writes = [ $this->socket ]; $errors = [ $this->socket ];
        
        $result = @socket_select($reads, $writes, $errors, 0, $timeout);

        if ($result === false) {
            throw new SocketUnableToPollException();
        }

        if ($result == 0) {
            $return = self::POLL_NONE;
        } else {
            if (isset($reads[0])) {
                $return = self::POLL_READ;
            } else if (isset($writes[0])) {
                $return = self::POLL_WRITE;
            } else {
                $return = self::POLL_ERROR;
            }
        }

        $this->trigger('poll', [
            'result'    => $return
        ]);
    
        return $return;
    }

    public function async($async = false) {
        if ($async == false) {
            @socket_set_block($this->socket);
        } else {
            @socket_set_nonblock($this->socket);
        }
    }

    public function socket() {
        return $this->socket;
    }

    public function localPeer() {
        $ip = null; $port = null;

        if ($this->localPeer) {
            return $this->localPeer;
        }
        
        if (@socket_getsockname($this->socket, $ip, $port) == false) {
            throw new SocketUnabletToGetLocalPeerException();
        }

        $this->localPeer = [
            'ip'    => $ip,
            'port'  => $port
        ];

        return $this->localPeer;
    }

    public function remotePeer() {
        $ip = null; $port = null;

        if ($this->remotePeer) {
            return $this->remotePeer;
        }
        
        if (@socket_getpeername($this->socket, $ip, $port) == false) {
            throw new SocketUnabletToGetRemotePeerException();
        }

        $this->remotePeer = [
            'ip'    => $ip,
            'port'  => $port
        ];

        return $this->remotePeer;
    }

    public function domain($domain = null) {
        if (func_num_args() > 0) {
            $this->domain = $domain;
        }

        return $this->domain;
    }

    public function type($type = null) {
        if (func_num_args() > 0) {
            $this->type = $type;
        }

        return $this->type;
    }

    public function protocol($protocol = null) {
        if (func_num_args() > 0) {
            $this->protocol = $protocol;
        }

        return $this->protocol;
    }

    protected function internalCreate($domain, $type, $protocol) {
        if(($socket = @socket_create($domain, $type, $protocol)) == false) {
            throw new SocketCreationFailedException();
        }

        $this->socket = $socket;

        $this->trigger('create', [
            'socket' => $socket
        ]);
    }
}

class SocketException extends Exception {
    private $errorCode;
    private $errorMessage;

    public function __construct() {
        $errorCode = socket_last_error();
        $errorMessage = socket_strerror($errorCode);

        $this->errorCode($errorCode);
        $this->errorMessage($errorMessage);
    }

    public function errorCode($errorCode = null) {
        if (func_num_args() > 0) {
            $this->errorCode = $errorCode;
        }

        return $this->errorCode;
    }

    public function errorMessage($errorMessage = null) {
        if (func_num_args() > 0) {
            $this->errorMessage = $errorMessage;
        }

        return $this->errorMessage;
    }
}

class SocketCreationFailedException extends SocketException {

}

class SocketUnableToPollException extends SocketException {

}

class SocketUnableToBindException extends SocketException {
    private $address;
    private $port;

    public function __construct($address, $port) {
        parent::__construct();

        $this->address($address);
        $this->port($port);
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

class SocketUnableToConnectException extends SocketException {
    private $address;
    private $port;

    public function __construct($address, $port) {
        parent::__construct();

        $this->address($address);
        $this->port($port);
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

class SocketUnableToListenException extends SocketException {
    private $backlog;

    public function __construct($backlog) {
        parent::__construct();

        $this->backlog($backlog);
    }

    public function backlog($backlog = null) {
        if (func_num_args() > 0) {
            $this->backlog = $backlog;
        }

        return $this->backlog;
    }
}

class SocketUnableToGetOptionException extends SocketException {
    private $level;
    private $name;

    public function __construct($level, $name) {
        parent::__construct();

        $this->level($level);
        $this->name($name);
    }

    public function level($level = null) {
        if (func_num_args() > 0) {
            $this->level = $level;
        }

        return $this->level;
    }

    public function name($name = null) {
        if (func_num_args() > 0) {
            $this->name = $name;
        }

        return $this->name;
    }
}

class SocketUnableToSetOptionException extends SocketException {
    private $level;
    private $name;
    private $value;

    public function __construct($level, $name, $value) {
        parent::__construct();

        $this->level($level);
        $this->name($name);
        $this->value($value);
    }

    public function level($level = null) {
        if (func_num_args() > 0) {
            $this->level = $level;
        }

        return $this->level;
    }

    public function name($name = null) {
        if (func_num_args() > 0) {
            $this->name = $name;
        }

        return $this->name;
    }

    public function value($value = null) {
        if (func_num_args() > 0) {
            $this->value = $value;
        }

        return $this->value;
    }
}

class SocketUnabletToGetLocalPeerException extends SocketException {

}

class SocketUnabletToGetRemotePeerException extends SocketException {

}
