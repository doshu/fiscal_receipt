<?php

    namespace Inoma\Receipt\Receipt;
    
    use Inoma\Receipt\Utility\JsonSerializeTrait;
    use Inoma\Receipt\Items\InfoAwareTrait;
    
    abstract class PriceModifier implements \JsonSerializable {
    
        use JsonSerializeTrait;
        use InfoAwareTrait;
        
        protected $_code = null;
        protected $_value = null;
        protected $_description = null;
        protected $_realValue = null;
        protected $_round = null;
        
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
        
        /**
         * setRound
         *
         * imposta l'arrotondamento dello sconto calcolato
         * se il valore round è positivo, l'arrotondamento viene fatto per eccesso, 
         * altrimenti per difetto
         * e round è null, non viene fatto arrotondamento
         * 
         * @param float $round
         * @return $this
         */
        public function setRound($round) {
            $this->_round = $round;
            return $this;
        }
        
        public function getRound() {
            return $this->_round;
        }
        
        public function round($value) {
            if(!$this->_round) {
                return $value;
            }
            $type = $this->_round > 0?'ceil':'floor';
            $round = abs($this->_round);
            $rounded = intval($value / $round) * $round;
            if($type == 'floor') {
                return $rounded;
            }
            return round($value, 2) == round($rounded, 2)?$rounded:($rounded + $round);
        }
        
    }

?>
