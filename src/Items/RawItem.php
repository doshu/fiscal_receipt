<?php

    namespace Inoma\Receipt\Items;
    
    /**
     * NumericCodeItem
     * 
     * stampa di un codice numerico
     *
     */
    class RawItem extends Item {
        
        protected $_value = null;
        protected $_publicType = 'string'; 
        
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
