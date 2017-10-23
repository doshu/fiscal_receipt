<?php

    namespace Inoma\Receipt\Receipt;
    
    class IncreaseByValue extends PriceModifier implements ReceiptModifier {
        
        public function apply(\Inoma\Receipt\Receipt $receipt) {
            $receipt->setTotal($receipt->getTotal(false) + $this->getValue());    
        }
        
    }

?>
