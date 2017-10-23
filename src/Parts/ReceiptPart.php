<?php

    namespace Inoma\Receipt\Parts;
    
    use Inoma\Receipt\Exceptions\NotAllowedItemException;
    
    abstract class ReceiptPart {
        
        protected $_items = [];
        protected $_allowedItems = '*';

        public function appendItem(\Inoma\Receipt\Items\Item $item) {
            $this->_checkAllowedItem($item);
            unset($this->_items[$item->getUuid()]);
            $this->_items += [$item->getUuid() => $item];
            return $this;
        }
        
        public function prependItem(\Inoma\Receipt\Items\Item $item) {
            $this->_checkAllowedItem($item);
            unset($this->_items[$item->getUuid()]);
            $this->_items = [$item->getUuid() => $item] + $this->_items;
            return $this;
        }
        
        protected function _checkAllowedItem(\Inoma\Receipt\Items\Item $item) {
            if((is_array($this->_allowedItems) && !in_array(get_class($item), $this->_allowedItems)) || $this->_allowedItems != '*') {
                throw new NotAllowedItemException();
            }
            return true;
        }
        
        public function deleteItem($uuid) {
            unset($this->_items[$uuid]);
            return $this;
        }
        
        public function getItems() {
            return $this->_items;
        }
        
        public function getItem($uuid) {
            return $this->getItems()[$uuid]??null;
        }
        
        public function getItemsByType($type) {
            $filteredItems = [];
            foreach($this->_items as $item) {
                if(get_class($item) == $type) {
                    $filteredItems[$item->getUuid()] = $item;
                }
            }
            return $filteredItems;
        }
    }
    
?>
