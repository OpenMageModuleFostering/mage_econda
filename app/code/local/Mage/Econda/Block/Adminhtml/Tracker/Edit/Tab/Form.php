<?php

class Mage_Econda_Block_Adminhtml_Tracker_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('tracker_form', array('legend'=>Mage::helper('tracker')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('tracker')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('tracker')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('tracker')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('tracker')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('tracker')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('tracker')->__('Content'),
          'title'     => Mage::helper('tracker')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getTrackerData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getTrackerData());
          Mage::getSingleton('adminhtml/session')->setTrackerData(null);
      } elseif ( Mage::registry('tracker_data') ) {
          $form->setValues(Mage::registry('tracker_data')->getData());
      }
      return parent::_prepareForm();
  }
}