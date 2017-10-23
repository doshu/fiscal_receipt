<?php

    namespace Inoma\Receipt\Receipt;
    
    abstract class PaymentMethod {
    
        protected $_value = null;
        protected $_code = null;
        
        public function __construct($value = null) {
            $this->setValue($value);
        }
        
        public function setValue($value) {
            $this->_value = $value;
            return $this;
        }
        
        public function getValue() {
            return $this->_value;
        }
        
        public function getCode() {
            return $this->_code;
        }
        
    }

?>
