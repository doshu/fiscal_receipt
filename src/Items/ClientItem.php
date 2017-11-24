<?php

    namespace Inoma\Receipt\Items;
    
    use Inoma\Receipt\Utility\Uuid;
    
    class ClientItem extends Item {
        
        protected $_publicType = 'client';
        protected $_code = null;
        protected $_cardCode = null;
        protected $_cf = null;
        protected $_vat = null;
        protected $_label = null;
        
        public function __construct(
            $code = null, 
            $cardCode = null, 
            $cf = null, 
            $vat = null, 
            $label = null
        ) {
            parent::__construct();
            $this->setCode($code);
            $this->setCardCode($cardCode);
            $this->setCf($cf);
            $this->setVat($vat);
            $this->setLabel($label);
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
         * imposta il codice della tessera associata allo scontrino
         *
         * @param string $code
         * @return $this
         */
        public function setCardCode($code) {
            $this->_cardCode = $code;
            return $this;
        }
        
        
        /**
         * ritorna il codice della tessera associata allo scontrino
         *
         * @return string
         */
        public function getCardCode() {
            return $this->_cardCode;
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
    }

?>
