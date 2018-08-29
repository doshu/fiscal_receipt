<?php

    namespace Inoma\Receipt\Items\Products;
    
    class DiscountByPercentage extends \Inoma\Receipt\Receipt\PriceModifier implements ProductModifier {
    
        protected $_code = "byPercentage";
        
        public function apply(\Inoma\Receipt\Items\ProductItem $product) {
            $discount = $this->round($product->getIntermediatePrice() / 100 * $this->getValue());
            $product->setIntermediatePrice($product->getIntermediatePrice() - $discount);
            $this->setRealValue($discount);    
        }
        
    }

?>
