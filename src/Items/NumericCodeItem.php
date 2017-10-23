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
        protected $_value = null;
        
        public function __construct($value) {
            parent::__construct();
            $this->setValue($value);
        }    
        
        public function setValue($value) {
            $this->_value = $value;
            return $this;
        }
        
        public function getValue() {
            return $this->_value;
        }
    }

?>
