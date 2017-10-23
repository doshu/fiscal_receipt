<?php

    namespace Inoma\Receipt;
    
    interface Printer {
    
        public function getType();
        
        public function getIp();
        
        public function getPort();
        
    }

?>
