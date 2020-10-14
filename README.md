
# Esempio utilizzo libreria

l'esempio seguente illustra l'utilizzo della libreria partendo da un oggetto serializzato
ricevuto tramite API

```php
    
    $serializedReceipt = "..." //oggetto serializzato ricevuto tramite API
    
    $receipt = unserialize($serializedReceipt);
    
    
    $total = $receipt->getTotal(); //totale dello scontrino
    //il totale dello scontrino può essere negativo in caso di resi
    
    $products = $receipt->getProducts(); //recupera tutti i prodotti dello scontrino
    
    //per ogni prodotto stampo codice, quantità e prezzo
    foreach($products as $product) {
        echo $product->getSku()."\n";
        echo $product->getQty()."\n";
        echo $product->getPrice()."\n\n";
    }
    
    
    $returns = $receipt->getReturns(); //recupera eventuali resi
    
    //per ogni reso stampo codice, quantità e prezzo
    foreach($returns as $return) {
        echo $product->getSku()."\n";
        echo $product->getQty()."\n";
        echo $product->getPrice()."\n\n";
    }
    
    
    $operator = $receipt->getOperator(); //recupera l'operatore che ha emesso lo scontrino
    $client = $receipt->getClient(); //recupera il cliente a cui è stato emesso lo scontrino
    
    
```





