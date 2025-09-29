<?php
/**
* SmartyExt config class
* Migrated from CMSMSExt
* A convenience class to centralize
* all Config values in one ArrayAccess type object
*
* @author Jo Morg (Fernando Morgado)
* @version 1.0
*/
namespace SmartyExt;

class config implements \ArrayAccess
{
  /**
   * @access private
   */
  private static $_data = [];

  /**
   * The instance
   * @access private
   */
  private static $_instance;

  /**
   * CMSMS's own config
   * @access private
   */
  private static $_CMSMS_CFG;

  /**
   * Private constructor to prevent objects being created directly
   * @access private
   */
  private function __construct() {}

  /**
   * Get this object as a singleton
   *
   * @return self
   */
  public static function GetInstance()
  {
    if( !isset(self::$_instance) ) {
      self::$_instance = new self();
      self::$_CMSMS_CFG = \cmsms()->GetConfig();
      $module_path = dirname(__DIR__);
      $file = cms_join_path($module_path, 'data', 'cfg.inc');
      $cfg = [];

      if( \file_exists($file) ) {
        include($file);
        self::$_data = isset($cfg) ? $cfg : [];
      }
      self::$_data['module_path'] = $module_path;
    }

    return self::$_instance;
  }

  /**
   * @param string $key
   *
   * @return bool
   */
  #[\ReturnTypeWillChange]
  private static function exists($key)
  {
    return ( isset(self::$_data[$key]) || self::$_CMSMS_CFG->offsetExists($key) );
  }

  /**
   * prevent cloning of the object: issues an E_USER_ERROR if this is attempted
   */
  public function __clone()
  {
    throw new \RuntimeException('Cloning the Config is not permitted');
  }

  /**
   * Get a data by key
   *
   * @param string $key The key data to retrieve
   *
   * @return mixed
   */
  #[\ReturnTypeWillChange]
  public function __get($key)
  {
    if( self::$_CMSMS_CFG->offsetExists($key) ) {
      return self::$_CMSMS_CFG[$key];
    }
    return self::$_data[$key];
  }

  /**
   * @param string $key The data key to assign the value to UNUSED
   * @param mixed $value The value to set UNUSED
   *
   * @throws \RuntimeException
   */
  public function __set($key, $value)
  {
    throw new \RuntimeException('Read Only');
  }

  /**
   * @param string $name
   *
   * @return bool
   */
  #[\ReturnTypeWillChange]
  public function __isset($key)
  {
    return ( isset(self::$_data[$key]) || self::$_CMSMS_CFG->offsetExists($key) );
  }


  /**
   * @param string $key
   *
   * @return bool
   */
  #[\ReturnTypeWillChange]
  public function offsetExists($key)
  {
    return ( isset(self::$_data[$key]) || self::$_CMSMS_CFG->offsetExists($key) );
  }

  /**
   * @param string $key
   *
   * @return mixed|null
   */
  #[\ReturnTypeWillChange]
  public function offsetGet($key)
   {
     if(self::$_CMSMS_CFG->offsetExists($key)) { return self::$_CMSMS_CFG[$key]; }

     return isset(self::$_data[$key]) ? self::$_data[$key] : NULL;
   }

  /**
   * @param string $key UNUSED
   * @param mixed $value UNUSED
   *
   * @throws \RuntimeException
   */
  #[\ReturnTypeWillChange]
  public function offsetSet($key, $value)
   {
     throw new \RuntimeException('Read Only');
   }

  /**
   * @param string $key UNUSED
   *
   * @throws \RuntimeException
   */
  #[\ReturnTypeWillChange]
  public function offsetUnset($key)
   {
     throw new \RuntimeException('Read Only');
   }

  /**
   * @param string $key
   *
   * @return mixed
   */
  public static function get($key)
  {
    if(self::$_CMSMS_CFG->offsetExists($key)) return self::$_CMSMS_CFG[$key];

    if( self::exists($key) )
    {
      return self::$_data[$key];
    }
  }

  /**
   * @param string $key UNUSED
   * @param mixed $value UNUSED
   * 
   * @throws \RuntimeException
   */
  public static function set($key, $value)
  {
    throw new \RuntimeException('Read Only');
  }

  /**
   * @param string $key UNUSED
   * 
   * @throws \RuntimeException
   */
  public static function erase($key)
  {
    throw new \RuntimeException('Read Only');
  }
}
