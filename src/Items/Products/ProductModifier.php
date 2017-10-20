<?php

    namespace Inoma\Receipt\Items\Products;
    
    interface ProductModifier {
        
        public function apply(\Inoma\Receipt\ItemsProductItem $product);
        
    }

?>
