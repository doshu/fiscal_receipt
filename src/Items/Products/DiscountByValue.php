<?php

    namespace Inoma\Receipt\Items\Products;
    
    class DiscountByValue extends PriceModifier implements ProductModifier {
    
        protected $_code = "byValue";
        
        public function apply(\Inoma\Receipt\Items\ProductItem $product) {
            $product->setIntermediatePrice($product->getIntermediatePrice() - $this->getValue());    
        }
        
    }

?>
