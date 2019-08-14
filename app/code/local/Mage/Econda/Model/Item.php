<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Mage
 * @package     Mage_Econda
 * @copyright   Copyright (c) 2012 econda GmbH (http://www.econda.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Econda_Model_Item extends Mage_Core_Model_Abstract
{

    /*
     * @var unique Identifier of a product e.g. article number
     */
     
    var $productID = "NULL";

    /*
     * @var the name of a product
     */

    var $productName = "NULL";

    /*
     * @var the price of the product, it is your choice wether its gross or net
     */
     
    var $price = "NULL";

    /*
     * @var the product group for this product, this is a drill down dimension
     * or tree-like structure
     * so you might want to use it like this:
     * productgroup/subgroup/subgroup/product
     */

    var $productGroup = "NULL";

    /*
     * @var the quantity / number of products viewed/bought etc..
     */
     
    var $quantity = "NULL";

    /*
     * @var variant of the product e.g. size, color, brand ....
     * remember to keep the order of theses variants allways the same
     * decide which variant is which feature and stick to it
     */
     
    var $variant1 = "NULL";
    var $variant2 = "NULL";
    var $variant3 = "NULL";
}
