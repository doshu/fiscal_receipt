<?php

    namespace Inoma\Receipt\Receipt;
    
    class IncreaseByPercentage extends PriceModifier implements ReceiptModifier {
    
        protected $_code = "byPercentage";
        
        public function apply(\Inoma\Receipt\Receipt $receipt) {
            $receipt->setIntermediateTotal($receipt->getIntermediateTotal() + ($receipt->getIntermediateTotal() / 100 * $this->getValue()));    
        }
        
    }

?>
