<?php

    namespace Inoma\Receipt\Items\Products;
    
    class DiscountByPercentage extends PriceModifier implements ProductModifier {
    
        protected $_code = "byPercentage";
        
        public function apply(\Inoma\Receipt\Items\ProductItem $product) {
            $product->setFinalPrice($product->getFinalPrice(false) - ($product->getFinalPrice(false) / 100 * $this->getValue()));    
        }
        
    }

?>
