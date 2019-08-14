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
 * Generate basket objects and values for tracking
 */
class Mage_Econda_Model_Basket extends Mage_Core_Model_Abstract
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
     * Get deepest category path for later drilldown
     *
     * @return string
     */
    public function getProductCategory($productId)
    {
        $getCatId = Mage::getModel('catalog/product')->load($productId)->getCategoryIds();
        $db = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $rootCat = Mage::app()->getStore()->getRootCategoryId();
        if(sizeof($getCatId) > 0) {
            $catLevel = 0;
			$catPath = array();
            for($i=0;$i<sizeof($getCatId);$i++) {
                $level = Mage::getModel('catalog/category')->load($getCatId[$i])->getLevel();
                $catPathPa = Mage::getModel('catalog/category')->load($getCatId[$i])->getPathIds();
                if($catLevel < intval($level) && intval($catPathPa[1]) == $rootCat) {
                    $catLevel = intval($level);
                    $catPath = $catPathPa;
                }
            }
            $category = "";
            for($i=0;$i<sizeof($catPath);$i++) {
                $catId = intval($catPath[$i]);
                //$category .= Mage::getModel('catalog/category')->load($catId)->getName()."/";
				$category .= str_replace('/', '|', Mage::getModel('catalog/category')->load($catId)->getName()). "/";
            }
            $category = substr($category,0,-1);
            if(substr($category, 0, 1) == '/') {
                $category = substr($category, 1);
            }
            return $category;
        }
        else {
            return false;
        }
    }

    /**
     * Get checkout steps and content path for each step
     *
     * @return array
     */
    public function getCheckoutSteps()
    {
        $contentCheckout = '';
        $stepCheckout = '';

        if(stristr($this->realUrl,'checkout/cart') != false) {
            $stepCheckout = 'BASKET';
        }
        if(stristr($this->realUrl,'checkout/multishipping/login/') != false) {
            $contentCheckout = $this->eLang[0][$this->lang];
            $stepCheckout = 'LOGIN';
        }
        if(stristr($this->realUrl,'checkout/multishipping/register/') != false) {
            $contentCheckout = $this->eLang[1][$this->lang];
            $stepCheckout = 'LOGINREGISTER';
        }
        if(stristr($this->realUrl,'checkout/multishipping/addresses/') != false) {
            $contentCheckout = $this->eLang[2][$this->lang].'/'.$this->eLang[3][$this->lang];
            $stepCheckout = 'SHIPPINGADRESS';
        }
        if(stristr($this->realUrl,'checkout/multishipping/shipping/') != false) {
            $contentCheckout = $this->eLang[2][$this->lang].'/'.$this->eLang[4][$this->lang];
            $stepCheckout = 'SHIPPINGMETHOD';
        }
        if(stristr($this->realUrl,'checkout/multishipping/billing/') != false) {
            $contentCheckout = $this->eLang[5][$this->lang];
            $stepCheckout = 'PAYMENT';
        }
        if(stristr($this->realUrl,'checkout/multishipping/overview/') != false ||
        stristr($this->realUrl,'paypal/express/review/') != false) {
            $contentCheckout = $this->eLang[36][$this->lang];
            $stepCheckout = 'REVIEW';
        }
        if(stristr($this->realUrl,'checkout/multishipping/success/') != false ||
        stristr($this->realUrl,'checkout/onepage/success/') != false ||
        stristr($this->realUrl,'uospayment/success/') != false) {
            $contentCheckout = $this->eLang[41][$this->lang];
            $stepCheckout = 'SUCCESS';
        }
        return array($contentCheckout,$stepCheckout);
    }

    /**
     * Get viewed product as econda object
     *
     * @return array
     */
    public function getProductView($billingOption, $storeId)
    {
        $item = Mage::registry('current_product');
        $getGroup = $this->getProductCategory($item->getId());
        if($getGroup) {
            $prodGroup = $getGroup;
        }
        else {
            $prodGroup = $this->eLang[39][$this->lang];
        }

        if(Mage::getStoreConfig($billingOption, $storeId) == '1') {
            if(Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), false) != 0) {
                $priceTax = Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), false);
            }
            else {
                $priceTax = Mage::helper('tax')->getPrice($item, $item->getPrice(), false);    
            }
        }
        else {
            if(Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), true) != 0) {
                $priceTax = Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), true);
            }
            else {
                $priceTax = Mage::helper('tax')->getPrice($item, $item->getPrice(), true);    
            }
        }
        $eItem = Mage::getModel('econda/item');
        $eItem->productName = trim($item->getName());
        if(Mage::getStoreConfig('econda/econda_settings/product_id', $storeId) == '1' && trim($item->getSKU()) != "") {
            $eItem->productID = $item->getSKU();
        }
        else {
            $eItem->productID = $item->getId();
        }
        $eItem->price = $this->convertPrice($priceTax);
        $eItem->quantity = '1';
        $eItem->productGroup = $prodGroup.'/'. str_replace('/', ' ', trim($item->getName()));
        return $eItem;
    }

    /**
     * Get added or removed product from basket as econda object
     *
     * @return array
     */
    public function getProductAddRmv($message, $storeId)
    {
        $isAddBasket = false;
        $billingOption = 'econda/econda_settings/billing_total';
        $isSku = Mage::getStoreConfig('econda/econda_settings/product_id', $storeId);
        $basketActions = Array();
        $nIdArray = array();
        $nQtyArray = array();
        $nNmeArray = array();
        $counter = 1;
        foreach($this->session->getQuote()->getAllItems() as $item) {
            if($isSku == '1' && trim($item->getSku()) != "") {
                $nIdArray[$counter] = $item->getSku();
            }
            else{
                $nIdArray[$counter] = $item->getproductId();
            }
            $nQtyArray[$counter] = $item->getQty();
            $nNmeArray[$counter] = $item->getItemId();
            $counter += 1;
        }
        if(!$this->session->getData('econda_card_id')){
            $this->session->setData('econda_card_id',$nIdArray);
            $this->session->setData('econda_card_qty',$nQtyArray);
            $this->session->setData('econda_card_nme',$nNmeArray);
            $oIdArray = $nIdArray;
            $oQtyArray = $nQtyArray;
            $oNmeArray = $nNmeArray;
            $eStarter = 1;
        }
        else {
            $oIdArray = $this->session->getData('econda_card_id');
            $oQtyArray = $this->session->getData('econda_card_qty');
            $oNmeArray = $this->session->getData('econda_card_nme');
            $eStarter = 0;
        }

        if($eStarter == 1) {
            foreach($this->session->getQuote()->getAllItems() as $item) {
                $getGroup = $this->getProductCategory($item->getproductId());
                if($getGroup) $prodGroup = $getGroup;
                else $prodGroup = $this->eLang[39][$this->lang];
                $eItem = Mage::getModel('econda/item');
                $eItem->productName = trim($item->getName());
                
                if($isSku == '1' && trim($item->getSku()) != "") {
                    $eItem->productID = Mage::getModel('catalog/product') ->load($item->getproductId())->getSku();
                    $eItem->productSku = $item->getSku();
                }
                else{
                    $eItem->productID = $item->getproductId();
                    $eItem->productSku = Mage::getModel('catalog/product') ->loadByAttribute('sku', $item->getSku())->getId();
                }
 
                // calculate tax
                if(Mage::getStoreConfig($billingOption, $storeId) == '1') {
                    if(Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), true, null, null, $item->getTaxClassId(), $storeId, true) != 0) {
                        $priceTax = Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), true, null, null, $item->getTaxClassId(), $storeId, true);
                    }
                    else {
                        $priceTax = Mage::helper('tax')->getPrice($item, $item->getPrice(), true, null, null, $item->getTaxClassId(), $storeId, true);
                    }
                }
                else {
                    if(Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), true, null, null, $item->getTaxClassId(), $storeId, false) != 0) {
                        $priceTax = Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), true, null, null, $item->getTaxClassId(), $storeId, false);
                    }
                    else {
                        $priceTax = Mage::helper('tax')->getPrice($item, $item->getPrice(), true, null, null, $item->getTaxClassId(), $storeId, false);
                    }
                }
                $eItem->price = $this->convertPrice($priceTax);
                $eItem->quantity = $item->getQty();
                $eItem->productGroup = $prodGroup.'/'.trim($item->getName());
                if($eItem->price != '0.00') {
                    $basketActions[] = Array('add', $eItem);
                }
                $isAddBasket = true;
            }
        }
        else {
            foreach($this->session->getQuote()->getAllItems() as $item) {
                $emosAction = false;
                $addRmv = false;
                $idKey = array_search($item->getItemId(),$oNmeArray);
                if($idKey == false) {
                    $emosAction = 'addBasket';
                    $basketQty = $item->getQty();
                }
                if($idKey != false) {
                    if($oQtyArray[$idKey] != $item->getQty()) {
                        if($oQtyArray[$idKey] > $item->getQty() && $oNmeArray[$idKey] == $item->getItemId()) {
                            $emosAction = 'rmvBasket';
                            $basketQty = $oQtyArray[$idKey] - $item->getQty();
                        }
                        if($oQtyArray[$idKey] < $item->getQty() && $oNmeArray[$idKey] == $item->getItemId()) {
                            $emosAction = 'addBasket';
                            $basketQty = $item->getQty() - $oQtyArray[$idKey];
                        }
                    }
                }
                if($emosAction != false) {
                    $getGroup = $this->getProductCategory($item->getproductId());
                    if($getGroup) $prodGroup = $getGroup;
                    else $prodGroup = $this->eLang[39][$this->lang];
                    $eItem = Mage::getModel('econda/item');
                    $eItem->productName = trim($item->getName());
                    if($isSku == '1' && trim($item->getSku()) != "") {
						$conf_sku =	Mage::getModel('catalog/product') ->load($item->getproductId()) ->getSku();
                        $eItem->productID = $conf_sku ;
						$eItem->productSku = $item->getSku();
                    }
                    else{
						$conf_id =	Mage::getModel('catalog/product') ->loadByAttribute('sku',  $item->getSku())->getId();
                        $eItem->productID = 	$item->getproductId();
						$eItem->productSku =$conf_id ;
                    }
                    if(Mage::getStoreConfig($billingOption, $storeId) == '1') {
                        if(Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), true, null, null, $item->getTaxClassId(), $storeId, true) != 0) {
                            $priceTax = Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), true, null, null, $item->getTaxClassId(), $storeId, true); // with tax
                        }
                    else {
                            $priceTax = Mage::helper('tax')->getPrice($item, $item->getPrice(), true, null, null, $item->getTaxClassId(), $storeId, true); // with tax
                        }
                    }
                    else {
                        if(Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), true, null, null, $item->getTaxClassId(), $storeId, false) != 0) {
                            $priceTax = Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), true, null, null, $item->getTaxClassId(), $storeId, false); // with tax
                        }
                        else {
                            $priceTax = Mage::helper('tax')->getPrice($item, $item->getPrice(), true, null, null, $item->getTaxClassId(), $storeId, false); // with tax
                        }
                    }
                    $eItem->price = $this->convertPrice($priceTax);
                    $eItem->quantity = $basketQty;
                    $eItem->productGroup = $prodGroup.'/'.trim($item->getName());
                    if($emosAction == 'addBasket' && stristr($message,'error-msg') == false && $eItem->price != '0.00') {
                        $basketActions[] = Array('add', $eItem);
                        $isAddBasket = true;
                        if(stristr($this->realUrl,'checkout/cart') == false) {
                            $addRmv = true;
                        }
                    }
                    if($emosAction == 'rmvBasket' && stristr($message,'error-msg') == false && $eItem->price != '0.00') {
                        if(!$addRmv) {
                            $basketActions[] = Array('rmv', $eItem);
                        }
                        $isAddBasket = true;
                    }
                }
            }
        }

        $this->session->setData('econda_card_qty',$nQtyArray);
        $this->session->setData('econda_card_id',$nIdArray);
        $this->session->setData('econda_card_nme',$nNmeArray);
        return Array($isAddBasket, $billingOption, $basketActions);
    }

    /**
     * Get products inside basket on checkout success as econda object
     * include some fallback code
     *
     * @return array
     */
    public function getBasket($billingOption, $storeId, $entityId)
    {
        try {
            $basket = array();
            $bCounter = 0;
            $prefix = Mage::getConfig()->getTablePrefix();
            $db = Mage::getSingleton('core/resource')->getConnection('core_write');
            $table = $prefix.'sales_flat_quote_item';
            $result = $db->query("SELECT product_id,name,qty,price,parent_item_id,sku FROM $table WHERE quote_id = $entityId");
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                if($row['parent_item_id'] == '') {
                    $getGroup = Mage::getModel('econda/basket')->getProductCategory($row['product_id']);
                    if($getGroup) {
                        $prodGroup = $getGroup;
                    }
                    else {
                        $prodGroup = $this->eLang[39][$this->lang];
                    }
                    $item = Mage::getModel('catalog/product')->load($row['product_id']);
                    $eItem = Mage::getModel('econda/item');
                    $eItem->productName = trim($row['name']);
                    if(Mage::getStoreConfig('econda/econda_settings/product_id', $storeId) == '1' && trim($row['sku']) != "") {
						$eItem->productID = Mage::getModel('catalog/product') ->load( $row['product_id'])->getSku();
                        $eItem->productSku = trim($row['sku']);
                    }
                    else{
                        $eItem->productID = $row['product_id'];
                        $eItem->productSku = Mage::getModel('catalog/product') ->loadByAttribute('sku',$row['sku'] )->getId();
                    }
                    $eItem->quantity = number_format($row['qty'],0);
                    $discount = $row['product_id'];
                    $table = $prefix.'catalog_product_entity_tier_price';
                    $resultp = $db->query("SELECT qty,value FROM $table WHERE entity_id = $discount");
                    $rowp = $resultp->fetch(PDO::FETCH_ASSOC);
                    $discountcheck = 0;
                    if($rowp) {
                        if($eItem->quantity >= $rowp['qty']) {
                            $discountcheck = $rowp['value'];
                        }
                    }
                    if(Mage::getStoreConfig($billingOption, $storeId) == '1') {
                        if($discountcheck > 0) {
                            $priceTax = Mage::helper('tax')->getPrice($item, $discountcheck, false, null, null, $item->getTaxClassId(), $storeId, true);
                        }
                        else if(Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), false, null, null, $item->getTaxClassId(), $storeId, true) != 0) {
                            $priceTax = Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), false, null, null, $item->getTaxClassId(), $storeId, true);
                        }
                        else {
                            $priceTax = Mage::helper('tax')->getPrice($item, $item->getPrice(), false, null, null, $item->getTaxClassId(), $storeId, true);
                        }
                    }
                    else {
                        if($discountcheck > 0) {
                            $priceTax = Mage::helper('tax')->getPrice($item, $discountcheck, false, null, null, $item->getTaxClassId(), $storeId, false);
                        }
                        else if(Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), false, null, null, $item->getTaxClassId(), $storeId, false) != 0) {
                            $priceTax = Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), false, null, null, $item->getTaxClassId(), $storeId, false);
                        }
                        else {
                            $priceTax = Mage::helper('tax')->getPrice($item, $item->getPrice(), false, null, null, $item->getTaxClassId(), $storeId, false);
                        }
                    }
                    $eItem->price = Mage::getModel('econda/basket')->convertPrice($priceTax);
                    $eItem->productGroup = $prodGroup.'/'.trim($row['name']);
                    $basket[$bCounter] = $eItem;
                    $bCounter += 1;
                }
            }
            return $basket;
        } catch (Exception $error) {
            Mage::log("Econda on success no basket available: ".$error); // Write something into log for later support
        }
        return null;
    }

    /**
     * Get billing on checkout success as econda object
     * include some fallback code
     *
     * @return array
     */
    public function getBilling($billingOption, $storeId)
    {
        try {
            $db = Mage::getSingleton('core/resource')->getConnection('core_write');
            $prefix = Mage::getConfig()->getTablePrefix();
            if(stristr($this->realUrl,'checkout/onepage/success/') != false ||
            stristr($this->realUrl,'uospayment/success/') != false) {//onepage
                $lastOrder = Mage::getSingleton('checkout/type_onepage')->getLastOrderId();
                $lastOrderId = $lastOrder;
                $order = Mage::getModel('sales/order')->loadByIncrementId($lastOrderId);
            	$entityId = $order->getQuoteId();
            }
            else{//multipage
                $entityId = intval($this->session->getData('econda_order_id'));
                $orderIds = Mage::getSingleton('core/session')->getOrderIds(false);
                if($orderIds && is_array($orderIds)) {
                $lastOrderId =  implode(' / ', $orderIds);
                }
                // fallback if there is no result
                else {
                    $customerId = Mage::getSingleton('customer/session')->getCustomerId();
                    $table = $prefix.'sales_order';
                    $result = $db->query("SELECT increment_id FROM $table WHERE customer_id = $customerId");
                    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                        $lastOrderId = $row['increment_id'];
                    }
                }
            }
            // get customer address from last order
            $table = $prefix.'sales_flat_quote_address';
            $result = $db->query("SELECT customer_id,city,postcode,country_id,base_grand_total,base_subtotal,base_tax_amount,base_shipping_tax_amount FROM $table WHERE quote_id = $entityId AND address_type = 'shipping'");
            $priceTotal = 0;
            $custId = 0;
            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $custCountry = $row['country_id'];
                $custPostCode = $row['postcode'];
                $custCity = $row['city'];
                $custId = $row['customer_id'];
                $ordId = $lastOrderId;
                if(Mage::getStoreConfig($billingOption, $storeId) == '1') {
                    $priceTotal += $row['base_subtotal'];
                }
                else if(Mage::getStoreConfig($billingOption, $storeId) == '2') {
                    $priceTotal += $row['base_subtotal'] + $row['base_tax_amount'] - $row['base_shipping_tax_amount'];
                }
                else {
                    $priceTotal += $row['base_grand_total'];
                }
            }
            $priceTotal = Mage::getModel('econda/basket')->convertPrice($priceTotal);
            $custAdress = $custCountry.'/'.substr($custPostCode,0,1).'/'.substr($custPostCode,0,2).'/'.$custCity.'/'.$custPostCode;
            return Array($ordId,$custId,$priceTotal,$custCountry,$custPostCode,$custCity,$entityId);
        } catch (Exception $error) {
            Mage::log("Econda on success no billing available: ".$error); // Write something into log for later support
        }
        return null;
    }
    /**
     * Change price format for econda
     *
     * @return string
     */
    public function convertPrice($price)
    {
        $price = number_format($price,2);
        $corPrice = substr($price,0,-3);
        $corPrice = str_replace(',','',$corPrice);
        $corPrice = str_replace('.','',$corPrice);
        return $corPrice.'.'.substr($price,-2);
    }
}