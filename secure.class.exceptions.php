<?php

/**
 *
 * @package SECUREPHP
 * @author Alexander Münch
 * @copyright Alexander Münch
 * @version 1.1
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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

            $message .= sprintf
                ('* %s %s %s, %s %s ' . SECUREPHP_LINE_BREAK,
                    get_class($e),
                    CONFIG::getInstance()->_('within'),
                    $e->getFile(),
                    CONFIG::getInstance()->_('line'),
                    $e->getLine()
                );

            $message .= '* ' . CONFIG::getInstance()->_('description') . ': ' . $e->getMessage() . SECUREPHP_LINE_BREAK;

            if(BOOTSTRAP::getInstance()->debug())
                {
                $message .= '*' . SECUREPHP_LINE_BREAK;
                $message .= '* ' . CONFIG::getInstance()->_('trace') . ': ' . SECUREPHP_LINE_BREAK;
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
                if(!isset($trace['line'])) $trace['line'] = ' '; else $trace['line'] = " ({$trace['line']})";
                if(!isset($trace['class'])) $trace['class'] = ' '; else $trace['class'] = ' ' . $trace['class'] . '->';
                $params = ARRAY();
                foreach($trace['args'] AS  $arg)
                    {
                    if(empty($arg)) $params[] = 'NULL';
                    elseif(is_object($arg)) $params[] = 'Object('.get_class($arg).')';
                    elseif(is_string($arg))
                        {
                        if(strlen($arg) > 10) $params[] = "'" . substr($arg, 0, 10) . "..'";
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