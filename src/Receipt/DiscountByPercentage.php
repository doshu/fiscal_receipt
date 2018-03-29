<?php

    namespace Inoma\Receipt\Receipt;
    
    class DiscountByPercentage extends PriceModifier implements ReceiptModifier {
        
        protected $_code = "byPercentage";
        
        public function apply(\Inoma\Receipt\Receipt $receipt) {
            $receipt->setIntermediateTotal(max(0, $receipt->getIntermediateTotal() - ($receipt->getIntermediateTotal() / 100 * $this->getValue())));    
        }
        
    }

?>
