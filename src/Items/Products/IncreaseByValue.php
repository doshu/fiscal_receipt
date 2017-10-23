<?php

    namespace Inoma\Receipt\Items\Products;
    
    class IncreaseByValue extends PriceModifier implements ProductModifier {
        
        public function apply(\Inoma\Receipt\Items\ProductItem $product) {
            $product->setFinalPrice($product->getFinalPrice(false) + $this->getValue());    
        }
        
    }

?>
