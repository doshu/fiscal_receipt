<?php

    namespace Inoma\Receipt\Items;
    
    use Inoma\Receipt\Utility\Uuid;
    
    class InvoiceRecipientItem extends Item {
        
        protected $_publicType = 'invoice_recipient';
        protected $_code = null;
        protected $_type = null;
        protected $_cf = null;
        protected $_vat = null;
        protected $_label = null;
        protected $_address = null;
        
        public function __construct(
            $code = null,
            $type = null, 
            $cf = null, 
            $vat = null, 
            $label = null,
            $address = null
        ) {
            parent::__construct();
            $this->setCode($code);
            $this->setType($type);
            $this->setCf($cf);
            $this->setVat($vat);
            $this->setLabel($label);
            $this->setAddress($address);
        } 
        
        /**
         * imposta il codice del cliente associato allo scontrino
         *
         * @param string $code
         * @return $this
         */
        public function setCode($code) {
            $this->_code = $code;
            return $this;
        }
        
        /**
         * ritorna il codice cliente associato allo scontrino
         *
         * @return string
         */
        public function getCode() {
            return $this->_code;
        }
        
        /**
         * imposta il tipo di destinatario 'phisical' o 'society'
         *
         * @param string $type
         * @return $this
         */
        public function setType($type) {
            $this->_type = $type;
            return $this;
        }
        
        
        /**
         * imposta il tipo di destinatario 'phisical' o 'society'
         *
         * @return string
         */
        public function getType() {
            return $this->_type;
        }
        
        /**
         * imposta il codice fiscale del cliente per scontrino parlante
         *
         * @param string $cf
         * @return $this
         */
        public function setCf($cf) {
            $this->_cf = $cf;
            return $this;
        }
        
        /**
         * ritorna il codice fiscale del cliente
         *
         * @return string
         */
        public function getCf() {
            return $this->_cf;
        }
        
        /**
         * imposta la partita iva del cliente per scontrino parlante
         *
         * @param string $vat
         * @return $this
         */
        public function setVat($vat) {
            $this->_vat = $vat;
            return $this;
        }
        
        /**
         * ritorna la partita iva del cliente
         *
         * @return string
         */
        public function getVat() {
            return $this->_vat;
        }
        
        /**
         * imposta il nominativo del cliente
         *
         * @param string $label
         * @return $this
         */
        public function setLabel($label) {
            $this->_label = $label;
            return $this;
        }
        
        /**
         * ritorna il nominativo del cliente
         *
         * @return string
         */
        public function getLabel() {
            return $this->_label;
        }
        
        /**
         * imposta l'indirizzo del cliente
         *
         * @param string $address
         * @return $this
         */
        public function setAddress($address) {
            $this->_address = $address;
            return $this;
        }
        
        /**
         * ritorna l'indirizzo del cliente
         *
         * @return string
         */
        public function getAddress() {
            return $this->_address;
        }
    }

?>
