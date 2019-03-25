<?php

    namespace Inoma\Receipt\Items\Products;
    
    class IncreaseByValue extends \Inoma\Receipt\Receipt\PriceModifier implements ProductModifier {
    
        protected $_code = "byValue";
        
        public function apply(\Inoma\Receipt\Items\ProductItem $product) {
            $product->setIntermediatePrice($product->getIntermediatePrice() + $this->getValue()); 
            $this->setRealValue($this->getValue());   
        }
        
    }

?>
