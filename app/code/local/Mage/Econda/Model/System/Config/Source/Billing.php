<?php

class Mage_Econda_Model_System_Config_Source_Billing
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'0', 'label'=>Mage::helper('econda')->__('Total Price (Grand Total)')),
            array('value'=>'1', 'label'=>Mage::helper('econda')->__('Value of Goods without Tax (Subtotal)')),
            array('value'=>'2', 'label'=>Mage::helper('econda')->__('Value of Goods with Tax (Subtotal + Tax)'))
        );
    }
}