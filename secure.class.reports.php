<?php

/**
 * @package SECUREPHP
 * @author Alexander Münch
 * @copyright Alexander Münch
 * @version 2.0
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace

	{

    use AUTOFLOW\SECUREPHP;

    /**
     * Version of current reports class.
     */
    define('SECUREPHP_REPORTS_VERSION', '2.0');


    /**
     * Class RAISABLE
     * @inherit Exception
     */

    trait RAISABLE_TRAIT

		{

        // TRAIT HEAD


        /**
         * @todo change to protected, @see set_state
         * Statusmeldung.
         * @var string
         */
		public $state = '';

        /**
         * @var string
         */
        protected $note = '';

        /**
         * @todo implement
         * @var string date()
         */
        protected $date;

        /**
         * Benutzerdefinierter Fehlercode, da Exception-Code durch status ersetzt.
         * @var int
         */
		protected $error_code = NULL;

        /**
         * @todo change to protected,
         * @todo @see add_params, @see add_param
         * Weitere benutzerdefinierte Parameter
         * @var array
         */
		public $params                  = ARRAY();

        /**
         * Individueller Timeout.
         * @var $timeout NULL
         */
		protected $timeout                 = NULL;

        /**
         * Nachricht wurde gesendet.
         * @var bool
         */
		protected $flag_has_raised      = false;

        /**
         * @var string
         */
		protected $application;

        // TRAIT MAGIC METHODS


        /**
         * @return string
         */
        final public function __toString()
            {
            return $this->toString($this);
            }

        // TRAIT METHODS


        /**
         * @param int|null $timeout
         * @return void
         * @throws \Exception
         */
        final public function raise($timeout = NULL)
            {

            if(true == $this->has_raised())
                {
                return;
                }
            elseif(false == defined('SECUREPHP'))
                {
                throw new Exception('sending report failed due to missing installation. report library is not available', false, $this);
                }
            elseif(false == SECUREPHP\BOOTSTRAP::getInstance())
                {
                throw new Exception('sending report failed due to missing initialisation. could not initialise report library', NULL, $this);
                }
            else
                {
                SECUREPHP\PROTECT::getInstance()->in_progress(true);
                if(NULL === $timeout) $timeout = $this->get_timeout();
                SECUREPHP\PROTECT::getInstance()->notify($this->get_send_to(), $this, $timeout);
                SECUREPHP\PROTECT::getInstance()->in_progress(false);
                $this->has_raised(true);
                }
            }

        /**
         * @return string
         */
        final public function toString(\Exception $e)
            {

            $message = '';

            $flag_is_raiseable = (bool) (is_a($e, 'Raisable') OR is_a($e, 'RaisableError'));

            if($e->getPrevious());
            elseif($flag_is_raiseable AND $e->flag_details)
                {
                $message .= sprintf
                    ("* %s %s %s, %s %s" . SECUREPHP_LINE_BREAK,
                    SECUREPHP\CONFIG::getInstance()->_($e->description),
                    SECUREPHP\CONFIG::getInstance()->_('within'),
                    $e->getFile(),
                    SECUREPHP\CONFIG::getInstance()->_('line'),
                    $e->getLine()
                    );
                }
            elseif($flag_is_raiseable AND !$e->flag_details);
            else
                {
                $message .= sprintf
                    ('* %s %s %s, %s %s ' . SECUREPHP_LINE_BREAK,
                        get_class($e),
                        SECUREPHP\CONFIG::getInstance()->_('within'),
                        $e->getFile(),
                        SECUREPHP\CONFIG::getInstance()->_('line'),
                        $e->getLine()
                    );
                }

            $message .= sprintf
                ('* %s: %s'  . SECUREPHP_LINE_BREAK,
                SECUREPHP\CONFIG::getInstance()->_('description'),
                $e->getMessage()
                );

            if($flag_is_raiseable AND $e->get_note())
                {
                $message .= '*' . SECUREPHP_LINE_BREAK;
                $message .= sprintf
                    ('* %s: %s'  . SECUREPHP_LINE_BREAK,
                    SECUREPHP\CONFIG::getInstance()->_('notes'),
                    $e->get_note()
                    );
                }

            if($flag_is_raiseable AND $e->get_status())
                {
                $message .= '*' . SECUREPHP_LINE_BREAK;
                $message .= sprintf
                    ('* %s: %s'  . SECUREPHP_LINE_BREAK,
                        SECUREPHP\CONFIG::getInstance()->_('state'),
                        $e->get_status()
                    );
                }

            if($e->getPrevious());
            elseif(($flag_is_raiseable AND $e->flag_details ) OR !$flag_is_raiseable)
                {
                $message .= '*' . SECUREPHP_LINE_BREAK;
                $message .= '* '. SECUREPHP\CONFIG::getInstance()->_('trace') . ':' . SECUREPHP_LINE_BREAK;
                $message .= $this->formatTrace($e);
                }

            if(is_a($e, 'BatchReport') AND $e->has_next())
                {
                $i = 0;
                $message .= '*'  . SECUREPHP_LINE_BREAK;
                $message .= '* ' . SECUREPHP\CONFIG::getInstance()->_('contents') . ': ' . SECUREPHP_LINE_BREAK;
                $message .= '* ' . SECUREPHP_LINE_BREAK;
                foreach ($e->get_attachments() AS $attachement)
                    {
                    $message .= '* ' . SECUREPHP_LINE_BREAK;
                    $message .= '* ' . ++$i . ') ' . get_class($attachement) . SECUREPHP_LINE_BREAK;
                    $message .= '* '. SECUREPHP_LINE_BREAK . (string) $attachement;
                    $message .= '* ' . SECUREPHP_LINE_BREAK;
                    }
                }

            if(is_a($e, 'ConfigError') AND count($e->params))
                {

                $message .= '*'  . SECUREPHP_LINE_BREAK;
                $message .= '* ' . SECUREPHP\CONFIG::getInstance()->_('config notes') . ':' . SECUREPHP_LINE_BREAK;
                $message .= '* ' . SECUREPHP\CONFIG::getInstance()->_('obsolete configuration') . SECUREPHP_LINE_BREAK;
                $message .= '*'  . SECUREPHP_LINE_BREAK;
                $message .= '* ' . SECUREPHP\CONFIG::getInstance()->_('config file') . ': ' . ($e->config_file ? : SECUREPHP\CONFIG::getInstance()->_('not present')) . SECUREPHP_LINE_BREAK;
                $message .= '*'  . SECUREPHP_LINE_BREAK;
                $message .= '* ' . SECUREPHP\CONFIG::getInstance()->_('current configuration') .': ' . SECUREPHP_LINE_BREAK;
                if( count($e->params) > 0 )
                    {
                    $count = 1;
                    $message .= '*' . SECUREPHP_LINE_BREAK;
                    foreach($e->params AS $name => $value)
                        {
                        $message .= '* '. $count .') '.$name .': '.(string) $value . SECUREPHP_LINE_BREAK;
                        $count++;
                        }
                    }
                else
                    {
                    $message .= SECUREPHP\CONFIG::getInstance()->_('not present') . SECUREPHP_LINE_BREAK;
                    }
                }

            if ($e->getPrevious())
                {
                $message .= SECUREPHP_LINE_BREAK;
                $message .= '* ' . SECUREPHP\CONFIG::getInstance()->_('previous') . ':' . SECUREPHP_LINE_BREAK;
                $message .= '*'  . SECUREPHP_LINE_BREAK;
                $message .= $this->toString($e->getPrevious());
                }

            return $message . "";
            }


        /**
         * @param null $flag
         * @return bool
         */
        final protected function has_raised($flag = NULL)
            {
            if(NULL === $flag) return $this->flag_has_raised;
            else
                {
                $this->flag_has_raised = $flag;
                return true;
                }
            }

        // TRAIT GETTERS & SETTERS

        /**
         * @return string
         */
        final public function get_md5()
            {

            // Nach adden von reminder funktioniert diese Zeile nicht mehr ..
            #return md5(serialize($this));

            // neu:
            return md5(($this->__toString()));
            }

        /**
         * Ermöglicht das Überschreiben
         * der Empfängerliste eines Berichtes.
         *
         * @param string $users
         * @return bool
         */
        final public function send_to($users)
            {
            $this->send_to = $users;
            return true;
            }

        /**
         * @return string
         */
        final public function get_send_to()
            {
            return $this->send_to;
            }

        /**
         * @param null $flag
         * @return bool
         */
        final public function details($flag = NULL)
            {
            if(NULL === $flag) return (bool) $this->flag_details;
            else $this->flag_details = (bool) $flag;
            }

        /**
         * @param int $code
         * @return bool
         */
        final public function set_error_code($code)
            {
            $this->error_code = $code;
            return true;
            }

        /**
         * @return int
         */
        final protected function get_error_code()
            {
            return $this->error_code;
            }

        /**
         * @param string $state
         * @return bool
         */
        final public function set_state($state)
            {
            $this->state = $state;
            return true;
            }

        /**
         * @return null|string
         */
        final protected function get_status()
            {
            return $this->state;
            }

        /**
         * @param $note
         * @return bool
         */
        final public function set_note($note)
            {
            $this->note = $note;
            return true;
            }

        /**
         * @return string
         */
        final protected function get_note()
            {
            return $this->note;
            }

        /**
         * @param null|int $timeout
         * @return bool
         */
        final public function set_timeout($timeout = NULL)
            {
            $this->timeout = $timeout;
            return true;
            }

        /**
         * @return int|null
         */
        final protected function get_timeout()
            {
            return $this->timeout;
            }

        /**
         * @return bool
         */
        final public function add_params(ARRAY $params)
            {
            $this->params = $params;
            return true;
            }

        /**
         * @param string $name
         * @param string $value
         * @return bool
         */
        final public function add_param($name='default', $value='')
            {
            $this->params[$name] = (string) $value;
            return true;
            }

        /**
         * @return array
         */
        final public function get_params()
			{
			return $this->params;
			}

        /**
         * @return string
         */
        public function get_mail_header()
            {
            return 'Neue Nachricht von ' . SECUREPHP;
            }

        /**
         * @param string $notice
         * @return string
         */
        public function get_mail_message($notice=NULL)
			{

			$message = '';
			$message .= $this->get_mail_message_header();
			
			if(!empty($notice))
				{
				$message .= SECUREPHP_MAIL_EOL;
				$message .= $notice;
				$message .= SECUREPHP_MAIL_EOL;
				}
			
            $message .= $this->get_mail_message_details();
			$message .= $this->get_mail_message_params();

			if($prev = $this->getPrevious())
					{
					$message .= SECUREPHP_MAIL_EOL;
					$message .= 'Vorrausgehende Fehler: ' . SECUREPHP_MAIL_EOL;
					$message .= SECUREPHP_MAIL_EOL;
					$message .= $this->get_mail_message_previous($prev);
					}
					
			$message .= $this->get_mail_message_footer();
			return $message;
			}

        /**
         * @param string $title
         * @return string
         */
        final protected function get_mail_message_header($title=NULL)
            {

            $message = '';

            if(!empty($title))
                {
                $message .= $title . SECUREPHP_MAIL_EOL;
                $message .= SECUREPHP_MAIL_EOL;
                }

            $message .= SECUREPHP\PROTECT::getInstance()->get_app() . SECUREPHP_MAIL_EOL;
            $message .= SECUREPHP_MAIL_EOL;
            $message .= $this->getMessage() . SECUREPHP_MAIL_EOL;
            $message .= SECUREPHP_MAIL_EOL;

            if($this->get_note())
                {
                $message .= 'Information und Hinweis:' . SECUREPHP_MAIL_EOL;
                $message .= $this->get_note() . SECUREPHP_MAIL_EOL;
                $message .= SECUREPHP_MAIL_EOL;
                }

            $message .= 'Ausführende Anwendung oder Script:' . SECUREPHP_MAIL_EOL;
            $message .= AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance()->get_abs_path() . SECUREPHP_MAIL_EOL;
            $message .= SECUREPHP_MAIL_EOL;
            $message .= 'gesendet von: ' . ErrorInfo::get_host() . SECUREPHP_MAIL_EOL;
            $message .= SECUREPHP_MAIL_EOL;

            return $message;
            }

        /**
         * @return string
         */
        final protected function get_mail_message_details()
            {
            $message = SECUREPHP_MAIL_EOL;
            $message .= 'Diese Nachricht wurde ausgelöst durch:' . SECUREPHP_MAIL_EOL;
            $message .= 'Datei: ' . ($this->getFile()?:'kein Wert vorhanden') . SECUREPHP_MAIL_EOL;
            $message .= 'Zeile: ' . ($this->getLine()?:'kein Wert vorhanden') . SECUREPHP_MAIL_EOL;
            $message .= 'Trace: ' . ($this->getTraceAsString()?:'kein Wert vorhanden') . SECUREPHP_MAIL_EOL;
            return $message;
            }

        /**
         * @param \Exception $e
         * @param string|null $level
         * @return string
         */
        final public function get_mail_message_previous(\Exception $e, $level = NULL)
            {

            $level = ($level ? chr(ord($level) + 1) : 'A');

            $message = "";
            $message .= $level . ')' . SECUREPHP_MAIL_EOL;

            if(is_a($e, 'ErrorTicket'))
                {

                $message .= $e->getMessage() . SECUREPHP_MAIL_EOL;
                $message .= SECUREPHP_MAIL_EOL;

                $message .= $e->get_mail_message_details();
                $message .= $e->get_mail_message_params();

                if (is_a($e, 'ErrorReport') AND $e->has_next())
                    {
                    $message .= $e->get_mail_message_attachments();
                    }

                if($prev = $e->getPrevious())
                    {
                    $message .= SECUREPHP_MAIL_EOL;
                    $message .= 'Vorrausgehende Fehler: ' . SECUREPHP_MAIL_EOL;
                    $message .= SECUREPHP_MAIL_EOL;
                    $message .= $e->get_mail_message_previous($prev, $level);
                    }
                }
            else
                {
                $message .= $e->getMessage() . SECUREPHP_MAIL_EOL;
                $message .= SECUREPHP_MAIL_EOL;
                $message .= 'Diese Nachricht wurde ausgelöst durch:' . SECUREPHP_MAIL_EOL;
                $message .= 'Datei: ' . ($e->getFile()?:'kein Wert vorhanden') . SECUREPHP_MAIL_EOL;
                $message .= 'Zeile: ' . ($e->getLine()?:'kein Wert vorhanden') . SECUREPHP_MAIL_EOL;
                $message .= 'Trace: ' . ($e->getTraceAsString()?:'kein Wert vorhanden') . SECUREPHP_MAIL_EOL;

                if($prev = $e->getPrevious())
                    {
                    $message .= SECUREPHP_MAIL_EOL;
                    $message .= 'Vorrausgehende Fehler: ' . SECUREPHP_MAIL_EOL;
                    $message .= SECUREPHP_MAIL_EOL;
                    $message .= $e->getPrevious();
                    }
                }

            if (isset($e->status))
                {
                $message .= SECUREPHP_MAIL_EOL;
                $message .= 'Statushinweis: ' . ($e->status ?: 'nicht vorhanden') . SECUREPHP_MAIL_EOL;
                }
            return $message;
            }

        /**
         * @param $e \Exception
         * @return string
         */
        final public function get_mail_message_params()
            {
            if(0 < count($this->get_params()))
                {
                $message = SECUREPHP_MAIL_EOL;
                $message .= 'Zusätzliche Parameter: ' . SECUREPHP_MAIL_EOL;
                $count = 1;
                $message .= SECUREPHP_MAIL_EOL;
                foreach($this->get_params() AS $name => $value)
                    {
                    $message .= $count++ . ') ' . $name .': '.(string) $value . SECUREPHP_MAIL_EOL;
                    }
                return $message;
                }
            else return '';
            }

        /**
         * @return string
         */
        final protected function get_mail_message_receipients()
            {

            $users = explode(',', $this->send_to);
            foreach($users AS $user)
                {
                $user = trim($user);
                if('all' == $user AND "all" == $this->send_to)
                    {
                    $_admins = AUTOFLOW\SECUREPHP\MAIL::getInstance()->get_admin();
                    $_users = AUTOFLOW\SECUREPHP\MAIL::getInstance()->get_user();
                    foreach(AUTOFLOW\SECUREPHP\MAIL::getInstance()->userlist AS $_user => $email) $_recipients[] = $_user . ' [' .$email. ']';
                    break;
                    }
                elseif(strpos($user, '>'))
                    {
                    $_list = explode('>', $user);
                    foreach($_list AS $name)
                        {

                        if('admin' == $name AND !empty(AUTOFLOW\SECUREPHP\MAIL::getInstance()->get_admin()))
                            {
                            $_admins = AUTOFLOW\SECUREPHP\MAIL::getInstance()->get_admin();
                            break;
                            }
                        elseif('user' == $name AND !empty(AUTOFLOW\SECUREPHP\MAIL::getInstance()->get_user()))
                            {
                            $_users = AUTOFLOW\SECUREPHP\MAIL::getInstance()->get_user();
                            break;
                            }
                        elseif('log' == $name);
                        elseif($_email = AUTOFLOW\SECUREPHP\MAIL::getInstance()->get_cc_mail($name))
                            {
                            $_recipients[] = $name . ' ['.$_email.']';
                            break;
                            }
                        }
                    }
                else
                    {
                    if('admin' == $user)
                        {
                        $_admins = AUTOFLOW\SECUREPHP\MAIL::getInstance()->get_admin();
                        }
                    elseif('user' == $user)
                        {
                        $_users = AUTOFLOW\SECUREPHP\MAIL::getInstance()->get_user();
                        }
                    elseif('log' == $user);
                    else
                        {
                        $_recipients[] = $user . ' [' .AUTOFLOW\SECUREPHP\MAIL::getInstance()->get_cc_mail($user) .']';
                        }
                    }
                }
            $message = "";
            $message .= 'Administratoren: ' . (!empty($_admins)?$_admins:'') . SECUREPHP_MAIL_EOL;
            $message .= 'Mitarbeiter: ' . (!empty($_users)?$_users:'') . SECUREPHP_MAIL_EOL;
            $message .= 'Weitere Empfänger: ' . (!empty($_recipients)?implode(',', $_recipients):'') . SECUREPHP_MAIL_EOL;
            return $message;
            }

        /**
         * @return string
         */
        final protected function get_mail_message_footer()
            {
            $message = SECUREPHP_MAIL_EOL;
            $message .= 'Statusinformation: ' . ($this->get_status()?:'keine Informationen vorhanden') . SECUREPHP_MAIL_EOL;
            $message .= SECUREPHP_MAIL_EOL;
            $message .= 'Empfänger dieser Nachricht:' . SECUREPHP_MAIL_EOL;
            $message .= $this->get_mail_message_receipients();
            $message .= SECUREPHP_MAIL_EOL;
            $message .= 'Diese Nachricht wurde gesendet durch: ' . SECUREPHP . ' von ' . ErrorInfo::get_host() . SECUREPHP_MAIL_EOL;
            return $message;
            }
		}

    /**
     * Base class E_RAISABLE.
     */
    class Raisable extends SECUREPHP\EXCEPTION
        {

        // E_RAISEABLE HEAD

        use \RAISABLE_TRAIT;

        }

    /**
     * Base class E_RAISABLE_ERROR.
     */
    class RaisableError extends SECUREPHP\ERROR_EXCEPTION
        {

        // E_RAISEABLE_ERROR HEAD

        use \RAISABLE_TRAIT;
        }

    /**
     * Class ErrorInfo.
     */
    final class ErrorInfo
        {
        /**
         * @return string
         */
        final public static function get_host()
            {
            return (gethostname()? gethostname() .' (' .getHostByName(getHostName()).')':'unbekannt');
            }
        }

    /**
     * Class ErrorTicket
     */
    class ErrorTicket extends \Raisable

        {

        /**
         * @var string
         */
        public $description    = 'error ticket';

        /**
         * Report-Empfänger.
         * @var string
         */
        protected $send_to     = "admin,log";

        /**
         * @var bool
         */
        protected $flag_details = true;

        // ERRORTICKET MAGIC METHODS

        /**
         * @param string $message
         * @param string $status
         * @param \Exception $previous
         * @extend Exception
         */
        public function __construct($message=NULL, $status=NULL, \Exception $previous=NULL)
            {
            parent::__construct($message, NULL, $previous);
            $this->application = SECUREPHP\PROTECT::getInstance()->get_app();
            $this->state = $status;
            }

        }

    /**
     * Class UserTicket
     * @inherit \ErrorTicket
     */
    class UserTicket extends \ErrorTicket
        {

        /**
         * @var string
         */
        public $description    = 'user info';

        /**
         * Report-Empfänger.
         * @var string
         */
        protected $send_to     = "user";

        /**
         * @var bool
         */
        protected $flag_details = false;

        }

    /**
     * Class SuccessTicket
     * @inherit \ErrorTicket
     */
    final class SuccessTicket extends \ErrorTicket

        {

        /**
         * @var string
         */
        public $description = 'confirmation ticket';

        /**
         * Report-Empfänger.
         * @var string
         */
        protected $send_to     = "user";

        /**
         * @var bool
         */
        protected $flag_details = true;

        /**
         * @param string $notice
         * @return string
         */
        final public function get_mail_message($notice = NULL)
            {

            $message = '';
            $message .= $this->get_mail_message_header('ALLES OK');
            $message .= $this->get_mail_message_params();

            if($this->getPrevious())
                {
                $message .= SECUREPHP_MAIL_EOL;
                $message .= 'Vorrausgegangene Meldungen:' . SECUREPHP_MAIL_EOL;
                $message .= SECUREPHP_MAIL_EOL;
                $message .= $this->get_mail_message_previous($this->getPrevious());
                }

            $message .= $this->get_mail_message_footer();
            return $message;
            }
        }

    /**
     * Class Warning.
     * @inherit \ErrorTicket
     */
    final class Warning extends \ErrorTicket
        {

        /**
         * @var string
         */
        public $description = 'warning';

        /**
         * Report-Empfänger.
         * @var string
         */
        protected $send_to     = "admin,log";

        /**
         * @var bool
         */
        protected $flag_details = true;

        }

    /**
     * Class Notice.
     * @inherit \ErrorTicket
     */
    final class Notice extends \ErrorTicket
        {

        /**
         * @var string
         */
        public $description = 'notice';

        /**
         * Report-Empfänger.
         * @var string
         */
        protected $send_to     = "admin,log";

        /**
         * @var bool
         */
        protected $flag_details = true;

        }


    /**
     * Class ConfigError
     * @inherit \ErrorTicket
     */
	final class ConfigError extends \ErrorTicket
		{

        // CONFIGERROR HEAD

        /**
         * @var string
         */
		public $description    = 'config error';

        /**
         * Report-Empfänger.
         * @var string
         */
        protected $send_to     = "admin,log";

        /**
         * @var bool
         */
        protected $flag_details = true;

        /**
         * @var string
         */
		public $config_file;

        /**
         * @var array[]
         */
		public $config_params;

        // CONFIGERROR METHODS

        /**
         * @param string|null $notice
         * @return string
         */
        final public function get_mail_message($notice=NULL)
            {

            $message = '';
            $message .= 'Hinweise zur Konfiguration' . SECUREPHP_MAIL_EOL;
            $message .= 'Möglicherweise sind die Verbindungsparameter nicht aktuell.' . SECUREPHP_MAIL_EOL;
            $message .= 'Hinweise zur manuellen Änderung finden sie im Folgenden:' . SECUREPHP_MAIL_EOL;
            $message .= 'Konfigurationsdatei: ' . ($this->config_file?:'Wert nicht angegeben') . SECUREPHP_MAIL_EOL;
            $message .= 'aktuelle Konfigurationsparameter: ';
            if( count($this->config_params) > 0 )
                {
                $count = 1;
                $message .= SECUREPHP_MAIL_EOL;
                foreach($this->config_params AS $name => $value)
                    {
                    $message .= $count .') '.$name .': '.(string) $value . SECUREPHP_MAIL_EOL;
                    $count++;
                    }
                }
            else
                {
                $message .= 'nicht vorhanden' . SECUREPHP_MAIL_EOL;
                }
            return parent::get_mail_message($message);
            }

        // GETTERS & SETTERS

        /**
         * @param string $file
         * @return bool
         */
        public function set_config_file($file)
			{
			$this->config_file = $file;
            return true;
			}

		}



    /**
     * Class InitError.
     *
     * Verbindungsfehler etc.
     * Definiert allgemeine Fehler, die in der
     * Prüfschleife, vor der Verarbeitungsschleife,
     * auftreten.
     *
     * @inherit \ErrorTicket
     */
	final class InitError extends \ErrorTicket

		{

        /**
         * @var string
         */
		public $description = 'init error';

        /**
         * Report-Empfänger.
         * @var string
         */
        protected $send_to     = "admin>user,log";

        /**
         * @var bool
         */
        protected $flag_details = true;

		}

    /**
     * Class TransitionError.
     *
     * Fehler bei Übergängen.
     * Z.B. Verzeichniswechsel oder Statusübergänge.
     * Auch Updatefehler bei Datenbanken.
     *
     * @inherit \ErrorTicket
     */
    final class TransitionError extends \ErrorTicket

        {

        /**
         * @var string
         */
        public $description = 'transition error';

        /**
         * Report-Empfänger.
         * @var string
         */
        protected $send_to     = "user>admin,log";

        /**
         * @var bool
         */
        protected $flag_details = true;

        }

    /**
     * Class TransactionError.
     * @inherit \ErrorTicket
     */
    final class TransactionError extends \ErrorTicket
        {

        /**
         * @var string
         */
        public $description = 'transaction error';

        /**
         * Report-Empfänger.
         * @var string
         */
        protected $send_to     = "user>admin,log";

        /**
         * @var bool
         */
        protected $flag_details = true;

        }


    /**
     * Class BatchReport
     * @inherit \ErrorTicket
     */
    class BatchReport extends \ErrorTicket
        {
        /**
         * @var string
         */
        public $description = 'batch report';

        /**
         * Report-Empfänger.
         * @var string
         */
        protected $send_to     = "admin>user";

        /**
         * @var bool
         */
        protected $flag_details = false;

        /**
         * @var \Exception[]
         */
        protected $stack = ARRAY();

        // ERRORREPORT METHODS

        /**
         * @param \Exception $e
         */
        public function add(\Exception $e)
            {
            $this->stack[] = $e;
            }

        /**
         * @return bool
         */
        final public function has_next()
            {
            if(count($this->stack) > 0) return true;
            else return false;
            }

        /**
         * @return \Exception[]
         */
        final protected function get_attachments()
            {
            return $this->stack;
            }

        /**
         * @return string
         */
        public function get_mail_header()
            {
            return AUTOFLOW\SECUREPHP\PROTECT::getInstance()->get_app() . ' Batch report ';
            }

        /**
         * @todo implement level
         * @todo rewrite
         * @param string $level
         * @return string
         */
        protected function get_mail_message_attachments($level = NULL)
            {

            $count = 0;
            $message = "";

            foreach($this->get_attachments() AS $error)
                {
                $count++;
                // Umstellen auf Exceptions !!
                $message .= ($level?:$count) . ')' . SECUREPHP_MAIL_EOL;
                $message .= SECUREPHP_MAIL_EOL;
                if($error->getMessage()) $message .= 'Beschreibung: ' . $error->getMessage() . SECUREPHP_MAIL_EOL;
                if(isset($error->status)) $message .=  'Statushinweis: ' . ($error->status?:'nicht vorhanden') . SECUREPHP_MAIL_EOL;
                $message .= 'Datei: ' . $error->getFile() . SECUREPHP_MAIL_EOL;
                $message .= 'Zeile: ' . $error->getLine() . SECUREPHP_MAIL_EOL;
                $message .= 'Ursache: ';

                $message .= 'Berichtsklasse: ' . get_class($error) . SECUREPHP_MAIL_EOL;
                $message .= 'Parameter: ';
                if(method_exists($error, 'get_params') AND count($error->get_params()) )
                    {
                    $count = 1;
                    $message .= SECUREPHP_MAIL_EOL;
                    foreach($error->get_params() AS $name => $value)
                        {
                        $message .= $count .') '.$name .': '.(string) $value . SECUREPHP_MAIL_EOL;
                        $count++;
                        }
                    }
                else
                    {
                    $message .= 'nicht vorhanden'. SECUREPHP_MAIL_EOL;
                    }
                if($prev = $error->getPrevious())
                    {
                    $message .= SECUREPHP_MAIL_EOL;
                    $message .= 'Vorausgehende Meldungen: ' . SECUREPHP_MAIL_EOL;
                    $message .= SECUREPHP_MAIL_EOL;
                    $message .= $this->get_mail_message_previous($prev);
                    }

                if(method_exists($error, 'has_next') AND $error->has_next())
                    {
                    $message .= SECUREPHP_MAIL_EOL;
                    $message .= $error->get_mail_message_attachments($level . "A");
                    }

                $message .= SECUREPHP_MAIL_EOL;
                }
            return $message;
            }

        /**
         * @param string $notice
         * @return string
         */
        public function get_mail_message($notice=NULL)
            {

            $message = $this->get_mail_message_header();
            $message .= $this->get_mail_message_params();

            if($prev = $this->getPrevious())
                {
                $message .= SECUREPHP_MAIL_EOL;
                $message .= 'Vorrausgegangene Fehler: ' . SECUREPHP_MAIL_EOL;
                $message .= SECUREPHP_MAIL_EOL;
                $message .= $this->get_mail_message_previous($prev);
                }

            if($this->has_next())
                {
                $message .= SECUREPHP_MAIL_EOL;
                $message .= 'Alle Fehler in der Übersicht:' . SECUREPHP_MAIL_EOL;
                $message .= SECUREPHP_MAIL_EOL;
                $message .= $this->get_mail_message_attachments();
                }

            $message .= $this->get_mail_message_footer();
            return $message;
            }
        }

    /**
     * Class ErrorReport
     * @extends Exception
     */
    class ErrorReport extends \BatchReport
        {

        // ERRORREPORT HEAD

        /**
         * @var string
         */
        public $description = "error report";

        /**
         * Report-Empfänger.
         * @var string
         */
        protected $send_to     = "user";

        /**
         * @var bool
         */
        protected $flag_details = false;


        }

    /**
     * Class SuccessReport
     * @inherit \ErrorReport
     */
    class SuccessReport extends \BatchReport

        {

        // SUCCESSREPORT HEAD

        /**
         * @var string
         */
        public $description = "working range";

        /**
         * Report-Empfänger.
         * @var string
         */
        protected $send_to     = "user";

        /**
         * @var bool
         */
        protected $flag_details = true;

        // SUCCESSREPORT METHODS

        /**
         * @return string
         */
        final public function get_mail_header()
            {
            return AUTOFLOW\SECUREPHP\PROTECT::getInstance()->get_app() . ' Verarbeitungsbericht ';
            }

        /**
         * @return string
         */
        public function get_mail_message_attachments($level = NULL)
            {
            $count = 0;
            $message = "";
            foreach($this->get_attachments() AS $error)
                {
                $count++;
                // Umstellen auf Exceptions !!
                $message .= $count . ')' . SECUREPHP_MAIL_EOL;
                $message .= SECUREPHP_MAIL_EOL;
                if($error->getMessage()) $message .= 'Beschreibung: ' . $error->getMessage() . SECUREPHP_MAIL_EOL;
                if(isset($error->status)) $message .=  'Statushinweis: ' . ($error->status?:'nicht vorhanden') . SECUREPHP_MAIL_EOL;
                $message .= SECUREPHP_MAIL_EOL;
                if($prev = $error->getPrevious())
                    {
                    $message .= SECUREPHP_MAIL_EOL;
                    $message .= 'Vorausgehende Meldungen: ' . SECUREPHP_MAIL_EOL;
                    $message .= SECUREPHP_MAIL_EOL;
                    $message .= $this->get_mail_message_previous($prev);
                    }
                $message .= SECUREPHP_MAIL_EOL;
                }
            return $message;
            }

        /**
         * @param string $notice
         * @return string
         */
        final public function get_mail_message($notice = NULL)
            {

            $message = '';
            $message .= $this->get_mail_message_header('Verarbeitungsbericht vom ' . date('d.m.Y H:i:s')) . SECUREPHP_MAIL_EOL;
            $message .= $this->get_mail_message_params();

            if($this->has_next())
                {
                $message .= SECUREPHP_MAIL_EOL;
                $message .= 'Verarbeitungsprotokoll:' . SECUREPHP_MAIL_EOL;
                $message .= SECUREPHP_MAIL_EOL;
                $message .= $this->get_mail_message_attachments();
                }

            $message .= $this->get_mail_message_footer();
            return $message;
            }
        }

    /**
     * Class TimerAlert
     * @inherit \ErrorReport
     */
    final class Reminder extends \BatchReport
        {

        /**
         * @var string
         */
        public $description = 'reminder alert';

        /**
         * Report-Empfänger.
         * @var string
         */
        protected $send_to     = "admin>user,log";

        /**
         * @var bool
         */
        protected $flag_details = false;

        }


    /**
     * Class Eof
     * @inherit Error
     */

    class EofError extends \ErrorTicket
        {
        /**
         * @var string
         */
        public $description = "eof error";

        /**
         * Report-Empfänger.
         * @var string
         */
        protected $send_to     = "admin,log";

        /**
         * @var bool
         */
        public $flag_details = false;

        }

    /**
     * Class RunTimeError
     *
     * Vielleicht kann man hier mit Implements() oder einem zweiten extends()
     * arbeiten um direkt von ErrorTicket abzuleiten
     *
     * @inherit \ErrorException
     */
	class PhpError extends \RaisableError
		{

        /**
         * @var string
         */
        public $description = "PHP runtime error";

        /**
         * Report-Empfänger.
         * @var string
         */
        protected $send_to     = "admin,log";

        /**
         * @var bool
         */
        protected $flag_details = true;

        // PHPRUNTIMEERROR MAGIC METHODS

        /**
         * @param string $message
         * @param null $code
         * @param null $severity
         * @param null $file
         * @param null $line
         * @param \Exception $previous
         */
        public function __construct($message, $code=NULL, $severity=NULL, $file=NULL, $line=NULL, \Exception $previous=NULL)
            {

            parent::__construct($message, $code, $severity, $file, $line, $previous);


            }
		}

    /**
     * Class PhpWarning
     * @inherit Error
     */
    class PhpWarning extends \PhpError

        {

        /**
         * @var string
         */
        public $description = "php warning";

        /**
         * Report-Empfänger.
         * @var string
         */
        protected $send_to     = "admin,log";

        /**
         * @var bool
         */
        public $flag_details = false;

        }


    /**
     * Class PhpNotice
     * @inherit Error
     */
    class PhpNotice extends \PhpError

        {

        /**
         * @var string
         */
        public $description = "php notice";

        /**
         * Report-Empfänger.
         * @var string
         */
        protected $send_to     = "admin,log";

        /**
         * @var bool
         */
        public $flag_details = false;

        }

    /**
     * Class UncaughtException
     * @inherit \ErrorTicket
     */
    final class UncaughtException extends \ErrorTicket
        {

        /**
         * @var string
         */
        public $description = 'uncaught exception';

        /**
         * Report-Empfänger.
         * @var string
         */
        protected $send_to     = "admin,log";

        /**
         * @var bool
         */
        protected $flag_details = false;

        }

    /**
     * Class ShutdownError
     * @inherit Error
     */
	class ShutdownError extends \PhpError

		{

        /**
         * @var string
         */
		public $description = "shutdown error";

        /**
         * Report-Empfänger.
         * @var string
         */
        protected $send_to     = "admin,log";

        /**
         * @var bool
         */
        public $flag_details = true;

		}

	}