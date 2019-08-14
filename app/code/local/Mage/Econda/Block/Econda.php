<?php

/*
Copyright (c) 2004 - 2010 ECONDA GmbH Karlsruhe
All rights reserved.

ECONDA GmbH
Eisenlohrstr. 43
76135 Karlsruhe
Tel.: 0721/663035-0
Fax.: 0721 663035-10
info@econda.de
www.econda.de

author: Edgar Gaiser <gaiser@econda.de>

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice,
      this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice,
      this list of conditions and the following disclaimer in the documentation
      and/or other materials provided with the distribution.
    * Neither the name of the ECONDA GmbH nor the names of its contributors may
      be used to endorse or promote products derived from this software without
      specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

/** This Class is a implementation of a PHP Function to include
 *  ECONDA Tracking into Magento Shop-Systems.
 */

//require_once Mage::getBaseDir().DS.'/app/Mage.php';
//Mage::app ();

class Mage_Econda_Block_Econda extends Mage_Core_Block_Template
{
    var $econdaInt = false;
    
    public function __construct()
    {
		parent::__construct();
        $this->setTemplate('econda/tracker.phtml');
    }
    
    public function getEmos() 
    {
        require_once Mage::getModuleDir('', 'Mage_Econda').DS.'Block'.DS.'emos.php';
        /*
         * config get language
         */
    	$storeId = Mage::app()->getStore()->getId();
    	$langValue = 'econda/econda/tracking_language';
    	$langPath = Mage::getStoreConfig($langValue, $storeId);
    	 
    	if($langPath == '0') {
    		$langFile = 'german';
    	}
    	else if($langPath == '1') {
    		$langFile = 'english';
    	}
    	else{
    		$langFile = 'german';
    	}
        require Mage::getModuleDir('', 'Mage_Econda').DS.'Language'.DS.$langFile.'.php';

        /*
         * config get emos version
         */

        $trackValue = 'econda/econda/tracking_version';
        if(Mage::getStoreConfig($trackValue, $storeId) == '' || Mage::getStoreConfig($trackValue, $storeId) == '0') {
            $checkVersion = $this->emosVersion();
            if(Mage::getStoreConfig($trackValue, $storeId) == '' || $checkVersion != '0') {
                Mage::getModel('core/config')->saveConfig($trackValue,$checkVersion);
                Mage::getModel('core/config')->cleanCache();
            }            
        }
        else{
            $checkVersion = Mage::getStoreConfig($trackValue, $storeId);
        }

    	/*
    	 * path to emos2.js
    	 */
		$jsUrl = $this->getJsUrl();
		$pathToFile = $jsUrl.'tracker/';
		
		/*
		 * start of emos string
		 */
	    $emosString = "\n\n<!-- Start Econda-Monitor M116 -->\n\n";

		$emos = new EMOS($pathToFile);
        
        if($checkVersion == '2') {
           $emos->trackMode(intval($checkVersion));  
        }
        
        $emos->addCdata();

		Mage::getSingleton('core/session', array('name'=>'frontend'));
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
        $session = Mage::getSingleton('checkout/session');	
        
		/*
		 *  emos addContent
		 */
	    $storeCode = Mage::app()->getStore()->getCode();
	    $storeNameLoad = Mage::getModel('core/store_group')->load($storeId);
        $storeName = $storeNameLoad->getName();
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $getPath = Mage::helper('catalog')->getBreadcrumbPath($this->getCategory());
        if(sizeof($getPath) > 0) {
        	$contentPath = "Start";	
        }
        else{
        	$contentPath = "";
        }
        
        $realUrl = Mage::helper('core/url')->getCurrentUrl();
        
        //content for checkout
        $contentCheckout = '';
        $stepCheckout = '';
        
    	if(stristr($realUrl,'checkout/multishipping/login/') != false) {
			$contentCheckout = $eLang[0];
			$stepCheckout = 'LOGIN';
		}
    	if(stristr($realUrl,'checkout/multishipping/register/') != false) {
			$contentCheckout = $eLang[1];
			$stepCheckout = 'LOGINREGISTER';
		}	
        if(stristr($realUrl,'checkout/multishipping/addresses/') != false) {
			$contentCheckout = $eLang[2].'/'.$eLang[3];
			$stepCheckout = 'SHIPPINGADRESS';
		}
        if(stristr($realUrl,'checkout/multishipping/shipping/') != false) {
			$contentCheckout = $eLang[2].'/'.$eLang[4];
			$stepCheckout = 'SHIPPINGMETHOD';
		}
        if(stristr($realUrl,'checkout/multishipping/billing/') != false) {
			$contentCheckout = $eLang[5];
			$stepCheckout = 'PAYMENT';
		}
        if(stristr($realUrl,'checkout/multishipping/overview/') != false || stristr($realUrl,'paypal/express/review/') != false) {
			$contentCheckout = $eLang[36];
			$stepCheckout = 'REVIEW';
		}	
        if(stristr($realUrl,'checkout/multishipping/success/') != false || stristr($realUrl,'checkout/onepage/success/') != false || stristr($realUrl,'uospayment/success/') != false) {
			$contentCheckout = $eLang[41];
			$stepCheckout = 'SUCCESS';
        }
	
        foreach($getPath as $pathID) {
            $contentPath .= '/'.$pathID['label'];
        }
        
		$getTagName = "";
        $getTagId = "";
        if(stristr($realUrl,'q=') != false) $contentPath .= 'Start/'.$eLang[6].'/'.$eLang[7];
        if(stristr($realUrl,'/about-') != false || stristr($realUrl,'/impressum') != false) $contentPath .= 'Start/'.$eLang[9].'/'.$eLang[10]; 
        if(stristr($realUrl,'/datenschutz') != false) $contentPath .= 'Start/'.$eLang[9].'/'.$eLang[11]; 
        if(stristr($realUrl,'/agb') != false) $contentPath .= 'Start/'.$eLang[9].'/'.$eLang[12];
        if(stristr($realUrl,'/rss') != false) $contentPath .= 'Start/'.$eLang[9].'/'.$eLang[13];
        if(stristr($realUrl,'/customer-service') != false) $contentPath .= 'Start/'.$eLang[9].'/'.$eLang[14];
        if(stristr($realUrl,'/seo_sitemap') != false) $contentPath .= 'Start/'.$eLang[9].'/'.$eLang[15];
        if(stristr($realUrl,'/seo_sitemap/product') != false) $contentPath .= '-'.$eLang[16];
        if(stristr($realUrl,'/term/popular') != false) $contentPath .= 'Start/'.$eLang[9].'/'.$eLang[17]; 
		if(stristr($realUrl,'/catalogsearch/advanced') != false) {
			$contentPath .= 'Start/'.$eLang[6].'/'.$eLang[8];
			if(stristr($realUrl,'catalogsearch/advanced/result/?') != false) {
				$contentPath .= '/'.$eLang[18];
			}
		} 
		if(stristr($realUrl,'/contacts/') != false) $contentPath .= 'Start/'.$eLang[9].'/'.$eLang[19]; 
        if(stristr($realUrl,'/login') != false) $contentPath .= 'Start/'.$eLang[0];
        if(stristr($realUrl,'/customer/account/create/') != false) $contentPath .= 'Start/'.$eLang[20];
        if(stristr($realUrl,'/customer/account/forgotpassword/') != false) $contentPath .= 'Start/'.$eLang[21];
        if(stristr($realUrl,'/customer/') != false && stristr($realUrl,'/customer/account/login/') == false  && stristr($realUrl,'/customer/account/forgotpassword/') == false && stristr($realUrl,'/customer/account/create/') == false) $contentPath .= 'Start/'.$eLang[22];
        if(stristr($realUrl,'/customer/account/') != false && stristr($realUrl,'/customer/account/edit/') == false && stristr($realUrl,'/customer/account/login/') == false && stristr($realUrl,'/customer/account/forgotpassword/') == false && stristr($realUrl,'/customer/account/create/') == false) $contentPath .= '/'.$eLang[23];
        if(stristr($realUrl,'/customer/account/edit/') != false) $contentPath .= '/'.$eLang[24];
        if(stristr($realUrl,'/customer/address/') != false) $contentPath .= '/'.$eLang[25];
        if(stristr($realUrl,'/sales/order/history/') != false) $contentPath .= 'Start/'.$eLang[22].'/'.$eLang[26];
        if(stristr($realUrl,'/review/customer/') != false) $contentPath .= '/'.$eLang[27];
        if(stristr($realUrl,'/tag/customer/') != false) $contentPath .= '/'.$eLang[28];
        if(stristr($realUrl,'/newsletter/manage/') != false) $contentPath .= 'Start/'.$eLang[22].'/'.$eLang[29];
        if(stristr($realUrl,'/wishlist/') != false) $contentPath .= 'Start/'.$eLang[22].'/'.$eLang[30];
        if(stristr($realUrl,'/downloadable/customer/products/') != false) $contentPath .= '/'.$eLang[31];
        if(stristr($realUrl,'checkout/cart') != false) $contentPath .= 'Start/'.$eLang[32].'/'.$eLang[33]; 
        if(stristr($realUrl,'checkout/onepage/success') != false) $contentPath .= 'Start/'.$eLang[32].'/'.$eLang[41];
        if(stristr($realUrl,'/checkout/multishipping') != false) $contentPath .= 'Start/'.$eLang[32].'/'.$contentCheckout;
        if(stristr($realUrl,'/review') != false && stristr($realUrl,'/review/customer/') == false) $contentPath .= '/'.$eLang[34];
        if($contentPath == '/Bewertungen') $contentPath = 'Start/'.$eLang[9].'/'.$eLang[37];
        if(stristr($realUrl,'/tag/') != false && stristr($realUrl,'/tag/customer/') == false) {
        	$contentPath .= 'Start/'.$eLang[35];
        	$tagIdPos = strpos($realUrl,'/tagId/');  //tag id from url, hmmm.
        	$getTagId = substr($realUrl,$tagIdPos+6);
        	$getTagId = intval(str_replace('/','',$getTagId));
        	$tableTag = $tablePrefix.'tag';
 			$result = $db->query("SELECT name FROM $tableTag WHERE tag_id = $getTagId");
 			$row = $result->fetch(PDO::FETCH_ASSOC);        	
        	$getTagName .= $row['name'];
        	if(trim($getTagName) == ''){
        		$contentPath .= '/'.$eLang[36];
        	}
        	else{
				$contentPath .= '/'.$getTagName;
        	}
        }

         /* not specified sites
         *  check if get current url match
         */
        if(trim($contentPath) == '') {
            $checkPath = str_replace($_SERVER['SERVER_NAME'],"",$realUrl);
        	$checkPath = str_replace("https://","",$checkPath);
        	$checkPath = str_replace("http://","",$checkPath);
        	if(strpos($checkPath,"?") != false) {
        		$reqPos = strPos($checkPath,"?");
        		$checkPath = substr($checkPath,0,$reqPos);
        	}
        	if(substr($checkPath,-1) == "/") {
        	    $checkPath = substr($checkPath,0,-1);
        	}
        	$checkPath = str_replace("/index.php/".$storeCode,"",$checkPath);
        	$checkPath = str_replace("/index.php","",$checkPath);
        	$codeLen = strlen($storeCode);
        	if(substr($checkPath,0,$codeLen+1) == '/'.$storeCode) {
        		$checkPath = substr($checkPath,$codeLen+1);
        	}
        	if(trim($checkPath) != '') {
        		$urlExtExp = explode("/",$checkPath);
				$urlExtO = '';
				for($i = 0; $i < sizeof($urlExtExp); $i++) {
					if(trim($urlExtExp[$i]) != ''){
					   $urlExtO .= ucfirst($urlExtExp[$i]).'/';
					}
				}       			
        		$contentPath = 'Start/'.$eLang[38].'/'.substr($urlExtO,0,-1);
        		$contentPath = str_replace("//","/",$contentPath);
        	}
        }

        //then if php self match
        if(trim($contentPath == '')){
			$urlExt = $_SERVER['PHP_SELF'];
			$urlExt = str_replace("/index.php/".$storeCode."/","",$urlExt);
    		$urlExt = str_replace("index.php","",$urlExt);
            $codeLen = strlen($storeCode);
        	if(substr($urlExt,0,$codeLen+1) == '/'.$storeCode) {
        		$urlExt = substr($urlExt,$codeLen+1);
        	}   		
    		$urlExt = str_replace(".html","",$urlExt);
    		$urlExt = str_replace("//","/",$urlExt);
    	
			if(substr($urlExt,0,1) == '/') {
				$urlExt = substr($urlExt,1);
			}
			
			if(substr($urlExt,-1) == '/') {
				$urlExt = substr($urlExt,0,-1);
			}
			$urlExtExp = explode("/",$urlExt);
			$urlExtO = '';
			for($i = 0; $i < sizeof($urlExtExp); $i++) {
				$urlExtO .= ucfirst($urlExtExp[$i]).'/';
			}
			$urlExtO = substr($urlExtO,0,-1);        	
        	$contentPath = 'Start/'.$eLang[38].'/'.$urlExtO;
            if(trim($contentPath == '' || $contentPath == 'Start/'.$eLang[38].'/' || $contentPath == 'Start/'.$eLang[38].'/index.php/')){
            	$contentPath = 'Start';
        	}        	
        }

        // if not onePage checkout steps
        if(stristr($realUrl,'checkout/onepage/') == false) {
	    	$emos->addContent($contentPath); 
        }
        
        //onePage checkout success
        if(stristr($realUrl,'checkout/onepage/success') != false) {
        	$emos->addContent($contentPath);
        }
        
        /*
         * emos addPageID
         */
        $emos->addPageID(md5($contentPath)); //same as $contentPath	

        /*
         * emos addSiteID
         */
        $siteidValue = 'econda/econda/tracking_siteid';
        $siteidOv = Mage::getStoreConfig($siteidValue, $storeId);
        if(!empty($siteidOv) && trim($siteidOv) != "") {
            $siteName = trim($siteidOv);   
        }
        else {
            $siteName = $_SERVER['SERVER_NAME'];            
        }                 
        $emos->addSiteID($siteName);
        
        /*
         * emos addLangID
         */
        $getLocale = Mage::app()->getStore()->getStoreId(); // locale as shopID        
		$emos->addLangID($getLocale);
		
        // save pageID, siteID and langID into session for onePageCheckout
        $jsSessionData = $contentPath.','.$siteName.','.$getLocale;
        $session->setData('econda_content',$jsSessionData);
		
		/*
		 *  emos addSearch
		 */
		if(Mage::helper('catalogSearch')->getQueryText() != null) {
			$searchQuery = Mage::helper('catalogSearch')->getQueryText();
			$getQuery = Mage::helper('catalogSearch')->getQuery();
            $searchQuery = $getQuery->query_text; 
            $searchHits = $getQuery->num_results; 
			$emos->addSearch($searchQuery, $searchHits);
		}

		/*
		 * emos addSearch advanced
		 */
		if(stristr($realUrl,'/catalogsearch/advanced/result/?') != false) {
			$searchQuery = '';
			$searchCriterias = Mage::getSingleton('catalogsearch/advanced')->getSearchCriterias();
			foreach($searchCriterias as $val) {
				$searchQuery .= $val['value'];
			}
			$productCollection = Mage::getSingleton('catalogsearch/advanced')->getProductCollection();
			$emos->addSearch($searchQuery, sizeof($productCollection));
   		 }			

		/*
		 * emos Basket add / remove item
		 */
       	if(stristr($realUrl,'checkout/cart') != false) {
       		$emos->addOrderProcess("1_".$eLang[33]);
       	}
       	
       	$isAddBasket = false;
        $billingOption = 'econda/econda/billing_total';
       	
		//bugfix for basket after customer re-login
		if(Mage::getSingleton('customer/session')->isLoggedIn() == 1) {
			if($session->getData('econda_logged') != 1) {
				$oldBasket = true;	
			}
			else{
				$oldBasket = false;
			}
		}
		else{
			$oldBasket = false;
		}
        $nIdArray = array();
        $nQtyArray = array();
        $nNmeArray = array();
        $counter = 1;
        
    	foreach($session->getQuote()->getAllItems() as $item) {
			$nIdArray[$counter] = $item->getproductId();
			$nQtyArray[$counter] = $item->getQty();	
			$nNmeArray[$counter] = $item->getItemId();
			$counter += 1;
		}
        if(!$session->getData('econda_card_id')){
            $session->setData('econda_card_id',$nIdArray);
            $session->setData('econda_card_qty',$nQtyArray);
            $session->setData('econda_card_nme',$nNmeArray);
            $oIdArray = $nIdArray;
            $oQtyArray = $nQtyArray;
            $oNmeArray = $nNmeArray;
            $eStarter = 1;
        }
        else {
            $oIdArray = $session->getData('econda_card_id');
            $oQtyArray = $session->getData('econda_card_qty');
            $oPrcArray = $session->getData('econda_card_prc');
            $oNmeArray = $session->getData('econda_card_nme');
            $eStarter = 0;            	
        }

        if(!$oldBasket) {
			if($eStarter == 1) {
				foreach($session->getQuote()->getAllItems() as $item) {
           		   $getGroup = $this->getProductCategory($item->getproductId());
         		   if($getGroup) $prodGroup = $getGroup;
        		   else $prodGroup = $eLang[39];							
				   $eItem = new EMOS_Item();
				   $eItem->productName = trim($item->getName());
				   $eItem->productID = $item->getproductId();
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
					  $emos->addToBasket($eItem);
				   }
				   $isAddBasket = true;
				}			
			}
			else {
				foreach($session->getQuote()->getAllItems() as $item) {
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
        				else $prodGroup = $eLang[39];							
						$eItem = new EMOS_Item();
						$eItem->productName = trim($item->getName());
						$eItem->productID = $item->getproductId();
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
						if($emosAction == 'addBasket' && stristr($this->getMessagesBlock()->getGroupedHtml(),'error-msg') == false && $eItem->price != '0.00') {
					       $emos->addToBasket($eItem);
						   $isAddBasket = true;
						   if(stristr($realUrl,'checkout/cart') == false) {
						    	$addRmv = true;
						    }
						}
						if($emosAction == 'rmvBasket' && stristr($this->getMessagesBlock()->getGroupedHtml(),'error-msg') == false && $eItem->price != '0.00') {
						    if(!$addRmv) {
						    	$emos->removeFromBasket($eItem);
						    }
							$isAddBasket = true;
					    }
					}
				}
			}
        }
        
		$session->setData('econda_card_qty',$nQtyArray);
		$session->setData('econda_card_id',$nIdArray);
		$session->setData('econda_card_nme',$nNmeArray);
			
        /*
		 * emos addDetailView
		 */
         if(Mage::registry('current_product') && !$isAddBasket) {
        	$item = Mage::registry('current_product');
            $getGroup = $this->getProductCategory($item->getId());
         	if($getGroup) $prodGroup = $getGroup;
        	else $prodGroup = $eLang[39];
			$eItem = new EMOS_Item();
			$eItem->productName = trim($item->getName());
			$eItem->productID = $item->getId();
            if(Mage::getStoreConfig($billingOption, $storeId) == '1') { 
                if(Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), false, null, null, $item->getTaxClassId(), $storeId, true) != 0) {
                     $priceTax = Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), false, null, null, $item->getTaxClassId(), $storeId, true); // with tax                    
                }
                else {
                     $priceTax = Mage::helper('tax')->getPrice($item, $item->getPrice(), false, null, null, $item->getTaxClassId(), $storeId, true); // with tax                                
                }                         
            }
            else {
                if(Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), false, null, null, $item->getTaxClassId(), $storeId, false) != 0) {
                     $priceTax = Mage::helper('tax')->getPrice($item, $item->getFinalPrice(), false, null, null, $item->getTaxClassId(), $storeId, false); // with tax                    
                }
                else {
                     $priceTax = Mage::helper('tax')->getPrice($item, $item->getPrice(), false, null, null, $item->getTaxClassId(), $storeId, false); // with tax                                
                }    
            }                 
            $eItem->price = $this->convertPrice($priceTax);
			$eItem->quantity = '1';
			$eItem->productGroup = $prodGroup.'/'.trim($item->getName());
			$emos->addDetailView($eItem);
       	}   				

       	/*
       	 * emos addContact
       	 */
       	if(stristr($realUrl,'/contacts/index/') != false) {
       		$emos->addContact($eLang[40]);
       	}
       	
       	/*
       	 * emos addRegister
       	 */
         if(stristr($realUrl,'customer/account/index/') != false || stristr($realUrl,'checkout/multishipping/addresses/') != false){
   	    	 if(stristr($this->getMessagesBlock()->getGroupedHtml(),'success-msg') != false) {
       		 	$customerId = Mage::getSingleton('customer/session')->getCustomerId();
       		 	$emos->addRegister($customerId,0);
       		 }
       	 }    
       	    	
       	/*
       	 * emos addLogin
       	 */
       	 //login success
       	 $isLoggedIn = Mage::getSingleton('customer/session')->isLoggedIn();
       	 if($isLoggedIn == 1 && $session->getData('econda_logged') != 2) {
       	 	$session->setData('econda_logged','1');	
       	 }
       	 if($session->getData('econda_logged') == 1) {
       	 	$customerId = Mage::getSingleton('customer/session')->getCustomerId();
       	 	$emos->addLogin($customerId,0);
       	 	$session->setData('econda_logged','2');
       	 }
       	 	
       	 //login failed
       	 if(stristr($realUrl,'customer/account/login/') != false || stristr($realUrl,'checkout/multishipping/login/') != false){
   	    	 if(stristr($this->getMessagesBlock()->getGroupedHtml(),'error-msg') != false) {
       		 	$customerId = Mage::getSingleton('customer/session')->getCustomerId();
       		 	$emos->addLogin($customerId,1);
       		 }
       	 }
         
  	    /*
  	     * emos orderProcess multishipping checkout
  	     */
         switch ($stepCheckout) { 
       		case 'LOGIN':	
       			$emos->addOrderProcess("2_".$eLang[0]);
       			break;
       		case 'LOGINREGISTER':	
       			$emos->addOrderProcess("3_".$eLang[1]);
       			break;
       		case 'SHIPPINGADRESS':	
       			$emos->addOrderProcess("4_".$eLang[2]."/".$eLang[3]);
       			break;
       		case 'SHIPPINGMETHOD':
       			$emos->addOrderProcess("4_".$eLang[2]."/".$eLang[4]);
       			break;
       		case 'PAYMENT':
       			$emos->addOrderProcess("5_".$eLang[5]);
       			break;
       		case 'REVIEW':
 				$emos->addOrderProcess("6_".$eLang[42]);
				$actOrder = Mage::getSingleton('checkout/session')->getQuoteId();
				$session->setData('econda_order_id',$actOrder);
				break;
       		case 'SUCCESS':
       			$emos->addOrderProcess("7_".$eLang[41]);
		
			/*
			 * addEmosBillingPageArray checkout
			 */	
             
                try {
       		 	     if(stristr($realUrl,'checkout/onepage/success/') != false || stristr($realUrl,'uospayment/success/') != false) {//onepage
                        $lastOrder = Mage::getSingleton('checkout/type_onepage')->getLastOrderId();
                        $lastOrderId = $lastOrder;
                        $quote = Mage::getSingleton('checkout/type_onepage')->getQuote();
                        $entityId = $quote->getData('entity_id');
                        if(empty($entityId)) {
                                $tableSfq = $tablePrefix.'sales_flat_quote';
                                $result = $db->query("SELECT entity_id FROM $tableSfq WHERE reserved_order_id = $lastOrder");
                                $row = $result->fetch(PDO::FETCH_ASSOC);            
                                $entityId = $row['entity_id'];
                        }
      	 	 	     }
       		 	     else{//multipage
                        $entityId = intval($session->getData('econda_order_id'));
                        $orderIds = Mage::getSingleton('core/session')->getOrderIds(false);
                        if($orderIds && is_array($orderIds)) {
                            $lastOrderId =  implode(' / ', $orderIds);
                        } 
                        else {
                            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
                            $tableSfqos = $tablePrefix.'sales_order'; 
                            $result = $db->query("SELECT increment_id FROM $tableSfqos WHERE customer_id = $customerId");
                            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                $lastOrderId = $row['increment_id'];    
                            } 
                        }                      
       		 	     }

                    $tableSfqa = $tablePrefix.'sales_flat_quote_address'; 
                    $result = $db->query("SELECT customer_id,city,postcode,country_id,base_grand_total,base_subtotal,base_tax_amount,base_shipping_tax_amount FROM $tableSfqa WHERE quote_id = $entityId and address_type = 'shipping'");
                    $priceTotal = 0;
                
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
                
                    $priceTotal = $this->convertPrice($priceTotal);               
                    $custAdress = $custCountry.'/'.substr($custPostCode,0,1).'/'.substr($custPostCode,0,2).'/'.$custCity.'/'.$custPostCode;
                    $emos->addEmosBillingPageArray($ordId,$custId,$priceTotal,$custCountry,$custPostCode,$custCity);

       	   /*
       	    * addEmosBasketPageArray checkout
       	    */	       	 	
       	 		    $basket = array();
       	 		    $bCounter = 0;
       	 		    $tableSfqi = $tablePrefix.'sales_flat_quote_item';
				    $result = $db->query("SELECT product_id,name,qty,price,parent_item_id FROM $tableSfqi WHERE quote_id = $entityId");

       		 	    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
       		 		    if($row['parent_item_id'] == '') {
  		  		 		    $getGroup = $this->getProductCategory($row['product_id']);
    			 		     if($getGroup) $prodGroup = $getGroup;
    			 		     else $prodGroup = $eLang[39];
                             $item = Mage::getModel('catalog/product')->load($row['product_id']);
 	   			 		     $eItem = new EMOS_Item();
    			 		     $eItem->productName = trim($row['name']);
    			 		     $eItem->productID = $row['product_id'];
                             $eItem->quantity = number_format($row['qty'],0);                        
                             $discount = $row['product_id'];
                             $tablePrpr = $tablePrefix.'catalog_product_entity_tier_price';
                             $resultp = $db->query("SELECT qty,value FROM $tablePrpr WHERE entity_id = $discount");
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
                             $eItem->price = $this->convertPrice($priceTax);                         
    			 		     $eItem->productGroup = $prodGroup.'/'.trim($row['name']);
    			 		     $basket[$bCounter] = $eItem;
    			 		     $bCounter += 1;
       		 		    }
    		 	    }        		
       	 	        $emos->addEmosBasketPageArray($basket);	
                } catch (Exception $e) {
                    Mage::log("Econda on success no billing available");
                }
                break;	
		}																
			
		$emosString .= $emos->toString();	
		$emosString .= "\n<!-- End Econda-Monitor -->\n\n";
        if(!$this->econdaInt) {
            $this->econdaInt = true;
    	    return $emosString;
        }
        else {
            return "";    
        }
    }
    
    /*
     * get highest level category path for a product
     */
    private function getProductCategory($productId) 
    {
    	Mage::getSingleton('core/session', array('name'=>'frontend'));
    	$db = Mage::getSingleton('core/resource')->getConnection('core_write');
    	$tablePrefix = Mage::getConfig()->getTablePrefix();
    	$tableCcp = $tablePrefix.'catalog_category_product';
    	$result = $db->query("SELECT category_id FROM $tableCcp WHERE product_id = $productId");
    	$getCatId = array();
        $catPath = array();
    	$count = 0;
    	$isCategory = false;
        $rootCat = Mage::app()->getStore()->getRootCategoryId();
        
    	while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    	   $getCatId[$count] = $row['category_id'];
    	   $count += 1;
    	   $isCategory = true;
    	}
   	 	if($isCategory) {
 		   $catLevel = 0;
    	   for($i=0;$i<sizeof($getCatId);$i++) {
    	       $getCat = $getCatId[$i]; 
    	 	   $tableCce = $tablePrefix.'catalog_category_entity';
    	 	   $result = $db->query("SELECT level,path FROM $tableCce WHERE entity_id = $getCat");
 		   	   $row = $result->fetch(PDO::FETCH_ASSOC);
               $catPathPa = explode('/',$row['path']);
 	 		   if($catLevel < intval($row['level']) && intval($catPathPa[1]) == $rootCat) {
    	 	     $catLevel = intval($row['level']);	
    	 		 $catPath = $catPathPa;
    		   }
    	   }
          
  		   $category = "";
 		   for($i=0;$i<sizeof($catPath);$i++) {
    		 	$catId = intval($catPath[$i]);
                $category .= Mage::getModel('catalog/category')->load($catId)->getName()."/";
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
    
    private function convertPrice($price) 
    {
        $price = number_format($price,2);
    	$corPrice = substr($price,0,-3);
    	$corPrice = str_replace(',','',$corPrice);
    	$corPrice = str_replace('.','',$corPrice);
        return $corPrice.'.'.substr($price,-2);
    }
    
    private function emosVersion() 
    {
        $emFile = $_SERVER{'DOCUMENT_ROOT'}."/js/tracker/emos2.js";
        if(file_exists($emFile)) {
           $fp = @fopen($emFile,'r');
           if($fp) {
              $rl = fgets($fp,60);
              fclose($fp);
              if(stristr($rl,'EMOS_VERSION') != false) {
                  $evp = strpos($rl,'EMOS_VERSION') ;
                  $evsb = substr($rl,$evp+12,8);
                  $evsb = str_replace('=','',$evsb);
                  $evsb = str_replace('\'','',$evsb);
                  $evsb = str_replace(',','',$evsb);
                  $evsb = str_replace('pt','',$evsb);
                  $evsb = str_replace('cm','',$evsb);
                  $evbiv = intval($evsb);
                  if($evbiv > 40) {
                      return '2';
                  }
              }
           }
           return '1';
        }
        return '0';
    }    
}
?>