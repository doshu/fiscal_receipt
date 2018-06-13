<?php

    namespace Inoma\Receipt\Receipt;
    
    class DiscountByPercentage extends PriceModifier implements ReceiptModifier {
        
        protected $_code = "byPercentage";
        
        public function apply(\Inoma\Receipt\Receipt $receipt) {
            $discount = ($receipt->getIntermediateTotal() / 100 * $this->getValue());
            $receipt->setIntermediateTotal(max(0, $receipt->getIntermediateTotal() - $discount));    
            $this->setRealValue($discount);
        }
        
    }

?>
