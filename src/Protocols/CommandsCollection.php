<?php

    namespace Inoma\Receipt\Protocols;
    
    class CommandsCollection extends \ArrayObject {
    
        public function getCommands() {
            return $this->getArrayCopy();
        }
        
        public function setCommands(array $commands) {
            $commands = array_filter($commands);
            $this->exchangeArray($commands);
        }   
        
        public function append($command) {
            if($command === null) {
                return true;
            }
            $commands = explode("\n", $command);
            foreach($commands as $_c) {
                parent::append($_c);
            }
        }
        
        public function prepend($command) {
            if($command === null) {
                return true;
            }
            $array = $this->getArrayCopy();
            $commands = explode("\n", $command);
            foreach($commands as $_c) {
                array_unshift($array, $command);
            }
            $this->exchangeArray($array);
        }
        
    }

?>
