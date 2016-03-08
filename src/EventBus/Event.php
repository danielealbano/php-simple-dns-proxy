<?php namespace SimpleDnsProxy\EventBus;

use Exception;

class Event {
    private $name;
    private $data;
    private $origin;
    
    public function __construct($name, $data = null, $origin = null) {
        if ($name == null) {
            throw new EventNullNameException();
        }

        $this->name($name);
        $this->data($data);
        $this->origin($origin);
    }

    public function name($name = null) {
        if (func_num_args() > 0) {
            $this->name = $name;
        }

        return $this->name;
    }

    public function data($data = null) {
        if (func_num_args() > 0) {
            $this->data = $data;
        }

        return $this->data;
    }

    public function origin($origin = null) {
        if (func_num_args() > 0) {
            $this->origin = $origin;
        }

        return $this->origin;
    }
}

class EventException extends Exception {

}

class EventNullNameException extends EventException {

}
