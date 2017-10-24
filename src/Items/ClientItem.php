<?php

    namespace Inoma\Receipt\Items;
    
    use Inoma\Receipt\Utility\Uuid;
    
    class ClientItem extends Items {
        
        protected $_publicType = 'client';
        protected $_code = null;
        protected $_cardCode = null;
        protected $_cf = null;
        protected $_vat = null;
        protected $_label = null;
        
        public function __construct($code = null, $cardCode = null, $cf = null, $vat = null, $label = null) {
            parent::__construct();
            $this->setCode($code);
            $this->setCardCode($cardCode);
            $this->setCf($cf);
            $this->setVat($vat);
            $this->setLabel($label);
        } 
        
        public function setCode($code) {
            $this->_code = $code;
            return $this;
        }
        
        public function getCode() {
            return $this->_code;
        }
        
        public function setCardCode($code) {
            $this->_cardCode = $code;
            return $this;
        }
        
        public function getCardCode() {
            return $this->_cardCode;
        }
        
        public function setCf($cf) {
            $this->_cf = $cf;
            return $this;
        }
        
        public function getCf() {
            return $this->_cf;
        }
        
        public function setVat($vat) {
            $this->_vat = $vat;
            return $this;
        }
        
        public function getVat() {
            return $this->_vat;
        }
        
        public function setLabel($label) {
            $this->_label = $label;
            return $this;
        }
        
        public function getLabel() {
            return $this->_label;
        }
    }

?>
