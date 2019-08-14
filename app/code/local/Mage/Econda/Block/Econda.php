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
 * Generate tracking code
 */
class Mage_Econda_Block_Econda extends Mage_Core_Block_Template
{
     
    /**
     * @var Current url and language values
     */
    private $eLang = array();
    private $lang = 1;
    private $realUrl = '';

    /**
     * Intialize
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('econda/tracker.phtml');
    }

    /**
     * Get econda JS code
     *
     * @return string
     */
    public function getEmos()
    {
        /**
         * Initial values
         */
        Mage::getSingleton('core/session', array('name'=>'frontend'));
        $db = Mage::getSingleton('core/resource')->getConnection('core_write');
        $session = Mage::getSingleton('checkout/session');
        $storeId = Mage::app()->getStore()->getId();
        $storeCode = Mage::app()->getStore()->getCode();
        $storeName = Mage::getModel('core/store_group')->load($storeId)->getName();
        $this->realUrl = Mage::helper('core/url')->getCurrentUrl();
        $langValue = 'econda/econda_settings/tracking_language';
        $this->eLang = Mage::helper('econda/data')->getTranslation();
        $cLang = Mage::getStoreConfig($langValue, $storeId);
        if($cLang == '1') {
            $this->lang = 0;
        }

        /**
         * Make instance of econda helper object
         */
        $jsUrl = $this->getJsUrl();
        $pathToFile = $jsUrl.'tracker/';
        $emos = Mage::getBlockSingleton('econda/emos');
        $emos->emos($pathToFile);
        $emos->trackMode(2);
        $emos->addCdata();

        /**
         * Get checkout steps inside order process
         */
        $checkoutSteps = Mage::getModel('econda/basket')->getCheckoutSteps();
        $stepCheckout = $checkoutSteps[1];

        /**
         * Get content string as drilldown path
         */
        $contentPath = "Start";
        $contentPath .= Mage::getModel('econda/base')->getContentBreadcrumb();
        $contentPath .= Mage::getModel('econda/base')->getContentCustom();
        $pageRoute = Mage::app()->getRequest()->getRouteName();
        $PageName = Mage::getSingleton('cms/page')->getIdentifier();

        if(($pageRoute != 'cms' && $PageName != 'home') || $contentPath == 'Start') {        
            if(trim($contentPath) == 'Start' || trim($contentPath) == 'Start/') {
                $contentPath = str_replace('/','',$contentPath);
                $contentPath .= Mage::getModel('econda/base')->getContentDefault();
            }
        }
        $contentPath = str_replace('//','/',$contentPath);

        /**
         * Add content path
         */
        if(stristr($this->realUrl,'checkout/onepage/') == false ||
        stristr($this->realUrl,'checkout/onepage/success') != false) {
            $emos->addContent($contentPath);
        }

        /**
         * Add page id
         */
        $emos->addPageID(Mage::getModel('econda/base')->getPageId($contentPath));

        /**
         * Add site id
         */
        $siteName = Mage::getModel('econda/base')->getSiteId($storeId);
        $emos->addSiteID($siteName);

        /**
         * Add lang id
         */
        $langName = Mage::getModel('econda/base')->getLangId($storeId);
        $emos->addLangID($langName);

        /**
         * Save pageID, siteID and langID into session for onePageCheckout
         */
        $jsSessionData = $contentPath.','.$siteName.','.$langName;
        $session->setData('econda_content',$jsSessionData);

        /**
         *  Add search results
         */
        $search = Mage::getModel('econda/base')->getSearch();
        if($search != null) {
            $emos->addSearch($search[0],$search[1]);
        }

        /**
         * Add results from contact form
         */
        if(Mage::getModel('econda/base')->getContact()) {
            $emos->addContact($this->eLang[40][$this->lang]);
        }

        $message = $this->getMessagesBlock()->getGroupedHtml();

        /**
         * Add results from registration
         */
        $register = Mage::getModel('econda/base')->getRegister($message);
        if($register != null) {
            $emos->addRegister($register[0],$register[1]);
        }
         
        /**
         * Add results from login
         */
        $login = Mage::getModel('econda/base')->getLogin($message);
        if($login != null) {
            $emos->addLogin($login[0],$login[1]);
        }
         
        /**
         * Add actions for add/rmv product item(s)
         */
        $addBasket = Mage::getModel('econda/basket')->getProductAddRmv($message, $storeId);
        $isAddBasket = $addBasket[0];
        $billingOption = $addBasket[1];
        $productActions = $addBasket[2];
        for($i = 0; $i < sizeof($productActions); $i++) {
            if($productActions[$i][0] == 'add') {
                $emos->addToBasket($productActions[$i][1]);
            }
            else {
                $emos->removeFromBasket($productActions[$i][1]);
            }
        }

        /**
         * Add action for product view
         */
        if(Mage::registry('current_product') && !$isAddBasket) {
            $productView = Mage::getModel('econda/basket')->getProductView($billingOption, $storeId);
            $emos->addDetailView($productView);
       	}

       	/**
       	 * Check if checkout process steps
       	 */
       	switch ($stepCheckout) {
            case 'BASKET':
                $emos->addOrderProcess("1_".$this->eLang[33][$this->lang]);
                break;
            case 'LOGIN':
                $emos->addOrderProcess("2_".$this->eLang[0][$this->lang]);
                break;
            case 'LOGINREGISTER':
                $emos->addOrderProcess("3_".$this->eLang[1][$this->lang]);
                break;
            case 'SHIPPINGADRESS':
                $emos->addOrderProcess("4_".$this->eLang[2][$this->lang]."/".$this->eLang[3][$this->lang]);
                break;
            case 'SHIPPINGMETHOD':
                $emos->addOrderProcess("4_".$this->eLang[2][$this->lang]."/".$this->eLang[4][$this->lang]);
                break;
            case 'PAYMENT':
                $emos->addOrderProcess("5_".$this->eLang[5][$this->lang]);
                break;
            case 'REVIEW':
                $emos->addOrderProcess("6_".$this->eLang[42][$this->lang]);
                $actOrder = Mage::getSingleton('checkout/session')->getQuoteId();
                $session->setData('econda_order_id',$actOrder);
                break;
            case 'SUCCESS':
                $emos->addOrderProcess("7_".$this->eLang[41][$this->lang]);

                /**
                 * Add billing for order success
                 */
                $billing = Mage::getModel('econda/basket')->getBilling($billingOption, $storeId);
                if($billing != null) {
                    $emos->addEmosBillingPageArray($billing[0],$billing[1],$billing[2],$billing[3],$billing[4],$billing[5]);
                }

                /**
                 * Add basket item(s) for order success
                 */
                $entityId = $billing[6];
                $basket = Mage::getModel('econda/basket')->getBasket($billingOption, $storeId, $entityId);
                if($basket != null) {
                    $emos->addEmosBasketPageArray($basket);
                }
                break;
        }

        /**
         * Display script code
         */
        $emosString = "\n\n<!-- Start Econda-Monitor M161 -->\n";
        $emosString .= $emos->toString();
        $emosString .= "<!-- End Econda-Monitor -->\n\n";

        if(stristr($this->realUrl,'checkout/onepage/') == false || stristr($this->realUrl,'checkout/onepage/success/') != false) {
            return $emosString;
        }
        else {
            return Mage::getBlockSingleton('econda/econdaOp')->getEmosOp();
        }
    }
}
