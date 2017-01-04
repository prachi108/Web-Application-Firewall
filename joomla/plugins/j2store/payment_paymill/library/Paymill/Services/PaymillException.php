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

namespace Paymill\Services;

use Paymill\Models\Response\Error;


// No direct access
defined('_JEXEC') or die('Restricted access');
/**
 * PaymillException
 */
class PaymillException extends \Exception
{

    private $_errorMessage;
    private $_responseCode;
    private $_httpStatusCode;

    /**
     *
     * @param Error $errorModel
     * @param string $message
     * @param int $code
     * @param Exception $previous
     */
    public function __construct($responseCode = null, $message = null, $code = null)
    {
        parent::__construct($message, $code, null);
        $this->_errorMessage = $message;
        $this->_responseCode = $responseCode;
        $this->_httpStatusCode = $code;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }

    /**
     * @return string
     */
    public function getStatusCode()
    {
        return $this->_httpStatusCode;
    }

    /**
     * @return integer
     */
    public function getResponseCode()
    {
        return $this->_responseCode;
    }

}
