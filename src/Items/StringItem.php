<?php

    namespace Inoma\Receipt\Items;
    
    /**
     * StringItem
     * 
     * stampa di un codice alfanumerico
     *
     */
    class StringItem extends Item {
        
        protected $_publicType = 'string';
        protected $_value = null;
        protected $_options = [];
        
        public function __construct($value, $options = []) {
            parent::__construct();
            $this->setValue($value);
            $this->setOptions($options);
        }    
        
        public function setValue($value) {
            $this->_value = $value;
            return $this;
        }
        
        public function getValue() {
            return $this->_value;
        }
        
        public function setOptions($options) {
            $this->_options = $options;
            return $this;
        }
        
        public function getOptions() {
            return $this->_options;
        }
        
    }

?>
