<?php

    namespace Inoma\Receipt\Items;
    
    use Inoma\Receipt\Utility\Uuid;
    
    class BarcodeItem extends Items {
        
        protected $_publicType = 'barcode';
        protected $_code = null;
        protected $_type = null;
        
        public function __construct($code, $type) {
            parent::__construct();
            $this->setCode($code);
            $this->setType($type);
        } 
        
        public function setCode($code) {
            $this->_code = $code;
            return $this;
        }
        
        public function getCode() {
            return $this->_code;
        }
        
        public function setType($type) {
            $this->_type = $type;
            return $this;
        }
        
        public function getType() {
            return $this->_type;
        }
    }

?>
