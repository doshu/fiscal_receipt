<?php

    namespace Inoma\Receipt\Protocols;
    
    use \Inoma\Receipt\Protocols\Exceptions\NotImplementedException;
    
    abstract class BaseProtocol {
    
        public $debug = false;
        protected $_logger = null;
        protected $_currentReceipt = null;
        protected $_printer = null;
        
        public function init(\Inoma\Receipt\Printer $printer) {
            if(strtolower($printer->getType()) != 'eth') {
                throw new \NotImplementedException('Only eth protocol support available');
            }
            
            $this->_printer = $printer;
        }
    
        public function printReceipt(\Inoma\Receipt\Receipt $receipt) {
            $this->log('--- start receipt ---');
            
            $this->_currentReceipt = $receipt;
            
            $commands = new CommandsCollection();
            
            if(!$this->_printer->supportsNoChangePayment()) {
                $noChangeAddition = round($receipt->getTotal() - $receipt->getPaid() - $receipt->getChange(), 2);
                if($noChangeAddition > 0) {
                    $receipt->addIncrease(new \Inoma\Receipt\Receipt\IncreaseByValue($noChangeAddition, "Varie"));
                }
            }
            
            foreach($receipt->getHeader()->getItems() as $item) {
                $commands[] = $this->printItem($item);
            }
            
            foreach($receipt->getBody()->getItems() as $item) {
                $commands[] = $this->printItem($item);
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
            
            foreach($receipt->getPayments() as $payment) {
                $commands[] = $this->printPaymentMethod($payment);
            }
            
            $this->beforePrintReceipt($receipt, $commands);
            
            if($this->debug) {
                return $commands->getCommands();
            }
            
            foreach($commands->getCommands() as $command) {
                if(!$this->sendCommand($command)) {
                    $this->_currentReceipt = null;
                    return false;
                }
            }
            
            $this->_currentReceipt = null;
            $this->log('--- end receipt ---');
            
            $this->afterPrintReceipt($receipt, $commands);
            
            return true;
        }
        
        abstract public function beforePrintReceipt(\Inoma\Receipt\Receipt $receipt, \Inoma\Receipt\Protocols\CommandsCollection $commandsCollection);
        
        abstract public function afterPrintReceipt(\Inoma\Receipt\Receipt $receipt, \Inoma\Receipt\Protocols\CommandsCollection $commandsCollection);
        
        public function printItem(\Inoma\Receipt\Items\Item $item) {
            switch(get_class($item)) {
                case \Inoma\Receipt\Items\ProductItem::class:
                    return $this->printProduct($item);
                    break;
                case \Inoma\Receipt\Items\ReturnItem::class:
                    return $this->printReturn($item);
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
        
        public function setLogger($callable) {
            $this->_logger = $callable;
        }
        
        public function log($message) {
            if($this->_logger) {
                $logger = $this->_logger;
                $logger($message);
            }
        }
    }

?>
