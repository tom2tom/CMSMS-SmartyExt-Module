<?php
/**
 * Cache recorded config values.
 * Migrated from CMSMSExt module
 * Almost none of the CMSMSExt config parameters are used in SmartyExt
 *
 * @author Robert Campbell
 */

namespace SmartyExt;

use InvalidArgumentException;
use LogicException;
//use SmartyExt\config;
use function get_parameter_value;

class xt_settings
{
    private $_data = [];

    public function __construct(config $config)
    {
        $this->_data['ignore_xt_csrf'] = get_parameter_value($config, 'ignore_xt_csrf', false); // TODO document this
    }

    #[\ReturnTypeWillChange]
    public function __get($key)
    {
        switch($key) {
            case 'ignore_xt_csrf':
                return (bool)$this->_data[$key];
            default:
                throw new InvalidArgumentException("$key is not a gettable property of " . __CLASS__);
        }
    }

    #[\ReturnTypeWillChange]
    public function __set($key, $val)
    {
        throw new LogicException("$key is not a settable property of " . __CLASS__);
    }
} // class
