<?php

    namespace Inoma\Receipt\Protocols;
    
    use Exceptions\NotImplementedException;
    use Exceptions\InvalidValueException;

    class CustomProtocol extends BaseProtocol implements Protocol {
    
        protected $_ip = null;
        protected $_port = null;
        protected $_type = null;
        
        private $_maxDescLength = 22;
        
        private $_counter = 0;
        
        protected $_connection = null;
        
        public function beforePrintReceipt(\Inoma\Receipt\Receipt $receipt, \Inoma\Receipt\Protocols\CommandsCollection $commandsCollection) {
            
            if(!$receipt->getIsFiscal()) {
                $commandsCollection->prepend('400110000000000');
                $commandsCollection->append('4004');
            }
            else {
                if($receipt->getTotal() < 0 && !$this->_printer->supportsNegativeTotal()) {
                    $commandsCollection->prepend('7102600000');
                    $commandsCollection->append('3011');
                    $commandsCollection->append('3013');
                }
                else {
                    $commandsCollection->append('3011');
                    $commandsCollection->append('3013');
                }
            }
            
        }
        
        public function afterPrintReceipt(\Inoma\Receipt\Receipt $receipt, \Inoma\Receipt\Protocols\CommandsCollection $commandsCollection) {
        
        }
        
        public function printProduct(\Inoma\Receipt\Items\ProductItem $product) {
            $cmds = [];
            $desc = substr(sprintf("%sX %s", $product->getQty(), $product->getDescription()), 0, $this->_maxDescLength);
            $cmds[] = sprintf("30011%02d%s%09d", strlen($desc), $desc, $this->_parsePrice($product->getPrice() * $product->getQty()));
            
            foreach($product->getDiscounts() as $discount) {
                $desc = substr($discount->getDescription(), 0, $this->_maxDescLength);
                switch($discount->getCode()) {
                    case 'byPercentage':
                        $cmds[] = sprintf("30013%02d%s%09d", strlen($desc), $desc, $this->_parsePrice($product->getPrice() * $product->getQty() / 100 * $discount->getValue()));
                        break;
                    case 'byValue':
                        $cmds[] = sprintf("30013%02d%s%09d", strlen($desc), $desc, $this->_parsePrice($discount->getValue()));
                        break;
                }
            }
            foreach($product->getIncreases() as $increase) {
                $desc = substr($discount->getDescription(), 0, $this->_maxDescLength);
                switch($increase->getCode()) {
                    case 'byPercentage':
                        $cmds[] = sprintf("30012%02d%s%09d", strlen($desc), $desc, $this->_parsePrice($product->getPrice() * $product->getQty() / 100 * $discount->getValue()));
                        break;
                    case 'byValue':
                        $cmds[] = sprintf("30012%02d%s%09d", strlen($desc), $desc, $this->_parsePrice($discount->getValue()));
                        break;
                }
            }
            
            return implode("\n", $cmds);
        }
        
        
        public function printReturn(\Inoma\Receipt\Items\ReturnItem $return) {
            /*
            $cmd = $this->_currentReceipt->getTotal() < 0 && !$this->_printer->supportsNegativeTotal()?
                '"%s"%s*%sH%sR':'9M"%s"%s*%sH%sR';
            */
            $desc = substr(sprintf("%sX %s", $return->getQty(), $return->getDescription()), 0, $this->_maxDescLength);
            if($this->_currentReceipt->getTotal() < 0 && !$this->_printer->supportsNegativeTotal()) {
                return sprintf("30011%02d%s%09d", strlen($desc), $desc, $this->_parsePrice($return->getPrice() * $return->getQty()));
            }
            return sprintf("30019%02d%s%09d", strlen($desc), $desc, $this->_parsePrice($return->getPrice() * $return->getQty()));
        }
        
        public function printString(\Inoma\Receipt\Items\StringItem $string) {
            if($this->_currentReceipt->getIsFiscal()) {
                $string = substr($string->getValue(), 0, 42);
                return sprintf("40031%02d%s000000000", strlen($string), $string);
            }
            
            $string = substr($string->getValue(), 0, 32);
            return sprintf("30021%02d%s", strlen($string), $string);
        }
        
        public function printNumericCode(\Inoma\Receipt\Items\NumericCodeItem $numericCode) {
            if(!ctype_digit($numericCode->getValue())) {
                throw new InvalidValueException('Numeric code '.$numericCode->getValue().' is not valid');
            }
            return $this->printString(new \Inoma\Receipt\Items\StringItem($numericCode->getValue()));
        }
        
        public function printSubtotal(\Inoma\Receipt\Items\SubtotalItem $subtotal) {
            return "3003";
        }
        
        public function printBarcode(\Inoma\Receipt\Items\BarcodeItem $barcode) {
            switch(strtolower($barcode->getType())) {
                case 'ean13':
                    $type = "1";
                    break;
                case 'ean8':
                    if(strlen($barcode->getCode()) < 7 || strlen($barcode->getCode()) > 8) {
                        throw new InvalidValueException('Ean8 barcode '.$barcode->getCode().' is not valid');
                    }
                    $type = "2";
                    break;
                case 'code39':
                    $type = "3";
                    break;
                case 'code128':
                    $type = "4";
                    break;
                case 'interleaved2of5':
                    $type = "5";
                    break;
                case 'qrcode':
                    $type = "6";
                    break;
                case 'databar':
                    $type = "7";   
                    break;
                default:
                    throw new InvalidValueException($barcode->getType().' barcode type is not valid');
            }
            
            return sprintf("3021%s250%02d%s", $type, strlen($barcode->getCode()), $barcode->getCode());
        }
        
        public function printPaymentMethod(\Inoma\Receipt\Receipt\PaymentMethod $payment) {
            $value = !$payment->getValue()?abs($this->_currentReceipt->getTotal()):$payment->getValue();
            switch(get_class($payment)) {
                case \Inoma\Receipt\Receipt\CashPayment::class:
                    return sprintf("3004%02d%s%09d", strlen("CONTANTI"), "CONTANTI", $this->_parsePrice($value));
                    break;
                case \Inoma\Receipt\Receipt\CheckPayment::class:
                    return sprintf("3004%02d%s%09d", strlen("ASSEGNO"), "ASSEGNO", $this->_parsePrice($value));
                    break;
                case \Inoma\Receipt\Receipt\CardPayment::class:
                    return sprintf("3004%02d%s%09d", strlen("CARTA"), "CARTA", $this->_parsePrice($value));
                    break;
                case \Inoma\Receipt\Receipt\MealVoucherPayment::class:
                    return sprintf("3004%02d%s%09d", strlen("BUONO PASTO"), "BUONO PASTO", $this->_parsePrice($value));
                    break;
                case \Inoma\Receipt\Receipt\CreditPayment::class:
                    return sprintf("3005%02d%s%09d", strlen("CREDITO"), "CREDITO", $this->_parsePrice($value));
                    break;
                case \Inoma\Receipt\Receipt\GenericPayment::class:
                    return sprintf("3004%02d%s%09d", strlen("GENERICO"), "GENERICO", $this->_parsePrice($value));
                    break;
            }
        }
        
        public function printOperator(\Inoma\Receipt\Items\OperatorItem $operator) {
            $string = substr('Operatore: '.$operator->getLabel(), 0, 32);
            return sprintf("30101%02d%s", strlen($string), $string);
        }
        
        public function printClient(\Inoma\Receipt\Items\ClientItem $client) {
            $cmds = [];
            if($client->getLabel()) {
                $label = substr($client->getLabel(), 0, 32);
                $cmds[] = sprintf("3010C%02d%s", strlen($label), $label);
            }
            if($client->getCode()) {
                $code = substr("Codice Cliente: ".$client->getCode(), 0, 32);
                $cmds[] = sprintf("3010C%02d%s", strlen($code), $code);
            }
            if($client->getCardCode()) {
                $cardCode = substr("Tessera: ".$client->getCardCode(), 0, 32);
                $cmds[] = sprintf("3010C%02d%s", strlen($cardCode), $cardCode);
            }
            
            if($client->getCf()) {
                $cf = substr("CF: ".$client->getCf(), 0, 32);
                $cmds[] = sprintf("3010C%02d%s", strlen($cf), $cf);
            }
            if($client->getVat()) {
                $vat = substr("P.IVA: ".$client->getVat(), 0, 32);
                $cmds[] = sprintf("3010C%02d%s", strlen($vat), $vat);
            }
            
            return implode("\n", $cmds);
        }
        
        public function printReceiptDiscount(\Inoma\Receipt\Receipt\PriceModifier $discount) {
            $desc = substr($discount->getDescription(), 0, $this->_maxDescLength);
            if($discount->getCode() == 'byPercentage') {
                return sprintf("30013%02d%s%09d", strlen($desc), $desc, $this->_parsePrice($this->_currentReceipt->getTotal(false) / 100 * $discount->getValue()));
            }
            elseif($discount->getCode() == 'byValue') {
                return sprintf("30013%02d%s%09d", strlen($desc), $desc, $this->_parsePrice($discount->getValue()));
            }
        }
        
        public function printReceiptIncrease(\Inoma\Receipt\Receipt\PriceModifier $increase) {
            $desc = substr($increase->getDescription(), 0, $this->_maxDescLength);
            if($increase->getCode() == 'byPercentage') {
                return sprintf("30012%02d%s%09d", strlen($desc), $desc, $this->_parsePrice($this->_currentReceipt->getTotal(false) / 100 * $increase->getValue()));
            }
            elseif($increase->getCode() == 'byValue') {
                return sprintf("30012%02d%s%09d", strlen($desc), $desc, $this->_parsePrice($increase->getValue()));
            }
        }
        
        public function sendCommand($command) {
            $this->log($command);
            
            echo $command."\n";
            $frame = $this->_createFrame($command);
            //var_dump($frame);
            
            $connection =  $this->_getConnection();
            
		    if($connection) {
			    if(fwrite($connection, $frame) === false) {
			        return false;
			    }
			    $res = fread($connection, 1);
			    var_dump($res);
			    fwrite($connection, "\x06");
			    $response = $this->_readFrame();
			    var_dump($response);
			    return true;
		    }
		    return false;
        }
        
        protected function _readFrame() {
            $connection =  $this->_getConnection();
            $frame = "";
            do {
                $char = fread($connection, 1);
                $frame .= $char;
            } while($char != "\x03");
            return $frame;
        }
        
        protected function _getConnection() {
            if($this->_connection === null) {
                $this->_connection = fsockopen($this->_printer->getIp(), $this->_printer->getPort(), $errno, $errstr, 10);
            }
            return $this->_connection;
        }
        
        public function cancel() {
            $desc = "ANNULLAMENTO";
            $this->sendCommand(sprintf('30018%02d%s000000000', strlen($desc), $desc));  
            $this->sendCommand('3011');    
            $this->sendCommand('3013');          
        }
        
        public function dailyFiscalReset() {
            return $this->sendCommand("2002");       
        }
        
        protected function _parsePrice($value) {
            return round($value, 2) * 100;
        }
        
        protected function _createFrame($command) {
            $stx = "\x02";
            $counter = $this->_counter % 100;
            $cnt = (string)sprintf("%02d", $counter);
            $ident = "\x30";
            $cks = $this->_checksum($cnt, $command);
            $etx = "\x03";
            $frame = $stx.$cnt.$ident.$command.$cks.$etx;
            
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
            
            foreach($receipt->getBody()->getItems() as $item) {
                $commands->append($this->printItem($item));
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
            
            if($receipt->getOperator()) {
                $commands->append($this->printOperator($receipt->getOperator()));
            }
            
            if($receipt->getClient()) {
                $commands->append($this->printClient($receipt->getClient()));
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
        
    }

?>
