<?php

    namespace Inoma\Receipt\Receipt;
    
    use Inoma\Receipt\Utility\JsonSerializeTrait;
    
    abstract class PaymentMethod implements \JsonSerializable {
    
        use JsonSerializeTrait;
    
        protected $_value = null;
        protected $_code = null;
        protected $_hasChange = true;
        protected $_paid = null;
        
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
        
        public function getHasChange() {
            return $this->_hasChange;
        }
        
        /**
         * setPaid
         * 
         * used from Receipt to set the real amount paid
         *
         * @param number $value
         * @return number
         */
        public function setPaid($value) {
            $this->_paid = $value;
            return $this;
        }
        
        public function getPaid() {
            return $this->_paid;
        }
        
    }

?>
