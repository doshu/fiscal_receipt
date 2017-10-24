<?php

    namespace Inoma\Receipt\Protocols;
    
    use Exceptions\NotImplementedException;

    interface Protocol {
    
        public function init(\Inoma\Receipt\Printer $printer);
        
        public function printReceipt(\Inoma\Receipt\Receipt $receipt);
        
        public function printItem(\Inoma\Receipt\Items\Item $item);
    
        public function printProduct(\Inoma\Receipt\Items\ProductItem $product);
        
        public function printString(\Inoma\Receipt\Items\StringItem $string);
        
        public function printNumericCode(\Inoma\Receipt\Items\NumericCodeItem $numericCode);
        
        public function printSubtotal(\Inoma\Receipt\Items\SubtotalItem $subtotal);
        
        public function printBarcode(\Inoma\Receipt\Items\BarcodeItem $barcode);
        
        public function printPaymentMethod(\Inoma\Receipt\Receipt\PaymentMethod $payment);
        
        public function printOperator();
        
        public function printClient();
        
        public function sendCommand($command);
        
        public function cancel();
    }

?>
