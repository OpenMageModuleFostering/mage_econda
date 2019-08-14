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
 * Generate standard tracking values
 */
class Mage_Econda_Model_Base extends Mage_Core_Model_Abstract
{

    /**
     * @var Current url and session object
     */
    private $realUrl = '';
    private $session = null;
    private $lang = 1;
    private $eLang = array();

    /**
     * Intialize
     */
    public function __construct()
    {
        $this->realUrl = Mage::helper('core/url')->getCurrentUrl();
        $this->session = Mage::getSingleton('checkout/session');
        $storeId = Mage::app()->getStore()->getId();
        $langValue = 'econda/econda_settings/tracking_language';
        $this->eLang = Mage::helper('econda/data')->getTranslation();
        $cLang = Mage::getStoreConfig($langValue, $storeId);
        if($cLang == '1') {
            $this->lang = 0;
        }
    }

    /**
     * Get search results
     *
     * @return array
     */
    public function getSearch()
    {
        $search = null;
        if(Mage::helper('catalogsearch')->getQueryText() != null) {
            $searchQuery = Mage::helper('catalogsearch')->getQueryText();
            $getQuery = Mage::helper('catalogsearch')->getQuery();
            $searchQuery = $getQuery->query_text;
            $searchHitsRes = $getQuery->num_results;
            $searchHits = (int)$searchHitsRes;
            $search = array($searchQuery, $searchHits);            
        }
        if(stristr($this->realUrl,'/catalogsearch/advanced/result/?') != false) {
            $searchQuery = '';
            $searchCriterias = Mage::getSingleton('catalogsearch/advanced')->getSearchCriterias();
            foreach($searchCriterias as $val) {
                $searchQuery .= $val['value'];
            }
            $productCollectionRes = Mage::getSingleton('catalogsearch/advanced')->getProductCollection();
            $productCollection = $productCollectionRes->getSize();
            $search = array($searchQuery, $productCollection);
        }
        return $search;
    }

    /**
     * Get page id for click tracking
     *
     * @return string
     */
    public function getPageId($content)
    {
        $pageId = md5($content);
        return $pageId;
    }

    /**
     * Get site id for multishops
     *
     * @return string
     */
    public function getSiteId($storeId)
    {
        $siteidOv = Mage::getStoreConfig('econda/econda_settings/tracking_siteid', $storeId);
        if(!empty($siteidOv) && trim($siteidOv) != "") {
            $siteName = trim($siteidOv);
        }
        else {
            $siteName =  Mage::app()->getFrontController()->getRequest()->getHttpHost();
        }
        return $siteName;
    }

    /**
     * Get language id from store id
     *
     * @return string
     */
    public function getLangId($storeId)
    {
    	$langidOv = Mage::getStoreConfig('econda/econda_settings/tracking_langid', $storeId);
    	if(!empty($langidOv) && trim($langidOv) != "") {
    		$langName = trim($langidOv);
    	}  
    	else {  	
        	$langName = Mage::app()->getStore()->getStoreId();
    	}
        return $langName;
    }

    /**
     * Get results from submitted contact form
     *
     * @return bool
     */
    public function getContact()
    {
        if(stristr($this->realUrl,'/contacts/index/') != false) {
            return true;
        }
        return false;
    }

    /**
     * Get results after registration
     *
     * @return array
     */
    public function getRegister($message)
    {
        if(stristr($this->realUrl,'customer/account/index/') != false ||
        stristr($this->realUrl,'checkout/multishipping/addresses/') != false){
            if(stristr($message,'success-msg') != false) {
                $customerId = Mage::getSingleton('customer/session')->getCustomerId();
                return array($customerId,0);
            }
        }
        return null;
    }

    /**
     * Get results after login
     *
     * @return array
     */
    public function getLogin($message)
    {
        $login = null;
         
        //login success
        $isLoggedIn = Mage::getSingleton('customer/session')->isLoggedIn();
        if($isLoggedIn == 1 && $this->session->getData('econda_logged') != 2) {
            $this->session->setData('econda_logged','1');
        }
        if($this->session->getData('econda_logged') == 1) {
            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
            $login = array($customerId,0);
            $this->session->setData('econda_logged','2');
        }
        //login failed
        if(stristr($this->realUrl,'customer/account/login/') != false ||
        stristr($this->realUrl,'checkout/multishipping/login/') != false){
            if(stristr($message,'error-msg') != false) {
                $customerId = Mage::getSingleton('customer/session')->getCustomerId();
                $login = array($customerId,1);
            }
        }
        return $login;
    }

