<?php

class Mage_Econda_Block_Adminhtml_Econda_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('econda_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('econda')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('econda')->__('Item Information'),
          'title'     => Mage::helper('econda')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('econda/adminhtml_econda_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}