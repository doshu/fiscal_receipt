<?php

    namespace Inoma\Receipt\Receipt;
    
    class IncreaseByValue extends PriceModifier implements ReceiptModifier {
    
        protected $_code = "byValue";
        
        public function apply(\Inoma\Receipt\Receipt $receipt) {
            $receipt->setIntermediateTotal($receipt->getIntermediateTotal() + $this->getValue());    
        }
        
    }

?>