    /**
     * Get content path from breadcrumb if available
     *
     * @return string
     */
    public function getContentBreadcrumb()
    {
        $contentPath = '';
        $getPath = Mage::helper('catalog')->getBreadcrumbPath($this->getCategory());
        if(sizeof($getPath) > 0) {
            foreach($getPath as $pathID) {
                //$contentPath .= '/'.$pathID['label'];
				$pathLabel = str_replace('/', ' ', $pathID['label']);
                $contentPath .= '/' . $pathLabel;
            }
        } 
        return $contentPath;
    }

    /**
     * Get content path for custom sites from url
     *
     * @return string
     */
    public function getContentCustom()
    {
        $contentPath = '';
        $checkoutSteps = Mage::getModel('econda/basket')->getCheckoutSteps();
        $contentCheckout = $checkoutSteps[0];
        if(stristr($this->realUrl,'q=') != false) {
            $contentPath .= '/'.$this->eLang[6][$this->lang].'/'.$this->eLang[7][$this->lang];
        }
        if(stristr($this->realUrl,'/about-') != false || stristr($this->realUrl,'/impressum') != false) {
            $contentPath .= '/'.$this->eLang[9][$this->lang].'/'.$this->eLang[10][$this->lang];
        }
        if(stristr($this->realUrl,'/datenschutz') != false) {
            $contentPath .= '/'.$this->eLang[9][$this->lang].'/'.$this->eLang[11][$this->lang];
        }
        if(stristr($this->realUrl,'/agb') != false) {
            $contentPath .= '/'.$this->eLang[9][$this->lang].'/'.$this->eLang[12][$this->lang];
        }
        if(stristr($this->realUrl,'/rss') != false) {
            $contentPath .= '/'.$this->eLang[9][$this->lang].'/'.$this->eLang[13][$this->lang];
        }
        if(stristr($this->realUrl,'/customer-service') != false) {
            $contentPath .= '/'.$this->eLang[9][$this->lang].'/'.$this->eLang[14][$this->lang];
        }
        if(stristr($this->realUrl,'/seo_sitemap') != false) {
            $contentPath .= '/'.$this->eLang[9][$this->lang].'/'.$this->eLang[15][$this->lang];
        }
        if(stristr($this->realUrl,'/seo_sitemap/product') != false) {
            $contentPath .= '-'.$this->eLang[16][$this->lang];
        }
        if(stristr($this->realUrl,'/term/popular') != false) {
            $contentPath .= '/'.$this->eLang[9][$this->lang].'/'.$this->eLang[17][$this->lang];
        }
        if(stristr($this->realUrl,'/catalogsearch/advanced') != false) {
            $contentPath .= '/'.$this->eLang[6][$this->lang].'/'.$this->eLang[8][$this->lang];
            if(stristr($this->realUrl,'catalogsearch/advanced/result/?') != false) {
                $contentPath .= '/'.$this->eLang[18][$this->lang];
            }
        }
        if(stristr($this->realUrl,'/contacts/') != false) {
            $contentPath .= '/'.$this->eLang[9][$this->lang].'/'.$this->eLang[19][$this->lang];
        }
        if(stristr($this->realUrl,'/login') != false) {
            $contentPath .= '/'.$this->eLang[0][$this->lang];
        }
        if(stristr($this->realUrl,'/customer/account/create/') != false) {
            $contentPath .= '/'.$this->eLang[20][$this->lang];
        }
        if(stristr($this->realUrl,'/customer/account/forgotpassword/') != false) {
            $contentPath .= '/'.$this->eLang[21][$this->lang];
        }
        if(stristr($this->realUrl,'/customer/') != false &&
        stristr($this->realUrl,'/customer/account/login/') == false &&
        stristr($this->realUrl,'/customer/account/forgotpassword/') == false &&
        stristr($this->realUrl,'/customer/account/create/') == false) {
            $contentPath .= '/'.$this->eLang[22][$this->lang];
        }
        if(stristr($this->realUrl,'/customer/account/') != false &&
        stristr($this->realUrl,'/customer/account/edit/') == false &&
        stristr($this->realUrl,'/customer/account/login/') == false &&
        stristr($this->realUrl,'/customer/account/forgotpassword/') == false &&
        stristr($this->realUrl,'/customer/account/create/') == false) {
            $contentPath .= '/'.$this->eLang[23][$this->lang];
        }
        if(stristr($this->realUrl,'/customer/account/edit/') != false) {
            $contentPath .= '/'.$this->eLang[24][$this->lang];
        }
        if(stristr($this->realUrl,'/customer/address/') != false) {
            $contentPath .= '/'.$this->eLang[25][$this->lang];
        }
        if(stristr($this->realUrl,'/sales/order/history/') != false) {
            $contentPath .= '/'.$this->eLang[22][$this->lang].'/'.$this->eLang[26][$this->lang];
        }
        if(stristr($this->realUrl,'/review/customer/') != false) {
            $contentPath .= '/'.$this->eLang[27][$this->lang];
        }
        if(stristr($this->realUrl,'/tag/customer/') != false) {
            $contentPath .= '/'.$this->eLang[28][$this->lang];
        }
        if(stristr($this->realUrl,'/newsletter/manage/') != false) {
            $contentPath .= '/'.$this->eLang[22][$this->lang].'/'.$this->eLang[29][$this->lang];
        }
        if(stristr($this->realUrl,'/wishlist/') != false) {
            $contentPath .= '/'.$this->eLang[22][$this->lang].'/'.$this->eLang[30][$this->lang];
        }
        if(stristr($this->realUrl,'/downloadable/customer/products/') != false) {
            $contentPath .= '/'.$this->eLang[31][$this->lang];
        }
        if(stristr($this->realUrl,'checkout/cart') != false) {
            $contentPath .= '/'.$this->eLang[32][$this->lang].'/'.$this->eLang[33][$this->lang];
        }

        if(stristr($this->realUrl,'checkout/onepage/success') != false) {
            $contentPath .= '/'.$this->eLang[32][$this->lang].'/'.$this->eLang[41][$this->lang];
        }
        if(stristr($this->realUrl,'/checkout/multishipping') != false) {
            $contentPath .= '/'.$this->eLang[32][$this->lang].'/'.$contentCheckout;
        }
        if(stristr($this->realUrl,'/review') != false && stristr($this->realUrl,'/review/customer/') == false) {
            $contentPath .= '/'.$this->eLang[34][$this->lang];
        }
        if($contentPath == '/Bewertungen') {
            $contentPath = '/'.$this->eLang[9][$this->lang].'/'.$this->eLang[37][$this->lang];
        }

        if(stristr($this->realUrl,'/tag/') != false && stristr($this->realUrl,'/tag/customer/') == false) {
            $contentPath .= '/'.$this->eLang[35][$this->lang];
            $tagId = Mage::app()->getRequest()->getParam('tagId');
            $tagName = Mage::getModel('tag/tag')->load($tagId)->getName();
            if(trim($tagName) == ''){
                $contentPath .= '/'.$this->eLang[36][$this->lang];
            }
            else{
                $contentPath .= '/'.$tagName;
            }
        }
        return $contentPath;
    }

    /**
     * Get content path from url if path is empty
     *
     * @return string
     */
    public function getContentDefault()
    {
        $contentPath = "";
        $checkPath = preg_replace(array('/(http:|https:)\/\/([^\/]*)/',
                                        '/index.php/',
                                        '/\/\//',
                                        '/\?(.*)/',
                                        '/(.html|.htm|.php)/'
                                        )
                                        ,array('','','','',''),$this->realUrl);
        if(trim($checkPath) != '') {
            $urlExtExp = explode("/",$checkPath);
            $urlExtO = '';
            for($i = 0; $i < sizeof($urlExtExp); $i++) {
                if(trim($urlExtExp[$i]) != ''){
                    $urlExtO .= ucfirst($urlExtExp[$i]).'/';
                }
            }
            $contentPath .= '/'.$this->eLang[38][$this->lang].'/'.substr($urlExtO,0,-1);
        }
        return $contentPath;
    }
}
