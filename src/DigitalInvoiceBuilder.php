<?php
    
    namespace Inoma\Receipt;

    use Inoma\FatturaElettronicaPR;

    class DigitalInvoiceBuilder {

        protected $_defaultCountryCode = null;

        protected $_invoice = null;
        protected $_senderCountryCode = null;
        protected $_senderVat = null;
        protected $_denomination = null;
        protected $_taxRegime = null;
        protected $_address = null;


        protected $_countryCodes = [
            'AD' => ['name'=>'ANDORRA'],
            'AE' => ['name'=>'UNITED ARAB EMIRATES'],
            'AF' => ['name'=>'AFGHANISTAN'],
            'AG' => ['name'=>'ANTIGUA AND BARBUDA'],
            'AI' => ['name'=>'ANGUILLA'],
            'AL' => ['name'=>'ALBANIA'],
            'AM' => ['name'=>'ARMENIA'],
            'AN' => ['name'=>'NETHERLANDS ANTILLES'],
            'AO' => ['name'=>'ANGOLA'],
            'AQ' => ['name'=>'ANTARCTICA'],
            'AR' => ['name'=>'ARGENTINA'],
            'AS' => ['name'=>'AMERICAN SAMOA'],
            'AT' => ['name'=>'AUSTRIA'],
            'AU' => ['name'=>'AUSTRALIA'],
            'AW' => ['name'=>'ARUBA'],
            'AZ' => ['name'=>'AZERBAIJAN'],
            'BA' => ['name'=>'BOSNIA AND HERZEGOVINA'],
            'BB' => ['name'=>'BARBADOS'],
            'BD' => ['name'=>'BANGLADESH'],
            'BE' => ['name'=>'BELGIUM'],
            'BF' => ['name'=>'BURKINA FASO'],
            'BG' => ['name'=>'BULGARIA'],
            'BH' => ['name'=>'BAHRAIN'],
            'BI' => ['name'=>'BURUNDI'],
            'BJ' => ['name'=>'BENIN'],
            'BL' => ['name'=>'SAINT BARTHELEMY'],
            'BM' => ['name'=>'BERMUDA'],
            'BN' => ['name'=>'BRUNEI DARUSSALAM'],
            'BO' => ['name'=>'BOLIVIA'],
            'BR' => ['name'=>'BRAZIL'],
            'BS' => ['name'=>'BAHAMAS'],
            'BT' => ['name'=>'BHUTAN'],
            'BW' => ['name'=>'BOTSWANA'],
            'BY' => ['name'=>'BELARUS'],
            'BZ' => ['name'=>'BELIZE'],
            'CA' => ['name'=>'CANADA'],
            'CC' => ['name'=>'COCOS (KEELING) ISLANDS'],
            'CD' => ['name'=>'CONGO, THE DEMOCRATIC REPUBLIC OF THE'],
            'CF' => ['name'=>'CENTRAL AFRICAN REPUBLIC'],
            'CG' => ['name'=>'CONGO'],
            'CH' => ['name'=>'SWITZERLAND'],
            'CI' => ['name'=>'COTE D IVOIRE'],
            'CK' => ['name'=>'COOK ISLANDS'],
            'CL' => ['name'=>'CHILE'],
            'CM' => ['name'=>'CAMEROON'],
            'CN' => ['name'=>'CHINA'],
            'CO' => ['name'=>'COLOMBIA'],
            'CR' => ['name'=>'COSTA RICA'],
            'CU' => ['name'=>'CUBA'],
            'CV' => ['name'=>'CAPE VERDE'],
            'CX' => ['name'=>'CHRISTMAS ISLAND'],
            'CY' => ['name'=>'CYPRUS'],
            'CZ' => ['name'=>'CZECH REPUBLIC'],
            'DE' => ['name'=>'GERMANY'],
            'DJ' => ['name'=>'DJIBOUTI'],
            'DK' => ['name'=>'DENMARK'],
            'DM' => ['name'=>'DOMINICA'],
            'DO' => ['name'=>'DOMINICAN REPUBLIC'],
            'DZ' => ['name'=>'ALGERIA'],
            'EC' => ['name'=>'ECUADOR'],
            'EE' => ['name'=>'ESTONIA'],
            'EG' => ['name'=>'EGYPT'],
            'ER' => ['name'=>'ERITREA'],
            'ES' => ['name'=>'SPAIN'],
            'ET' => ['name'=>'ETHIOPIA'],
            'FI' => ['name'=>'FINLAND'],
            'FJ' => ['name'=>'FIJI'],
            'FK' => ['name'=>'FALKLAND ISLANDS (MALVINAS)'],
            'FM' => ['name'=>'MICRONESIA, FEDERATED STATES OF'],
            'FO' => ['name'=>'FAROE ISLANDS'],
            'FR' => ['name'=>'FRANCE'],
            'GA' => ['name'=>'GABON'],
            'GB' => ['name'=>'UNITED KINGDOM'],
            'GD' => ['name'=>'GRENADA'],
            'GE' => ['name'=>'GEORGIA'],
            'GH' => ['name'=>'GHANA'],
            'GI' => ['name'=>'GIBRALTAR'],
            'GL' => ['name'=>'GREENLAND'],
            'GM' => ['name'=>'GAMBIA'],
            'GN' => ['name'=>'GUINEA'],
            'GQ' => ['name'=>'EQUATORIAL GUINEA'],
            'GR' => ['name'=>'GREECE'],
            'GT' => ['name'=>'GUATEMALA'],
            'GU' => ['name'=>'GUAM'],
            'GW' => ['name'=>'GUINEA-BISSAU'],
            'GY' => ['name'=>'GUYANA'],
            'HK' => ['name'=>'HONG KONG'],
            'HN' => ['name'=>'HONDURAS'],
            'HR' => ['name'=>'CROATIA'],
            'HT' => ['name'=>'HAITI'],
            'HU' => ['name'=>'HUNGARY'],
            'ID' => ['name'=>'INDONESIA'],
            'IE' => ['name'=>'IRELAND'],
            'IL' => ['name'=>'ISRAEL'],
            'IM' => ['name'=>'ISLE OF MAN'],
            'IN' => ['name'=>'INDIA'],
            'IQ' => ['name'=>'IRAQ'],
            'IR' => ['name'=>'IRAN, ISLAMIC REPUBLIC OF'],
            'IS' => ['name'=>'ICELAND'],
            'IT' => ['name'=>'ITALY'],
            'JM' => ['name'=>'JAMAICA'],
            'JO' => ['name'=>'JORDAN'],
            'JP' => ['name'=>'JAPAN'],
            'KE' => ['name'=>'KENYA'],
            'KG' => ['name'=>'KYRGYZSTAN'],
            'KH' => ['name'=>'CAMBODIA'],
            'KI' => ['name'=>'KIRIBATI'],
            'KM' => ['name'=>'COMOROS'],
            'KN' => ['name'=>'SAINT KITTS AND NEVIS'],
            'KP' => ['name'=>'KOREA DEMOCRATIC PEOPLES REPUBLIC OF'],
            'KR' => ['name'=>'KOREA REPUBLIC OF'],
            'KW' => ['name'=>'KUWAIT'],
            'KY' => ['name'=>'CAYMAN ISLANDS'],
            'KZ' => ['name'=>'KAZAKSTAN'],
            'LA' => ['name'=>'LAO PEOPLES DEMOCRATIC REPUBLIC'],
            'LB' => ['name'=>'LEBANON'],
            'LC' => ['name'=>'SAINT LUCIA'],
            'LI' => ['name'=>'LIECHTENSTEIN'],
            'LK' => ['name'=>'SRI LANKA'],
            'LR' => ['name'=>'LIBERIA'],
            'LS' => ['name'=>'LESOTHO'],
            'LT' => ['name'=>'LITHUANIA'],
            'LU' => ['name'=>'LUXEMBOURG'],
            'LV' => ['name'=>'LATVIA'],
            'LY' => ['name'=>'LIBYAN ARAB JAMAHIRIYA'],
            'MA' => ['name'=>'MOROCCO'],
            'MC' => ['name'=>'MONACO'],
            'MD' => ['name'=>'MOLDOVA, REPUBLIC OF'],
            'ME' => ['name'=>'MONTENEGRO'],
            'MF' => ['name'=>'SAINT MARTIN'],
            'MG' => ['name'=>'MADAGASCAR'],
            'MH' => ['name'=>'MARSHALL ISLANDS'],
            'MK' => ['name'=>'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF'],
            'ML' => ['name'=>'MALI'],
            'MM' => ['name'=>'MYANMAR'],
            'MN' => ['name'=>'MONGOLIA'],
            'MO' => ['name'=>'MACAU'],
            'MP' => ['name'=>'NORTHERN MARIANA ISLANDS'],
            'MR' => ['name'=>'MAURITANIA'],
            'MS' => ['name'=>'MONTSERRAT'],
            'MT' => ['name'=>'MALTA'],
            'MU' => ['name'=>'MAURITIUS'],
            'MV' => ['name'=>'MALDIVES'],
            'MW' => ['name'=>'MALAWI'],
            'MX' => ['name'=>'MEXICO'],
            'MY' => ['name'=>'MALAYSIA'],
            'MZ' => ['name'=>'MOZAMBIQUE'],
            'NA' => ['name'=>'NAMIBIA'],
            'NC' => ['name'=>'NEW CALEDONIA'],
            'NE' => ['name'=>'NIGER'],
            'NG' => ['name'=>'NIGERIA'],
            'NI' => ['name'=>'NICARAGUA'],
            'NL' => ['name'=>'NETHERLANDS'],
            'NO' => ['name'=>'NORWAY'],
            'NP' => ['name'=>'NEPAL'],
            'NR' => ['name'=>'NAURU'],
            'NU' => ['name'=>'NIUE'],
            'NZ' => ['name'=>'NEW ZEALAND'],
            'OM' => ['name'=>'OMAN'],
            'PA' => ['name'=>'PANAMA'],
            'PE' => ['name'=>'PERU'],
            'PF' => ['name'=>'FRENCH POLYNESIA'],
            'PG' => ['name'=>'PAPUA NEW GUINEA'],
            'PH' => ['name'=>'PHILIPPINES'],
            'PK' => ['name'=>'PAKISTAN'],
            'PL' => ['name'=>'POLAND'],
            'PM' => ['name'=>'SAINT PIERRE AND MIQUELON'],
            'PN' => ['name'=>'PITCAIRN'],
            'PR' => ['name'=>'PUERTO RICO'],
            'PT' => ['name'=>'PORTUGAL'],
            'PW' => ['name'=>'PALAU'],
            'PY' => ['name'=>'PARAGUAY'],
            'QA' => ['name'=>'QATAR'],
            'RO' => ['name'=>'ROMANIA'],
            'RS' => ['name'=>'SERBIA'],
            'RU' => ['name'=>'RUSSIAN FEDERATION'],
            'RW' => ['name'=>'RWANDA'],
            'SA' => ['name'=>'SAUDI ARABIA'],
            'SB' => ['name'=>'SOLOMON ISLANDS'],
            'SC' => ['name'=>'SEYCHELLES'],
            'SD' => ['name'=>'SUDAN'],
            'SE' => ['name'=>'SWEDEN'],
            'SG' => ['name'=>'SINGAPORE'],
            'SH' => ['name'=>'SAINT HELENA'],
            'SI' => ['name'=>'SLOVENIA'],
            'SK' => ['name'=>'SLOVAKIA'],
            'SL' => ['name'=>'SIERRA LEONE'],
            'SM' => ['name'=>'SAN MARINO'],
            'SN' => ['name'=>'SENEGAL'],
            'SO' => ['name'=>'SOMALIA'],
            'SR' => ['name'=>'SURINAME'],
            'ST' => ['name'=>'SAO TOME AND PRINCIPE'],
            'SV' => ['name'=>'EL SALVADOR'],
            'SY' => ['name'=>'SYRIAN ARAB REPUBLIC'],
            'SZ' => ['name'=>'SWAZILAND'],
            'TC' => ['name'=>'TURKS AND CAICOS ISLANDS'],
            'TD' => ['name'=>'CHAD'],
            'TG' => ['name'=>'TOGO'],
            'TH' => ['name'=>'THAILAND'],
            'TJ' => ['name'=>'TAJIKISTAN'],
            'TK' => ['name'=>'TOKELAU'],
            'TL' => ['name'=>'TIMOR-LESTE'],
            'TM' => ['name'=>'TURKMENISTAN'],
            'TN' => ['name'=>'TUNISIA'],
            'TO' => ['name'=>'TONGA'],
            'TR' => ['name'=>'TURKEY'],
            'TT' => ['name'=>'TRINIDAD AND TOBAGO'],
            'TV' => ['name'=>'TUVALU'],
            'TW' => ['name'=>'TAIWAN, PROVINCE OF CHINA'],
            'TZ' => ['name'=>'TANZANIA, UNITED REPUBLIC OF'],
            'UA' => ['name'=>'UKRAINE'],
            'UG' => ['name'=>'UGANDA'],
            'US' => ['name'=>'UNITED STATES'],
            'UY' => ['name'=>'URUGUAY'],
            'UZ' => ['name'=>'UZBEKISTAN'],
            'VA' => ['name'=>'HOLY SEE (VATICAN CITY STATE)'],
            'VC' => ['name'=>'SAINT VINCENT AND THE GRENADINES'],
            'VE' => ['name'=>'VENEZUELA'],
            'VG' => ['name'=>'VIRGIN ISLANDS, BRITISH'],
            'VI' => ['name'=>'VIRGIN ISLANDS, U.S.'],
            'VN' => ['name'=>'VIET NAM'],
            'VU' => ['name'=>'VANUATU'],
            'WF' => ['name'=>'WALLIS AND FUTUNA'],
            'WS' => ['name'=>'SAMOA'],
            'XK' => ['name'=>'KOSOVO'],
            'YE' => ['name'=>'YEMEN'],
            'YT' => ['name'=>'MAYOTTE'],
            'ZA' => ['name'=>'SOUTH AFRICA'],
            'ZM' => ['name'=>'ZAMBIA'],
            'ZW' => ['name'=>'ZIMBABWE']
        ];

        public function __construct(\Inoma\Receipt\Receipt $receipt) {
            FatturaElettronicaPR\FatturaElettronicaPR::init();
            $this->_invoice = $receipt;
        }

        public function setDefaultCountryCode($countryCode) {
            $this->_defaultCountryCode = $countryCode;
        }

        public function setSenderData(
            $countryCode, 
            $vat,
            $denomination,
            $taxRegime,
            $address
        ) {
            $this->_senderCountryCode = $countryCode;
            $this->_senderVat = $vat;
            $this->_denomination = $denomination;
            $this->_taxRegime = $taxRegime;
            $this->_address = $address;
        }

        public function build($progressive) {

            $invoiceRecipient = $this->_invoice->getInvoiceRecipient();
            if(!$invoiceRecipient) {
                throw new \Exception("The invoice has no invoice recipient");
            }

            $datiTrasmissione = new FatturaElettronicaPR\Elements\DatiTrasmissione(
                new FatturaElettronicaPR\Elements\IdTrasmittente($this->_senderCountryCode, $this->_senderVat),
                $progressive,
                FatturaElettronicaPR\FatturaElettronicaPR::FORMATO_TRASMISSIONE,
                $invoiceRecipient->getSdiCode() ??
                    FatturaElettronicaPR\FatturaElettronicaPR::DEFAULT_CODICE_DESTINATARIO
            );
            
            if(empty($invoiceRecipient->getSdiCode()) && !empty($invoiceRecipient->getPec())) {
                $datiTrasmissione->setPECDestinatario($invoiceRecipient->getPec());
            }

            $cedentePrestatore = new FatturaElettronicaPR\Elements\CedentePrestatore(
                (new FatturaElettronicaPR\Elements\DatiAnagrafici(
                    (new FatturaElettronicaPR\Elements\Anagrafica())->setDenominazione($this->_denomination),
                    $this->_taxRegime
                ))->setIdFiscaleIVA(
                    new FatturaElettronicaPR\Elements\IdFiscaleIVA($this->_senderCountryCode, $this->_senderVat)
                ),
                $this->_createSedeFromArray($this->_address)
            );
            
            $cessionarioCommittente = $this->_buildCessionarioCommittente($invoiceRecipient);

            $datiGenerali = $this->_buildGeneralData();

            $datiBeniServizi = $this->_buildGoodsAndServicesData();

            $fattura = new FatturaElettronicaPR\FatturaElettronicaPR(
                $datiTrasmissione,
                $cedentePrestatore,
                $cessionarioCommittente,
                $datiGenerali,
                $datiBeniServizi
            );

            $payments = $this->_buildPayments();
            foreach($payments as $payment) {
                $fattura->addDatiPagamento($payment);
            }

            return $fattura;
        }        

        /**
         * crea un oggetto sede partendo da un array contente i dati dell'indirizzo
         *
         * @param array $address
         * @return \Inoma\FatturaElettronicaPR\Elements\Sede
         */
        protected function _createSedeFromArray(array $address) {
            $sede = new FatturaElettronicaPR\Elements\Sede(
                $address['address'],
                $address['zip'],
                $address['city'],
                $address['nation']
            );
            if(isset($address['civic'])) {
                $sede->setNumeroCivico($address['civic']);
            }
            if(isset($address['province'])) {
                $sede->setProvincia($address['province']);
            }

            return $sede;
        }

        protected function _buildGeneralData() {
            return new FatturaElettronicaPR\Elements\DatiGenerali(
                (new FatturaElettronicaPR\Elements\DatiGeneraliDocumento(
                    FatturaElettronicaPR\Values\TipoDocumento::FATTURA, 
                    'EUR', 
                    $this->_invoice->getInvoiceDate(), 
                    $this->_invoice->getInvoiceNumber())
                )
                    ->setImportoTotaleDocumento($this->_invoice->getTotal())
            );
        }

        protected function _buildGoodsAndServicesData() {
            $datiBeniServizi = new FatturaElettronicaPR\Elements\DatiBeniServizi();

            $products = $this->_invoice->getProducts();
            $lineNumber = 1;
            foreach($products as $product) {
                $detailLine = new FatturaElettronicaPR\Elements\DettaglioLinee(
                    $lineNumber,
                    $product->getDescription(), 
                    $product->getPrice() / (1 + $product->getTax() / 100),
                    $product->getFinalPrice() / (1 + $product->getTax() / 100), 
                    $product->getTax()
                );

                $detailLine->setCodiceArticolo(new FatturaElettronicaPR\Elements\CodiceArticolo(
                    'Cod Art. fornitore',
                    $product->getSku()
                ));

                foreach($product->getIncreases() as $increase) {
                    $detailLine->addScontoMaggiorazione(
                        (new FatturaElettronicaPR\Elements\ScontoMaggiorazione(
                            FatturaElettronicaPR\Elements\ScontoMaggiorazione::TIPO_MAGGIORAZIONE
                        ))->setImporto($increase->getRealValue() / (1 + $product->getTax() / 100))
                    );
                }
                foreach($product->getDiscounts() as $discount) {
                    $detailLine->addScontoMaggiorazione(
                        (new FatturaElettronicaPR\Elements\ScontoMaggiorazione(
                            FatturaElettronicaPR\Elements\ScontoMaggiorazione::TIPO_SCONTO
                        ))->setImporto($discount->getRealValue() / (1 + $product->getTax() / 100))
                    );
                }
                
                $detailLine->setQuantita($product->getQty());

                $datiBeniServizi->addDettaglioLinee($detailLine);

                $lineNumber++;
            }  

            $globalIncreasesAndDiscounts = $this->_invoice->splitIncreasesAndDiscountsForRipartition();
            foreach($globalIncreasesAndDiscounts['increases'] as $increase) {
                $detailLine = new FatturaElettronicaPR\Elements\DettaglioLinee(
                    $lineNumber, 
                    $increase['increase']->getDescription(), 
                    $increase['value'] / (1 + $increase['tax'] / 100),
                    $increase['value'] / (1 + $increase['tax'] / 100), 
                    $increase['tax']
                );
                $detailLine->setTipoCessionePrestazione(FatturaElettronicaPR\Elements\DettaglioLinee::TIPO_SPESA_ACCESSORIA);
                $detailLine->setQuantita(1);
                $datiBeniServizi->addDettaglioLinee($detailLine);

                $lineNumber++;
            }

            foreach($globalIncreasesAndDiscounts['discounts'] as $discount) {
                $detailLine = new FatturaElettronicaPR\Elements\DettaglioLinee(
                    $lineNumber, 
                    $discount['discount']->getDescription(), 
                    -$discount['value'] / (1 + $discount['tax'] / 100),
                    -$discount['value'] / (1 + $discount['tax'] / 100), 
                    $discount['tax']
                );
                $detailLine->setTipoCessionePrestazione(FatturaElettronicaPR\Elements\DettaglioLinee::TIPO_SCONTO);
                $detailLine->setQuantita(1);
                $datiBeniServizi->addDettaglioLinee($detailLine);
                
                $lineNumber++;
            }

            $taxSummary = $this->_invoice->getTaxSummary();
            foreach($taxSummary as $tax => $taxData) {
                $datiRiepilogo = new FatturaElettronicaPR\Elements\DatiRiepilogo(
                    $tax,
                    $taxData['taxable'],
                    $taxData['tax']
                );

                $datiBeniServizi->addDatiRiepilogo($datiRiepilogo);
            }
            
            return $datiBeniServizi;
        } 

        protected function _buildPayments() {
            $datiPagamenti = [];
            $payments = $this->_invoice->getPayments();
            foreach($payments as $payment) {
                $datiPagamento = new FatturaElettronicaPR\Elements\DatiPagamento(
                    FatturaElettronicaPR\Elements\DatiPagamento::TIPO_PAGAMENTO_COMPLETO
                );
                switch($payment->getCode()) {
                    case 'card':
                        $paymentType = FatturaElettronicaPR\Values\ModalitaPagamento::CARTA_DI_PAGAMENTO;
                        break;
                    case 'generic':
                    case 'cash':
                    case 'self':
                        $paymentType = FatturaElettronicaPR\Values\ModalitaPagamento::CONTANTI;
                        break;
                    case 'check':
                        $paymentType = FatturaElettronicaPR\Values\ModalitaPagamento::ASSEGNO;
                        break;
                    case 'credit':
                    throw new \Exception('Cannot emit an invoice with a credit payment');
                        break;
                    case 'meal_voucher':
                    case 'meal_voucher_with_change':
                        throw new \Exception('Cannot emit an invoice with a meal voucher payment');
                        break;
                    default:
                        throw new \Exception('Unknown payment type');
                }
                $datiPagamento->addDettaglioPagamento(
                    new FatturaElettronicaPR\Elements\DettaglioPagamento(
                        $paymentType,
                        $payment->getRealPaid()
                    )
                );

                $datiPagamenti[] = $datiPagamento;
            }

            return $datiPagamenti;
        }


        protected function _buildCessionarioCommittente($invoiceRecipient) {
            if($invoiceRecipient->getType() == 'phisical') {
                $cessionarioCommittente = new FatturaElettronicaPR\Elements\CessionarioCommittente(
                    (new FatturaElettronicaPR\Elements\DatiAnagrafici(
                        (new FatturaElettronicaPR\Elements\Anagrafica())
                            ->setNome($invoiceRecipient->getName())
                            ->setCognome($invoiceRecipient->getSurname())
                        ,
                        null
                    ))->setCodiceFiscale($invoiceRecipient->getCf()),
                    new FatturaElettronicaPR\Elements\Sede(
                        $invoiceRecipient->getAddress(),
                        $invoiceRecipient->getZip(),
                        $invoiceRecipient->getCity(),
                        !empty($invoiceRecipient->getNation()) ?  $invoiceRecipient->getNation() : $this->_defaultCountryCode
                    )
                );
            }
            else {
                $clientVat = $this->_parseVat($invoiceRecipient->getVat());
                $cessionarioCommittente = new FatturaElettronicaPR\Elements\CessionarioCommittente(
                    (new FatturaElettronicaPR\Elements\DatiAnagrafici(
                        (new FatturaElettronicaPR\Elements\Anagrafica())->setDenominazione($invoiceRecipient->getBusinessName()),
                        null
                    ))->setIdFiscaleIVA(new FatturaElettronicaPR\Elements\IdFiscaleIVA(
                            $clientVat['country_code'], 
                            $clientVat['vat']
                        )),
                    new FatturaElettronicaPR\Elements\Sede(
                        $invoiceRecipient->getAddress(),
                        $invoiceRecipient->getZip(),
                        $invoiceRecipient->getCity(),
                        !empty($invoiceRecipient->getNation()) ?  $invoiceRecipient->getNation() : $this->_defaultCountryCode
                    )
                );
            }

            return $cessionarioCommittente;
        }

        /**
         * parsa una partita iva estraendo il country code e la partita iva
         *
         * @param string $vat
         * @return array
         */
        protected function _parseVat($vat) {
            if(preg_match('/^([a-z]{2})(.*)/i', $vat, $matches)) {
                [, $country, $_vat] = $matches;
                $country = strtoupper($country);
                if(array_key_exists($country, $this->_countryCodes)) {
                    return ['country_code' => $country, 'vat' => $_vat];
                }
            }
            return ['country_code' => $this->_defaultCountryCode, 'vat' => $vat];
        }
    }
