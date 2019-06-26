<?php

    namespace Inoma\Receipt\Protocols;
    
    use Exceptions\NotImplementedException;
    use Exceptions\InvalidValueException;

    class CustomTelematicProtocol extends CustomProtocol implements Protocol {
    
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
                //$commandsCollection->prepend(sprintf('71023%05s', $receipt->getInvoiceNumber()));
                $commandsCollection->prepend('7102300000');
                $commandsCollection->append('3011');
                $commandsCollection->append('3013');
            }
            else {
                $commandsCollection->prepend(sprintf('400110%09s', 0));
                $commandsCollection->append('4002');
            }
            
        }
        
        public function printProduct(\Inoma\Receipt\Items\ProductItem $product) {
            
            $taxRates = $this->_readTaxRates();
            if(!$taxRates) {
                return false;
            }
            $taxID = array_search($product->getTax(), $taxRates);
            if($taxID === false) {
                $this->log('Tax Rate '.$product->getTax().' not configured');
                return false;
            }
            $cmds = [];
            $desc = substr(sprintf("%sX %s", $product->getQty(), $this->s($product->getDescription())), 0, $this->_maxDescLength);
            $cmds[] = sprintf(
                "33011%09s%03s%02s%s%09s%s", 
                number_format($product->getQty(), 3, "", ""),
                $taxID,
                strlen($desc), 
                $desc, 
                $this->_parsePrice($product->getPrice()),
                $taxID
            );
            
            foreach($product->getDiscounts() as $discount) {
                $desc = substr($this->s($discount->getDescription()), 0, $this->_maxDescLength);
                switch($discount->getCode()) {
                    case 'byPercentage':
                        $cmds[] = sprintf(
                            "33013%09s%03s%02s%s%09s%s", 
                            number_format(1, 3, "", ""),
                            $taxID,
                            strlen($desc), 
                            $desc, 
                            $this->_parsePrice($product->getPrice() * $product->getQty() / 100 * $discount->getValue()),
                            $taxID
                        );
                        break;
                    case 'byValue':
                        $cmds[] = sprintf(
                            "33013%09s%03s%02s%s%09s%s", 
                            number_format(1, 3, "", ""),
                            $taxID,
                            strlen($desc), 
                            $desc, 
                            $this->_parsePrice($discount->getValue() * $product->getQty()),
                            $taxID
                        );
                        break;
                }
            }
            foreach($product->getIncreases() as $increase) {
                $desc = substr($this->s($increase->getDescription()), 0, $this->_maxDescLength);
                switch($increase->getCode()) {
                    case 'byPercentage':
                        $cmds[] = sprintf(
                            "33012%09s%03s%02s%s%09s%s", 
                            number_format(1, 3, "", ""),
                            $taxID,
                            strlen($desc), 
                            $desc, 
                            $this->_parsePrice($product->getPrice() * $product->getQty() / 100 * $increase->getValue()),
                            $taxID
                        );
                        break;
                    case 'byValue':
                        $cmds[] = sprintf(
                            "33012%09s%03s%02s%s%09s%s", 
                            number_format(1, 3, "", ""),
                            $taxID,
                            strlen($desc), 
                            $desc, 
                            $this->_parsePrice($increase->getValue() * $product->getQty()),
                            $taxID
                        );
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
                $cmds[] = sprintf("3010F%02s%s", strlen($cf), $cf);
            }
            if($client->getVat()) {
                $vat = substr("P.IVA: ".$client->getVat(), 0, 32);
                $cmds[] = sprintf("3010F%02s%s", strlen($vat), $vat);
            }
            
            return implode("\n", $cmds);
        }
        
        public function printInvoiceRecipient(\Inoma\Receipt\Items\InvoiceRecipientItem $invoiceRecipient) {
            $cmds = [];
            if($invoiceRecipient->getLabel()) {
                $label = substr($this->s($invoiceRecipient->getLabel()), 0, 32);
                $cmds[] = sprintf("3010C%02s%s", strlen($label), $label);
            }
            if($invoiceRecipient->getFullAddress()) {
                $cmds[] = sprintf("3010C%02s%s", strlen('Indirizzo: '), 'Indirizzo: ');
                foreach(explode(',', $invoiceRecipient->getFullAddress()) as $addressPart) {
                    $text = trim($this->s($addressPart));
                    $cmds[] = sprintf("3010C%02s%s", strlen($text), $text);
                }
            }
            if($invoiceRecipient->getCf()) {
                $cf = substr("CF: ".$invoiceRecipient->getCf(), 0, 32);
                $cmds[] = sprintf("3010F%02s%s", strlen($cf), $cf);
            }
            if($invoiceRecipient->getVat()) {
                $vat = substr("P.IVA: ".$invoiceRecipient->getVat(), 0, 32);
                $cmds[] = sprintf("3010F%02s%s", strlen($vat), $vat);
            }
            
            return implode("\n", $cmds);
        }
        
        
        public function printReceiptDiscount(\Inoma\Receipt\Receipt\PriceModifier $discount) {
            $desc = substr($this->s($discount->getDescription()), 0, $this->_maxDescLength);
            return sprintf("30013%02s%s%09s", strlen($desc), $desc, $this->_parsePrice($discount->getRealValue()));
        }
        
        public function printReceiptIncrease(\Inoma\Receipt\Receipt\PriceModifier $increase) {
            $desc = substr($this->s($increase->getDescription()), 0, $this->_maxDescLength);
            return sprintf("30012%02s%s%09s", strlen($desc), $desc, $this->_parsePrice($increase->getRealValue()));
        }
        
        public function dailyFiscalReset() {
            return $this->sendCommand("2002");       
        }
        
        public function _readTaxRates() {
            $getRates = $this->sendCommand("7012");
            if($getRates) {
                $rates = $this->_getLastCommandResponse();
                $rates = unpack('a4CMD/a*DATA', $rates);
                $rates = unpack('A4A/A4B/A4C/A4D/A4E/A4F', $rates['DATA']);
                $parsedRates = [];
                $id = 1;
                foreach($rates as $rate) {
                    $parsedRates[$id++] = round(intval($rate) / 100, 2);
                }
                return $parsedRates;
            }
            return false;
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
            foreach($receipt->getFooter()->getItems() as $item) {
                $commands->append($this->printItem($item));
            }
            
            $receipt->getTotal(); //trigger discounts and increases calculation
            
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
            $this->_currentReceipt = $receipt;
            
            $commands = new CommandsCollection();
            
            $invoiceNumber = $receipt->getInvoiceNumber();
            $invoiceDate = $receipt->getInvoiceDate();
            
            if(!$this->_printer->supportsNoChangePayment()) {
                $noChangeAddition = round($receipt->getPaid() - $receipt->getTotal() - $receipt->getChange(), 2);
                if($noChangeAddition > 0) {
                    $receipt->addIncrease(new \Inoma\Receipt\Receipt\IncreaseByValue($noChangeAddition, "Varie"));
                }
            }
            
            if(!$printCopy) {
            
                foreach($receipt->getHeader()->getItems() as $item) {
                    $commands->append($this->printItem($item));
                }
                
                $aggregator = new \Inoma\Receipt\Items\ItemsAggregator();
                foreach($aggregator->aggregate($receipt->getBody()->getItems()) as $item) {
                    $commands->append($this->printItem($item));
                }
                foreach($receipt->getFooter()->getItems() as $item) {
                    $commands->append($this->printItem($item));
                }
                
                $receipt->getTotal(); //trigger discounts and increases calculation
                
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
                
                if($receipt->getInvoiceRecipient()) {
                    $commands->append($this->printInvoiceRecipient($receipt->getInvoiceRecipient()));
                }
                
            }
            else {
                $receipt->setIsFiscal(false);
            
                $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem("COPIA FATTURA ".$invoiceNumber, ['style' => 'double']));
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
                $aggregator = new \Inoma\Receipt\Items\ItemsAggregator();
                
                foreach($aggregator->aggregate($receipt->getProducts()) as $product) {
                    $productString = $tf->format(
                        ['20%', '40%', '20%', '20%'],
                        [$product->getQty(), $this->s($product->getDescription()), number_format($product->getFinalPrice(), 2), $product->getTax()]
                    );
                    
                    $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem($productString));
                    $totalPieces += $product->getQty();
                }
                
                foreach($receipt->getIncreases() as $increase) {
                    $increaseString = $tf->format(
                        ['70%', '30%'],
                        [$this->s($increase->getDescription()), number_format($increase->getRealValue(), 2)]
                    );
                    
                    $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem($increaseString));
                }
                
                foreach($receipt->getDiscounts() as $discount) {
                    $discountString = $tf->format(
                        ['70%', '30%'],
                        [$this->s($discount->getDescription()), number_format($discount->getRealValue(), 2)]
                    );
                    
                    $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem($discountString));
                }
                
                
                $receipt->getHeader()->appendItem(new \Inoma\Receipt\Items\StringItem('IMPORTO EURO '.$this->_parsePrice($receipt->getTotal())));
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
                
                $taxSummary = $this->_getTaxSummary($receipt);
                
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
                    if($invoiceRecipient->getFullAddress()) {
                        $receipt->getFooter()->appendItem(new \Inoma\Receipt\Items\StringItem('Indirizzo: '));
                        foreach(explode(',', $invoiceRecipient->getFullAddress()) as $addressPart) {
                            $receipt->getFooter()->appendItem(new \Inoma\Receipt\Items\StringItem(trim($this->s($addressPart))));
                        }
                    }
                }
                $receipt->getFooter()->appendItem(new \Inoma\Receipt\Items\StringItem('COPIA CONFORME A QUANTO'));
                $receipt->getFooter()->appendItem(new \Inoma\Receipt\Items\StringItem('TRASMESSO TELEMATICAMENTE'));
                
                foreach($receipt->getHeader()->getItems() as $item) {
                    $commands->append($this->printItem($item));
                }
                
                foreach($receipt->getFooter()->getItems() as $item) {
                    $commands->append($this->printItem($item));
                }
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
        
        
    }

?>
