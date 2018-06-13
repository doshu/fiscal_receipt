<?php

    namespace Inoma\Receipt\Items\Products;
    
    class IncreaseByPercentage extends PriceModifier implements ProductModifier {
    
        protected $_code = "byPercentage";
        
        public function apply(\Inoma\Receipt\Items\ProductItem $product) {
            $increase = ($product->getIntermediatePrice() / 100 * $this->getValue());
            $product->setIntermediatePrice($product->getIntermediatePrice() + $increase);
            $this->setRealValue($increase);    
        }
        
    }

?>
