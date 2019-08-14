<?php

class Mage_Econda_Model_System_Config_Source_Language
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'0', 'label'=>Mage::helper('econda')->__('deutsch')),
            array('value'=>'1', 'label'=>Mage::helper('econda')->__('english'))
        );
    }
}