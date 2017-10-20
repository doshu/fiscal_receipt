<?php

    namespace Inoma\Receipt\Items;
    
    /**
     * NumericCodeItem
     * 
     * stampa di un codice numerico
     *
     */
    class NumericCodeItem {
        
        protected $_publicType = 'numeric_code';
        protected $_code = null;
        
        public function __construct($code) {
            parent::__construct();
            $this->setCode($code);
        }    
        
        public function setCode($code) {
            $this->_code = $code;
            return $this;
        }
        
        public function getCode() {
            return $this->_code;
        }
    }

?>
