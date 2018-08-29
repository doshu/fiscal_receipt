<?php
    
    namespace Inoma\Receipt;
    
    use Inoma\Receipt\Utility\Uuid;
    use Inoma\Receipt\Utility\JsonSerializeTrait;
    use Inoma\Receipt\Items\InfoAwareTrait;
    
    use Inoma\Receipt\Parts\{ReceiptHeader, ReceiptBody, ReceiptFooter};
    
    /**
     * Receipt
     * 
     * classe che rappresenta lo scontrino emesso dalla cassa
     *
     */
    class Receipt implements \JsonSerializable {
    
        use JsonSerializeTrait {
            JsonSerializeTrait::jsonSerialize as _jsonSerialize;
        }
        
        use InfoAwareTrait;
        
        /**
         * @var string tipologia di scontrino. Default "sales" indica uno scontrino fiscale di vendita
         * 
         */
        protected $_receiptType = 'sales';
        
        /**
         * @var string identificativo univoco dello scontrino
         * 
         */
        protected $_uuid = null;
        
        /**
         * @var int timestamp di creazione dello scontrino
         * 
         */
        protected $_created = null;
        
        /**
         * @var \Inoma\Receipt\Items\OperatorItem operatore che ha emesso lo scontrino
         * 
         */
        protected $_operator = null;
        
        /**
         * @var \Inoma\Receipt\Items\ClientItem cliente a cui è stato emesso lo scontrino
         * 
         */
        protected $_client = null;
        
        /**
         * @var \Inoma\Receipt\Items\InvoiceRecipientItem cliente destinatario della fattura
         */
        protected $_invoiceRecipient = null;
        
        /**
         * @var string numero della fattura
         */
        protected $_invoiceNumber = null;
        
        /**
         * @var string data della fattura
         */
        protected $_invoiceDate = null;
        
        /**
         * @var boolean indica se lo scontrino è fiscale o no
         * 
         */
        protected $_isFiscal = true;
        
        /**
         * @var \Inoma\Receipt\Parts\ReceiptHeader $_header Parte superiore dello scontrino
         * @var \Inoma\Receipt\Parts\ReceiptBody $_body Parte centrale dello scontrino contenente i prodotti
         * @var \Inoma\Receipt\Parts\ReceiptFooter $_footer Parte finale dello scontrino
         */
        protected $_header;
        protected $_body;
        protected $_footer;
        
        /**
         * @var array $_discounts contiene gli sconti sul totale dello scontrino
         * @var array $_increases contiene le maggiorazioni sul totale dello scontrino
         */
        protected $_discounts = [];
        protected $_increases = [];
        
        /**
         * @var array $_payments contiene i metodi di pagamento dello scontrino
         */
        protected $_payments = [];
        
        /**
         * @var int numero di crediti che lo scontrino fa acquisire
         */
        protected $_credits = null;
        
        /**
         * @var float totale dello scontrino
         */
        protected $_total = null;
        protected $_intermediateTotal = null;
        
        public function __construct() {
        
           $this->setUuid(Uuid::create());
           $this->setCreated(time());
           
           $this->_header = new ReceiptHeader();
           $this->_body = new ReceiptBody();
           $this->_footer = new ReceiptFooter();
        }
        
        /**
         * ritorna il tipo di scontrino
         * 
         * @return string
         */
        public function getReceiptType() {
            return $this->_receiptType;
        }
        
        /**
         * imposta l'id univoco dello scontrino
         *
         * @param string $uuid
         * @return $this
         */
        public function setUuid($uuid) {
            $this->_uuid = $uuid;
            return $this;
        }
        
        /**
         * ritorna l'id univoco dello scontrino
         *
         * @return string
         */
        public function getUuid() {
            return $this->_uuid;
        }
        
        /**
         * imposta il timestamp della creazione dello scontrino
         *
         * @param int $time
         * @return $this
         */
        public function setCreated($time) {
            $this->_created = $time;
            return $this;
        }
        
        /**
         * ritorna il timestamp della creazione dello scontrino
         *
         * @return int
         */
        public function getCreated() {
            return $this->_created;
        }
        
        /**
         * imposta l'operatore che ha emesso lo scontrino
         *
         * @param \Inoma\Receipt\Items\OperatorItem $operator
         * @return $this
         */
        public function setOperator(\Inoma\Receipt\Items\OperatorItem $operator) {
            $this->_operator = $operator;
            return $this;
        }
        
        /**
         * ritorna l'operatore che ha emesso lo scontrino
         *
         * @return \Inoma\Receipt\Items\OperatorItem
         */
        public function getOperator() {
            return $this->_operator;
        }
        
        /**
         * imposta il cliente a cui è stato emesso lo scontrino
         *
         * @param \Inoma\Receipt\Items\ClientItem $client
         * @return $this
         */
        public function setClient(\Inoma\Receipt\Items\ClientItem $client) {
            $this->_client = $client;
            return $this;
        }
        
        /**
         * ritorna il cliente destinatario della fattura
         *
         * @return \Inoma\Receipt\Items\InvoiceRecipientItem
         */
        public function getInvoiceRecipient() {
            return $this->_invoiceRecipient;
        }
        
        
        /**
         * imposta il cliente destinatario della fattura
         *
         * @param \Inoma\Receipt\Items\InvoiceRecipientItem $recipient
         * @return $this
         */
        public function setInvoiceRecipient(\Inoma\Receipt\Items\InvoiceRecipientItem $recipient) {
            $this->_invoiceRecipient = $recipient;
            return $this;
        }
        
        /**
         * ritorna il numero della fattura
         *
         * @return string
         */
        public function getInvoiceNumber() {
            return $this->_invoiceNumber;
        }
        
        
        /**
         * imposta il numero della fattura
         *
         * @param string $number
         * @return $this
         */
        public function setInvoiceNumber($number) {
            $this->_invoiceNumber = $number;
            return $this;
        }
        
        
        /**
         * ritorna il numero della fattura
         *
         * @return string
         */
        public function getInvoiceDate() {
            return $this->_invoiceDate;
        }
        
        
        /**
         * imposta il numero della fattura
         *
         * @param string $number
         * @return $this
         */
        public function setInvoiceDate(\DateTimeInterface $date) {
            $this->_invoiceDate = $date;
            return $this;
        }
        
        
        /**
         * ritorna il cliente a cui è stato emesso lo scontrino
         *
         * @return \Inoma\Receipt\Items\ClientItem
         */
        public function getClient() {
            return $this->_client;
        }
        
        
        /**
         * rimuove il cliente impostato per lo scontrino
         *
         * @return $this
         */
        public function deleteClient() {
            $this->_client = null;
            return $this;
        }
        
        /**
         * rimuove il cliente destinatario della fattura
         *
         * @return $this
         */
        public function deleteInvoiceRecipient() {
            $this->_invoiceRecipient = null;
            return $this;
        }
        
        /**
         * imposta se lo scontrino è fiscale o no
         *
         * @param boolean $isFiscal
         * @return $this
         */
        public function setIsFiscal($isFiscal) {
            $this->_isFiscal = $isFiscal;
            return $this;
        }
        
        /**
         * ritorna se lo scontrino è fiscale o no
         *
         * @return boolean
         */
        public function getIsFiscal() {
            return $this->_isFiscal;
        }
        
        /**
         * ritorna la parte superiore dello scontrino
         *
         * @return \Inoma\Receipt\Parts\ReceiptHeader
         */
        public function getHeader() {
            return $this->_header;
        }
        
        /**
         * ritorna la parte centrale dello scontrino contenente i prodotti
         *
         * @return \Inoma\Receipt\Parts\ReceiptBody
         */
        public function getBody() {
            return $this->_body;
        }
        
        /**
         * ritorna la parte inferiore dello scontrino
         *
         * @return \Inoma\Receipt\Parts\ReceiptFooter
         */
        public function getFooter() {
            return $this->_footer;
        }
        
        /**
         * inserisce un prodotto o un reso allo scontrino
         *
         * @param \Inoma\Receipt\Items\ProductItem $product
         * @return $this
         */
        public function addProduct(\Inoma\Receipt\Items\ProductItem $product) {
            $this->_body->appendItem($product);
            return $this;
        }
        
        /**
         * rimuove un prodotto o un reso dallo scontrino
         *
         * @param string $uuid
         * @return $this
         */
        public function deleteProduct($uuid) {
            $this->_body->deleteItem($uuid);
            return $this;
        }
        
        /**
         * rimuove tutti i prodotti da uno scontrino
         *
         * @param string $uuid
         * @return $this
         */
        public function clearProducts() {
            $products = $this->getProducts();
            foreach($products as $product) {
                $this->_body->deleteItem($product->getUuid());
            }
            return $this;
        }
        
        /**
         * rimuove tutti i prodotti da uno scontrino
         *
         * @param string $uuid
         * @return $this
         */
        public function clearReturns() {
            $returns = $this->getReturns();
            foreach($returns as $return) {
                $this->_body->deleteItem($return->getUuid());
            }
            return $this;
        }
        
        /**
         * ritorna un determinato prodotto o reso dello scontrino
         *
         * @param string $uuid
         * @return \Inoma\Receipt\Items\ProductItem
         */
        public function getProduct($uuid) {
            return $this->_body->getItem($uuid);
        }
        
        /**
         * ritorna tutti i prodotti dello scontrino
         *
         * @return \Inoma\Receipt\Items\ProductItem[]
         */
        public function getProducts() {
            return $this->_body->getItemsByType(\Inoma\Receipt\Items\ProductItem::class);
        }
        
        /**
         * ritorna tutti i resi dello scontrino
         *
         * @return \Inoma\Receipt\Items\ReturnItem[]
         */
        public function getReturns() {
            return $this->_body->getItemsByType(\Inoma\Receipt\Items\ReturnItem::class);
        }
        
        /**
         * aggiunge uno sconto al totale dello scontrino
         *
         * @param \Inoma\Receipt\Receipt\PriceModifier $discount
         * @return $this
         */
        public function addDiscount(\Inoma\Receipt\Receipt\PriceModifier $discount) {
            $this->_discounts[Uuid::create()] = $discount;
            return $this;
        }
        
        /**
         * ritorna tutti gli sconti applicati al totale dello scontrino
         *
         * @return \Inoma\Receipt\Receipt\PriceModifier[]
         */
        public function getDiscounts() {
            return $this->_discounts;
        }
        
        /**
         * ritorna uno sconto applicato al totale dello scontrino
         *
         * @param string $uuid
         * @return \Inoma\Receipt\Receipt\PriceModifier|null
         */
        public function getDiscount($uuid) {
            return $this->_discounts[$uuid]??null;
        }
        
        /**
         * rimuove uno sconto applicato al totale dello scontrino
         *
         * @param string $uuid
         * @return $this
         */
        public function deleteDiscount($uuid) {
            unset($this->_discounts[$uuid]);
            return $this;
        }
        
        
        /**
         * rimuove tutti gli sconti applicati al totale dello scontrino
         *
         * @return $this
         */
        public function clearDiscounts() {
            $this->_discounts = [];
            return $this;
        }
        
        
        /**
         * aggiunge una maggiorazione al totale dello scontrino
         *
         * @param \Inoma\Receipt\Receipt\PriceModifier $increase
         * @return $this
         */
        public function addIncrease(\Inoma\Receipt\Receipt\PriceModifier $increase) {
            $this->_increases[Uuid::create()] = $increase;
            return $this;
        }
        
        /**
         * ritorna tutte le maggiorazioni applicate al totale dello scontrino
         *
         * @param string $uuid
         * @return \Inoma\Receipt\Receipt\PriceModifier|null
         */
        public function getIncreases() {
            return $this->_increases;
        }
        
        /**
         * rimuove tutte le maggiorazioni applicate al totale dello scontrino
         *
         * @return $this
         */
        public function clearIncreases() {
            $this->_increases = [];
            return $this;
        }
        
        
        /**
         * ritorna una maggiorazione applicata al totale dello scontrino
         *
         * @param string $uuid
         * @return \Inoma\Receipt\Receipt\PriceModifier|null
         */
        public function getIncrease($uuid) {
            return $this->_increases[$uuid]??null;
        }
        
        /**
         * rimuove una maggiorazione applicata al totale dello scontrino
         *
         * @param string $uuid
         * @return $this
         */
        public function deleteIncrease($uuid) {
            unset($this->_increases[$uuid]);
            return $this;
        }
        
        
        /**
         * aggiunge un metodo di pagamento allo scontrino
         *
         * @param \Inoma\Receipt\Receipt\PaymentMethod $payment
         * @return $this
         */
        public function addPayment(\Inoma\Receipt\Receipt\PaymentMethod $payment) {
            $payments = $this->_payments;
            $hasNoAmountPayment = false;
            $last = end($payments);
            if($last && empty($last->getValue())) {
                $hasNoAmountPayment = true;
            }
            if(empty($payment->getValue()) && $hasNoAmountPayment) {
                //only one payment is allowed without amount
                throw new Exceptions\NotAllowedItemException();
            }
            
            if(empty($payment->getValue()) || !$hasNoAmountPayment) {
                //no amount payment always on bottom
                $this->_payments[Uuid::create()] = $payment;
            }
            else {
                $count = count($payments);
                $newPayments = [];
                $i = 0;
                foreach($payments as $uuid => $_payment) {
                    if($i++ < $count - 1) {
                        $newPayments[$uuid] = $_payment;
                    } 
                    else {
                        $newPayments[Uuid::create()] = $payment;
                        $newPayments[$uuid] = $_payment;
                    }
                }
                $this->_payments = $newPayments;
            }
            
            $this->_rebuildPayments();
            
            return $this;
        }
        
        /**
         * ritorna tutti i metodi di pagamento applicati allo scontrino
         *
         * @return \Inoma\Receipt\Receipt\PaymentMethod[]
         */
        public function getPayments() {
            return $this->_payments;
        }
        
        
        /**
         * ritorna un metodo di pagamento applicato allo scontrino
         *
         * @param string $uuid
         * @return \Inoma\Receipt\Receipt\PaymentMethod
         */
        public function getPayment($uuid) {
            return $this->getPayments()[$uuid]??null;
        }
        
        /**
         * rimuove un metodo di pagamento applicato allo scontrino
         *
         * @param string $uuid
         * @return $this
         */
        public function deletePayment($uuid) {
            unset($this->_payments[$uuid]);
            $this->_rebuildPayments();
            return $this;
        }
        
        
        /**
         * rimuove tutti i metodo di pagamento applicati allo scontrino
         *
         * @param string $uuid
         * @return $this
         */
        public function clearPayments() {
            $this->_payments = [];
            $this->_rebuildPayments();
            return $this;
        }
        
        
        protected function _rebuildPayments() {
            $total = $this->getTotal();
            $toPay = $total;
            foreach($this->getPayments() as &$payment) {
                $paid = $payment->getValue()??($total > 0?max(0, $toPay):min(0, $toPay));
                $payment->setPaid($paid);
                $payment->setRealPaid($total > 0?min($paid, $toPay):max($paid, $toPay));
                $toPay -= $paid;
            }
        }
        
        
        public function recalculatePaymentsTotal() {
            $this->_rebuildPayments();
        }
        
        
        /**
         * setIntermediateTotal
         *
         * imposta un totale intermedio utilizzato durante il calcolo del prezzo finale
         * 
         * @param decimal $price
         * @return $this
         */
        public function setIntermediateTotal($total) {
            $this->_intermediateTotal = round($total, 2);
            return $this;
        }
        
        /**
         * getIntermediateTotal
         *
         * ritorna un totale intermedio utilizzato durante il calcolo del prezzo finale
         * 
         * @return decimal
         */
        public function getIntermediateTotal() {
            return $this->_intermediateTotal;
        }
        
        /**
         * ritorna il totale dello scontrino
         * il parametro applyModifier permette ad eventi esterni di recuperare il totale
         * senza eseguire il calcolo di sconti o resi
         *
         * @param boolean $applyModifier = true
         * @return void
         */
        public function getTotal($applyModifier = true) {
        
            $this->setTotal(0);
            foreach($this->getProducts() as $product) {
                $this->setTotal($this->_total + $product->getFinalPrice());
            } 
            $this->setIntermediateTotal($this->_total);
            if($applyModifier) {
            
                foreach($this->getIncreases() as $increase) {
                    $increase->apply($this);
                }
                
                foreach($this->getDiscounts() as $discount) {
                    $discount->apply($this);
                }
                
                foreach($this->getReturns() as $return) {
                    $this->setIntermediateTotal($this->getIntermediateTotal() - $return->getFinalPrice());
                }
            }
            $this->setTotal($this->getIntermediateTotal());
            return $this->_total;
        }
        
        /**
         * imposta il totale dello scontrino
         *
         * @param float $total
         * @return $this
         */
        public function setTotal($total) {
            $this->_total = round($total, 2);
            return $this;
        }
        
        /**
         * ritorna il totale pagato nello scontrino
         *
         * @return float
         */
        public function getPaid() {
            $toPay = $this->getTotal();
            $paid = 0;
            foreach($this->getPayments() as $payment) {
                $paymentPay = $payment->getValue()??$toPay;
                $paid += $paymentPay;
                $toPay -= $paymentPay;
            }
            return round($paid, 2);
        }
        
        /**
         * ritorna il resto dello scontrino
         *
         * @return float
         */
        public function getChange() {
            $payments = $this->getPayments();
            $sum = ['change' => 0, 'nochange' => 0];
            $total = $this->getTotal();
            foreach($payments as $payment) {
                $sum[$payment->getHasChange()?'change':'nochange'] += $payment->getPaid();
            }
            return max(0, round($sum['change'] + $sum['nochange'] - max(0, $sum['nochange'] - $total) - $total, 2));
        }
        
        /**
         * imposta il numero di crediti che lo scontrino fa acquisire
         *
         * @param int $credits
         * @return $this
         */
        public function setCredits($credits) {
            $this->_credits = $credits;
            return $this;
        }
        
        /**
         * ritorna il numero di crediti che lo scontrino fa acquisire
         *
         * @return int
         */
        public function getCredits() {
            return $this->_credits;
        }   
        
        
        public function jsonSerialize() {
            return ['total' => $this->getTotal(true), 'paid' => $this->getPaid(), 'change' => $this->getChange()] + $this->_jsonSerialize();
        }
        
        
        public function __clone() {
            $this->setUuid(Uuid::create());
            foreach($this as $key => $val) {
                if (is_object($val) || (is_array($val))) {
                    $this->{$key} = unserialize(serialize($val));
                }
            }       
        }
    }
    
?>
