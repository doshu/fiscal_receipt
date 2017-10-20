<?php

    namespace Inoma\Receipt\Receipt;
    
    interface ReceiptModifier {
    
        public function apply(\Inoma\Receipt\Receipt $receipt);
        
    }

?>
