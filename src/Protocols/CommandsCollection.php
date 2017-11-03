<?php

    namespace Inoma\Receipt\Protocols;
    
    class CommandsCollection implements \ArrayAccess, \Countable {
    
        protected $_commands = [];
        
        public function __construct($commands = []) {
            $this->setCommands($commands);
        }
        
        public function offsetSet($offset, $value) {
            if($value !== null && $value !== "") {
                if (is_null($offset)) {
                    $this->_commands[] = $value;
                } else {
                    $this->_commands[$offset] = $value;
                }
            }
        }

        public function offsetExists($offset) {
            return isset($this->_commands[$offset]);
        }

        public function offsetUnset($offset) {
            unset($this->_commands[$offset]);
        }

        public function offsetGet($offset) {
            return isset($this->_commands[$offset]) ? $this->_commands[$offset] : null;
        }
        
        public function count() {
            return count($this->_commands);
        }
        
        public function clear() {
            $this->_commands = [];
        }
        
        public function getCommands() {
            return $this->_commands;
        }
        
        public function setCommands(array $commands) {
            foreach($commands as $command) {
                if($command !== null && $command !== "") {
                    $this->_commands = $commands;
                }
            }
        }   
    }

?>
