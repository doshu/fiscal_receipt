<?php

    namespace Inoma\Receipt\Items\Products;
    
    class DiscountByPercentage extends PriceModifier implements ProductModifier {
    
        protected $_code = "byPercentage";
        
        public function apply(\Inoma\Receipt\Items\ProductItem $product) {
            $discount = ($product->getIntermediatePrice() / 100 * $this->getValue());
            $product->setIntermediatePrice($product->getIntermediatePrice() - $discount);
            $this->setRealValue($discount);    
        }
        
    }

?>
