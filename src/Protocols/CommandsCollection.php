<?php

    namespace Inoma\Receipt\Protocols;
    
    class CommandsCollection extends \ArrayObject {
    
        public function getCommands() {
            return $this->getArrayCopy();
        }
        
        public function setCommands(array $commands) {
            
            $this->exchangeArray($commands);
        }   
        
        public function prepend($command) {
            $array = $this->getArrayCopy();
            array_unshift($array, $command);
            $this->exchangeArray($array);
        }
        
    }

?>
