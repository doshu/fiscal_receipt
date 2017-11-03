<?php

    namespace Inoma\Receipt\Items\Products;
    
    use Inoma\Receipt\Utility\JsonSerializeTrait;
    
    abstract class PriceModifier implements \JsonSerializable {
    
        use JsonSerializeTrait;
        
        protected $_code = null;
        protected $_value = null;
        protected $_description = null;
        
        public function __construct($value = null, $description = null) {
            $this->setValue($value);
            $this->setDescription($description);
        }
        
        public function getCode() {
            return $this->_code;
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
