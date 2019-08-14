<?php

class Mage_Econda_Model_Mysql4_Econda extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the econda_id refers to the key field in your database table.
        $this->_init('econda/econda', 'econda_id');
    }
}