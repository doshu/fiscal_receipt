<?php

    namespace Inoma\Receipt\Items;
    
    use Inoma\Receipt\Utility\Uuid;
    
    class OperatorItem extends Item {
        
        protected $_publicType = 'operator';
        protected $_code = null;
        protected $_label = null;
        
        public function __construct($code, $label) {
            parent::__construct();
            $this->setCode($code);
            $this->setLabel($label);
        } 
        
        /**
         * imposta il codice operatore
         *
         * @param string $code
         * @return $this
         */
        public function setCode($code) {
            $this->_code = $code;
            return $this;
        }
        
        /**
         * ritorna il codice operatore
         *
         * @return tring
         */
        public function getCode() {
            return $this->_code;
        }
        
        /**
         * imposta il nominativo dell'operatore
         *
         * @param string $label
         * @return $this
         */
        public function setLabel($label) {
            $this->_label = $label;
            return $this;
        }
        
        /**
         * ritorna il nominativo dell'operatore
         *
         * @return string
         */
        public function getLabel() {
            return $this->_label;
        }
    }

?>
