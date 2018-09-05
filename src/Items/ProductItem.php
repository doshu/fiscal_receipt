<?php

    namespace Inoma\Receipt\Items;
    
    use Inoma\Receipt\Utility\Uuid;
    use Inoma\Receipt\Utility\JsonSerializeTrait;
    use Inoma\Receipt\Items\InfoAwareTrait;
    
    class ProductItem extends Item {
    
        use JsonSerializeTrait {
            JsonSerializeTrait::jsonSerialize as _jsonSerialize;
        }
        use InfoAwareTrait;
        
        protected $_publicType = 'product';
        protected $_sku = null;
        protected $_price = null;
        protected $_intermediatePrice = null;
        protected $_qty = null;
        protected $_description = null;
        protected $_tax = null;
        
        protected $_discounts = [];
        protected $_increases = [];
        
        protected $_finalPrice = null;
        
        public function __construct($sku = null, $price = null, $qty = 0, $description = null, $tax = null) {
            parent::__construct();
            $this
                ->setSku($sku)
                ->setPrice($price)
                ->setQty($qty)
                ->setDescription($description)
                ->setTax($tax);
        }
        
        /**
         * imposta il codice prodotto
         *
         * @param string $sku
         * @return $this
         */
        public function setSku($sku) {
            $this->_sku = $sku;
            return $this;
        }
        
        /**
         * ritorna il codice prodotto
         *
         * @return string
         */
        public function getSku() {
            return $this->_sku;
        }
        
        /**
         * imposta la quantità di prodotto
         *
         * @param number $qty
         * @return $this
         */
        public function setQty($qty) {
            $this->_qty = $qty;
            return $this;
        }
        
        /**
         * ritorna la quantità di prodotto
         *
         * @return $this
         */
        public function getQty() {
            return $this->_qty;
        }
        
        /**
         * imposta il prezzo unitario del prodotto
         *
         * @param number $price
         * @return $this
         */
        public function setPrice($price) {
            $this->_price = $price;
            return $this;
        }
        
        /**
         * ritorna il prezzo unitario del prodotto
         *
         * @return number
         */
        public function getPrice() {
            return $this->_price;
        }
        
        /**
         * imposta la descrizione del prodotto
         *
         * @param string $description
         * @return $this
         */
        public function setDescription($description) {
            $this->_description = $description;
            return $this;
        }
        
        /**
         * ritorna la descrizione del prodotto
         *
         * @return string
         */
        public function getDescription() {
            return $this->_description;
        }
        
        /**
         * aggiunge uno sconto al prodotto
         *
         * @param \Inoma\Receipt\Items\Products\PriceModifier $discount
         * @return $this
         */
        public function addDiscount(\Inoma\Receipt\Receipt\PriceModifier $discount) {
            $this->_discounts[Uuid::create()] = $discount;
            return $this;
        }
        
        /**
         * ritorna tutti gli sconti applicati al prodotto
         *
         * @return \Inoma\Receipt\Items\Products\PriceModifier[]
         */
        public function getDiscounts() {
            return $this->_discounts;
        }
        
        /**
         * ritorna uno sconto applicato al prodotto
         *
         * @param string $uuid
         * @return \Inoma\Receipt\Items\Products\PriceModifier
         */
        public function getDiscount($uuid) {
            return $this->_discounts[$uuid]??null;
        }
        
        /**
         * rimuove uno sconto dal prodotto
         *
         * @param unknown $uuid
         * @return $this
         */
        public function deleteDiscount($uuid) {
            unset($this->_discounts[$uuid]);
            return $this;
        }
        
        /**
         * aggiunge una maggiorazione al prodotto
         *
         * @param \Inoma\Receipt\Items\Products\PriceModifier $increase
         * @return $this
         */
        public function addIncrease(\Inoma\Receipt\Receipt\PriceModifier $increase) {
            $this->_increases[Uuid::create()] = $increase;
            return $this;
        }
        
        /**
         * ritorna tutti le maggiorazioni associate al prodotto
         *
         * @return \Inoma\Receipt\Items\Products\PriceModifier[]
         */
        public function getIncreases() {
            return $this->_increases;
        }
        
        /**
         * ritorna una maggiorazione associata al prodotto
         *
         * @param string $uuid
         * @return \Inoma\Receipt\Items\Products\PriceModifier
         */
        public function getIncrease($uuid) {
            return $this->_increases[$uuid]??null;
        }
        
        /**
         * rimuove una maggiorazione associata al prodotto
         *
         * @param string $uuid
         * @return $this
         */
        public function deleteIncrease($uuid) {
            unset($this->_increases[$uuid]);
            return $this;
        }
        
        /**
         * setIntermediatePrice
         *
         * imposta un prezzo intermedio utilizzato durante il calcolo del prezzo finale
         * 
         * @param decimal $price
         * @return $this
         */
        public function setIntermediatePrice($price) {
            $this->_intermediatePrice = $price;
            return $this;
        }
        
        /**
         * getIntermediatePrice
         *
         * ritorna un prezzo intermedio utilizzato durante il calcolo del prezzo finale
         * 
         * @return decimal
         */
        public function getIntermediatePrice() {
            return $this->_intermediatePrice;
        }
        
        /**
         * ritorna il prezzo finale del prodotto
         * $appllyModifier permette di decidere se applicare sconti e maggiorazioni al prezzo
         *
         * @param boolean $applyModifier = true
         * @return number
         */
        public function getFinalPrice($applyModifier = true) {
            $this->setIntermediatePrice($this->getPrice() * $this->getQty());
            if($applyModifier) {
                foreach($this->getIncreases() as $increase) {
                    $increase->apply($this);
                }
                
                foreach($this->getDiscounts() as $discount) {
                    $discount->apply($this);
                }
            }
            $this->setFinalPrice($this->getIntermediatePrice());
            return $this->_finalPrice;
        }
        
        /**
         * imposta il prezzo finale del prodotto
         *
         * @param number $finalPrice
         * @return $this
         */
        public function setFinalPrice($finalPrice) {
            $this->_finalPrice = $finalPrice;
            return $this;
        }
        
        /**
         * getTax
         * 
         * imposta l'iva del prodotto
         *
         * @param number $tax
         * @return $this
         */
        public function setTax($tax) {
            $this->_tax = $tax;
            return $this;
        }
        
        /**
         * getTax
         * 
         * ritorna l'iva del prodotto
         *
         * @return number
         */
        public function getTax() {
            return $this->_tax;
        }
        
        
        public function jsonSerialize() {
            return ['finalPrice' => $this->getFinalPrice()] + $this->_jsonSerialize();
        }
    }

?>
