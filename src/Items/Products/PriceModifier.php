<?php

    namespace Inoma\Receipt\Items\Products;
    
    use Inoma\Receipt\Utility\JsonSerializeTrait;
    use Inoma\Receipt\Items\InfoAwareTrait;
    
    abstract class PriceModifier implements \JsonSerializable {
    
        use JsonSerializeTrait;
        use InfoAwareTrait;
        
        protected $_code = null;
        protected $_value = null;
        protected $_description = null;
        protected $_realValue = null;
        
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
        
        public function setRealValue($value) {
            $this->_realValue = round($value, 2);
            return $this;
        } 
        
        public function getRealValue() {
            return $this->_realValue;
        } 
        
        
    }

?>
