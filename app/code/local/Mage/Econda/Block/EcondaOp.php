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
 * @copyright   Copyright (c) 2012 econda GmbH (http://www.econda.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Generate tracking code for one page checkout
 */
class Mage_Econda_Block_EcondaOp extends Mage_Core_Block_Template
{

    /**
     * Get econda JS code
     *
     * @return string
     */
    public function getEmosOp()
    {
        $realUrlOp = Mage::helper('core/url')->getCurrentUrl();
        $opsession = Mage::getSingleton('checkout/session');
        if(stristr($realUrlOp,'checkout/onepage/') != false) {
            $storeIdOp = $this->getStore();
            $pathToEmos = $this->getJsUrl().'tracker/emos2.js';
            $pathToOpJs = $this->getJsUrl().'tracker/emosop.js';
            $emosInclude = "\n<script type=\"text/javascript\" src='".$pathToEmos."'></script>\n";
            $opInclude = "<script type=\"text/javascript\" src='".$pathToOpJs."'></script>\n";
            $customerId = md5(Mage::getSingleton('customer/session')->getCustomerId());
            $getSessionData = $opsession->getData('econda_content');
            $splitSessionData = explode(',',$getSessionData);
            $setPageId = md5($splitSessionData[0]);
            $setSiteID = $this->dataFormat($splitSessionData[1]);
            $setLangID = $splitSessionData[2];
            $langValueOP = 'econda/econda_settings/tracking_language';
            $cLangOp = Mage::getStoreConfig($langValueOP, $storeIdOp);
            if($cLangOp == '1') {
                $lang = 0;
            }
            else {
                $lang = 1;
            }
            $eLang = Mage::helper('econda/data')->getTranslation();
            $langStep[0] = $eLang[0][$lang]; $langStep[1] = $eLang[43][$lang]; $langStep[2] = $eLang[2].'/'.$eLang[3][$lang];
            $langStep[3] = $eLang[2][$lang].'/'.$eLang[4][$lang]; $langStep[4] = $eLang[5][$lang]; $langStep[5] = $eLang[42][$lang];
            $langStep[6] = $eLang[43][$lang]; $langStep[7] = $eLang[32][$lang];
            $emosLogin = "";
            if(stristr($this->getMessagesBlock()->getGroupedHtml(),'error-msg') != false) {
                $emosLogin .= "    emospro.login = [['".$customerId."','1']];\n";
            }
            $isLoggedIn = Mage::getSingleton('customer/session')->isLoggedIn();
            if($isLoggedIn == 1 && $opsession->getData('econda_logged') != 2) {
                $emosLogin .= "    emospro.login = [['".$customerId."','0']];\n";
                $opsession->setData('econda_logged','2');
            }
            $emosOut = "\n\n<!-- Start Econda-Monitor M158 -->\n";
            $emosOut .= "<script type=\"text/javascript\">\n//<![CDATA[\n";
            $emosOut .= "    window.emosTrackVersion = 2;\n";
            $emosOut .= "//]]>\n</script>";
            $emosOut .= $emosInclude;
            $emosOut .= "<script type=\"text/javascript\">\n//<![CDATA[\n";
            $emosOut .= "    ecStep = new Array('".$langStep[0]."','".$langStep[1]."','".$langStep[2]."','".$langStep[3].
                        "','".$langStep[4]."','".$langStep[5]."','".$langStep[6]."','".$langStep[7]."');\n";        
            $emosOut .= "    var emospro = {};\n";
            $emosOut .= "    emospro.content = 'Start/".$eLang[38][$lang]."';\n";
            $emosOut .= "    emospro.pageId = '".$setPageId."';\n";
            $emosOut .= "    emospro.siteid = '".$setSiteID."';\n";
            $emosOut .= "    emospro.langid = '".$setLangID."';\n";
            $emosOut .= $emosLogin;
            $emosOut .= "//]]>\n</script>\n".$opInclude;
            $emosOut .= "<!-- End Econda-Monitor -->\n\n";
            return $emosOut;
        }
        return "";
    }

    /**
     * Encode values for javascript
     *
     * @return string
     */
    private function dataFormat($str)
    {
        $str = utf8_decode($str);
        $str = html_entity_decode($str);
        $str = utf8_encode($str);
        $str = addcslashes($str, "\\\"'&<>]");
        return $str;
    }
}
