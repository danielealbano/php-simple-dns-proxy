<?php namespace SimpleDnsProxy\EventBus;

use SimpleDnsProxy\EventBus\Event;

use Exception;

class EventBus {
    private $registeredEvents = [ ];

    public function on($name, $callback) {
        if (isset($this->registeredEvents[$name]) == false) {
            $this->registeredEvents[$name] = [ ];
        }

        $this->registeredEvents[$name][] = $callback;

        return $this;
    }

    public function trigger($name, $userData = null) {
        if ($name == null) {
            throw new EventBusNullNameException();
        }

        if (isset($this->registeredEvents[$name]) == false) {
            return false;
        }

        $event = new Event($name, $userData, $this);

        foreach($this->registeredEvents[$name] as $callback) {
            $return = call_user_func($callback, $event);

            if ($return === false) {
                return false;
            }
        }
        
        return true;
    }
}

class EventBusException extends Exception {

}

class EventBusNullNameException extends Exception {

}
