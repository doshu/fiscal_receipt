<?php

    namespace Inoma\Receipt\Receipt;
    
    class MealVoucherWithChangePayment extends PaymentMethod {
    
        protected $_code = 'meal_voucher_with_change';
        protected $_hasChange = true;
        
    }

?>
