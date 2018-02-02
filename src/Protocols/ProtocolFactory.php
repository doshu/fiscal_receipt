<?php

    namespace Inoma\Receipt\Protocols;
    
    use Exceptions\NotImplementedException;
    
    class ProtocolFactory {
            
        public static build($protocol) {
            $protocolClass = $protocol.'Protocol';
            return new $protocolClass();
        }
        
    }
