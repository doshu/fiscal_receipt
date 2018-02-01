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
            $commands = explode("\n", $command);
            foreach($commands as $_c) {
                parent::append($_c);
            }
        }
        
        public function prepend($command) {
            $array = $this->getArrayCopy();
            $commands = explode("\n", $command);
            foreach($commands as $_c) {
                array_unshift($array, $command);
            }
            $this->exchangeArray($array);
        }
        
    }

?>
