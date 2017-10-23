<?php

    namespace Inoma\Receipt\Items;
    
    use Inoma\Receipt\Utility\Uuid;
    
    class ProductItem extends Item {
        
        protected $_publicType = 'product';
        protected $_sku = null;
        protected $_price = null;
        protected $_qty = null;
        protected $_description = null;
        
        protected $_discounts = [];
        protected $_increases = [];
        
        protected $_finalPrice = null;
        
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
        
        public function addDiscount(\Inoma\Receipt\Items\Products\PriceModifier $discount) {
            $this->_discounts[Uuid::create()] = $discount;
        }
        
        public function getDiscounts() {
            return $this->_discounts;
        }
        
        public function getDiscount($uuid) {
            return $this->_discounts[$uuid]??null;
        }
        
        public function deleteDiscount($uuid) {
            unset($this->_discounts[$uuid]);
        }
        
        public function addIncrease(\Inoma\Receipt\Items\Products\PriceModifier $increase) {
            $this->_increases[Uuid::create()] = $increase;
        }
        
        public function getIncreases() {
            return $this->_increases;
        }
        
        public function getIncrease($uuid) {
            return $this->_increases[$uuid]??null;
        }
        
        public function deleteIncrease($uuid) {
            unset($this->_increases[$uuid]);
        }
        
        public function getFinalPrice($applyModifier = true) {
            if($applyModifier) {
                $this->setFinalPrice($this->getPrice());
                foreach($this->getDiscounts() as $discount) {
                    $discount->apply($this);
                }
                foreach($this->getIncreases() as $increase) {
                    $increase->apply($this);
                }
            }
            return $this->_finalPrice;
        }
        
        public function setFinalPrice($finalPrice) {
            $this->_finalPrice = $finalPrice;
            return $this;
        }
    }

?>
