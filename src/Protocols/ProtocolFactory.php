<?php

    namespace Inoma\Receipt\Protocols;
    
    use Exceptions\NotImplementedException;
    
    class ProtocolFactory {
            
        public static function build($protocol) {
            $protocolClass = $protocol.'Protocol';
            return new $protocolClass();
        }
        
    }
