<?php

// Version 2.0, 24.10.2016
# 1) Initial release, no info

/**
 * Base package for SecurePHP reports and exceptions.
 *
 * Don't use this classed directly in code.
 *
 * \ErrorException
 *  -> \SECUREPHP\ERROR_EXCEPTION
 *     -> i.e. \SECUREPHP\PhpRunTimeError
 *
 * \Exceptions
 *  -> \SecurePHP\EXCEPTION
 *     -> \SecurePHP\E_FATAL
 *        -> \SecurePHP\E_ACCESS
 *        -> \SecurePHP\E_RECURSION
 *        -> \SecurePHP\E_INIT
 *        -> \SecurePHP\E_CONFLICT
 *     -> \SecurePHP\E_CONFIG
 *     -> ie. \SECUREPHP\ERRORTICKET
 *     -> ie. \SecurePHP\ERRORREPORT
 *
 * @package SECUREPHP
 * @author Alexander Münch
 * @copyright Alexander Münch
 * @version 1.1
 */

namespace AUTOFLOW\SECUREPHP

    {

    /**
     * Class EXCEPTION.
     * @package SECUREPHP
     */
    trait BASE
        {

        /**
         * @return string
         */
        public function __toString()
            {

            $message = '';

            $message .= $this->toString($this);

            if ($prev = $this->getPrevious()) do
                {
                $message .= '*' . SECUREPHP_LINE_BREAK;
                $message .= "* Vorausgehend:" . SECUREPHP_LINE_BREAK;
                $message .= '*' . SECUREPHP_LINE_BREAK;
                $message .= '* '.get_class($prev) . SECUREPHP_LINE_BREAK;
                $message .= $this->toString($prev);
                }
            while
                (
                $prev = $prev->getPrevious()
                );

            return $message;
            }

        /**
         * @param \Exception $e
         * @return string
         */
        public function toString(\Exception $e)
            {

            $message = '';

            $message .= '* Erstellt in: ' . $e->getFile() . ', Zeile ' . $e->getLine() . SECUREPHP_LINE_BREAK;

            $message .= '* Beschreibung: ' . $e->getMessage() . SECUREPHP_LINE_BREAK;

            if(\AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance()->debug())
                {
                $message .= '*' . SECUREPHP_LINE_BREAK;
                $message .= '* Programmablauf: ' . SECUREPHP_LINE_BREAK;
                $message .= $this->formatTrace($e);
                }
            return $message;
            }


        /**
         * @param \Exception $e
         * @return string
         */
        final public function formatTrace(\Exception $e)
            {

            $message = '';
            $tracestack = $e->getTrace();

            foreach($tracestack AS $l => $trace)
                {
                if(!isset($trace['file'])) $trace['file'] = '(intern) ';
                if(!isset($trace['line'])) $trace['line'] = ''; else $trace['line'] = "({$trace['line']})";
                if(!isset($trace['class'])) $trace['class'] = ''; else $trace['class'] = $trace['class'] . '->';
                $params = ARRAY();
                foreach($trace['args'] AS  $arg)
                    {
                    if(empty($arg)) $params[] = 'NULL';
                    elseif(is_object($arg)) $params[] = 'Object('.get_class($arg).')';
                    elseif(is_string($arg))
                        {
                        if(strlen($arg) > 10) $params[] = "'" . substr($arg, 0, 5) . " .. " . substr($arg, -5) ."'";
                        else $params[] = (string) "'$arg'";
                        }
                    elseif(is_numeric($arg)) $params[] = (int) $arg;
                    elseif(is_array($arg)) $params[] = 'ARRAY';
                    else $params[] = gettype($arg);
                    }
                $message .= '* ' .$l. ' '.$trace['file'] . $trace['line'] . $trace['class'] . $trace['function'] . '(' . implode(', ', $params) .')' . SECUREPHP_LINE_BREAK;
                }


            $message .= '* {main}' . SECUREPHP_LINE_BREAK;

            return $message;
            }

        }

    /**
     * Class EXCEPTION.
     * @package SECUREPHP
     */
    class EXCEPTION extends \Exception
        {

        use MAGIC;
        use BASE;

        }

    /**
     * Class ERROR_EXCEPTION
     * @package SECUREPHP
     */
    class ERROR_EXCEPTION extends \ErrorException
        {
        use MAGIC;
        use BASE;
        }

    /**
     * Class E_FATAL
     *
     * Base class of fatal SecurePHP exceptions.
     *
     * Das Versenden eines Reports hat zu einem
     * schwerwiegenden, internen Fehler geführt.
     *
     * Fehler dieser Klasse sollten auf einem
     * anderen Weg als über SecurePHP gemeldet
     * werden.
     *
     * @package SECUREPHP
     */
    class E_FATAL extends EXCEPTION
        {
        }

    /**
     * Class E_INIT.
     *
     * SecurePHP Initialisierungsfehler.
     *
     * @package SECUREPHP
     */
    class E_INIT extends E_FATAL
        {
        }


    /**
     * Class E_CONFIG.
     *
     * Konfigurationsfehler.
     *
     * @package SECUREPHP
     */
    class E_CONFIG extends EXCEPTION
        {
        }

    /**
     * Class E_SHUTDOWN
     * @package SECUREPHP
     */
    class E_SHUTDOWN extends ERROR_EXCEPTION
        {

        }

    /**
     * Class E_SHUTDOWN
     * @package SECUREPHP
     */
    class E_EOF extends ERROR_EXCEPTION
        {

        }

    }

// EOF