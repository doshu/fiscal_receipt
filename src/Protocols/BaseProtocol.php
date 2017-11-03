<?php

    namespace Inoma\Receipt\Protocols;
    
    use \Inoma\Receipt\Protocols\Exceptions\NotImplementedException;
    
    abstract class BaseProtocol {
    
        public $debug = false;
    
        public function printReceipt(\Inoma\Receipt\Receipt $receipt) {
            $commands = new CommandsCollection();
            
            foreach($receipt->getHeader()->getItems() as $item) {
                $commands[] = $this->printItem($item);
            }
            foreach($receipt->getBody()->getItems() as $item) {
                $commands[] = $this->printItem($item);
            }
            
            foreach($receipt->getPayments() as $payment) {
                $commands[] = $this->printPaymentMethod($payment);
            }
            
            if($receipt->getOperator()) {
                $commands[] = $this->printOperator($receipt->getOperator());
            }
            if($receipt->getClient()) {
                $commands[] = $this->printClient($receipt->getClient());
            }
            
            foreach($receipt->getFooter()->getItems() as $item) {
                $commands[] = $this->printItem($item);
            }
            
            $this->beforePrintReceipt($receipt, $commands);
            
            if($this->debug) {
                return $commands->getCommands();
            }
            
            foreach($commands->getCommands() as $command) {
                $this->sendCommand($command);
            }
            
            $this->afterPrintReceipt($receipt, $commands);
        }
        
        abstract public function beforePrintReceipt(\Inoma\Receipt\Receipt $receipt, \ArrayAccess $commandsCollection);
        
        abstract public function afterPrintReceipt(\Inoma\Receipt\Receipt $receipt, \ArrayAccess $commandsCollection);
        
        public function printItem(\Inoma\Receipt\Items\Item $item) {
            switch(get_class($item)) {
                case \Inoma\Receipt\Items\ProductItem::class:
                    return $this->printProduct($item);
                    break;
                case \Inoma\Receipt\Items\StringItem::class:
                    return $this->printString($item);
                    break;
                case \Inoma\Receipt\Items\NumericCodeItem::class:
                    return $this->printNumericCode($item);
                    break;
                case \Inoma\Receipt\Items\SubtotalItem::class:
                    return $this->printSubtotal($item);
                    break;
                case \Inoma\Receipt\Items\BarcodeItem::class:
                    return $this->printBarcode($item);
                    break;
                case \Inoma\Receipt\Items\OperatorItem::class:
                    return $this->printOperator($item);
                    break;
                case \Inoma\Receipt\Items\ClientItem::class:
                    return $this->printClient($item);
                    break;
                default:
                    throw new NotImplementedException(get_class($item).' printing not implemented yet');
            }
        }
    }

?>
