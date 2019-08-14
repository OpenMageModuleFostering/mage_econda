<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Mage
 * @package     Mage_Econda
 * @copyright   Copyright (c) 2015 econda GmbH (http://www.econda.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
/**
 * Receive translation values for tracking 
 */ 
class Mage_Econda_Helper_Data extends Mage_Core_Block_Template
{
    /**
     * Get translation array.
     * 
     * @return array
     */
    public function getTranslation()
    {
        $translation = array(array("Login","Anmeldung"),
                            array("Register","Registrierung"),
                            array("Shipping","Versand"),
                            array("Address","Adresse"),
                            array("Method","Methode"),
                            array("Payment","Zahlung"),
                            array("Search","Suche"),
                            array("Basic Search","Einfache Suche"),
                            array("Advanced Search","Erweiterte Suche"),
                            array("Information","Informationen"),
                            array("About","Impressum"),
                            array("Privacy","Datenschutz"),                            
                            array("General Business Terms","AGB"),
                            array("RSS","RSS"),
                            array("Customer Service","Kundenservice"),                            
                            array("Sitemap","Sitemap"),
                            array("Products","Produkte"),
                            array("Popular Search Terms","Meistgesucht"),
                            array("Results","Ergebnis"),
                            array("Contact","Kontakt"),
                            array("Create Account","Konto erstellen"),
                            array("Forgot Password","Passwort anfordern"),
                            array("My Account","Kundenbereich"),
                            array("Account Dashboard","Uebersichtsseite"),
                            array("Account Information","Kundeninformationen"),
                            array("Adress Book","Adressbuch"),
                            array("My Orders","Bestellhistorie"),
                            array("My Product Reviews","Eigene Bewertungen"),
                            array("My Tags","Eigene Tags"),
                            array("Newsletter Subscriptions","Newsletter abbonieren"),
                            array("My Wishlist","Wunschliste"),
                            array("My Downloadable Products","Herunterladbare Produkte"),
                            array("Order Process","Kaufprozess"),
                            array("Shopping Cart","Warenkorb"),
                            array("Reviews","Bewertungen"),
                            array("Tags","Tags"),
                            array("Overview","Uebersicht"),
                            array("Review","Bewertung"),
                            array("Other Sites","Sonstige Seiten"),
                            array("No Category","Ohne Kategorie"),
                            array("Contact","Kontakt"),
                            array("Order Confirmation","Bestaetigung"),
                            array("Order Review","Bestelluebersicht"),
                            array("Billing","Rechnung")
                            );
        return $translation;
    }  
}
