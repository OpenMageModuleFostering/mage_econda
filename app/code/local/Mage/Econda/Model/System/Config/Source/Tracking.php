<?php

class Mage_Econda_Model_System_Config_Source_Tracking
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'1', 'label'=>Mage::helper('econda')->__('Version 1 - html')),
            array('value'=>'2', 'label'=>Mage::helper('econda')->__('Version 2 - javascript'))
        );
    }
}