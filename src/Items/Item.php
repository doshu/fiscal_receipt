<?php

    namespace Inoma\Receipt\Items;
    
    use Inoma\Receipt\Utility\Uuid;
    
    abstract class Item {
        
        protected $_uuid = null;
        protected $_publicType = null;
        
        public function __construct() {
            $this->setUuid(Uuid::create());
        }    
        
        public function setUuid($uuid) {
            $this->_uuid = $uuid;
            return $this;
        }
        
        public function getUuid() {
            return $this->_uuid;
        }
    }

?>
