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
                throw new NotImplementedException('Only eth protocol support available');
            }
            
            $this->_printer = $printer;
        }
    
        public function printReceipt(\Inoma\Receipt\Receipt $receipt) {
            $this->log('--- start receipt ---');
            
            $this->_currentReceipt = $receipt;
            
            $commands = new CommandsCollection();
            
            if(!$this->_printer->supportsNoChangePayment()) {
                $noChangeAddition = round($receipt->getPaid() - $receipt->getTotal() - $receipt->getChange(), 2);
                if($noChangeAddition > 0) {
                    $receipt->addIncrease(new \Inoma\Receipt\Receipt\IncreaseByValue($noChangeAddition, "Varie"));
                }
            }
            
            foreach($receipt->getHeader()->getItems() as $item) {
                $commands->append($this->printItem($item));
            }
            
            $aggregator = new \Inoma\Receipt\Items\ItemsAggregator();
            foreach($aggregator->aggregate($receipt->getBody()->getItems()) as $item) {
                $commands->append($this->printItem($item));
            }
            
            if($receipt->getOperator()) {
                $commands->append($this->printOperator($receipt->getOperator()));
            }
            
            if($receipt->getClient()) {
                $commands->append($this->printClient($receipt->getClient()));
            }
            
            foreach($receipt->getFooter()->getItems() as $item) {
                $commands->append($this->printItem($item));
            }
            
            foreach($receipt->getDiscounts() as $discount) {
                $commands->append($this->printReceiptDiscount($discount));
            }
            
            foreach($receipt->getIncreases() as $increase) {
                $commands->append($this->printReceiptIncrease($increase));
            }
            
            foreach($receipt->getPayments() as $payment) {
                $commands->append($this->printPaymentMethod($payment));
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
                case \Inoma\Receipt\Items\ImageItem::class:
                    return $this->printImage($item);
                    break;
                case \Inoma\Receipt\Items\RawItem::class:
                    return $item->getValue();
                    break;
                default:
                    throw new NotImplementedException(get_class($item).' printing not implemented yet');
            }
        }
        
        
        public function printInvoice(\Inoma\Receipt\Receipt $receipt, $printCopy = false) {
            throw new NotImplementedException('Invoice printing not implemented yet');
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
        
        protected function s($string) {
            return \Cake\Utility\Text::transliterate($string);
        }
        
        
        protected function _getTaxSummary($receipt) {
            $taxSummary = [];
            foreach($receipt->getProducts() as $product) {
                if($product->getTax() !== null) {
                    if(!isset($taxSummary[$product->getTax()])) {
                        $taxSummary[$product->getTax()] = 0;
                    }
                    $taxSummary[$product->getTax()] += $product->getFinalPrice();
                }
            }
            
            //discounts and increases taxable ripartitions
            $getTaxable = function() use (&$taxSummary) {
                $taxable = 0;
                array_walk($taxSummary, function($total, $tax) use (&$taxable) {
                    $taxable += $total / (1 + $tax/100);
                });
                return $taxable;
            };
            
            foreach($receipt->getIncreases() as $increase) {
                $fraction = $increase->getRealValue() / $getTaxable();
                foreach($taxSummary as $tax => $total) {
                    $taxSummary[$tax] += $fraction * $total / (1 + $tax/100);
                }
            }
            
            foreach($receipt->getDiscounts() as $discount) {
                $fraction = $discount->getRealValue() / $getTaxable();
                foreach($taxSummary as $tax => $total) {
                    $taxSummary[$tax] -= $fraction * $total / (1 + $tax/100);
                }
            }
            
            return $taxSummary;
        }
    }

?>
