<?php

    namespace Inoma\Receipt\Protocols;
    
    use Exceptions\NotImplementedException;
    use Exceptions\InvalidValueException;
    use Mike42\Escpos;

    class EscPosProtocol extends BaseProtocol implements Protocol {
    
        protected $_ip = null;
        protected $_port = null;
        protected $_type = null;
        
        private $_counter = 0;
        
        protected $_connection = null;
        
        protected function _printLines($lines) {
            if(!is_array($lines)) {
                $lines = [$lines];
            }
            foreach($lines as $line) {
                $this->_getConnection()->text($line);
                $this->_getConnection()->feed();
            }
        }
        
        public function beforePrintReceipt(\Inoma\Receipt\Receipt $receipt, \Inoma\Receipt\Protocols\CommandsCollection $commandsCollection) {
            $this->_getConnection()->feed(2);
        }
        
        public function beforePrintInvoice(\Inoma\Receipt\Receipt $receipt, \Inoma\Receipt\Protocols\CommandsCollection $commandsCollection, $printCopy) {
            $this->_getConnection()->feed(2);
            $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem("SCONTRINO", ['style' => 'double']));
        }
        
        public function afterPrintReceipt(\Inoma\Receipt\Receipt $receipt, \Inoma\Receipt\Protocols\CommandsCollection $commandsCollection) {
            $this->_getConnection()->feed(8);
            $this->_getConnection()->cut();
            $this->openCashDrawer();
            $this->_getConnection()->close();
        }
        
        public function afterPrintInvoice(\Inoma\Receipt\Receipt $receipt, \Inoma\Receipt\Protocols\CommandsCollection $commandsCollection) {
            $this->_getConnection()->feed(8);
            $this->_getConnection()->cut();
            $this->openCashDrawer();
            $this->_getConnection()->close();
        }
        
        public function printProduct(\Inoma\Receipt\Items\ProductItem $product) {
            $lines = [];
            $lines[] = sprintf("%sX %s: %s", $product->getQty(), $this->s($product->getDescription()), $this->_parsePrice($product->getPrice() * $product->getQty()));
            
            foreach($product->getDiscounts() as $discount) {
                $desc = substr($this->s($discount->getDescription()), 0, $this->_printer->getMaxLineLength());
                switch($discount->getCode()) {
                    case 'byPercentage':
                        $lines[] = sprintf("%s %s", $desc, $this->_parsePrice($product->getPrice() * $product->getQty() / 100 * $discount->getValue()));
                        break;
                    case 'byValue':
                        $lines[] = sprintf("%s %s", $desc, $this->_parsePrice($discount->getValue()));
                        break;
                }
            }
            foreach($product->getIncreases() as $increase) {
                $desc = substr($this->s($increase->getDescription()), 0, $this->_printer->getMaxLineLength());
                switch($increase->getCode()) {
                    case 'byPercentage':
                        $lines[] = sprintf("%s %s", $desc, $this->_parsePrice($product->getPrice() * $product->getQty() / 100 * $increase->getValue()));
                        break;
                    case 'byValue':
                        $lines[] = sprintf("%s %s", $desc, $this->_parsePrice($increase->getValue()));
                        break;
                }
            }
            
            $this->_printLines($lines);
        }
        
        
        public function printReturn(\Inoma\Receipt\Items\ReturnItem $return) {
            $line = sprintf("%sX %s: %s", $product->getQty(), $this->s($product->getDescription()), $this->_parsePrice($product->getPrice() * $product->getQty()));
            $this->_printLines($line);
            
        }
        
        public function printString(\Inoma\Receipt\Items\StringItem $string) {
            $options = $string->getOptions();
            
            $string = substr($this->s($string->getValue()), 0, $this->_printer->getMaxLineLength());
            if(isset($options['style'])) {
                $styles = explode('|', $options['style']);
                foreach($styles as $style) {
                    switch($style) {
                        case 'normal':
                            $this->_getConnection()->selectPrintMode(Escpos\Printer::MODE_FONT_A);
                            break;
                        case 'bold':
                            $this->_getConnection()->selectPrintMode(Escpos\Printer::MODE_EMPHASIZED);
                            break;
                        case 'double':
                            $this->_getConnection()->selectPrintMode(Escpos\Printer::MODE_DOUBLE_HEIGHT);
                            break;
                        case 'underline':
                            $this->_getConnection()->selectPrintMode(Escpos\Printer::MODE_UNDERLINE);
                            break;
                        case 'highlight':
                            $this->_getConnection()->setReverseColors(true);
                            break;
                    }
                }
                
                if(in_array('center', $styles)) {
                    $this->_getConnection()->setJustification(Escpos\Printer::JUSTIFY_CENTER);
                }
            }
            
            $this->_printLines($string);
            $this->_getConnection()->setJustification(Escpos\Printer::JUSTIFY_LEFT);
            $this->_getConnection()->selectPrintMode(Escpos\Printer::MODE_FONT_A);
            $this->_getConnection()->setReverseColors(false);
        }
        
        public function printNumericCode(\Inoma\Receipt\Items\NumericCodeItem $numericCode) {
            if(!ctype_digit($numericCode->getValue())) {
                throw new InvalidValueException('Numeric code '.$numericCode->getValue().' is not valid');
            }
            return $this->printString(new \Inoma\Receipt\Items\StringItem($numericCode->getValue()));
        }
        
        public function printSubtotal(\Inoma\Receipt\Items\SubtotalItem $subtotal) {
            throw new NotImplementedException('EscPos Protocol do not imlements subtotal');
        }
        
        public function printBarcode(\Inoma\Receipt\Items\BarcodeItem $barcode) {
            switch(strtolower($barcode->getType())) {
                case 'upca':
                    $this->_getConnection()->barcode($barcode->getCode(), BARCODE_UPCA);
                    break;
                case 'upce':
                    $this->_getConnection()->barcode($barcode->getCode(), BARCODE_UPCE);
                    break;
                case 'itf':
                    $this->_getConnection()->barcode($barcode->getCode(), BARCODE_ITF);
                    break;
                case 'codabar':
                    $this->_getConnection()->barcode($barcode->getCode(), BARCODE_CODABAR);
                    break;
                case 'ean13':
                    $this->_getConnection()->barcode($barcode->getCode(), BARCODE_JAN13);
                    break;
                case 'ean8':
                    $this->_getConnection()->barcode($barcode->getCode(), BARCODE_JAN8);
                    break;
                case 'code39':
                    $this->_getConnection()->barcode($barcode->getCode(), BARCODE_CODE39);
                    break;
                case 'code128':
                    throw new NotImplementedException($barcode->getType().' barcode type is not implemented');
                    break;
                case 'interleaved2of5':
                    throw new NotImplementedException($barcode->getType().' barcode type is not implemented');
                    break;
                case 'qrcode':
                    $this->_getConnection()->qrCode($barcode->getCode());
                    break;
                case 'databar':
                    throw new NotImplementedException($barcode->getType().' barcode type is not implemented');
                    break;
                default:
                    throw new InvalidValueException($barcode->getType().' barcode type is not valid');
            }
        }
        
        public function printPaymentMethod(\Inoma\Receipt\Receipt\PaymentMethod $payment) {
            $value = $payment->getPaid();
            $label = strtoupper($this->_getPaymentLabel($payment->getCode()));
            $this->_printLines(sprintf('%s: %s', $label, $this->_parsePrice($value)));
        }
        
        public function printOperator(\Inoma\Receipt\Items\OperatorItem $operator) {
            $string = substr('Operatore: '.$operator->getLabel(), 0, 32);
            $this->_printLines($string);
        }
        
        public function printClient(\Inoma\Receipt\Items\ClientItem $client) {
            $lines = [];
            if($client->getLabel()) {
                $lines[] = substr($this->s($client->getLabel()), 0, 32);
            }
            if($client->getCode()) {
                $lines[] = substr("Codice Cliente: ".$client->getCode(), 0, 32);
            }
            if($client->getCardCode()) {
                $lines[] = substr("Tessera: ".$client->getCardCode(), 0, 32);
            }
            
            if($client->getCf()) {
                $lines[] = substr("CF: ".$client->getCf(), 0, 32);
            }
            if($client->getVat()) {
                $lines[] = substr("P.IVA: ".$client->getVat(), 0, 32);
            }
            
            $this->_printLines($lines);
        }
        
        public function printReceiptDiscount(\Inoma\Receipt\Receipt\PriceModifier $discount) {
            $desc = substr($this->s($discount->getDescription()), 0, $this->_printer->getMaxLineLength());
            if($discount->getCode() == 'byPercentage') {
                $line = sprintf("%s: %s", $desc, $this->_parsePrice($this->_currentReceipt->getTotal(false) / 100 * $discount->getValue()));
            }
            elseif($discount->getCode() == 'byValue') {
                $line = sprintf("%s: %s", $desc, $this->_parsePrice($discount->getValue()));
            }
            $this->_printLines($line);
        }
        
        public function printReceiptIncrease(\Inoma\Receipt\Receipt\PriceModifier $increase) {
            $desc = substr($this->s($increase->getDescription()), 0, $this->_printer->getMaxLineLength());
            if($increase->getCode() == 'byPercentage') {
                $line = sprintf("%s: %s", $desc, $this->_parsePrice($this->_currentReceipt->getTotal(false) / 100 * $increase->getValue()));
            }
            elseif($increase->getCode() == 'byValue') {
                $line = sprintf("%s: %s", $desc, $this->_parsePrice($increase->getValue()));
            }
            $this->_printLines($line);
        }
        
        public function printImage(\Inoma\Receipt\Items\ImageItem $image) {
            //$this->_getConnection()->setJustification(Escpos\Printer::JUSTIFY_CENTER);
            $escposimage = \Mike42\Escpos\EscposImage::load($image->getImage(), false);
            $this->_getConnection()->bitImage($escposimage);
            //$this->_getConnection()->setJustification(Escpos\Printer::JUSTIFY_LEFT);
            $this->_getConnection()->feed(1);
        }
        
        protected function _getConnection() {
            if($this->_connection === null) {
                $connector = new Escpos\PrintConnectors\NetworkPrintConnector($this->_printer->getIp(), $this->_printer->getPort(), 10);
                $this->_connection = new EscPos\Printer($connector);
            }
            return $this->_connection;
        }
        
        public function openCashDrawer() {
            $this->_getConnection()->pulse();        
        }
        
        public function cancel() {
            $this->_getConnection()->feed(8);
            $this->_getConnection()->cut();         
        }
        
        public function dailyFiscalReset() {
            throw new NotImplementedException('EscPos Protocol do not implements daily fiscal reset');
        }
        
        public function sendCommand($command) {
            throw new NotImplementedException('EscPos Protocol do not implements direct commands');
        }
        
        protected function _parsePrice($value) {
            $fmt = new \NumberFormatter( 'de_DE', \NumberFormatter::DECIMAL );
            return $fmt->format($value).' E';
        }
        
        
        public function printReceipt(\Inoma\Receipt\Receipt $receipt) {
            $this->log('--- start receipt ---');
            
            $this->_currentReceipt = $receipt;
            
            try {
                $commands = new CommandsCollection();
                
                $this->beforePrintReceipt($receipt, $commands);
                
                foreach($receipt->getHeader()->getItems() as $item) {
                    $this->printItem($item);
                }
                
                foreach($receipt->getBody()->getItems() as $item) {
                    $this->printItem($item);
                }
                
                foreach($receipt->getFooter()->getItems() as $item) {
                    $this->printItem($item);
                }
                
                foreach($receipt->getDiscounts() as $discount) {
                    $this->printReceiptDiscount($discount);
                }
                
                foreach($receipt->getIncreases() as $increase) {
                    $this->printReceiptIncrease($increase);
                }
                
                if($receipt->getIsFiscal()) {
                    $this->printItem(new \Inoma\Receipt\Items\StringItem("TOTALE: ".$this->_parsePrice($receipt->getTotal()), ['style' => 'double']));
                }
                
                foreach($receipt->getPayments() as $payment) {
                    $this->printPaymentMethod($payment);
                }
                
                if($receipt->getIsFiscal()) {
                    $this->printItem(new \Inoma\Receipt\Items\StringItem("RESTO: ".$this->_parsePrice($receipt->getChange()), ['style' => 'double']));
                }
                
                if($receipt->getOperator()) {
                    $this->printOperator($receipt->getOperator());
                }
                
                if($receipt->getClient()) {
                    $this->printClient($receipt->getClient());
                }
                
                $this->_currentReceipt = null;
                $this->log('--- end receipt ---');
                $this->afterPrintReceipt($receipt, $commands);
                return true;
            }
            catch(\Throwable $e) {
                $this->_currentReceipt = null;
                $this->log($e->getMessage());
                $this->log('--- end receipt ---');
                return false;
            }
            
        }
        
        
        public function printInvoice(\Inoma\Receipt\Receipt $receipt, $printCopy = false) {
            
            $this->log('--- start invoice ---');
            
            $receipt->setIsFiscal(false);
            
            $this->_currentReceipt = $receipt;
            $commands = new CommandsCollection();
            try {
            
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
                        [$product->getQty(), $this->s($product->getDescription()), $this->_parsePrice($product->getFinalPrice()), $product->getTax()]
                    );
                    
                    $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem($productString));
                    $totalPieces += $product->getQty();
                }
                
                $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem('IMPORTO EURO '.$this->_parsePrice($receipt->getTotal())));
                $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem("TOTALE PEZZI ".$totalPieces));
                
                foreach($receipt->getPayments() as $payment) {
                    $label = $this->_getPaymentLabel($payment->getCode());
                    $paymentString = $tf->format(
                        ['70%', '30%'],
                        [strtoupper($this->s($label)), $this->_parsePrice($payment->getPaid())]
                    );
                    $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem($paymentString));
                }
                $changeString = $tf->format(
                    ['70%', '30%'],
                    ['RESTO', $this->_parsePrice($receipt->getChange())]
                );
                $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem($changeString));
                
                $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem(str_repeat('-', $this->_printer->getMaxLineLength())));
                
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
                        [$this->_parsePrice($total), $this->_parsePrice($total / (1 + $tax/100)), $this->_parsePrice($total - ($total / (1 + $tax/100)))]
                    );
                    $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem($taxSummaryStrings));
                }
                
                $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem(str_repeat('-', $this->_printer->getMaxLineLength())));
                
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
                
                $this->beforePrintInvoice($receipt, $commands, $printCopy);
                
                
                foreach($receipt->getHeader()->getItems() as $item) {
                    $this->printItem($item);
                }
                
                foreach($receipt->getFooter()->getItems() as $item) {
                    $this->printItem($item);
                }
                
                $this->_currentReceipt = null;
                $this->log('--- end invoice ---');
                $this->afterPrintInvoice($receipt, $commands);
                
                return true;
            }
            catch(\Throwable $e) {
                $this->_currentReceipt = null;
                $this->log($e->getMessage());
                $this->log('--- end invoice ---');
                return false;
            }
            
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
