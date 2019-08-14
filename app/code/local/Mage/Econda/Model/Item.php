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
 * @copyright   Copyright (c) 2015 econda GmbH (http://www.econda.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Econda_Model_Item extends Mage_Core_Model_Abstract
{

    /**
     * SKU / article number. Always SKU of main product. Ignore variants here.
     */
    public $productID = "NULL";

    /**
     * SKU / article number. Variants SKU if article is a variant of a main product.
     */
    public $productSku = null;

    /**
     * Name of product without variant extensions
     */
    public $productName = "NULL";

    /**
     * @var the price of the product, it is your choice wether its gross or net
     */
    public $price = "NULL";

    /**
     * @var the product group for this product, this is a drill down dimension
     * or tree-like structure
     * so you might want to use it like this:
     * productgroup/subgroup/subgroup/product
     */
    public $productGroup = "NULL";

    /**
     * @var the quantity / number of products viewed/bought etc..
     */
    public $quantity = "NULL";

    /**
     * @var variant of the product e.g. size, color, brand ....
     * remember to keep the order of theses variants allways the same
     * decide which variant is which feature and stick to it
     */
    public $variant1 = "NULL";
    public $variant2 = "NULL";
    public $variant3 = "NULL";
}
