<?php

    namespace Inoma\Receipt\Utility;
    
    class Uuid {
    
        public static function create() {
        
            $random = function_exists('random_int') ? 'random_int' : 'mt_rand';
            return sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                $random(0, 65535),
                $random(0, 65535),
                $random(0, 65535),
                $random(0, 4095) | 0x4000,
                $random(0, 0x3fff) | 0x8000,
                $random(0, 65535),
                $random(0, 65535),
                $random(0, 65535)
            );
        }
    }
    
?>
