<?php

    namespace Inoma\Receipt\Utility;

    trait JsonSerializeTrait {
    
        protected $_expose = '*';
        
        public function jsonSerialize() {
            $return = [];
            if($this->_expose == '*') {
                $reflect = new \ReflectionClass($this);
                $props = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);
                foreach ($props as $prop) {
                    $newName = $prop->getName();
                    $newName = $newName[0] == '_'?substr($newName, 1):$newName;
                    $prop->setAccessible(true);
                    $return[$newName] = $prop->getValue($this);
                }
            }
            else {
                foreach($this->_expose as $prop) {
                    $newName = $prop[0] == '_'?substr($prop, 1):$prop;
                    $return[$newName] = $this->{$prop};
                }
            }
            return $return;
        }
    }
?>
