<?php

    namespace Inoma\Receipt\Protocols;
    
    use Exceptions\NotImplementedException;
    
    class ProtocolFactory {
            
        public static function build($protocol) {
            $protocolClass = "\\Inoma\\Receipt\\Protocols\\".$protocol.'Protocol';
            return new Protocols\CustomProtocol();
        }
        
    }
