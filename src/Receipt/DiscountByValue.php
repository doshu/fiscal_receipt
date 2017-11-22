<?php

    namespace Inoma\Receipt\Receipt;
    
    class DiscountByValue extends PriceModifier implements ReceiptModifier {
    
        protected $_code = "byValue";
        
        public function apply(\Inoma\Receipt\Receipt $receipt) {
            $receipt->setTotal(max(0, $receipt->getTotal(false) - $this->getValue()));    
        }
        
    }

?>
