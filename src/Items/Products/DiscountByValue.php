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
        
        public function getDescription() {
            return $this->_description;
        }
        
        public function apply(\Inoma\Receipt\Items\ProductItem $product) {
            $product->setFinalPrice($product->getFinalPrice(false) - $this->getValue());    
        }
        
    }

?>
