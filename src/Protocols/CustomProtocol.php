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
        
        public function beforePrintInvoice(\Inoma\Receipt\Receipt $receipt, \Inoma\Receipt\Protocols\CommandsCollection $commandsCollection, $printCopy) {
            
            $commandsCollection->prepend('4002');
            if(!$printCopy) {
                $commandsCollection->prepend(sprintf('400121%09s', $this->_parsePrice($receipt->getTotal())));
                $commandsCollection->append('4006');
            }
            else {
                $commandsCollection->prepend(sprintf('400110%09s', 0));
                $commandsCollection->append('4002');
            }
            
        }
        
        public function afterPrintReceipt(\Inoma\Receipt\Receipt $receipt, \Inoma\Receipt\Protocols\CommandsCollection $commandsCollection) {
            $this->openCashDrawer();
        }
        
        public function afterPrintInvoice(\Inoma\Receipt\Receipt $receipt, \Inoma\Receipt\Protocols\CommandsCollection $commandsCollection) {
            $this->openCashDrawer();
        }
        
        public function printProduct(\Inoma\Receipt\Items\ProductItem $product) {
            $cmds = [];
            $desc = substr(sprintf("%sX %s", $product->getQty(), $this->s($product->getDescription())), 0, $this->_maxDescLength);
            $cmds[] = sprintf("30011%02s%s%09s", strlen($desc), $desc, $this->_parsePrice($product->getPrice() * $product->getQty()));
            
            foreach($product->getDiscounts() as $discount) {
                $desc = substr($this->s($discount->getDescription()), 0, $this->_maxDescLength);
                switch($discount->getCode()) {
                    case 'byPercentage':
                        $cmds[] = sprintf("30013%02s%s%09s", strlen($desc), $desc, $this->_parsePrice($product->getPrice() * $product->getQty() / 100 * $discount->getValue()));
                        break;
                    case 'byValue':
                        $cmds[] = sprintf("30013%02s%s%09s", strlen($desc), $desc, $this->_parsePrice($discount->getValue()));
                        break;
                }
            }
            foreach($product->getIncreases() as $increase) {
                $desc = substr($this->s($increase->getDescription()), 0, $this->_maxDescLength);
                switch($increase->getCode()) {
                    case 'byPercentage':
                        $cmds[] = sprintf("30012%02s%s%09s", strlen($desc), $desc, $this->_parsePrice($product->getPrice() * $product->getQty() / 100 * $increase->getValue()));
                        break;
                    case 'byValue':
                        $cmds[] = sprintf("30012%02s%s%09s", strlen($desc), $desc, $this->_parsePrice($increase->getValue()));
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
            $desc = substr(sprintf("%sX %s", $return->getQty(), $this->s($return->getDescription())), 0, $this->_maxDescLength);
            if($this->_currentReceipt->getTotal() < 0 && !$this->_printer->supportsNegativeTotal()) {
                return sprintf("30011%02s%s%09s", strlen($desc), $desc, $this->_parsePrice($return->getPrice() * $return->getQty()));
            }
            return sprintf("30019%02s%s%09s", strlen($desc), $desc, $this->_parsePrice($return->getPrice() * $return->getQty()));
        }
        
        public function printString(\Inoma\Receipt\Items\StringItem $string) {
            if(!$this->_currentReceipt->getIsFiscal()) {
                $options = $string->getOptions();
                $string = substr($this->s($string->getValue()), 0, $this->_printer->getMaxLineLength());
                if(isset($options['style'])) {
                    $styles = explode('|', $options['style']);
                    foreach($styles as $_style) {
                        switch($_style) {
                            case 'normal':
                                $style = 1;
                                break;
                            case 'bold':
                                $style = 2;
                                break;
                            case 'double':
                                $style = 4;
                                break;
                            case 'italic':
                                $style = 6;
                                break;
                            case 'invoice_total':
                                $style = 'F';
                                break;
                        }
                    }
                    if(in_array('center', $styles)) {
                        $pad = $this->_printer->getMaxLineLength() - strlen($string);
                        $string = str_repeat(' ', (int)$pad / 2).$string.str_repeat(' ', ceil($pad / 2));
                    }
                }
                else {
                    $style = 1;
                }
                
                return sprintf("4003%d%02s%s000000000", $style, strlen($string), $string);
            }
            $string = substr($string->getValue(), 0, 32);
            return sprintf("30021%02s%s", strlen($string), $string);
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
            
            return sprintf("3021%s250%02s%s", $type, strlen($barcode->getCode()), $barcode->getCode());
        }
        
        public function printPaymentMethod(\Inoma\Receipt\Receipt\PaymentMethod $payment) {
            //$value = !$payment->getValue()?abs($this->_currentReceipt->getTotal()):$payment->getValue();
            $value = $payment->getPaid();
            switch(get_class($payment)) {
                case \Inoma\Receipt\Receipt\CashPayment::class:
                    return sprintf("3004%02s%s%09s", strlen("CONTANTI"), "CONTANTI", $this->_parsePrice($value));
                    break;
                case \Inoma\Receipt\Receipt\CheckPayment::class:
                    return sprintf("3004%02s%s%09s", strlen("ASSEGNO"), "ASSEGNO", $this->_parsePrice($value));
                    break;
                case \Inoma\Receipt\Receipt\CardPayment::class:
                    return sprintf("3004%02s%s%09s", strlen("CARTA"), "CARTA", $this->_parsePrice($value));
                    break;
                case \Inoma\Receipt\Receipt\MealVoucherPayment::class:
                    return sprintf("3004%02s%s%09s", strlen("BUONO PASTO"), "BUONO PASTO", $this->_parsePrice($value));
                    break;
                case \Inoma\Receipt\Receipt\CreditPayment::class:
                    return sprintf("3004%02s%s%09s", strlen("CREDITO"), "CREDITO", $this->_parsePrice($value));
                    break;
                case \Inoma\Receipt\Receipt\GenericPayment::class:
                    return sprintf("3004%02s%s%09s", strlen("GENERICO"), "GENERICO", $this->_parsePrice($value));
                    break;
            }
        }
        
        public function printOperator(\Inoma\Receipt\Items\OperatorItem $operator) {
            $string = substr('Operatore: '.$operator->getLabel(), 0, 32);
            return sprintf("30101%02s%s", strlen($string), $string);
        }
        
        public function printClient(\Inoma\Receipt\Items\ClientItem $client) {
            $cmds = [];
            if($client->getLabel()) {
                $label = substr($this->s($client->getLabel()), 0, 32);
                $cmds[] = sprintf("3010C%02s%s", strlen($label), $label);
            }
            if($client->getCode()) {
                $code = substr("Codice Cliente: ".$client->getCode(), 0, 32);
                $cmds[] = sprintf("3010C%02s%s", strlen($code), $code);
            }
            if($client->getCardCode()) {
                $cardCode = substr("Tessera: ".$client->getCardCode(), 0, 32);
                $cmds[] = sprintf("3010C%02s%s", strlen($cardCode), $cardCode);
            }
            
            if($client->getCf()) {
                $cf = substr("CF: ".$client->getCf(), 0, 32);
                $cmds[] = sprintf("3010C%02s%s", strlen($cf), $cf);
            }
            if($client->getVat()) {
                $vat = substr("P.IVA: ".$client->getVat(), 0, 32);
                $cmds[] = sprintf("3010C%02s%s", strlen($vat), $vat);
            }
            
            return implode("\n", $cmds);
        }
        
        
        public function printImage(\Inoma\Receipt\Items\ImageItem $image) {
            return null;
        }
        
        
        public function printReceiptDiscount(\Inoma\Receipt\Receipt\PriceModifier $discount) {
            $desc = substr($this->s($discount->getDescription()), 0, $this->_maxDescLength);
            if($discount->getCode() == 'byPercentage') {
                return sprintf("30013%02s%s%09s", strlen($desc), $desc, $this->_parsePrice($this->_currentReceipt->getTotal(false) / 100 * $discount->getValue()));
            }
            elseif($discount->getCode() == 'byValue') {
                return sprintf("30013%02s%s%09s", strlen($desc), $desc, $this->_parsePrice($discount->getValue()));
            }
        }
        
        public function printReceiptIncrease(\Inoma\Receipt\Receipt\PriceModifier $increase) {
            $desc = substr($this->s($increase->getDescription()), 0, $this->_maxDescLength);
            if($increase->getCode() == 'byPercentage') {
                return sprintf("30012%02s%s%09s", strlen($desc), $desc, $this->_parsePrice($this->_currentReceipt->getTotal(false) / 100 * $increase->getValue()));
            }
            elseif($increase->getCode() == 'byValue') {
                return sprintf("30012%02s%s%09s", strlen($desc), $desc, $this->_parsePrice($increase->getValue()));
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
			    $response = $this->_readFrame();
			    return $res == "\x06";
		    }
		    return false;
        }
        
        protected function _readFrame() {
            $connection =  $this->_getConnection();
            $frame = "";
            $timeout = 10;
            do {
                $start = time();
                $char = fread($connection, 1);
                $frame .= $char;
                $timeout -= (time() - $start);
            } while($char != "\x03" && $timeout);
            return $frame;
        }
        
        protected function _getConnection() {
            if($this->_connection === null) {
                $this->_connection = fsockopen($this->_printer->getIp(), $this->_printer->getPort(), $errno, $errstr, 10);
                if($this->_connection) {
                    stream_set_timeout($this->_connection, 10);
                }
            }
            return $this->_connection;
        }
        
        public function openCashDrawer() {
            return $this->sendCommand("70081");          
        }
        
        public function cancel() {
            $desc = "ANNULLAMENTO";
            $this->sendCommand(sprintf('30018%02s%s000000000', strlen($desc), $desc));  
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
        
        
        public function printInvoice(\Inoma\Receipt\Receipt $receipt, $printCopy = false) {
            
            $this->log('--- start invoice ---');
            
            $receipt->setIsFiscal(false);
            
            $this->_currentReceipt = $receipt;
            
            $commands = new CommandsCollection();
            
            $invoiceNumber = $receipt->getInvoiceNumber();
            $invoiceDate = $receipt->getInvoiceDate();
            
            if(!$printCopy) {
                $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem("FATTURA ".$invoiceNumber, ['style' => 'double']));
            }
            else {
                $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem("COPIA FATTURA ".$invoiceNumber, ['style' => 'double']));
            }
            $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem($invoiceDate->format('d/m/Y')));
            
            $tf = new \splitbrain\phpcli\TableFormatter();
            $tf->setMaxWidth($this->_printer->getMaxLineLength());
            $tf->setBorder(' '); // nice border between colmns
            $header = $tf->format(
                ['20%', '40%', '20%', '20%'],
                ['Qta', 'Desc', 'Prezzo', 'IVA']
            );
            
            $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem($header));
            
            $totalPieces = 0;
            foreach($receipt->getProducts() as $product) {
                $productString = $tf->format(
                    ['20%', '40%', '20%', '20%'],
                    [$product->getQty(), $this->s($product->getDescription()), number_format($product->getFinalPrice(), 2), $product->getTax()]
                );
                
                $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem($productString));
                $totalPieces += $product->getQty();
            }
            
            if(!$printCopy) {
                $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\RawItem(sprintf('40031%02s%s%09s', strlen('IMPORTO EURO'), 'IMPORTO EURO', $this->_parsePrice($receipt->getTotal()))));
            }
            else {
                $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem('IMPORTO EURO '.$this->_parsePrice($receipt->getTotal())));
            }
            $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem("TOTALE PEZZI ".$totalPieces));
            
            foreach($receipt->getPayments() as $payment) {
                $label = $this->_getPaymentLabel($payment->getCode());
                $paymentString = $tf->format(
                    ['70%', '30%'],
                    [strtoupper($this->s($label)), number_format($payment->getPaid(), 2)]
                );
                $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem($paymentString));
            }
            $changeString = $tf->format(
                ['70%', '30%'],
                ['RESTO', number_format($receipt->getChange(), 2)]
            );
            $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem($changeString));
            
            $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem(str_repeat('-', 32)));
            
            $taxSummary = [];
            foreach($receipt->getProducts() as $product) {
                if($product->getTax() !== null) {
                    if(!isset($taxSummary[$product->getTax()])) {
                        $taxSummary[$product->getTax()] = 0;
                    }
                    $taxSummary[$product->getTax()] += $product->getFinalPrice();
                }
            }
            
            $taxSummaryHeader = $tf->format(
                ['*', '30%', '30%'],
                ['CORRISP', 'IMPONIB', 'IVA']
            );
            
            $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem($taxSummaryHeader));
            foreach($taxSummary as $tax => $total) {
                $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem('IVA '.$tax.'%'));
                $taxSummaryStrings = $tf->format(
                    ['*', '30%', '30%'],
                    [number_format($total, 2), number_format($total / (1 + $tax/100), 2), number_format($total - ($total / (1 + $tax/100)), 2)]
                );
                $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem($taxSummaryStrings));
            }
            
            $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem(str_repeat('-', 32)));
            
            $receipt->getFooter()->appendItem(new \Inoma\Receipt\Items\StringItem('DATI DESTINATARIO'));
            $invoiceRecipient = $receipt->getInvoiceRecipient();
            if($invoiceRecipient) {
                $receipt->getFooter()->appendItem(new \Inoma\Receipt\Items\StringItem($this->s($invoiceRecipient->getLabel()), ['style' => 'double']));
                if($invoiceRecipient->getVat()) {
                    $receipt->getFooter()->appendItem(new \Inoma\Receipt\Items\StringItem('P.IVA: '.$invoiceRecipient->getVat()));
                }
                if($invoiceRecipient->getCf()) {
                    $receipt->getFooter()->appendItem(new \Inoma\Receipt\Items\StringItem('C.F: '.$invoiceRecipient->getCf()));
                }
                if($invoiceRecipient->getAddress()) {
                    $receipt->getFooter()->appendItem(new \Inoma\Receipt\Items\StringItem('Indirizzo: '));
                    foreach(explode(',', $invoiceRecipient->getAddress()) as $addressPart) {
                        $receipt->getFooter()->appendItem(new \Inoma\Receipt\Items\StringItem(trim($this->s($addressPart))));
                    }
                }
            }
            
            foreach($receipt->getHeader()->getItems() as $item) {
                $commands->append($this->printItem($item));
            }
            
            foreach($receipt->getFooter()->getItems() as $item) {
                $commands->append($this->printItem($item));
            }
            
            
            $this->beforePrintInvoice($receipt, $commands, $printCopy);
            
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
            $this->log('--- end invoice ---');
            
            $this->afterPrintInvoice($receipt, $commands);
            
            return true;
            
        }
        
        protected function _getPaymentLabel($code) {
        
            switch($code) {
                case 'card':
                    return 'Carta';
                    break;
                case 'cash':
                    return 'Contanti';
                    break;
                case 'check':
                    return 'Assegno';
                    break;
                case 'credit':
                    return 'Credito';
                    break;
                case 'meal_voucher':
                    return 'Buono Pasto';
                    break;
                case 'meal_voucher_with_change':
                    return 'Buono Pasto';
                    break;
                default:
                    return 'Generico';
            }
        }
        
    }

?>
