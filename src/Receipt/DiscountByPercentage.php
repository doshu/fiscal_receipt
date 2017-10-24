<?php

    namespace Inoma\Receipt\Receipt;
    
    class DiscountByPercentage extends PriceModifier implements ReceiptModifier {
        
        protected $_code = "byPercentage";
        
        public function apply(\Inoma\Receipt\Receipt $receipt) {
            $receipt->setTotal($receipt->getTotal(false) - ($receipt->getTotal(false) / 100 * $this->getValue()));    
        }
        
    }

?>
