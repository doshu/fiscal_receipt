<?php

    namespace Inoma\Receipt\Items;
    
    use Inoma\Receipt\Utility\Uuid;
    use Inoma\Receipt\Utility\JsonSerializeTrait;
    
    class InvoiceRecipientItem extends Item {
    
        use JsonSerializeTrait {
            JsonSerializeTrait::jsonSerialize as _jsonSerialize;
        }
        
        protected $_publicType = 'invoice_recipient';
        protected $_code = null;
        protected $_type = null;
        protected $_cf = null;
        protected $_vat = null;
        protected $_label = null;
        protected $_name = null;
        protected $_surname = null;
        protected $_businessName = null;
        protected $_email = null;
        protected $_birthday = null;
        protected $_address = null;
        protected $_zip = null;
        protected $_city = null;
        protected $_province = null;
        
        public function __construct(
            $code = null,
            $type = null, 
            $cf = null, 
            $vat = null, 
            $label = null,
            $address = null,
            $name = null,
            $surname = null,
            $businessName = null,
            $email = null,
            $birthday = null,
            $zip = null,
            $city = null,
            $province = null
        ) {
            parent::__construct();
            $this->setCode($code);
            $this->setType($type);
            $this->setCf($cf);
            $this->setVat($vat);
            $this->setLabel($label);
            $this->setAddress($address);
            $this->setName($name);
            $this->setSurname($surname);
            $this->setBusinessName($businessName);
            $this->setEmail($email);
            $this->setBirthday($birthday);
            $this->setZip($zip);
            $this->setCity($city);
            $this->setProvince($province);
        } 
        
        /**
         * imposta il codice del cliente associato allo scontrino
         *
         * @param string $code
         * @return $this
         */
        public function setCode($code) {
            $this->_code = $code;
            return $this;
        }
        
        /**
         * ritorna il codice cliente associato allo scontrino
         *
         * @return string
         */
        public function getCode() {
            return $this->_code;
        }
        
        /**
         * imposta il tipo di destinatario 'phisical' o 'society'
         *
         * @param string $type
         * @return $this
         */
        public function setType($type) {
            $this->_type = $type;
            return $this;
        }
        
        
        /**
         * imposta il tipo di destinatario 'phisical' o 'society'
         *
         * @return string
         */
        public function getType() {
            return $this->_type;
        }
        
        /**
         * imposta il codice fiscale del cliente per scontrino parlante
         *
         * @param string $cf
         * @return $this
         */
        public function setCf($cf) {
            $this->_cf = $cf;
            return $this;
        }
        
        /**
         * ritorna il codice fiscale del cliente
         *
         * @return string
         */
        public function getCf() {
            return $this->_cf;
        }
        
        /**
         * imposta la partita iva del cliente per scontrino parlante
         *
         * @param string $vat
         * @return $this
         */
        public function setVat($vat) {
            $this->_vat = $vat;
            return $this;
        }
        
        /**
         * ritorna la partita iva del cliente
         *
         * @return string
         */
        public function getVat() {
            return $this->_vat;
        }
        
        /**
         * imposta il nominativo del cliente
         *
         * @param string $label
         * @return $this
         */
        public function setLabel($label) {
            $this->_label = $label;
            return $this;
        }
        
        /**
         * ritorna il nominativo del cliente
         *
         * @return string
         */
        public function getLabel() {
            return $this->_label;
        }
        
        /**
         * imposta l'indirizzo del cliente
         *
         * @param string $address
         * @return $this
         */
        public function setAddress($address) {
            $this->_address = $address;
            return $this;
        }
        
        /**
         * ritorna l'indirizzo del cliente
         *
         * @return string
         */
        public function getAddress() {
            return $this->_address;
        }
        
        public function setName($name) {
            $this->_name = $name;
            return $this;
        }
        
        public function getName() {
            return $this->_name;
        }
        
        public function setSurname($surname) {
            $this->_surname = $surname;
            return $this;
        }
        
        public function getSurname() {
            return $this->_surname;
        }
        
        public function setBusinessName($businessName) {
            $this->_businessName = $businessName;
            return $this;
        }
        
        public function getBusinessName() {
            return $this->_businessName;
        }
        
        public function setEmail($email) {
            $this->_email = $email;
            return $this;
        }
        
        public function getEmail() {
            return $this->_email;
        }
        
        public function setBirthday($birthday) {
            $this->_birthday = $birthday;
            return $this;
        }
        
        public function getBirthday() {
            return $this->_birthday;
        }
        
        public function setZip($zip) {
            $this->_zip = $zip;
            return $this;
        }
        
        public function getZip() {
            return $this->_zip;
        }
        
        public function setCity($city) {
            $this->_city = $city;
            return $this;
        }
        
        public function getCity() {
            return $this->_city;
        }
        
        public function setProvince($province) {
            $this->_province = $province;
            return $this;
        }
        
        public function getProvince() {
            return $this->_province;
        }
        
        public function getFullAddress() {
            $address = $this->_address;
            if($this->_zip) {
                $address .= ", ".$this->_zip;
            }
            if($this->_city) {
                $address .= ", ".$this->_city;
            }
            if($this->_province) {
                $address .= ", ".$this->_province;
            }
            return $address;
        }
        
        public function jsonSerialize() {
            return ['fullAddress' => $this->getFullAddress()] + $this->_jsonSerialize();
        }
        
    }

?>
