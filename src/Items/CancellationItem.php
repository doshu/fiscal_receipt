<?php

    namespace Inoma\Receipt\Items;
    
    /**
     * CancellationCodeItem
     * 
     * Storno di un elemento
     *
     * TODO
     */
    class CancellationCodeItem extends Items {
        
        protected $_publicType = 'cancellation';
        
        protected $_sku = null;
        protected $_price = null;
        protected $_qty = null;
        protected $_description = null;
        
        public function __construct($sku = null, $price = null, $qty = 0, $description = null) {
            parent::__construct();
            $this
                ->setSku($sku)
                ->setPrice($price)
                ->setQty($qty)
                ->setDescription($description);
        }
        
        public function setSku($sku) {
            $this->_sku = $sku;
            return $this;
        }
        
        public function getSku() {
            return $this->_sku;
        }
        
        public function setQty($qty) {
            $this->_qty = $qty;
            return $this;
        }
        
        public function getQty() {
            return $this->_qty;
        }
        
        public function setPrice($price) {
            $this->_price = $price;
            return $this;
        }
        
        public function getPrice() {
            return $this->_price;
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
