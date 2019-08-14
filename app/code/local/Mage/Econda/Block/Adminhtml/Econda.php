<?php
class Mage_Econda_Block_Adminhtml_Econda extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_econda';
    $this->_blockGroup = 'econda';
    $this->_headerText = Mage::helper('econda')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('econda')->__('Add Item');
    parent::__construct();
  }
}