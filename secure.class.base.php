<?php

// Version 2.0, 24.10.2016
# 1) Initial release, no info

/**
 * @package SECUREPHP
 * @author Alexander Münch
 * @copyright Alexander Münch
 * @version 2.0
 * @date 26.10.2016
 */

namespace AUTOFLOW\SECUREPHP

    {

    trait MAGIC

        {

        /**
         * Public setter method to prevent from bugs.
         * @param string $name
         * @param mixed $value
         * @throws E_ACCESS
         */
        final public function x__set($name, $value)
            {
            $message = 'Schreibzugriff auf nicht vorhandene ' . get_class($this) . '-Eigenschaft ' . $name;
            if(PROTECT::getInstance()->in_progress()) trigger_error($message, E_USER_NOTICE);
            else throw new E_ACCESS($message);
            }

        /**
         * Public getter method to prevent from bugs.
         * @param string $name
         * @throws E_ACCESS
         */
        final public function x__get($name)
            {
            $message = 'Lesezugriff auf nicht vorhandene ' . get_class($this) . '-Eigenschaft ' . $name;
            if(PROTECT::getInstance()->in_progress()) trigger_error($message, E_USER_NOTICE);
            else throw new E_ACCESS($message);
            }

        /**
         * Public call method to prevent from bugs.
         * @param string $name
         * @param array $arguments
         * @throws E_ACCESS
         */
        final public function x__call($name, $arguments)
            {
            $message = 'Zugriff auf nicht vorhandene ' . get_class($this) . '-Methode ' . $name;
            if(PROTECT::getInstance()->in_progress()) trigger_error($message, E_USER_NOTICE);
            else throw new E_ACCESS($message);
            }
        }

    /**
     * Class USERCLASS
     * @package SECUREPHP
     */
    class USERCLASS
        {

        use MAGIC;

        }

    /**
     * Class BASECLASS
     * @package SECUREPHP
     */
    class SINGLETON

        {

        use MAGIC;

        /**
         * Private clone method to prevent cloning of the instance of the
         * *Singleton* instance.
         *
         * @return void
         */
        final private function __clone()
            {
            }

        /**
         * Private unserialize method to prevent unserializing of the *Singleton*
         * instance.
         *
         * @return void
         */
        final private function __wakeup()
            {
            }

        /**
         * Private construct method to prevent cloning of the instance of the
         * Singleton instance.
         */
        protected function __construct()
            {
            }
        }
    }

// EOF