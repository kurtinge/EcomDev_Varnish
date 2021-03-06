<?php
/**
 * Varnish extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   EcomDev
 * @package    EcomDev_Varnish
 * @copyright  Copyright (c) 2014 EcomDev BV (http://www.ecomdev.org)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Ivan Chepurnyi <ivan.chepurnyi@ecomdev.org>
 */

/**
 * Observer of the customer actions
 */
class EcomDev_Varnish_Model_Customer_Observer
{
    const COOKIE_CART = 'quote_checksum';
    const COOKIE_CUSTOMER = 'customer_checksum';
    const COOKIE_IS_LOGGED_IN = 'is_logged_in';
    const COOKIE_SEGMENT = 'segment_checksum';
    
    /**
     * Returns helper instance for the module
     *
     * @return EcomDev_Varnish_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('ecomdev_varnish');
    }

    /**
     * Hashes data for varnish cookie
     *
     * @param string $hashData
     * @return string
     */
    protected function _hashData($hashData)
    {
        if (is_array($hashData)) {
            $hashData = serialize($hashData);
        }

        // Add device type
        $hashData .= Mage::app()->getRequest()->getServer('HTTP_X_UA_DEVICE');

        return md5($hashData);
    }

    /**
     * Sets card cookie
     * 
     * @param Varien_Event_Observer $observer
     */
    public function setCardCookie($observer)
    {
        $quoteData = $observer->getQuote()->debug();
        unset($quoteData['updated_at']);
        $this->_getHelper()
            ->addCookie(self::COOKIE_CART, $this->_hashData($quoteData))
            ->addCookie(self::COOKIE_SEGMENT, $this->_hashData($this->_getHelper()->getCustomerSegment()));
    }

    /**
     * Sets login cookie
     * 
     */
    public function setLoginCookie()
    {
        $this->_getHelper()
            ->addCookie(self::COOKIE_CART, $this->_hashData(Varien_Date::now()))
            ->addCookie(self::COOKIE_IS_LOGGED_IN, 1)
            ->addCookie(self::COOKIE_SEGMENT, $this->_hashData($this->_getHelper()->getCustomerSegment()))
            ->addCookie(self::COOKIE_CUSTOMER, $this->_hashData(Varien_Date::now()));
    }

    /**
     * Unsets login data cookies
     * 
     */
    public function unsetLoginCookie()
    {
        $this->_getHelper()
            ->addCookie(self::COOKIE_CUSTOMER, $this->_hashData(Varien_Date::now()))
            ->addCookie(self::COOKIE_IS_LOGGED_IN, 0)
            ->addCookie(self::COOKIE_SEGMENT, 0)
            ->addCookie(self::COOKIE_CART, $this->_hashData(Varien_Date::now()));
    }
}
