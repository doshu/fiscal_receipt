<?php

    namespace Inoma\Receipt\Receipt;
    
    class DiscountByValue extends PriceModifier implements ReceiptModifier {
    
        protected $_code = "byValue";
        
        public function apply(\Inoma\Receipt\Receipt $receipt) {
            $receipt->setIntermediateTotal(max(0, $receipt->getIntermediateTotal() - $this->getValue()));
            $this->setRealValue($this->getValue());   
        }
        
    }

?>
