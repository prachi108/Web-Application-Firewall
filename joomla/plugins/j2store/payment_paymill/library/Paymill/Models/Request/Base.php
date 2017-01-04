<?php
/**
 * --------------------------------------------------------------------------------
 * Payment Plugin - Paymill
 * --------------------------------------------------------------------------------
 * @package     Joomla 2.5 -  3.x
 * @subpackage  J2Store
 * @author      Paymill
 * @copyright   Copyright (c) Paymill
 * @license     GNU/GPL license: http://www.gnu.org/licenses/gpl-2.0.html
 * --------------------------------------------------------------------------------
 *
 * */


namespace Paymill\Models\Request;
// No direct access
defined('_JEXEC') or die('Restricted access');
/**
 * Abstract Model class for request models
 */
abstract class Base
{

    /**
     *
     * @var string
     */
    protected $_id;
    protected $_serviceResource = null;
    protected $_filter;

    /**
     * Converts the model into an array to prepare method calls
     * @param string $method should be used for handling the required parameter
     * @return array
     */
    public abstract function parameterize($method);

    /**
     * Returns the service ressource for this request
     * @return string
     */
    public final function getServiceResource()
    {
        return $this->_serviceResource;
    }

    /**
     * Returns this objects unique identifier
     * @return string identifier
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Sets the unique identifier of this object
     * @param string $id
     * @return \Paymill\Models\Request\Base
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }
    /**
     * Returns the filterArray for getAll
     * @return array
     */
    public function getFilter()
    {
        return $this->_filter;
    }

    /**
     * Sets the filterArray for getAll
     * @param array $filter
     * @return \Paymill\Models\Request\Base
     */
    public function setFilter($filter)
    {
        $this->_filter = $filter;
        return $this;
    }
}
