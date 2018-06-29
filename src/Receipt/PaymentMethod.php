<?php

    namespace Inoma\Receipt\Receipt;
    
    use Inoma\Receipt\Utility\JsonSerializeTrait;
    
    abstract class PaymentMethod implements \JsonSerializable {
    
        use JsonSerializeTrait;
    
        protected $_value = null;
        protected $_code = null;
        protected $_hasChange = true;
        protected $_paid = null;
        protected $_realPaid = null;
        protected $_allowNoAmount = true;
        
        public function __construct($value = null) {
            $this->setValue($value);
        }
        
        /**
         * imposta il valore pagato con il metodo di pagamento
         *
         * @param number $value
         * @return $this
         */
        public function setValue($value) {
            $this->_value = $value;
            return $this;
        }
        
        /**
         * ritorna il valore pagato con il metodo di pagamento
         *
         * @return number
         */
        public function getValue() {
            return $this->_value;
        }
        
        /**
         * ritorna il codice del pagamento
         *
         * @return string
         */
        public function getCode() {
            return $this->_code;
        }
        
        /**
         * ritorna se il metodo di pagamento da diritto al resto
         *
         * @return boolean
         */
        public function getHasChange() {
            return $this->_hasChange;
        }
        
        /**
         * ritorna se il metodo di pagamento può essere utilizzato senza inserire l'ammontare
         *
         * @return boolean
         */
        public function getAllowNoAmount() {
            return $this->_allowNoAmount;
        }
        
        /**
         * metodo utilizzato dalla classe scontrino per impostare il valore reale del pagamento
         * dopo aver calcolato i pagamenti totali
         *
         * @param number $value
         * @return number
         */
        public function setPaid($value) {
            $this->_paid = $value;
            return $this;
        }
        
        /**
         * ritorna il reale pagato con il metodo calcolato dalla classe scontrino
         *
         * @return void
         */
        public function getPaid() {
            return $this->_paid;
        }
        
        
        /**
         * metodo utilizzato dalla classe scontrino per impostare il reale pagato 
         * tenendo conto del totale dello scontrino
         *
         * @param number $value
         * @return number
         */
        public function setRealPaid($value) {
            $this->_realPaid = $value;
            return $this;
        }
        
        /**
         * ritorna il reale pagato con il metodo calcolato dalla classe scontrino
         * tenendo conto del totale dello scontrino
         *
         * @return void
         */
        public function getRealPaid() {
            return $this->_realPaid;
        }
        
    }

?>
