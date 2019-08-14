<?php

class Mage_Econda_Block_Adminhtml_Econda_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('econda_form', array('legend'=>Mage::helper('econda')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('econda')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('econda')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('econda')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('econda')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('econda')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('econda')->__('Content'),
          'title'     => Mage::helper('econda')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getEcondaData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getEcondaData());
          Mage::getSingleton('adminhtml/session')->setEcondaData(null);
      } elseif ( Mage::registry('econda_data') ) {
          $form->setValues(Mage::registry('econda_data')->getData());
      }
      return parent::_prepareForm();
  }
}