<?php

    namespace Inoma\Receipt\Receipt;
    
    use Inoma\Receipt\Utility\JsonSerializeTrait;
    
    abstract class PriceModifier {
    
        use JsonSerializeTrait;
        
        protected $_code = null;
        protected $_value = null;
        protected $_description = null;
        
        public function __construct($value = null, $description = null) {
            $this->setValue($value);
            $this->setDescription($description);
        }
        
        public function setValue($value) {
            $this->_value = $value;
            return $this;
        }
        
        public function getValue() {
            return $this->_value;
        }
        
        public function setDescription($description) {
            $this->_description = $description;
            return $this;
        }
        
        public function getDescription() {
            return $this->_description;
        }
        
    }

?>
