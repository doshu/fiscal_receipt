<?php

    namespace Inoma\Receipt\Items;
    
    trait InfoAwareTrait {
    
        /**
         * @var array informazioni aggiuntive
         */
        protected $_info = [];
        
        
        /**
         * setInfo
         * 
         * imposta un informazione aggiuntiva
         *
         * @param mixed $key
         * @param mixed $value
         * @return this
         */
        public function setInfo($key, $value) {
            $this->_info[$key] = $value;
            return $this;
        }
        
        /**
         * getInfo
         * 
         * ritorna un informazione aggiuntiva
         *
         * @param mixed $key
         * @return mixed
         */
        public function getInfo($key) {
            return $this->_info[$key]??null;
        }
        
    }
    
?>
