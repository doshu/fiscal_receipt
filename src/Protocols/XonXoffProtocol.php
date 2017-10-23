<?php

    namespace Inoma\Receipt\Protocols;
    
    use Exceptions\NotImplementedException;
    use Exceptions\InvalidValueException;

    class XonXoffProtocol extends BaseProtocol implements Protocol {
    
        protected $_ip = null;
        protected $_port = null;
        protected $_type = null;
        
        protected $_printer = null;
        
        public function beforePrintReceipt(\Inoma\Receipt\Receipt $receipt, \ArrayAccess $commandsCollection) {
            if(!$receipt->isFiscal()) {
                array_unshift($commandsCollection, 'j');
                array_push($commandsCollection, 'J');
            }
        }
        
        public function afterPrintReceipt(\Inoma\Receipt\Receipt $receipt, \ArrayAccess $commandsCollection) {
        
        }
        
        public function init(\Inoma\Receipt\Printer $printer) {
            if(strtolower($printer->getType()) != 'eth') {
                throw new \NotImplementedException('Only eth protocol support available');
            }
            
            $this->_printer = $printer;
        }
        
        public function printProduct(\Inoma\Receipt\Items\ProductItem $product);
        
        public function printString(\Inoma\Receipt\Items\StringItem $string) {
            return sprintf('"%s"@', $string->getValue());
        }
        
        public function printNumericCode(\Inoma\Receipt\Items\NumericCodeItem $numericCode) {
            if(!ctype_digit($numericCode->getValue())) {
                throw new InvalidValueException('Numeric code '.$numericCode->getValue().' is not valid');
            }
            return sprintf('%s#', $numericCode->getValue());
        }
        
        public function printSubtotal(\Inoma\Receipt\Items\SubtotalItem $subtotal) {
            return "=";
        }
        
        public function printBarcode(\Inoma\Receipt\Items\BarcodeItem $barcode) {
            switch(strtolower($barcode->getType())) {
                case 'ean13':
                    if(strlen($barcode->getCode()) < 12 strlen($barcode->getCode()) > 13) {
                        throw new InvalidValueException('Ean13 barcode '.$barcode->getCode().' is not valid');
                    }
                    return sprintf('"%s"1Z', $barcode->getCode());
                    break;
                case 'ean8':
                    if(strlen($barcode->getCode()) < 7 strlen($barcode->getCode()) > 8) {
                        throw new InvalidValueException('Ean8 barcode '.$barcode->getCode().' is not valid');
                    }
                    return sprintf('"%s"2Z', $barcode->getCode());
                    break;
                case 'code39':
                    return sprintf('"%s"3Z', strtoupper($barcode->getCode()));
                    break;
                case 'code128':
                    return sprintf('"%s"4Z', $barcode->getCode());
                    break;
                case 'interleaved2of5':
                    return sprintf('"%s"5Z', strtoupper($barcode->getCode()));
                    break;
                case 'barcode':
                    return sprintf('"%s"6Z', strtoupper($barcode->getCode()));
                    break;
                default:
                    throw new InvalidValueException($barcode->getType().' barcode type is not valid');
            }
        }
        
        public function printPaymentMethod(\Inoma\Receipt\Receipt\PaymentMethod $payment) {
            switch(get_class($payment)) {
                case \Inoma\Receipt\Receipt\CashPayment::class:
                    if($payment->getValue()) {
                        return sprintf('%sH1T', $this->_parsePrice($payment->getValue()));
                    }
                    return "1T";
                    break;
                case \Inoma\Receipt\Receipt\CheckPayment::class:
                    if($payment->getValue()) {
                        return sprintf('%sH2T', $this->_parsePrice($payment->getValue()));
                    }
                    return "2T";
                    break;
                case \Inoma\Receipt\Receipt\CardPayment::class:
                    if($payment->getValue()) {
                        return sprintf('%sH3T', $this->_parsePrice($payment->getValue()));
                    }
                    return "3T";
                    break;
                case \Inoma\Receipt\Receipt\MealVoucherPayment::class:
                    if($payment->getValue()) {
                        return sprintf('%sH5T', $this->_parsePrice($payment->getValue()));
                    }
                    return "5T";
                    break;
                case \Inoma\Receipt\Receipt\CreditPayment::class:
                    if($payment->getValue()) {
                        return sprintf('%sH7T', $this->_parsePrice($payment->getValue()));
                    }
                    return "7T";
                    break;
                case \Inoma\Receipt\Receipt\GenericPayment::class:
                    if($payment->getValue()) {
                        return sprintf('%sH4T', $this->_parsePrice($payment->getValue()));
                    }
                    return "4T";
                    break;
            }
        }
        
        public function printOperator($operator) {
            return sprintf('"%s%s"@', 'Operatore:', $operator);
        }
        
        public function sendCommand($command) {
            $fp = fsockopen($this->_printer->getIp(), $this->_printer->getPort(), $errno, $errstr, 10);
		    if($fp) {
			    if(fwrite($fp, $command) === false) {
			        return false;
			    }
			    fclose($fp);
			    return true;
		    }
		    return false;
        }
        
        public function cancel() {
            return $this->sendCommand('k');        
        }
        
        protected function _parsePrice($value) {
            return round($value, 2) * 100;
        }
        
    }

?>
