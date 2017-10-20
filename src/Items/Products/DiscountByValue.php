<?php

    namespace Inoma\Receipt\Items\Products;
    
    class DiscountByValue extends PriceModifier implements ProductModifier {
        
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
        
        public function apply(\Inoma\Receipt\ItemsProductItem $product) {
            $product->setFinalPrice($this->getFinalPrice(false) - $this->getValue());    
        }
        
    }

?>
