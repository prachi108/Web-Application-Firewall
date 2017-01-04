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

namespace Paymill\Models\Response;
// No direct access
defined('_JEXEC') or die('Restricted access');
/**
 * Error
 *
 * @category   PayIntelligent
 * @copyright  Copyright (c) 2011 PayIntelligent GmbH (http://payintelligent.de)
 */
class Error
{

    /**
     * @var string
     */
    private $_errorMessage;
    /**
     * @var int
     */
    private $_responseCode;

    /**
     * @var int
     */
    private $_httpStatusCode;

    /**
     * Returns the error message stored in the model
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }

    /**
     * Sets the error message stored in this model
     * @param string $errorMessage
     * @return \Paymill\Models\Response\Error
     */
    public function setErrorMessage($errorMessage)
    {
        $this->_errorMessage = $errorMessage;
        return $this;
    }

    /**
     * Returns the response code
     * @return int
     */
    public function getResponseCode()
    {
        return $this->_responseCode;
    }

    /**
     * Sets the response code
     * @param int $responseCode
     * @return \Paymill\Models\Response\Error
     */
    public function setResponseCode($responseCode)
    {
        $this->_responseCode = $responseCode;
        return $this;
    }

    /**
     * Returns the status code
     * @return int
     */
    public function getHttpStatusCode()
    {
        return $this->_httpStatusCode;
    }

    /**
     * Sets the status code
     * @param int $httpStatusCode
     * @return \Paymill\Models\Response\Error
     */
    public function setHttpStatusCode($httpStatusCode)
    {
        $this->_httpStatusCode = $httpStatusCode;
        return $this;
    }

}
