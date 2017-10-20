<?php

    namespace Inoma\Receipt\Receipt;
    
    class DiscountByValue extends PriceModifier implements ReceiptModifier {
        
        protected $_value = null;
        protected $_description = null;
        
        public function __construct($value = null, $description = null) {
            $this->setValue($value);
            $this->setDescription($description);
        }
        
        public function setValue($value) {
            $this->_value = $value;
            return $this;
        }
        
        public function getValue() {
            return $this->_value;
        }
        
        public function setDescription($description) {
            $this->_description = $description;
            return $this;
        }
        
        public function getValue() {
            return $this->_description;
        }
        
        public function apply(\Inoma\Receipt\Receipt $receipt) {
            $receipt->setTotal($this->getTotal(false) - $this->getValue());    
        }
        
    }

?>
