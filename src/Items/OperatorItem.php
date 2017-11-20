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
        
        public function setCode($code) {
            $this->_code = $code;
            return $this;
        }
        
        public function getCode() {
            return $this->_code;
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
