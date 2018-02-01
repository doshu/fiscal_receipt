<?php

    namespace Inoma\Receipt\Protocols;
    
    use Exceptions\NotImplementedException;
    use Exceptions\InvalidValueException;

    class CustomProtocol extends BaseProtocol implements Protocol {
    
        protected $_ip = null;
        protected $_port = null;
        protected $_type = null;
        
        private $_maxDescLength = 22;
        
        private $_charOverride = [
            '€' => "\x7f"  
        ];
        
        protected $_connection = null;
        
        public function beforePrintReceipt(\Inoma\Receipt\Receipt $receipt, \Inoma\Receipt\Protocols\CommandsCollection $commandsCollection) {
            if(!$receipt->getIsFiscal()) {
                $commandsCollection->prepend('400110000000000');
                $commandsCollection->append('4004');
            }
            else {
                $commandsCollection->append('3011');
                $commandsCollection->append('3013');
            }
            if($receipt->getTotal() < 0 && !$this->_printer->supportsNegativeTotal()) {
                $commandsCollection->prepend('102M');
            }
        }
        
        public function afterPrintReceipt(\Inoma\Receipt\Receipt $receipt, \Inoma\Receipt\Protocols\CommandsCollection $commandsCollection) {
        
        }
        
        public function printProduct(\Inoma\Receipt\Items\ProductItem $product) {
            $cmds = [];
            $desc = substr(sprintf("%sX %s", $product->getQty(), $product->getDescription()), 0, $this->_maxDescLength);
            $cmds[] = sprintf("30011%02d%09d", $desc, strlen($desc), $this->_parsePrice($product->getPrice()));
            
            foreach($product->getDiscounts() as $discount) {
                switch($discount->getCode()) {
                    case 'byPercentage':
                        $desc = sub_str($discount->getDescription(), 0, $this->_maxDescLength);
                        $cmds[] = sprintf("30013%02d%09d", $desc, strlen($desc), $this->_parsePrice($product->getPrice() / 100 *$discount->getValue()));
                        break;
                    case 'byValue':
                        $cmds[] = sprintf("30013%02d%09d", $desc, strlen($desc), $this->_parsePrice($discount->getValue()));
                        break;
                }
            }
            foreach($product->getIncreases() as $increase) {
                switch($increase->getCode()) {
                    case 'byPercentage':
                        $desc = sub_str($discount->getDescription(), 0, $this->_maxDescLength);
                        $cmds[] = sprintf("30012%02d%09d", $desc, strlen($desc), $this->_parsePrice($product->getPrice() / 100 *$discount->getValue()));
                        break;
                    case 'byValue':
                        $cmds[] = sprintf("30012%02d%09d", $desc, strlen($desc), $this->_parsePrice($discount->getValue()));
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
            $value = !$payment->getValue()?$this->_currentReceipt->getTotal():$payment->getValue();
            switch(get_class($payment)) {
                case \Inoma\Receipt\Receipt\CashPayment::class:
                    return sprintf("3004%02d%09d", strlen("CONTANTI"), "CONTANTI", $this->_parsePrice($value));
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
            
            $frame = $this->_createFrame($command);
            
            $connection =  $this->_getConnection();
            
		    if($connection) {
			    if(fwrite($connection, $frame) === false) {
			        return false;
			    }
			    $res = fread($connection, 1);
			    fwrite($connection, "\x06");
			    return true;
		    }
		    return false;
        }
        
        protected function _getConnection() {
            if($this->_connection === null) {
                $this->_connection = fsockopen($this->_printer->getIp(), $this->_printer->getPort(), $errno, $errstr, 10);
            }
            return $this->_connection;
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
        
        protected function _createFrame($command) {
            $stx = "\x02";
            $cnt = "\x00\x00";
            $ident = "\x30";
            $cks = $this->_checksum($cnt, $command);
            $etx = "\x03";
            $frame = $stx.$cnt.$ident.$m.$cks.$etx;
            
            return $frame;
        }
        
        protected function _checksum($cnt, $m) {
        
            $cntSum = 0;
            for($i = 0; $i < strlen($cnt); $i++) {
                $cntSum += ord($cnt[$i]);
            }
            
            $identSum = ord('0');
            
            $mSum = 0;
            for($i = 0; $i < strlen($m); $i++) {
                $mSum += ord($m[$i]);
            }
            
            $cks = ($cntSum + $identSum + $mSum) % 100;
            return str_pad((string)$cks, 2, '0', STR_PAD_LEFT);
        }
        
    }

?>
