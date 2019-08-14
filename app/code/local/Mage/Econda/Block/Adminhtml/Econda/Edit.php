<?php

class Mage_Econda_Block_Adminhtml_Econda_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'econda';
        $this->_controller = 'adminhtml_econda';
        
        $this->_updateButton('save', 'label', Mage::helper('econda')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('econda')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('econda_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'econda_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'econda_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('econda_data') && Mage::registry('econda_data')->getId() ) {
            return Mage::helper('econda')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('econda_data')->getTitle()));
        } else {
            return Mage::helper('econda')->__('Add Item');
        }
    }
}