<?php

// Version 2.0, 24.10.2016
# @todo: vielleicht Klasse "Ticket" als Basis für ErrorTicket und SuccessTicket
# 1) ErrorTicket::description geändert zu ErrorTicket::status
# 2) ErrorTicket::error geändert zu ErrorTicket::description
# 3) added static class ErrorInfo{}
# 4) ErrorTicket::description entfernt, wird von ErrorTicket::note abgelöst

// Version 1.0
# 1) Initial release, no info


/**
 * @package SECUREPHP
 * @author Alexander Münch
 * @copyright Alexander Münch
 * @version 2.0
 */

namespace

	{

    /**
     * Version of current reports class.
     */
    define('SECUREPHP_REPORTS_VERSION', '2.0');


    /**
     * Class RAISABLE
     * @inherit Exception
     */

    trait RAISEABLE

		{

        // TRAIT HEAD


        /**
         * @todo change to protected, @see set_status
         * Statusmeldung.
         * @var string
         */
		public $status = '';

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
         * @todo @see set_params, @see add_param
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
         * Email-Empfänger.
         * @var string
         */
        protected $send_to              = "admin>user,log";

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
         * @return string
         */
        final public function toString(\Exception $e)
            {

            $message = '';

            $flag_is_raiseable = (bool) (is_a($e, 'Raisable') OR is_a($e, 'RaisableError'));

            if($flag_is_raiseable AND $e->flag_details)
                {
                $message .= '* '.$e->description.' in ' . $e->getFile() . ', Zeile ' . $e->getLine() . SECUREPHP_LINE_BREAK;
                }

            $message .= '* Beschreibung: ' . $e->getMessage() . SECUREPHP_LINE_BREAK;

            if($flag_is_raiseable AND $e->get_note())
                {
                $message .= '*' . SECUREPHP_LINE_BREAK;
                $message .= '* Hinweis: ' . $e->get_note() . SECUREPHP_LINE_BREAK;
                }

            if($flag_is_raiseable AND $e->get_status())
                {
                $message .= '*' . SECUREPHP_LINE_BREAK;
                $message .= '* Status: ' . $e->get_status() . SECUREPHP_LINE_BREAK;
                }

            if(( $flag_is_raiseable AND $e->flag_details ) OR !$flag_is_raiseable)
                {
                $message .= '*' . SECUREPHP_LINE_BREAK;
                $message .= '* Trace:' . SECUREPHP_LINE_BREAK;
                $message .= $this->formatTrace($e);
                }

            if(is_a($e, 'ErrorReport') AND $e->has_next())
                {
                $i = 0;
                $message .= '*' . SECUREPHP_LINE_BREAK;
                $message .= '* Fehler: ' . SECUREPHP_LINE_BREAK;
                $message .= '* ' . SECUREPHP_LINE_BREAK;
                foreach ($e->get_attachments() AS $attachement)
                    $message .= '*'.SECUREPHP_LINE_BREAK."* " . ++$i . ') ' . get_class($attachement) . SECUREPHP_LINE_BREAK . (string) $attachement . "";
                }

            if ($e->getPrevious())
                {
                $message .= SECUREPHP_LINE_BREAK;
                $message .= "* Vorrausgehend:" . SECUREPHP_LINE_BREAK;
                $message .= '*' . SECUREPHP_LINE_BREAK;
                $message .= $this->toString($e->getPrevious());
                }

            return $message . "";
            }


        /**
         * @param int|null $timeout
         * @return void
         * @throws \Exception
         */
        final public function raise($timeout = NULL)
            {

            if(true == $this->has_raised())
                {
                return NULL;
                }
            elseif(false == defined('SECUREPHP'))
                {
                throw new Exception('Fehler beim Versenden des angefügten Berichts. ' . SECUREPHP . ' steht nicht zur Verfügung um diesen Fehlerbericht zu versenden.', NULL, $this);
                }
            elseif(false == \AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance())
                {
                throw new \AUTOFLOW\SECUREPHP\E_INIT('Fehler beim Versenden des angefügten Berichts. ' . SECUREPHP . ' ist nicht initialisiert.', NULL, $this);
                }
            elseif(true == \AUTOFLOW\SECUREPHP\PROTECT::getInstance()->in_progress())
                {
                // @todo U.u. diese Recursion erlauben um Berichte innerhalb eines
                // Berichtes freizugeben. Vorher prüfen auf Richtigkeit, z.B. E_FATAL
                throw new \AUTOFLOW\SECUREPHP\E_FATAL('RAISE_RECURSION',NULL, $this);
                }
            else
                {
                \AUTOFLOW\SECUREPHP\PROTECT::getInstance()->in_progress(true);
                if(NULL === $timeout) $timeout = $this->get_timeout();
                \AUTOFLOW\SECUREPHP\PROTECT::getInstance()->notify($this->get_send_to(), $this, $timeout);
                \AUTOFLOW\SECUREPHP\PROTECT::getInstance()->in_progress(false);
                $this->has_raised(true);
                }
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
            return md5(serialize($this));
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
         * @param string $status
         * @return bool
         */
        final public function set_status($status)
            {
            $this->status = $status;
            return true;
            }

        /**
         * @return null|string
         */
        final protected function get_status()
            {
            return $this->status;
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
        final public function set_params(ARRAY $params)
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

            $message .= \AUTOFLOW\SECUREPHP\PROTECT::getInstance()->get_app() . SECUREPHP_MAIL_EOL;
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
                if('all' == $user AND "all" == $this->send_to) {
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
                        elseif($_email = AUTOFLOW\SECUREPHP\MAIL::getInstance()->get_user_email($name))
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
                        $_recipients[] = $user . ' [' .AUTOFLOW\SECUREPHP\MAIL::getInstance()->get_user_email($user) .']';
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
    class Raisable extends \AUTOFLOW\SECUREPHP\EXCEPTION
        {

        // E_RAISEABLE HEAD

        use \RAISEABLE;

        }

    /**
     * Base class E_RAISABLE_ERROR.
     */
    class RaisableError extends \AUTOFLOW\SECUREPHP\ERROR_EXCEPTION
        {

        // E_RAISEABLE_ERROR HEAD

        use \RAISEABLE;
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
        public $description    = 'Fehlerticket';

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
            $this->application = \AUTOFLOW\SECUREPHP\PROTECT::getInstance()->get_app();
            $this->status = $status;
            }

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
        public $description = 'Bestätigungsnachricht';

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
     * Class ErrorReport
     * @extends Exception
     */
    class ErrorReport extends \ErrorTicket
        {

        // ERRORREPORT HEAD

        /**
         * @var string
         */
        public $description = "Fehlerbericht";

        /**
         * @var bool
         */
        protected $flag_details = true;

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
     * Class SuccessReport
     * @inherit \ErrorReport
     */
    final class SuccessReport extends \ErrorReport

        {

        // SUCCESSREPORT HEAD

        /**
         * @var string
         */
        public $description = "Verabeitungsbericht";

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
     * Class Notice.
     * @inherit \ErrorTicket
     */
    final class Notice extends \ErrorTicket
        {

        /**
         * @var string
         */
        public $description = 'Hinweis';

        /**
         * @var bool
         */
        protected $flag_details = true;

        /**
         * @return string
         */
        final public function get_mail_header()
            {
            return SECUREPHP . $this->getMessage();
            }
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
		public $description    = 'Konfigurationsfehler';

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

        /**
         * @param string $name
         * @param string $value
         * @return bool
         */
        public function add_config_param($name='default', $value='')
            {
            $this->config_params[$name] = $value;
            return true;
            }

        /**
         * @param array $params
         * @return bool
         */
        public function set_config_params(ARRAY $params)
			{
			$this->config_params = $params;
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
		public $description = 'Start- oder Initialisierungsfehler';

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
        public $description = 'Übergangsfehler';

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
        public $description = 'Transaktionsfehler';

        /**
         * @var bool
         */
        protected $flag_details = true;

        }

    /**
     * @todo zu allgemein, spezifizieren
     * Class ClassError
     * @inherit \ErrorTicket
     */
	final class ClassError extends \ErrorTicket

		{

        /**
         * @var string
         */
		public $description = 'Objektfehler';

        /**
         * @var bool
         */
        protected $flag_details = true;

		}

    /**
     * Class TimerAlert
     * @inherit \ErrorReport
     */
	final class TimerAlert extends \ErrorReport
		{

        /**
         * @var string
         */
		public $description = 'Erinnerung an weiterhin bestehenden Fehler';

        /**
         * @var bool
         */
        protected $flag_details = true;
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
        public $description = 'Unbehandelte Exception';

        /**
         * @var bool
         */
        protected $flag_details = false;
        }

    /**
     * Class RunTimeError
     *
     * Vielleicht kann man hier mit Implements() oder einem zweiten extends()
     * arbeiten um direkt von ErrorTicket abzuleiten
     *
     * @inherit \ErrorException
     */
	class Error extends \RaisableError
		{

        /**
         * @var string
         */
        public $description = "Laufzeitfehler";

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
            #if(class_exists("\SECUREPHP\PROTECT", false))
            #    {
                 #$this->application = \SECUREPHP\PROTECT::getInstance()->get_app();
            #    }
            }
		}

    /**
     * Class ShutdownError
     * @inherit Error
     */
	class ShutdownError extends \Error

		{

        /**
         * @var string
         */
		public $description = "Shutdown-Fehler";

        /**
         * @var bool
         */
        public $flag_details = true;

		}

    /**
     * Class Eof
     * @inherit Error
     */

    class EofError extends \Error
        {
        /**
         * @var string
         */
        public $description = "Ablauffehler";

        /**
         * @var bool
         */
        public $flag_details = false;
        }

	}