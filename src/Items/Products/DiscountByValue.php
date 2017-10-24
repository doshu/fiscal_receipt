<?php

    namespace Inoma\Receipt\Items\Products;
    
    class DiscountByValue extends PriceModifier implements ProductModifier {
    
        protected $_code = "byValue";
        
        public function apply(\Inoma\Receipt\Items\ProductItem $product) {
            $product->setFinalPrice($product->getFinalPrice(false) - $this->getValue());    
        }
        
    }

?>
