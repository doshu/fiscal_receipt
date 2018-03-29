<?php

    namespace Inoma\Receipt\Items\Products;
    
    class IncreaseByPercentage extends PriceModifier implements ProductModifier {
    
        protected $_code = "byPercentage";
        
        public function apply(\Inoma\Receipt\Items\ProductItem $product) {
            $product->setIntermediatePrice($product->getIntermediatePrice() + ($product->getIntermediatePrice() / 100 * $this->getValue()));    
        }
        
    }

?>
