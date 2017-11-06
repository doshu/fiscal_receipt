<?php
    
    namespace Inoma\Receipt;
    
    use Inoma\Receipt\Utility\Uuid;
    use Inoma\Receipt\Utility\JsonSerializeTrait;
    
    use Inoma\Receipt\Parts\{ReceiptHeader, ReceiptBody, ReceiptFooter};
    
    class Receipt implements \JsonSerializable {
    
        use JsonSerializeTrait {
            JsonSerializeTrait::jsonSerialize as _jsonSerialize;
        }
        
        protected $_receiptType = 'sales';
        
        protected $_uuid = null;
        protected $_created = null;
        protected $_operator = null;
        protected $_client = null;
        
        protected $_isFiscal = true;
        
        protected $_header;
        protected $_body;
        protected $_footer;
        
        protected $_discounts = [];
        protected $_increases = [];
        
        protected $_payments = [];
        protected $_credits = null;
        
        protected $_total = null;
        
        public function __construct() {
        
           $this->setUuid(Uuid::create());
           $this->setCreated(time());
           
           $this->_header = new ReceiptHeader();
           $this->_body = new ReceiptBody();
           $this->_footer = new ReceiptFooter();
        }
        
        public function getReceiptType() {
            return $this->_receiptType;
        }
        
        public function setUuid($uuid) {
            $this->_uuid = $uuid;
            return $this;
        }
        
        public function getUuid() {
            return $this->_uuid;
        }
        
        public function setCreated($time) {
            $this->_created = $time;
            return $this;
        }
        
        public function getCreated() {
            return $this->_created;
        }
        
        public function setOperator(\Inoma\Receipt\Items\OperatoreItem $operator) {
            $this->_operator = $operator;
            return $this;
        }
        
        public function getOperator() {
            return $this->_operator;
        }
        
        public function setClient(\Inoma\Receipt\Items\ClientItem $client) {
            $this->_client = $client;
            return $this;
        }
        
        public function getClient() {
            return $this->_client;
        }
        
        public function deleteClient() {
            return $this->_client = null;
        }
        
        public function setIsFiscal($isFiscal) {
            $this->_isFiscal = $isFiscal;
            return $this;
        }
        
        public function getIsFiscal() {
            return $this->_isFiscal;
        }
        
        public function getHeader() {
            return $this->_header;
        }
        
        public function getBody() {
            return $this->_body;
        }
        
        public function getFooter() {
            return $this->_footer;
        }
        
        public function addProduct(\Inoma\Receipt\Items\ProductItem $product) {
            $this->_body->appendItem($product);
            return $this;
        }
        
        public function deleteProduct($uuid) {
            $this->_body->deleteItem($uuid);
            return $this;
        }
        
        public function getProduct($uuid) {
            return $this->_body->getItem($uuid);
        }
        
        public function getProducts() {
            return $this->_body->getItemsByType(\Inoma\Receipt\Items\ProductItem::class);
        }
        
        public function getReturns() {
            return $this->_body->getItemsByType(\Inoma\Receipt\Items\ReturnItem::class);
        }
        
        public function addDiscount(\Inoma\Receipt\Receipt\PriceModifier $discount) {
            $this->_discounts[Uuid::create()] = $discount;
            return $this;
        }
        
        public function getDiscounts() {
            return $this->_discounts;
        }
        
        public function getDiscount($uuid) {
            return $this->_discounts[$uuid]??null;
        }
        
        public function deleteDiscount($uuid) {
            unset($this->_discounts[$uuid]);
            return $this;
        }
        
        public function addIncrease(\Inoma\Receipt\Receipt\PriceModifier $increase) {
            $this->_increases[Uuid::create()] = $increase;
            return $this;
        }
        
        public function getIncreases() {
            return $this->_increases;
        }
        
        public function getIncrease($uuid) {
            return $this->_increases[$uuid]??null;
        }
        
        public function deleteIncrease($uuid) {
            unset($this->_increases[$uuid]);
            return $this;
        }
        
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
        
        public function getPayments() {
            return $this->_payments;
        }
        
        public function getPayment($uuid) {
            return $this->getPayments()[$uuid]??null;
        }
        
        public function deletePayment($uuid) {
            unset($this->_payments[$uuid]);
            $this->_rebuildPayments();
            return $this;
        }
        
        protected function _rebuildPayments() {
            $toPay = $this->getTotal();
            foreach($this->getPayments() as &$payment) {
                $paid = $payment->getValue()??$toPay;
                $payment->setPaid($paid);
                $toPay -= $paid;
            }
        }
        
        public function getTotal($applyModifier = true) {
            if($applyModifier) {
                $this->setTotal(0);
                foreach($this->getProducts() as $product) {
                    $this->setTotal($this->_total + $product->getFinalPrice());
                }                
                foreach($this->getDiscounts() as $discount) {
                    $discount->apply($this);
                }
                foreach($this->getIncreases() as $increase) {
                    $increase->apply($this);
                }
                
                foreach($this->getReturns() as $return) {
                    $this->setTotal($this->_total - $return->getPrice());
                }
            }
            return $this->_total;
        }
        
        public function setTotal($total) {
            $this->_total = $total;
            return $this;
        }
        
        public function getPaid() {
            $toPay = $this->getTotal();
            $paid = 0;
            foreach($this->getPayments() as $payment) {
                $paid += $payment->getValue()??$toPay;
                $toPay -= $paid;
            }
            return $paid;
        }
        
        public function getChange() {
            $payments = $this->getPayments();
            $sum = ['change' => 0, 'nochange' => 0];
            $total = $this->getTotal();
            foreach($payments as $payment) {
                $sum[$payment->getHasChange()?'change':'nochange'] += $payment->getPaid();
            }
            return max(0, round($sum['change'] + $sum['nochange'] - max(0, $sum['nochange'] - $total) - $total, 2));
        }
        
        public function setCredits($credits) {
            $this->_credits = $credits;
            return $this;
        }
        
        public function getCredits() {
            return $this->_credits;
        }   
        
        
        public function jsonSerialize() {
            return ['total' => $this->getTotal(true), 'paid' => $this->getPaid(), 'change' => $this->getChange()] + $this->_jsonSerialize();
        }
        
    }
    
?>
