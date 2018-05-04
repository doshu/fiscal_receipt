<?php

    namespace Inoma\Receipt\Items;
    
    use Inoma\Receipt\Utility\Uuid;
    
    class ImageItem extends Item {
        
        protected $_publicType = 'image';
        protected $_image = null;
        
        public function __construct($image) {
            parent::__construct();
            $this->setImage($image);
        } 
        
        public function setImage($image) {
            $this->_image = $image;
            return $this;
        }
        
        public function getImage() {
            return $this->_image;
        }
    }

?>
