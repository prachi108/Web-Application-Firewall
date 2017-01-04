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

namespace Paymill\API;
// No direct access
defined('_JEXEC') or die('Restricted access');
/**
 * Interface
 */
abstract class CommunicationAbstract
{

    /**
     * Perform API and handle exceptions
     *
     * @param $action
     * @param array $params
     * @param string $method
     * @return mixed
     */
    abstract protected function requestApi($action = '', $params = array(), $method = 'POST');
}
