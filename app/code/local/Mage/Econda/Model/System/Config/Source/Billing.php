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
class Mage_Econda_Model_System_Config_Source_Billing
{
    
   /**
    * returns the value for billing select in backend
    */        
    public function toOptionArray()
    {
        return array(
            array('value'=>'0', 'label'=>Mage::helper('econda')->__('Total Price (Grand Total)')),
            array('value'=>'1', 'label'=>Mage::helper('econda')->__('Value of Goods without Tax (Subtotal)')),
            array('value'=>'2', 'label'=>Mage::helper('econda')->__('Value of Goods with Tax (Subtotal + Tax)'))
        );
    }
}
