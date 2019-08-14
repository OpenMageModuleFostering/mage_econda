<?php
class Mage_Econda_Block_Adminhtml_Tracker extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_tracker';
    $this->_blockGroup = 'tracker';
    $this->_headerText = Mage::helper('tracker')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('tracker')->__('Add Item');
    parent::__construct();
  }
}