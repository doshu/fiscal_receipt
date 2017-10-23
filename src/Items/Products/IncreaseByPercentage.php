<?php

    namespace Inoma\Receipt\Items\Products;
    
    class IncreaseByPercentage extends PriceModifier implements ProductModifier {
        
        public function apply(\Inoma\Receipt\Items\ProductItem $product) {
            $product->setFinalPrice($product->getFinalPrice(false) + ($product->getFinalPrice(false) / 100 * $this->getValue()));    
        }
        
    }

?>