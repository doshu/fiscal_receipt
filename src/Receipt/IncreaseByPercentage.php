<?php

    namespace Inoma\Receipt\Receipt;
    
    class IncreaseByPercentage extends PriceModifier implements ReceiptModifier {
    
        protected $_code = "byPercentage";
        
        public function apply(\Inoma\Receipt\Receipt $receipt) {
            $increase = ($receipt->getIntermediateTotal() / 100 * $this->getValue());
            $receipt->setIntermediateTotal($receipt->getIntermediateTotal() + $increase);  
            $this->setRealValue($increase);  
        }
        
    }

?>
