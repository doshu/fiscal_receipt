<?php

    namespace Inoma\Receipt\Protocols;
    
    use Exceptions\NotImplementedException;

    interface Protocol {
    
        public function init(\Inoma\Receipt\Printer $printer);
        
        public function printReceipt(\Inoma\Receipt\Receipt $receipt);
        
        public function printInvoice(\Inoma\Receipt\Receipt $receipt, $printCopy = false);
        
        public function printItem(\Inoma\Receipt\Items\Item $item);
    
        public function printProduct(\Inoma\Receipt\Items\ProductItem $product);
        
        public function printReturn(\Inoma\Receipt\Items\ReturnItem $return);
        
        public function printString(\Inoma\Receipt\Items\StringItem $string);
        
        public function printNumericCode(\Inoma\Receipt\Items\NumericCodeItem $numericCode);
        
        public function printSubtotal(\Inoma\Receipt\Items\SubtotalItem $subtotal);
        
        public function printBarcode(\Inoma\Receipt\Items\BarcodeItem $barcode);
        
        public function printPaymentMethod(\Inoma\Receipt\Receipt\PaymentMethod $payment);
        
        public function printOperator(\Inoma\Receipt\Items\OperatorItem $operator);
        
        public function printClient(\Inoma\Receipt\Items\ClientItem $client);
        
        public function printReceiptDiscount(\Inoma\Receipt\Receipt\PriceModifier $discount);
        
        public function printReceiptIncrease(\Inoma\Receipt\Receipt\PriceModifier $increase);
        
        public function cancel();
        
        public function dailyFiscalReset();
        
        public function sendCommand($command);
    }

?>
