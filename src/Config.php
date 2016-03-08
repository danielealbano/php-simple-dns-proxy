<?php namespace SimpleDnsProxy;

use Exception;
use ArrayAccess;

class Config implements ArrayAccess {
    private $config;

    public function __construct($configFile = null) {
        if ($configFile) {
            $this->load($configFile);
        }
    }

    public function get($key) {
        return isset($this->config[$key])
            ? $this->config[$key]
            : null;
    }

    public function set($key, $value) {
        $this->config[$key] = $value;

        return $this;
    }

    public function remove($key) {
        unset($this->config[$key]);

        return $this;
    }

    public function exists($key) {
        return isset($this->config[$key]);
    }

    public function offsetSet($key, $value) {
        $this->set($key, $value);
    }

    public function offsetExists($key) {
        return $this->exists($key);
    }

    public function offsetUnset($key) {
        $this->remove($key);
    }

    public function offsetGet($key) {
        return $this->get($key);
    }

    public function load($configFile) {
        if (($configFileData = @file_get_contents($configFile)) == false) {
            throw new ConfigFileNotReadableException($configFile);
        }

        $config = @json_decode($configFileData, true);

        if ($config == false) {
            throw new ConfigFileJsonErrorException($configFile);
        }

        $this->config = $config;
    }

    public function dump($configFile) {
        if (@file_put_contents(json_encode($configFile)) == false) {
            throw new ConfigFileNotWritableException($configFile);
        }
    }
}

class ConfigFileException extends Exception {
    private $configFile;

    public function __construct($configFile) {
        $this->configFile($configFile);
    }

    public function configFile($configFile = null) {
        if (func_num_args() > 0) {
            $this->configFile = $configFile;
        }

        return $this->configFile;
    }
}

class ConfigFileNotReadableException extends ConfigFileException {
}

class ConfigFileNotWritableException extends ConfigFileException {
}

class ConfigFileJsonErrorException extends ConfigFileException {
}
