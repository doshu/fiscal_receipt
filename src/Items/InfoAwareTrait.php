<?php

    namespace Inoma\Receipt\Items;
    
    use \Cake\Utility\Inflector;
    
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
        
        /**
         * removeInfo
         * 
         * rimuove un informazione aggiuntiva
         *
         * @param mixed $key
         * @return mixed
         */
        public function removeInfo($key) {
            unset($this->_info[$key]);
        }
        
        
        public function __get($key) {
            return $this->getInfo($key);
        }
        
        public function __set($key, $value) {
            return $this->setInfo($key, $value);
        }
        
        public function __call($name, $arguments) {
            if(preg_match('/^get(.*)$/', $name, $matches)) {
                if(isset($matches[1]) && !empty(trim($matches[1]))) {
                    $key = Inflector::underscore(trim($matches[1]));
                    return $this->getInfo($key);
                }
            }
            if(preg_match('/^set(.*)$/', $name, $matches)) {
                if(isset($matches[1]) && !empty(trim($matches[1]))) {
                    $key = Inflector::underscore(trim($matches[1]));
                    return $this->setInfo($key, ...$arguments);
                }
            }
        }
        
        
    }
    
?>
