<?php

    namespace Inoma\Receipt\Items;
    
    class ItemsAggregator {
        
        protected $_options = [];
        
        protected $_map = [
            'product' => '_aggregateProducts'
        ];
        
        public function __construct($options = []) {
            
        }
        
        public function aggregate($items) {
            $out = [];
            $buffer = [];
            $lastType = null;
            foreach($items as $item) {
                if(!$item instanceof \Inoma\Receipt\Items\Item) {
                    $out[] = $item;
                    continue;
                }
                if($lastType !== null && $item->getPublicType() != $lastType) {
                    if(isset($this->_map[$lastType])) {
                        $out = array_merge($out, $this->{$this->_map[$lastType]}($buffer));
                    }
                    else {
                        $out = array_merge($out, $buffer);
                    }
                    $buffer = [];
                }
                $buffer[] = $item;
                $lastType = $item->getPublicType();
            }
            if(!empty($buffer)) {
                if(isset($this->_map[$lastType])) {
                    $out = array_merge($out, $this->{$this->_map[$lastType]}($buffer));
                }
                else {
                    $out = array_merge($out, $buffer);
                }
            }
            
            return $out;
        }
        
        protected function _aggregateProducts($items) {
            $itemsCollection = new \Cake\Collection\Collection($items);
            return $itemsCollection->groupBy(function($item) {
            
                    $hash = [$item->getSku(), $item->getFinalPrice()];
                    
                    $discounts = $item->getDiscounts();
                    $increases = $item->getIncreases();
                    
                    if(!empty($discounts)) {
                        $discountCollection = new \Cake\Collection\Collection($discounts);
                        $discountHash = $discountCollection->sortBy(function($discount) {
                                return $discount->getDescription();
                            }, SORT_ASC, SORT_NATURAL)
                            ->reduce(function ($accumulated, $discount) {
                                return array_merge(
                                    $accumulated, 
                                    ['discount-'.$discount->getCode().'-'.$discount->getDescription().'-'.$discount->getValue()]
                                );
                            }, []);
                        $hash = array_merge($hash, $discountHash);
                    }
                    
                    if(!empty($increases)) {
                        $increaseCollection = new \Cake\Collection\Collection($increases);
                        $increaseHash = $increaseCollection->sortBy(function($increase) {
                                return $increase->getDescription();
                            }, SORT_ASC, SORT_NATURAL)
                            ->reduce(function ($accumulated, $increase) {
                                return array_merge(
                                    $accumulated, 
                                    ['increase-'.$increase->getCode().'-'.$increase->getDescription().'-'.$increase->getValue()]
                                );
                            }, []);
                        $hash = array_merge($hash, $increaseHash);
                    }
                    
                    return implode('-', $hash);
                    
                })
                ->map(function($itemGroup, $groupKey) {
                    $referer = clone $itemGroup[0];
                    $qty = 0;
                    foreach($itemGroup as $item) {
                        $qty += $item->getQty();
                    }
                    $referer->setQty($qty);
                    return $referer;
                })
                ->toArray();
        }
    }

?>
