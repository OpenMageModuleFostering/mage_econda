<?php
class Mage_Econda_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/econda?id=15 
    	 *  or
    	 * http://site.com/econda/id/15 	
    	 */
    	/* 
		$econda_id = $this->getRequest()->getParam('id');

  		if($econda_id != null && $econda_id != '')	{
			$econda = Mage::getModel('econda/econda')->load($econda_id)->getData();
		} else {
			$econda = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($econda == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$econdaTable = $resource->getTableName('econda');
			
			$select = $read->select()
			   ->from($econdaTable,array('econda_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$econda = $read->fetchRow($select);
		}
		Mage::register('econda', $econda);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}