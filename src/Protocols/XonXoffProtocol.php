<?php

    namespace Inoma\Receipt\Protocols;
    
    use Exceptions\NotImplementedException;
    use Exceptions\InvalidValueException;

    class XonXoffProtocol extends BaseProtocol implements Protocol {
    
        protected $_ip = null;
        protected $_port = null;
        protected $_type = null;
        
        private $_maxDescLength = 22;
        
        private $_charOverride = [
            '€' => "\x7f"  
        ];
        
        public function beforePrintReceipt(\Inoma\Receipt\Receipt $receipt, \Inoma\Receipt\Protocols\CommandsCollection $commandsCollection) {
            if(!$receipt->getIsFiscal()) {
                $commandsCollection->prepend('j');
                $commandsCollection->append('J');
            }
            if($receipt->getTotal() < 0 && !$this->_printer->supportsNegativeTotal()) {
                $commandsCollection->prepend('102M');
            }
        }
        
        public function afterPrintReceipt(\Inoma\Receipt\Receipt $receipt, \Inoma\Receipt\Protocols\CommandsCollection $commandsCollection) {
        
        }
        
        public function printProduct(\Inoma\Receipt\Items\ProductItem $product) {
            $cmds = [];
            $cmds[] = sprintf('"%s"%s*%sH%sR', substr($product->getDescription(), 0, $this->_maxDescLength), $product->getQty(), $this->_parsePrice($product->getPrice()), 1);
            foreach($product->getDiscounts() as $discount) {
                switch($discount->getCode()) {
                    case 'byPercentage':
                        $cmds[] = sprintf('%d*1M', $discount->getValue());
                        break;
                    case 'byValue':
                        $cmds[] = sprintf('%sH3M', $this->_parsePrice($discount->getValue()));
                        break;
                }
            }
            foreach($product->getIncreases() as $increase) {
                switch($increase->getCode()) {
                    case 'byPercentage':
                        $cmds[] = sprintf('%d*5M', $increase->getValue());
                        break;
                    case 'byValue':
                        $cmds[] = sprintf('%sH7M', $this->_parsePrice($increase->getValue()));
                        break;
                }
            }
            
            return implode("\n", $cmds);
        }
        
        
        public function printReturn(\Inoma\Receipt\Items\ReturnItem $return) {
            $cmd = $this->_currentReceipt->getTotal() < 0 && !$this->_printer->supportsNegativeTotal()?
                '"%s"%s*%sH%sR':'9M"%s"%s*%sH%sR';
            return sprintf($cmd, substr($return->getDescription(), 0, $this->_maxDescLength), $return->getQty(), $return->getPrice(), 1);
        }
        
        public function printString(\Inoma\Receipt\Items\StringItem $string) {
            return sprintf(
                '"%s"@', 
                str_replace(array_keys($this->_charOverride), array_values($this->_charOverride), $string->getValue())
            );
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
                    if(strlen($barcode->getCode()) < 12  || strlen($barcode->getCode()) > 13) {
                        throw new InvalidValueException('Ean13 barcode '.$barcode->getCode().' is not valid');
                    }
                    return sprintf('"%s"1Z', $barcode->getCode());
                    break;
                case 'ean8':
                    if(strlen($barcode->getCode()) < 7 || strlen($barcode->getCode()) > 8) {
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
        
        public function printOperator(\Inoma\Receipt\Items\OperatorItem $operator) {
            return sprintf('"%s%s"@', 'Operatore:', substr($operator->getLabel(), 0, $this->_maxDescLength - 10));
        }
        
        public function printClient(\Inoma\Receipt\Items\ClientItem $client) {
            $cmds = [];
            if($client->getLabel()) {
                $cmds[] = sprintf('"%s"@38F', substr($client->getLabel(), 0, $this->_maxDescLength));
            }
            if($client->getCode()) {
                $cmds[] = sprintf('"Cod. Cliente: %s"@38F', substr($client->getCode(), 0, $this->_maxDescLength - 14));
            }
            if($client->getCardCode()) {
                $cmds[] = sprintf('"Tessera: %s"@38F', substr($client->getCardCode(), 0, $this->_maxDescLength - 10));
            }
            
            if($client->getCf()) {
                $cmds[] = sprintf('"%s"@39F', strtoupper($client->getCf()));
            }
            if($client->getVat()) {
                $cmds[] = sprintf('"%s"@39F', $client->getVat());
            }
            
            return implode("\n", $cmds);
        }
        
        public function printReceiptDiscount(\Inoma\Receipt\Receipt\PriceModifier $discount) {
            if($discount->getCode() == 'byPercentage') {
                return sprintf('"%s"%s*2M', $discount->getDescription(), $discount->getValue());
            }
            elseif($discount->getCode() == 'byValue') {
                return sprintf('"%s"%sH4M', $discount->getDescription(), $this->_parsePrice($discount->getValue()));
            }
        }
        
        public function printReceiptIncrease(\Inoma\Receipt\Receipt\PriceModifier $increase) {
            if($increase->getCode() == 'byPercentage') {
                return sprintf('"%s"%s*6M', $increase->getDescription(), $increase->getValue());
            }
            elseif($increase->getCode() == 'byValue') {
                return sprintf('"%s"%sH8M', $increase->getDescription(), $this->_parsePrice($increase->getValue()));
            }
        }
        
        public function sendCommand($command) {
            $this->log($command);
            $fp = fsockopen($this->_printer->getIp(), $this->_printer->getPort(), $errno, $errstr, 10);
		    if($fp) {
			    if(fwrite($fp, trim($command)) === false) {
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
        
        public function dailyFiscalReset() {
            return $this->sendCommand('1F');       
        }
        
        protected function _parsePrice($value) {
            return round($value, 2) * 100;
        }
        
    }

?>
