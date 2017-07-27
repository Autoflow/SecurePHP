<?php

// Version 2.0
# 1) Initial release, no info

/**
 * SecurePHP Fehlerklasse.
 *
 * Klasse zum Abfangen von Laufzeitfehlern und Exceptions sowie
 * zum Senden dieser und weiterer vorgefertigter bzw. eigener
 * Berichte und Zusammenfassungen per Email.
 *
 * Sendet Fehler und Berichte/Zusammenfassungen per Standard an ein
 * mittels @see BOOTSTRAP::getInstance->reference
 * definiertes, zentrales Logfile sowie, je nach Bedarf,
 * zusätzlich nach STDERR und per Mail an Anwender.
 *
 * Basis der Berichte, Fehler und Zusammenfassungen bilden Exceptions
 * bzw. die davon mittels @see secure.class.reports.php abgeleiteten
 * Klassen für Exception/Error- und Shutdown-Handler.
 *
 * Beispiel:
 *
 * $error = new ErrorTicket('Fehlerbeschreibung', 'Statusbeschreibung');
 * $error->set_error('exakte Fehlerursache');
 * $error->send_to('admin>user', log); # Reihenfolge der Zustellung festlegen
 * $error->raise(); # versendet den Fehlerbericht
 *
 * Im obigen Beispiel wird ein allgemeines Fehlerticket mit Fehlerbeschreibung,
 * Statusinformation und Fehlerursache erstellt. Das Ticket wird per Email an
 * eine Liste von Administratoren (CONFIG::admin()) gesendet. Wenn die Zu-
 * stellung fehlschlägt stattdessen an die vorher mittels CONFIG::user() fest-
 * gelegten Anwendungsbenutzer. Zusätzlich werden die Fehlerinformationen
 * in das Logfile geschrieben.
 *
 * Die eingebundene SecurePHP-Klasse muß mittels BOOTSTRAP::init()
 * zuerst  initialisert werden. Ohne Initialisierung können keine
 * Emails versendet werden und die Fehlerbehandlung wird weiterhin von
 * der PHP-Laufzeitumgebung festgelegt.
 *
 * Details zur Initialisierungsequenz siehe @see BOOTSTRAP::init()
 *
 * Nach der Initialisierung mittels BOOTSTRAP::init() ändern sich folgende
 * Eigenschaften der PHP-Laufzeitumgebung:
 *
 * 1) mittels error_log() ist eine zentrale Fehlerlogdatei
 *    festgelegt auf den Namen der ausgeführten Datei mit der
 *    Endung .log.txt. Mittels des Init-Parameters 'reference'
 *    lässt sich ein globales Logfile festlegen, welche die
 *    Fehler aus verschiedenen Scripten bündelt (Beispiel:
 *    Webanwendung mit zentralem Controller)
 *
 * 2) E_NOTICE-Fehler werden als Fatale Fehler behandelt.
 *    Der laufende Prozess wird abgebrochen.
 *
 * 3) Wiederholungsfehler können mittels der TIMEOUT-Klasse
 *    als solche erkannt und abgefangen werden. Verhindert
 *    den Massenversand von Emails bei sich kurzfristig
 *    wiederholenden Cronjobs.
 *
 * 4) Fehler können mittels der ERRORLOG-Klasse in ein Logfile
 *    und gleichzeitig auf die Ausgabe, STDERR und nach error_log
 *    geschrieben werden.
 *
 *  5) Der @-Parameter wurde dahingehend erweitert, daß das Skript
 *     weiterläuft.
 *
 * Um Fehler oder Berichte bzw. Zusammenfassungen per Email
 * zu versenden ist die Konfiguration der Email-Umgebung vor-
 * zunehmen.
 *
 * 1) Absender festlegen mittels PROTECT->set_from()
 * 2) Administratoren festlegen: PROTECT->admin()
 * 3) Benutzer festlegen: PROTECT->user();
 * 4) weitere Benutzer für individuelle Nachrichten: PROTECT->add_user()
 *
 *     Beispiel:
 *
 * // Initialisierung
 * 1) $securephp = include_once 'secure.php';
 * 2) $securephp->init($_SERVER['SCRIPT_FILENAME']);
 *
 * // Email-Konfiguration
 * 3) $securephp->config->set_from('securephp@localhost');
 * 4) $securephp->config->admin('admin@localhost');
 * 5) $securephp->config->user('operator@localhost');
 * 6) $securephp->config->add_user('manager', 'manager@localhost');
 *
 * // Timeout festlegen - unterbindet Wiederholungsfehler.
 * 7) $securephp->config->set_timeout( 5 * 60 );
 *
 * // Fehlerbehandlung (senden, bzw. Log)
 * 8) if($secure->config->has_errors()) $securephp->config->errors->raise();
 * oder mittels Exception
 * 9) if($secure->config->has_errors()) throw $securephp->config->errors;
 *
 *
 * @package SECUREPHP
 * @author Alexander Münch
 * @copyright Alexander Münch
 * @version 2.0
 */

namespace AUTOFLOW\SECUREPHP

    {


    // @TODO:
    #1 ErrorReport && SuccessReport ableiten von allgemeiner Report-Klasse
    #2 Wenn @ in error_handler() sende keine Emails. Das Skript läuft weiter
    #  und kann die benutzerseitige Fehlerbehandlung nutzen.
    #4 PHPRUNTIMEERROR: Trace an Log schicken mit Informationen z.B. zum Dateipfad bei ftp_rename()
    #5 Standardempfänger von send_to() überprüfen


    // Version 2.0, 18.09.2016
    #1 Full rewrite (TIMEOUT)
    #2 added CONFIG-class
    #3 changed init sequence
    #4 ConfigError -> send_to(admin,user,log)
    #5 Errorticket -> send_to(admin>user,log)
    #6 E_USER_ERROR --> send_to(admin>user,log)
    #7 ERRORLOG erweitert um getPrevious()
    #8 Exceptions im Fehlerfall
    #9 Recursion vermieden

    // Update 08.08.2016
    #1 PROTECT::need() liefert Objekt zurück wenn kein Fehler

    // Update 02.08.2016
    #1 shutdown_function wird nur noch im Fehlerfall ausgeführt.
    #2 TicketError::get_md_5() --> verwendet serialize() anstelle __toString()
    #3 PHPRUNTIMEERROR::__toString (neu) und get_md5() neu
    #4 Datum in Tickets deaktiviert
    #5 error_handler() --> $error->send_to('admin>user, log');
    #  $error->raise(); anstelle PROTECT::notify($error)

    // Update 31.07.2016
    #1 set_error_handler() jetzt mit Unterstützung für Timeout (Change)
    #2 added get_md5() to class PhpRunTimeError (Feature)
    #3 added break; to set_error_handler() -> E_NOTICE && E_USER_NOTICE (BUG)
    #4 reset cwd() within shutdown handler (Update)
    #5 Diverse Fehler beseitigt (Bugs)
    #6 Timeout überarbeitet, Empfänger korrigiert,
    #  Erinnerung/Wiederholungsfehlerroutine neu (Update)

    // Version 28.07.2016
    // Release 1.0
    #1 Keine Emails wenn Timer-Daten gelöscht werden (Update)
    #2 Funktion need(), check_files() zum Prüfen von Verzeichnissen/Pfaden/Dateien .. (Feature)
    #3 Diverse Bugs beseitigt



	// AUFBAU:

	# SECUREPHP::BOOTSTRAP
	# SECUREPHP::PROTECT
    # SECUREPHP::CONFIG
	# SECUREPHP::ERRORLOG
	# SECUREPHP::MAIL
    # SECUREPHP::TIMEOUT

 	// CONSTANTS

    /**
     * Name der aktuellen Klassenbibliothek.
     *
     * Wird als Bezugsgröße in Fehlerberichten
     * und Nachrichten verwendet.
     *
     * @const string
     */

	define('SECUREPHP'                  , 'SecurePHP');

    /**
     * Die Version der aktuellen Klassenbibliothek.
     *
     * @const string
     */

	define('SECUREPHP_VERSION'          , '2.0');

    /**
     * Liste der möglichen Laufzeit-Modi.
     *
     * DEBUG:
     * Es werden z.B. keine Emails versendet.
     * Aktivieren mittels $DEBUG = true;
     *
     * OFF:
     * Aktivieren mittels
     * PROTECT::getInstance()->disable().
     * Exception/Error/Shutdown-Handler sind
     * danach nicht mehr aktiv. Es werden keine
     * Emails versendet. Fehler-Reporting wird
     * von PHP verwaltet. Deaktivieren mittels
     * PROTECT::enabled() um zu SAFE-Modus zurück-
     * zugelangen.
     *
     * MUTE:
     * Aktivieren mittels
     * PROTECT::getInstance()->mute().
     * Es werden keine Emails versendet und
     * der Fehlerlog ist deaktiviert. Zurück
     * zu SAFE-Modus mittels PROTECT::mute(false);
     *
     * @const
     */
    define('SECUREPHP_HANDLE_MUTE'      , 1);
    define('SECUREPHP_HANDLE_PROMPT'    , 2);
	define('SECUREPHP_HANDLE_DEBUG'     , 4);
	define('SECUREPHP_HANDLE_OFF'       , 8);

    /**
     *
     */
    define('SECUREPHP_HANDLE_LOOSE'     , 16);
    define('SECUREPHP_HANDLE_STRICT'    , 32);

    /**
     * Exit-Code.
     *
     * Legt fest ob der aktuelle Prozess nach
     * einem durch PROTECT::error_handler()
     * behandelten Fehler beendet wird.
     *
     * Die Referenztabelle befindet sich in
     * PROTECT::get_php_error().
     *
     * @const int
     */
    define('SECUREPHP_EXIT_ON_ERROR'    , 1);

    /**
     * Anzahl der Prozesse nach deren Durchlauf
     * Wiederholungsfehler gelöscht werden.
     *
     * @const int
     */
    define('SECUREPHP_TIMEOUT'          , 2);

    /**
     * Dateiendung der Logdatei
     *
     * @const int
     */
    define('SECUREPHP_LOGEXT'           , 'log.txt');

    /**
     * Zeilenende für Emails.
     *
     * @const int
     */
    define('SECUREPHP_MAIL_EOL'         , "\r\n");

    /**
     * Allgemeines Zeilenende.
     * PHP CLI und Web wiesen leider
     * unterschiedliche Werte
     * für SECUREPHP_NEW_LINE auf ...
     */
    define('SECUREPHP_NEW_LINE'         , "\n");

    /**
     * Zeilenende für Messages.
     *
     * @const int
     */
    define('SECUREPHP_LINE_BREAK'       , "" . SECUREPHP_NEW_LINE);




    // INCLUDES

    spl_autoload_register(function ($class = '')
        {
        include_once 'secure.class.base.php';
        include_once 'secure.class.exceptions.php';
        });

    /**
     * Class BOOTSTRAP
     * @package SECUREPHP
     */
	final class BOOTSTRAP extends SINGLETON
	
		{
			
		// BOOTSTRAP HEAD

        /**
         * Das Konfigurationsobjekt.
         * @var CONFIG
         */
        public $config;

        /**
         * Datumsformatierung.
         * @var string
         */
        public static $date = '[d-M-Y H:i:s] ';

        /**
         * @var string
         */
        private $abs_path = '';

        /**
         * Speichert den Startzeitpunkt.
         * @var int time()
         */
        public static $starttime = '';

        /**
         * Wert der Logdatei.
         *
         * Mögliche Werte:
         * Keine Log -> false
         * Standardlogdatei -> true
         * eigene Logdatei -> Pfad zur Logdatei
         *
         * @var string|bool
         */
		private $logfile = true;

        /**
         * Cache für current working directory.
         * @var string
         */
		private $wd	= '';

        /**
         * Cache für display_errors
         * @var bool
         */
        private $restore_display = false;

        /**
         * Liste erlaubter Interfaces (cli, sapi, ..).
         * TODO: implementieren
         * @var string[]
         */
		private $permit = ARRAY();

        /**
         * Gültige Instanz erzeugt ja/nein.
         * @var bool
         */
		private $flag_is_configured = false;

        /**
         * Letzter Initialisierungsfehler.
         * @var \Exception
         */
		private $init_error;

        /**
         * Letzter Anwendungssfehler.
         * @var \ErrorTicket
         */
        private $error;

        /**
         * @var static
         */
		private static $instance;

		// BOOTSTRAP METHODS
			
		/**
		* Returns the *Singleton* instance of this class.
		*
        * @param string|bool $logfile
        * @param bool $flag_to_stderr
        * @param string $permit_for
        *
		* @return static BOOTSTRAP
        * @throws \EXCEPTION
		*/
		final public static function getInstance($logfile=NULL, $flag_to_stderr=false, $permit_for=NULL)
			{
			if (NULL === static::$instance)
				{
				static::$instance = new static();
                if(false == static::$instance->init($logfile, $flag_to_stderr, $permit_for))
                    {
                    throw static::$instance->get_init_error();
                    }
				}
				return static::$instance;
			}


        /**
         * @param string $logfile
         * @param bool $flag_to_stderr
         * @param string $permit_for
         * @return bool
         *
         * @todo  When using CLI ( and perhaps command line without CLI - I didn't test it) the shutdown function doesn't get called if the process gets a SIGINT or SIGTERM. only the natural exit of PHP calls the shutdown function.
                  To overcome the problem compile the command line interpreter with --enable-pcntl and add this code:

                  <?php
                    function sigint()
                    {
                    exit;
                    }
                    pcntl_signal(SIGINT, 'sigint');
                    pcntl_signal(SIGTERM, 'sigint');
                  ?>
         *
         */
        final public function init($logfile=NULL, $flag_to_stderr=false, $permit_for=NULL)
			{
            if ($this->is_configured())
                {
                return true;
                }
            elseif(version_compare(PHP_VERSION, '5.5') < 0)
                {
                // AB PHP 5.3
                // - finally-Block
                // - <5.2 no error_get_last
                // - Export von Klassen ab PHP 5.1.0 mittels var_export()
                // - traits ab 5.4
                // - passing null to set_error_handler was added in PHP 5.5
                // - PHP 5.4.0, the CLI SAPI provides a built-in web server
                $this->set_init_error(new E_INIT('PHP-Version wird nicht unterstützt'));
                }
            elseif (false === $this->set_logfile($logfile))
                {
                $this->set_init_error(new E_INIT('konnte Konfiguration für das Logfile nicht ermitteln', false, $this->get_init_error));
                }
            elseif (false === $this->set_error_log())
                {
                $this->set_init_error(new E_INIT('konnte das Logifle nicht umleiten'), false, $this->get_init_error);
                }
            elseif (false === (BOOTSTRAP::$starttime = time()))
                {
                $this->set_init_error(new E_INIT('konnte den Startzeitpunkt nicht ermitteln'));
                }
            elseif (false === (include $this->get_local_dir() . 'secure.class.reports.php'))
                {
                $this->set_init_error(new E_INIT('die Berichte-Bibliothek secure.reports.php steht nicht zur Verfügung in ' . getcwd()));
                }
            elseif(ini_get('log_errors') AND !ini_set('log_errors', false))
                {
                $this->set_init_error(new E_INIT('Konnte log_errors nicht deaktivieren'));
                }
            elseif(ini_get('display_errors') AND !$this->mute(1))
                {
                $this->set_init_error(new E_INIT('Konnte Standardmodus nicht aktivieren.'));
                }
            elseif (false === $this->set_abs_path())
                {
                $this->set_init_error(new E_INIT('konnte das Basisverzeichnis  nicht ermitteln'));
                }
            elseif (false === $this->check_cwd())
                {
                $this->set_init_error(new E_INIT('konnte das Basisverzeichnis nicht prüfen'));
                }
            elseif (false === $this->backup_cwd())
                {
                $this->set_init_error(new E_INIT('konnte das Basisverzeichnis nicht sichern'));
                }
            if (false == ($this->config = CONFIG::getInstance()))
                {
                $this->set_init_error(new E_INIT('konnte CONFIG-Instanz nicht starten'));
                }
            elseif (false === (ERRORLOG::getInstance()))
                {
                $this->set_init_error(new E_INIT('konnte keine gültige ERRORLOG-Klasse ableiten. Fehler beim Erzeugen der Instanz', false, ERRORLOG::getInstance()->get_init_error()));
                }
            elseif (false == CONFIG::getInstance()->to_stderr($flag_to_stderr))
                {
                $this->set_init_error(new E_INIT('konnte die Einstellungen für STDERR  nicht festlegen'));
                }
            elseif (false === (DB::getInstance()->init()))
                {
                $this->set_init_error(new E_INIT('konnte keine gültige DB-Klasse ableiten. Fehler beim Erzeugen der Instanz.', false, DB::getInstance()->get_init_error()));
                }
            elseif (false === $this->set_interfaces($permit_for))
                {
                $this->set_init_error(new E_INIT('konnte die Liste erlaubter Endgeräte nicht festelegen'));
                }
            elseif (false === $this->check_interface())
                {
                $this->set_init_error(new E_INIT('konnte das Endgerät nicht prüfen'));
                }
            elseif (false === (PROTECT::getInstance()))
                {
                $this->set_init_error(new E_INIT('konnte keine gültige PROTECT-Klasse ableiten. Fehler beim Erzeugen der Instanz.'));
                }
            elseif (false === PROTECT::getInstance()->protect())
                {
                $this->set_init_error(PROTECT::getInstance()->get_init_error());
                }
            elseif (false === ($this->flag_is_configured = true))
                {
                $this->set_init_error(new E_INIT('konnte die Initialisierung von ' . SECUREPHP . ' nicht erfolgreich abschließen'));
                }

            if($e = $this->get_init_error())
                {
                $this->terminate($this->get_init_error());
                }
            else
                {
                $this->is_configured(true);
                return true;
                }

			}

        /**
         * @return bool
         */
        final public function startmail()
            {
            if(false == MAIL::getInstance())
                {
                return false;
                }
            elseif(false == MAIL::getInstance()->init())
                {
                return false;
                }
            else return true;
            }

        /**
         * @param $e \Exception
         */
        final private function terminate(\Exception $e)
            {
            ERRORLOG::getInstance()->log($e, false);
            throw $e;
            }

        /**
         * @return bool
         */
        final public function is_configured($flag_configured = NULL)
			{
			if(NULL === $flag_configured) return $this->flag_is_configured;
            else
                {
                $this->flag_is_configured = (bool) $flag_configured;
                return true;
                }
			}


        /**
         * @return bool
         */
        final public function is_cli()
            {
            if(defined('STDIN') )
                {
                return true;
                }
            if(empty($_SERVER['REMOTE_ADDR']) AND empty($_SERVER['HTTP_USER_AGENT']) AND count($_SERVER['argv']) > 0)
                {
                return true;
                }
            return false;
            }

        /**
         * Versetzt SecurePHP zurück
         * in den Standardmodus.
         *
         * @return bool
         */
        final public function enabled($enable = NULL)
            {

            static $restore_handle;

            if(NULL === $enable)
                {
                return (SECUREPHP_HANDLE_OFF != PROTECT::getInstance()->handle());
                }
            elseif(false == $enable AND SECUREPHP_HANDLE_OFF == PROTECT::getInstance()->handle())
                {
                // Schon deaktivert
                return true;
                }
            elseif(true == $enable AND SECUREPHP_HANDLE_OFF != PROTECT::getInstance()->handle())
                {
                // Schon aktiviert
                return true;
                }
            elseif(false == (bool) $enable)
                {
                // Restore display errors
                ini_set('display_errors', $this->restore_display);
                // Save current handle
                $restore_handle = PROTECT::getInstance()->handle();
                // disable all handler ..
                PROTECT::getInstance()->remove_handler();
                // Set state
                return PROTECT::getInstance()->handle(SECUREPHP_HANDLE_OFF);
                }
            else
                {
                // Re-enable all handler
                PROTECT::getInstance()->add_handler();
                return PROTECT::getInstance()->handle($restore_handle);
                }
            }

        /**
         * @param null $flag
         * @return bool
         */
        final public function debug($flag=NULL)
            {

            static $restore_handle;

            if(NULL === $flag)
                {
                return (SECUREPHP_HANDLE_DEBUG == PROTECT::getInstance()->handle());
                }
            elseif(!(bool)$flag AND SECUREPHP_HANDLE_DEBUG != PROTECT::getInstance()->handle())
                {
                // Wenn der Debug-Modus nicht aktiv
                // ist kann dieser auch nicht beendet werden.
                return true;
                }
            elseif(true == (bool)$flag)
                {
                ini_set('display_errors', true);
                ini_set('log_errors', true);
                $restore_handle = PROTECT::getInstance()->handle();
                PROTECT::getInstance()->handle(SECUREPHP_HANDLE_DEBUG);
                return true;
                }
            elseif(!(bool) $flag)
                {
                ini_set('display_errors', false);
                ini_set('log_errors', false);
                PROTECT::getInstance()->handle($restore_handle);
                return true;
                }
            }

        /**
         * @return bool
         */
        final public function mute($handle = NULL)
            {
            if(NULL === $handle)
                {
                #if(ini_get('display_errors')) ini_set('display_errors', false);
                return PROTECT::getInstance()->handle();
                }
            elseif(SECUREPHP_HANDLE_OFF == PROTECT::getInstance()->handle())
                {
                return true;
                }
            elseif(true === (bool) $handle AND BOOTSTRAP::getInstance()->debug())
                {
                //
                PROTECT::getInstance()->handle(SECUREPHP_HANDLE_PROMPT);
                return true;
                }
            elseif(false == (bool)$handle)
                {
                PROTECT::getInstance()->handle(SECUREPHP_HANDLE_PROMPT);
                return true;
                }
            elseif(true == (bool)$handle)
                {
                if(ini_get('display_errors'))
                    {
                    $this->restore_display = true;
                    ini_set('display_errors', 0);
                    }
                PROTECT::getInstance()->handle(SECUREPHP_HANDLE_MUTE);
                return true;
                }
            else return false;
            }

        /**
         * @return bool
         */
        final public function loose($mode = NULL)
            {
            if(NULL === $mode) return PROTECT::getInstance()->mode();
            elseif((bool)$mode)
                {
                return PROTECT::getInstance()->mode(SECUREPHP_HANDLE_LOOSE);
                }
            else
                {
                return PROTECT::getInstance()->mode(SECUREPHP_HANDLE_STRICT);
                }
            }

        /**
         * @return bool
         */
        final public function end()
            {
            PROTECT::getInstance()->set_eof();
            return true;
            }

        /**
         * Alias for end()
         * @return bool
         */
        final public function eof()
            {
            PROTECT::getInstance()->set_eof();
            return true;
            }


        /**
         * Speichert das aktuelle Arbeitsverzeichnis.
         * Notwendig als Backup für den Shutdown-Handler.
         * @return bool
         */
        final private function backup_cwd()
            {
            if(false === ($this->wd = getcwd()))
                {
                $this->set_init_error(new E_INIT('konnte das aktuelle Arbeitsverzeichnis nicht ermitteln'));
                return false;
                }
            else return true;
            }

        /**
         * Prüft das aktuelle Arbeitsverzeichnis.
         * Notwendig als Backup für den Shutdown-Handler.
         * @return bool
         */
        final private function check_cwd()
            {
            if (realpath(getcwd()) != realpath(dirname(get_included_files()[0])))
                {
                $this->set_init_error(new E_INIT('das aktuelle Arbeitsverzeichnis (' . realpath(getcwd()) . ') und das Verzeichnis der ausgeführten Scriptdatei (' . realpath(dirname(get_included_files()[0])) . ') stimmen nicht überein'));
                return false;
                }
            else return true;
            }


        /**
         * Retrieve working directory as
         * saved by @see backup_cwd().
         *
         * @return string
         */
        final public function get_wd()
            {
            return $this->wd;
            }

        /**
         * TODO: implement check
         * @return bool
         */
        final private function check_interface()
            {
            #if($this->interfaces NOT CLI OR SAPI) return false;
            return true;
            }

			
		// BOOTSTRAP GETTERS & SETTERS

        /**
         * @param \Exception $e
         * @return bool
         */
        final private function set_init_error(\Exception $e)
			{
            $this->init_error = $e;
            return true;
			}

        /**
         * @return \Exception
         */
        final public function get_init_error()
			{
			return $this->init_error;
			}

        /**
         * @param \Exception $e
         * @return bool
         */
        final private function set_error(\Exception $e)
            {
            $this->error = $e;
            return true;
            }

        /**
         * @return \Exception
         */
        final public function get_error()
            {
            return $this->error;
            }

        /**
         * @return mixed
         */
        final protected function get_previous_trace()
            {
            return debug_backtrace (DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
            }

        /**
         * Retrieve directory SecurePHP-Lib relies in.
         *
         * @return string
         */
        final public function get_local_dir()
            {
            return dirname(__FILE__) . DIRECTORY_SEPARATOR;
            }

        /**
         * Get current SecurePHP version.
         * @return string
         */
        final public function get_version()
            {
            return SECUREPHP_VERSION;
            }

        /**
         * Set strict mode.
         *
         * This is the default error_handler() mode.
         *
         * error_handler() will exit on mostly all errors.
         */
        final public function set_strict()
            {
            CONFIG::getInstance()->error_handling = SECUREPHP_HANDLE_STRICT;
            return true;
            }

        /**
         * Set to loose mode.
         *
         * error_handler() will exit like PHP default settings.
         */
        final public function set_loose()
            {
            CONFIG::getInstance()->error_handling = SECUREPHP_HANDLE_LOOSE;
            return true;
            }

        /**
         * Save abs path of current script file.
         *
         * @return bool
         */
        final public function set_abs_path()
            {
            $this->abs_path = realpath(get_included_files()[0]);
            return true;
            }

        /**
         * Get abs path of current script file.
         *
         * @return string
         */
        final public function get_abs_path()
            {
            if(empty($this->abs_path))
                {
                $this->abs_path = $this->set_abs_path();
                return $this->abs_path;
                }
            else return $this->abs_path;
            }

        /**
         * Get name of current script.
         *
         * @return string
         */
        final public function get_script_name()
            {
            return basename(get_included_files()[0]);
            }

        /**
         * @param string|bool $logfile
         * @return bool
         */
        final private function set_logfile($logfile=true)
			{
            if(false === (bool) $logfile) $this->logfile = false;
            elseif(!is_string($logfile) AND true === (bool) $logfile)
                {
                if(false === ($ext = pathinfo($this->get_script_name(), PATHINFO_EXTENSION)))
                    {
                    $this->set_init_error(new E_INIT('allgemeiner Fehler beim Ermitteln der Dateiendung der Fehlerrdatei'));
                    return false;
                    }
                else
                    {
                    $this->logfile = basename($this->get_script_name(), ($ext ? '.' . $ext : '')) . '.' . SECUREPHP_LOGEXT;;
                    return true;
                    }
                }
 			elseif(!is_string($logfile))
				{
				$this->set_init_error(new E_INIT("der Parameter #Referenz# mit Wert $logfile als Basis der Fehlerdatei ist ungültig"));
				return false;
				}
			else
				{
				$this->logfile = $logfile;
				return true;
				}
			}

        /**
         * @return string
         */
        final public function get_logfile()
			{
			return $this->logfile;
			}

        /**
         * @return bool
         */
        final private function set_error_log()
			{
            if(false === $this->get_logfile()) return true;
			elseif(false === ini_set('error_log', $this->get_logfile()))
				{
				$this->set_init_error(new E_INIT("konnte die Log-Datei {$this->get_logfile()} nicht festlegen"));
				return false;
				}
			else return true;
			}

        /**
         * @return string
         */
        final public function get_error_log()
			{
            return ini_get('error_log');
			}

        /**
         * @param string $interfaces
         * @return bool
         */
        final private function set_interfaces($interfaces)
			{
			if(NULL == $interfaces) return true;
			elseif(!is_string($interfaces)) 
				{
				$this->set_init_error(new E_INIT('Der für "interfaces" übergebene Wert ist keine gültige Liste von erlaubten Interfaces'));
				return false;
				}
			else
				{
				$this->permit = array_map(
					function($interface) 
						{
						return trim($interface);
						}
					, explode(',', $interfaces)
					);
				return true;
				}
			}

        /**
         * @return array
         */
        final private function get_interfaces()
            {
            return $this->permit;
            }


		} // final class BOOTSTRAP


    /**
     * Class PROTECT
     * @package SECUREPHP
     */
		
	final class PROTECT extends SINGLETON
	
		{
	
		// PROTECT HEAD

        /**
         *
         * @var int
         */
        private $mode                           = SECUREPHP_HANDLE_STRICT;

        /**
         * @var int
         */
        private $handle                          = SECUREPHP_HANDLE_MUTE;

        /**
         * @var string
         */
        private $application_name 	            = "";

        /**
         * @var bool
         */
		private $flag_is_protected 				= false;

        /**
         * @var bool
         */
        private $flag_end_of_script             = false;

        /**
         * Vermeidet Recursion durch Folgefehler
         * in @see raise().
         *
         * Throws Exception E_RECURSION in case.
         *
         * @var bool
         */
        public $flag_in_progress                = false;


        /**
         * Letzter Laufzeitfehler.
         * @var \Exception
         */
		private $error;

        /**
         * Letzter Initialiserungsfehler.
         * @var \Exception
         */
        private $init_error;

        /**
         * Aktuelle Instanz.
         *
         * @var PROTECT
         */
		private static $instance                = NULL;
		
		// PROTECT MAGIC METHODS
		
		/**
        * Private construct method to prevent cloning of the instance of the
        * Singleton instance.
		*/
		final protected function __construct()
			{
            $this->application_name = $_SERVER['SCRIPT_FILENAME'];
			}

			
		// PROTECT METHODS
			
		/**
		* Returns the *Singleton* instance of this class.
		*
		* @return static
		*/
		final public static function getInstance()
            {
            if (NULL === static::$instance)
                {
                static::$instance = new static();
                }
            return static::$instance;
            }


        /**
         * @return bool
         */
        final public function protect()
			{

            if ($this->is_protected())
                {
                return true;
                } // Register first ..
            elseif (false == $this->add_handler())
                {
                if (false == $this->remove_handler())
                    {
                    $message = SECUREPHP . "konnte Fehlerbehandlungsroutinen nach Fehler nicht wieder freigeben";
                    $this->set_init_error(new \E_INIT($message));
                    return false;
                    }
                else
                    {
                    $message = SECUREPHP . "die Fehlerbehandlungsroutinen wurden fehlerhaft initialisiert";
                    $this->set_init_error(new \E_INIT($message));
                    return false;
                    }
                } // Then check ..
            else
                {
                $this->is_protected(true);
                return true;
                }
			}

        /**
         * @return bool|string
         */
        final public function display()
            {
            if(BOOTSTRAP::getInstance()->debug()) return true;
            elseif(SECUREPHP_HANDLE_PROMPT == $this->handle) return true;
            else return false;
            }

        /**
         * @return bool
         */
        final public function add_handler()
			{

            // Autoload for mountign MYSQL/FTP
            spl_autoload_register(__NAMESPACE__ . '\PROTECT::autoload_handler');

            // Gibt eine Zeichenkette, die die zuvor definierte Fehlerbehandlungsroutine
            // enthält (falls eine definiert wurde). Wenn der eingebaute Errorhandler verwendet
            // wurde, wird NULL zurückgegeben. NULL wird ebenfalls zurückgegeben, falls ein Fehler
            // wie z.B. ein ungültiger Callback aufgetreten ist. Wenn der vorgenannte Errorhandler
            // eine Klassenmethode war, gibt die Funktion ein indiziertes Array mit dem Klassen- und
            // dem Methodennamen zurück.
            set_error_handler(array($this, 'error_handler'));

            // Gibt den Namen des zuvor definierten Exceptionhandlers zurück oder
            // NULL bei Fehlern oder wenn kein vorheriger Exceptionhandler installiert war.
            set_exception_handler (array($this, 'exception_handler'));

            // Wirft im Fehlerfall eine E_WARNING. Sollte an dieser Stelle durch
            // set_error_handler korrekt abgefangen werden.
            // Mehrere Shutdown-Hanlder sind möglich.
            register_shutdown_function(array($this, 'shutdown_handler'));

            // AUTOLOAD HANDLER

            return true;
			}

        /**
         * @return bool
         */
        final public function remove_handler()
			{

            // Disable shutdown handler
            PROTECT::getInstance()->handle(SECUREPHP_HANDLE_OFF);

            // Disable error_handler
            /** As the PHP docs specify, passing null to set_error_handler was added in PHP 5.5 - source.
            As Nicolas's comment from that same page mentions, a workaround is to pass any function as the first argument
            to set_error_handler, and pass 0 as the second argument. As an example:
             */
            #set_error_handler(NULL);
            restore_error_handler();

            // Disable exception handler
            restore_exception_handler();

            return true;
			}

        /**
         * @param null $state
         * @return bool|int
         */
        final public function handle($handle = NULL)
            {

            if(NULL === $handle) return $this->handle;
            elseif(!is_int($handle)) return false;
            else
                {
                $this->handle = $handle;
                return true;
                }
            }

        /**
         * @param null $state
         * @return bool|int
         */
        final public function mode($mode = NULL)
            {
            if(NULL === $mode) return $this->mode;
            elseif(!is_int($mode)) return false;
            else
                {
                $this->mode = $mode;
                return true;
                }
            }

        /**
         * Catch uncaught exceptions.
         *
         * Der Exception Handler wird vor dem shutdown_handler aufgerufen!
         *
         * Entstehen hier weitere Exceptions werden diese im Trace zusammengefasst.
         * Der exception_handler wird nur EINMAL aufgerufen.
         *
         * Fatal Errors die hier entstehen sind im shutdown-handler verfügbar!
         *
         * @param \Exception $e
         * @return void
         * @throws \Exception
         */
        final public function exception_handler(\Exception $e)
			{

            #echo "exceptionhandler";

            if(is_a($e, '\SECUREPHP\E_RAISEABLE') OR is_a($e, '\SECUREPHP\E_RAISABLE_ERROR'))
                {
                $e->raise();
                exit(0);
                }
            else
                {
                $e = new \UncaughtException('eine nicht behandelte Exception liegt vor', false, $e);
                $e->raise();
                exit(0);
                }

            }

        /**
         * @param int $error_level
         * @param string $error_message
         * @param string $error_file
         * @param int $error_line
         * @param mixed $error_context
         * @return bool|NULL
         * @throws E_RECURSION
         */
        final public function error_handler($error_level, $error_message, $error_file, $error_line, $error_context)
			{

            #echo "error_handler";

			// Detailed info: http://php.net/manual/en/errorfunc.constants.php
				
			// Die folgenden Fehlertypen können nicht von einer benutzerdefinierten Funktion behandelt werden: 
			// E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING 
			// und die meisten E_STRICT, die in der Datei ausgelöst werden, in der set_error_handler() aufgerufen wird. 
			
			// ACHTUNG !!!!!!
			// E_WARNING: trigger_error() expects parameter 1 to be string, object given in C:\Bi...
			// Das Skript stoppt nicht!! bei einem Fehler dieses Ausmaßes ..
		
			// Alternatively, you can just set it to use all errors, and just ignore it if they're not in error_reporting 
			// (which you set to the same value as the above line, also, the @ operator works then):
			//if(!(error_reporting() & $errno)) return;
			
			// die() doesn't prevent destructors from being run, so the script doesn't exit immediately, 
			// it still goes through cleanup routines.
			
			// Beware that when using PHP on the command line, 
			// die("Error") simply prints "Error" to stdout and terminates the program with a normal exit code of 0.
			// If you are looking to follow UNIX conventions for CLI programs, you may consider the following:
			// fwrite(STDERR, "An error occurred.\n");
			// exit(1); // A response code other than 0 is a failure
		
			
			// NOTES!!
			// Don't use the  exit() function in the auto prepend file with fastcgi (linux/bsd os).
			// It has the effect of leaving opened files with for result at least a nice  "Too many open files  ..." error.
			// As I'm sure you're aware, exit() kills the whole  process
			// Possible solutions:
			// exit(0);
			// header('location: /'); --> Script wird bis zum Ende ausgeführt -> erst dann Umleitung!!
			// fastcgi_finish_request()
			// class SystemExit extends Exception {}; if (SOME_EXIT_CONDITION) throw new SystemExit(); // instead of exit()
			
			// INFOS:
			// Calling to exit() will flush all buffers started by ob_start() to default output.

            // Back to PHP default handling
            // when reporting turned off.
            if(SECUREPHP_HANDLE_OFF == PROTECT::getInstance()->handle())
                {
                return false;
                }
            // error handling.
            else
                {

                // Error handling (strict or loose)

                $error = new \PhpError($error_message, NULL, $error_level, $error_file, $error_line);
                $error->set_note($this->get_php_error($error_level)[0]);
                $error->set_state(CONFIG::getInstance()->_('script run') . (!error_reporting() ? CONFIG::getInstance()->_('continued') . '(@)' : (SECUREPHP_HANDLE_STRICT == PROTECT::getInstance()->mode() ? CONFIG::getInstance()->_('terminates') . ' (Strict-Mode).' : CONFIG::getInstance()->_('continues') . ' (Loose-Mode)')));

                // Supressed by @
                if ((error_reporting() & $error_level)) switch ($error_level)
                    {
                    case E_ERROR:
                    case E_CORE_ERROR:
                    case E_COMPILE_ERROR:
                    case E_PARSE:
                        // @TODO entfernen
                        $error->send_to('admin>user,log');
                        $error->raise();
                        break;
                    case E_USER_ERROR:
                        $error->send_to('admin>user,log');
                        $error->raise();
                        break;
                    case E_RECOVERABLE_ERROR:
                        // Z.B. Typehints: Object of class Closure could not be converted to string
                        $error->send_to('admin>user,log');
                        $error->raise();
                        break;
                    case E_WARNING:
                        // TODO: mittels @ abfangen, nicht hier
                        if (
                            preg_match('/ftp_get().*File not found/', $error_message)
                            || preg_match('/ftp_rename().*Permission denied/', $error_message)
                            || preg_match('/simplexml_load_file().*/', $error_message)
                        )
                            {
                            $error->set_state('Das Skript wurde durch eine Sonderbehandlung nicht abgebrochen!');
                            $error->send_to('log');
                            $error->raise();
                            return true;
                            break;
                            }
                        else
                            {
                            $error->send_to('admin>user,log');
                            $error->raise();
                            }
                        break;
                    case E_CORE_WARNING:
                    case E_COMPILE_WARNING:
                        $error->send_to('admin>user,log');
                        $error->raise();
                        break;
                    case E_USER_WARNING:
                        $error->send_to('user>admin,log');
                        $error->raise();
                        break;
                    case E_USER_NOTICE:
                        $error->send_to('user>admin,log');
                        $error->raise();
                        break;
                    case E_NOTICE:
                        $error->send_to('admin>user,log');
                        $error->raise();
                        break;
                    case E_DEPRECATED:
                        $error->send_to('admin,log');
                        $error->raise();
                        break;
                    case E_STRICT:
                        $error->send_to('admin,log');
                        $error->raise();
                        break;
                    default:
                        $error->send_to('admin>user,log');
                        $error->raise();
                        break;
                    }

                // @ here, supress it, go back to script
                if (!(error_reporting()))
                    {
                    return true;
                    }
                // else handle exit in strict mode
                elseif(SECUREPHP_HANDLE_STRICT == PROTECT::getInstance()->mode())
                    {
                    if (SECUREPHP_EXIT_ON_ERROR == $this->get_php_error($error_level)[1]) exit(0);
                    }
                // else handle exit in loose mode
                elseif(SECUREPHP_HANDLE_LOOSE == PROTECT::getInstance()->mode())
                    {
                    if (SECUREPHP_EXIT_ON_ERROR == $this->get_php_error($error_level)[2]) exit(0);
                    }
                }
			}

        /**
         * Shutdown-Handler.
         * @todo Überprüfen inwiefern secure.php mit weiteren Shutdown-Handlern in die Quere kommt.
         *
         * @todo testen ob error_handler -> shutdown_handler -> E_NOTICE wieder zu error_handler zurückkehrt.
         *
         * Dieser Shutdown-Handler wird immer ausgeführt, auch
         * wenn exit() in error_handler() aufgerufen wird.
         *
         * Fehler wie E_WARNINGS die hier enstehen werden an error_handler() zurückgegeben wenn error_handler() selbst nicht durch das Skript im Vorfeld aktiv war.
         *
         * Fatal-Errors die hier enstehen sind nicht manuell abfangbar.
         *
         * The error_get_last() function will give you the most recent error even when that error is a Fatal error.
         *
         * If an error handler (see set_error_handler ) successfully handles an error then that error will not be reported by this function.
         *
         * error_get_last() is an array with all the information regarding the fatal error that you should need to debug, though it will not have a backtrace, as has been mentioned.
         *
         * You may get the idea to call debug_backtrace or debug_print_backtrace from inside a shutdown function, to trace where a fatal error occurred. Unfortunately, these functions will not work inside a shutdown function.
         *
         * Wird immer vor __desctruct() aufgerufen.
         *
         *
         *
         * @return void
         * @throws E_RECURSION
         */
        final public function shutdown_handler()
			{

            #echo "shutdownhandler";

            // Unregister shutdown handler
            // @todo check https://stackoverflow.com/questions/2726524/can-you-unregister-a-shutdown-function
            if(SECUREPHP_HANDLE_OFF == $this->handle()) return;


            // error_get_last enthält nun Fehler die nicht
            // durch error_handler() behandelt wurden !!
            $lasterror = error_get_last();

            if(BOOTSTRAP::getInstance()->debug())
                {
                echo "LastError (dump):";
                var_dump($lasterror);
                }

            #header("HTTP/1.0 500 Service not available");
            #include_once('500.php');


            // Shutdown-Funktion ausführen.
            if (false === $this->is_eof() && is_object(CONFIG::getInstance()->shutdown_function))
                {
                $shutdown_function = CONFIG::getInstance()->shutdown_function->bindto($this);
                $shutdown_function();
                }

            // Recursion detection.
            /** if($this->in_progress())
                {
                // Diese Exception wird direkt abgefangen.
                // Recursion von exception_handler() durch Aufruf ist nicht gegeben.
                $e = new E_FATAL('SHUTDOWNHANDLER: raise not finished. Das Skript wird beendet.');
                $this->terminate($e);
                }
            // Deactivated.
            else  */

            // Arbeitsverzeichnis wiederherstellen
            chdir(BOOTSTRAP::getInstance()->get_wd());

            if (NULL !== $lasterror) switch ($lasterror['type'])
                {
                case E_ERROR:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                case E_RECOVERABLE_ERROR:
                case E_CORE_WARNING:
                case E_COMPILE_WARNING:
                case E_PARSE:
                case E_STRICT;
                    $error = new \ShutdownError($lasterror['message'], 0, $lasterror['type'], $lasterror['file'], $lasterror['line']);
                    $error->set_note($this->get_php_error($lasterror['type'])[0]);
                    $error->set_state(CONFIG::getInstance()->_('script aborted before'));
                    $error->raise();
                    break;
                default:
                    $error = new \ShutdownError($lasterror['message'], 0, $lasterror['type'], $lasterror['file'], $lasterror['line']);
                    $error->set_note($this->get_php_error($lasterror['type'])[0]);
                    $error->set_state(CONFIG::getInstance()->_('script aborted before'));
                    $error->raise();
                    break;
                }

            // EOF error
            if (false === $this->is_eof())
                {
                $error = new \EofError(CONFIG::getInstance()->_('eof error'));
                $error->send_to('admin>user,log');
                $error->raise();
                }
            }



        /**
         * Autoload von safemysqli & safeftp.
         *
         * @param $class_name
         * @return mixed
         */
        protected function autoload_handler($class)
            {
            $file_include = 'secure.class.' . (str_replace(__NAMESPACE__ . '\\', '', $class)) . '.php';
            if(file_exists(BOOTSTRAP::getInstance()->get_local_dir() . $file_include))
                {
                include BOOTSTRAP::getInstance()->get_local_dir() . $file_include;
                }
            }

        /**
         * @var bool|null $flag_protected
         * @return bool
         */
        final public function is_protected($flag_protected = NULL)
            {
            if(NULL === $flag_protected) return $this->flag_is_protected;
            else
                {
                $this->flag_is_protected = (bool) $flag_protected;
                return true;
                }
            }

        /**
         * @param null|bool $flag
         * @return bool
         */
        final public function in_progress($flag = NULL)
            {
            if(NULL === $flag) return $this->flag_in_progress;
            else
                {
                $this->flag_in_progress = $flag;
                return true;
                }
            }


        /**
         * @param string $users
         * @param \Exception $e
         * @param int $timeout
         * @return void|null
         */
        final public function notify($users, \Exception $e, $timeout=NULL)
            {

            // Clear whitespaces
            $users = trim($users);

            // Get receipient list
            $_users = explode(',', $users);

            // Clear whitespace characters on receipient list
            $_users = array_map (
                function($user)
                    {
                    return trim($user);
                    }, $_users);

            // Don't send emails in debug mode
            if(SECUREPHP_HANDLE_DEBUG == PROTECT::getInstance()->handle()) $send = false;
            // don't send emails when mail not needed
            elseif(false == MAIL::getInstance()->is_required()) $send = false;
            // don't send emails when mail not configured
            elseif(false == MAIL::getInstance()->is_ready()) $send = false;
            // send emails a) when forced b) when timeout check succeded
            elseif(true === ($return = TIMEOUT::getInstance()->check($e, $timeout)))
                {
                $send = true;
                }
            // Wiederholungsfehler melden
            elseif(is_a($return, 'Exception'))
                {
                $e = $return;
                $send = true;
                }
            else $send = false;

            if(in_array('log', $_users))
                {
                ERRORLOG::getInstance()->log($e);
                }

            foreach($_users AS $user)
                {

                $user = trim($user);

                if('all' == $user && 'all' == $users)
                    {
                    #ERRORLOG::getInstance()->log($e);
                    if(!$send) return NULL;
                    else
                        {
                        $this->notify_user($e);
                        $this->notify_admin($e);
                        foreach (MAIl::getInstance()->userlist AS $name => $email)
                            {
                            $this->notify_cc($name, $e);
                            }
                        }
                    break;
                    }

                elseif(strpos($user, '>'))
                    {
                    $order = explode('>', $user);

                    foreach($order AS $user)
                        {
                        if("log" == $user AND true == ERRORLOG::getInstance()->log($e))
                            {
                            break;
                            }
                        elseif($send AND "user" == $user AND true == $this->notify_user($e))
                            {
                            break;
                            }
                        elseif($send AND "admin" == $user AND true == $this->notify_admin($e))
                            {
                            break;
                            }
                        elseif($send AND true == $this->notify_cc($user, $e))
                            {
                            break;
                            }
                        }
                    }
                else
                    {
                    if($send AND "admin" == $user)
                        {
                        $this->notify_admin($e);
                        }
                    elseif($send AND "user" == $user)
                        {
                        $this->notify_user($e);
                        }
                    elseif($send AND array_key_exists($user, MAIL::getInstance()->userlist))
                        {
                        $this->notify_cc($user, $e);
                        }
                    }
                }
            }

        /**
         * @param \Exception $e
         * @return bool|NULL
         */
        final private function notify_user(\Exception $e)
            {
            return MAIL::getInstance()->raise('user', $e);
            }

        /**
         * @param \Exception $e
         * @return bool|NULL
         */
        final private function notify_admin(\Exception $e)
            {
            return MAIL::getInstance()->raise('admin', $e);
            }

        /**
         * @param string $user
         * @param \Exception $e
         * @return bool|NULL
         */
        final private function notify_cc($user, \Exception $e)
            {
            return MAIL::getInstance()->raise($user, $e);
            }

        // PROTECT GETTER & SETTER

        /**
         * @param \Exception $error
         * @return bool
         */
        final private function set_init_error(\Exception $error)
            {
            $this->init_error = $error;
            return true;
            }

        /**
         * @return string
         */
        final public function get_init_error()
            {
            return $this->init_error;
            }

        /**
         * @param string $text
         * @return bool
         */
       final private function set_error($text)
            {
            $trace = debug_backtrace(NULL, 3);
            $prev = $trace[1];
            $before = "";
            if(isset($trace[2]))
                {
                $before = $trace[2];
                $before = sprintf(", aufgerufen durch: %s #%s (%s::%s())", __FILE__, $prev['line'], __CLASS__, $before['function']);
                }
            $string = SECUREPHP . " Startfehler in %s #%s (%s::%s()) mit der Nachricht: " . $text . $before;
            $this->error = sprintf($string, __FILE__, $prev['line'], __CLASS__, $prev['function']);
            return true;
            }

        /**
         * @return string
         */
        final public function get_error()
            {
            return $this->error;
            }

        /**
         * @param int $error
         * @return array
         */
        final public function get_php_error($error)
            {
            $ERRORS = ARRAY(
                E_ERROR             => ARRAY('E_ERROR', SECUREPHP_EXIT_ON_ERROR, SECUREPHP_EXIT_ON_ERROR),
                E_WARNING           => ARRAY('E_WARNING', SECUREPHP_EXIT_ON_ERROR, false),
                E_PARSE             => ARRAY('E_PARSE', SECUREPHP_EXIT_ON_ERROR, SECUREPHP_EXIT_ON_ERROR),
                E_NOTICE            => ARRAY('E_NOTICE', SECUREPHP_EXIT_ON_ERROR, false), // E_NOTICE: Undefined variable $result in ..
                E_CORE_ERROR        => ARRAY('E_CORE_ERROR', SECUREPHP_EXIT_ON_ERROR, SECUREPHP_EXIT_ON_ERROR),
                E_CORE_WARNING      => ARRAY('E_CORE_WARNING', SECUREPHP_EXIT_ON_ERROR, false),
                E_COMPILE_ERROR     => ARRAY('E_COMPILE_ERROR', SECUREPHP_EXIT_ON_ERROR, SECUREPHP_EXIT_ON_ERROR),
                E_COMPILE_WARNING   => ARRAY('E_COMPILE_WARNING', SECUREPHP_EXIT_ON_ERROR, SECUREPHP_EXIT_ON_ERROR),
                E_USER_ERROR        => ARRAY('E_USER_ERROR', SECUREPHP_EXIT_ON_ERROR, SECUREPHP_EXIT_ON_ERROR), // // will terminate script per default
                E_USER_WARNING      => ARRAY('E_USER_WARNING', false, false),
                E_USER_NOTICE       => ARRAY('E_USER_NOTICE', false, false),
                E_STRICT            => ARRAY('E_STRICT', false, false),
                E_RECOVERABLE_ERROR => ARRAY('E_RECOVERABLE_ERROR', SECUREPHP_EXIT_ON_ERROR, false),
                E_DEPRECATED        => ARRAY('E_DEPRECATED', false, false),
                E_USER_DEPRECATED   => ARRAY('E_USER_DEPRECATED', false, false),
            );
            if(!array_key_exists($error, $ERRORS)) return ARRAY('UNKNOWN PHP ERROR ('.$error.') - please debug secure.php!', SECUREPHP_EXIT_ON_ERROR, SECUREPHP_EXIT_ON_ERROR);
            else return $ERRORS[$error];
            }

        /**
         * @param string $name
         * @return bool
         */
        final public function set_app($name)
            {
            $this->application_name = $name;
            return true;
            }

        /**
         * @return string
         */
        final public function get_app()
            {
            return $this->application_name;
            }


        /**
         * @return bool
         */
        final public function is_eof()
            {
            return $this->flag_end_of_script;
            }

        /**
         * @return bool
         */
        final public function set_eof()
            {
            $this->flag_end_of_script = true;
            return true;
            }


		} // final class PROTECT

    /**
     * Class CONFIG
     * @package SECUREPHP
     */
    final class CONFIG extends SINGLETON
        {

        // CONFIG HEAD

        /**
         * @var \Exception|false $config_error
         */
        public $config_error = false;

        /**
         * @var string
         */
        protected $locale = "en_EN";
        /**
         * @var int
         */
        public $error_handling = SECUREPHP_HANDLE_STRICT;

        /**
         * @var bool
         */
        private $flag_merge = true;

        /**
         * @var bool
         */
        private $flag_to_stderr = true;

        /**
         * @var \CLOSURE
         */
        public $shutdown_function               = false;

        /**
         * @var static
         */
        private static $instance;

        // CONFIG MAGIC METHODS

        // CONFIG METHODS

        /**
         * Returns the *Singleton* instance of this class.
         *
         * @return static
         */
        final public static function getInstance()
            {
            if (NULL === static::$instance)
                {
                static::$instance = new static();
                }
            return static::$instance;
            }

        /**
         * @param \Exception $e
         * @throws \Exception
         */
        final public function terminate(\Exception $e)
            {
            ERRORLOG::getInstance()->log($e, false);
            throw $e;
            }

        /**
         * Info:
         * Wenn ein Log-File konfiguriert ist wird STDERR nicht mit Nachrichten versorgt.
         * to_stderr() ermöglicht dann ein zusätzlichen Schreiben nach STDERR.
         * Wenn ini_set('display_errors', 'off') werden CLI-Fehler angezeigt,
         * außer man leitet um z.B. mit 2> error.log
         * weiterer Link: https://stackoverflow.com/questions/10771959/error-reporting-behavior-in-cli-binary
         *
         * @param boolean $flag
         * @return bool
         **/
        final public function to_stderr($flag=NULL)
            {

            // Wenn $flag = NULL wird die aktuelle Einstellung beibehalten.
            // Wenn $flag = "auto" wird geprüft ob es Sinn macht dass zusätzlich geloggt wird.
            // Wenn $flag = bool wird das Flag entsprechend auf 0 bzw. 1 gesetzt.

            if(NULL === $flag)
                {
                // Kein CLI dann auch kein Stderr.
                if(false == BOOTSTRAP::getInstance()->is_cli()) return false;
                // Debug-Modus behandeln.
                elseif(BOOTSTRAP::getInstance()->debug())
                    {

                    // IM Debug-Modus ist display_errors aktiviert.
                    // Sprich Fehler und Berichte werden auf der Konsole ausgegeben.
                    // Zusätzlich nach STDERR loggen, wenn gewünscht, führt, wenn STDERR nicht umgeleitet wird,
                    // zur doppelten Ausgabe.

                    // Wir deaktiveren to_stderr nicht, da to_stderr nicht ohne Grund aktiviert wurde.
                    #return false;

                    // Im Debug-Modus ist log_errors = true.
                    // Wenn zusätzlich ein Logfile angegeben ist, werden Fehler weg von STDERR ins Logfile geschrieben.
                    // Wir können dann zusätzlich nach STDERR loggen wie gewünscht ..
                    if(BOOTSTRAP::getInstance()->get_logfile())
                        {
                        return $this->flag_to_stderr;
                        }
                    // Wenn kein Logfile angegeben ist werden Fehler im Debug-Modus automatisch nach STDERR geschrieben.
                    // STDERR würde dann doppelt versorgt werden.
                    else
                        {
                        return false;
                        }
                    }
                // Standardbetrieb behandeln
                // Error-Log ist false, display_errors = false, Logfile = manuell ja oder nein.
                // STDERR würde nicht mit Nachrichten versorgt werden.
                // STDERR kann dann wie gewünscht versorgt werden.
                else
                    {
                    return $this->flag_to_stderr;
                    }
                }
            elseif(false == BOOTSTRAP::getInstance()->is_cli())
                {
                $this->flag_to_stderr = false;
                return true;
                }
            else
                {
                $this->flag_to_stderr = (bool) $flag;
                return true;
                }
            }


        /**
         * @param string $token
         * @param null $language
         * @return string
         */
        final public function _($token, $language = 'en_EN')
            {
            $language = $this->locale;
            $result = DB::getInstance()->server->query("SELECT * FROM translations WHERE token = '$token'");
            $row = $result->fetchArray();
            if(empty($row) || empty($row[$language]))
                {
                return $token;
                }
            else
                {
                return $row[$language];
                }
            }

        /**
         * @param string $name
         * @return bool
         */
        final public function app($name)
            {
            if(false == (PROTECT::getInstance()->set_app($name)))
                {
                $this->config_error = PROTECT::getInstance()->get_error();
                $this->terminate($this->config_error);
                }
            else return true;
            }

        /**
         * @param int $timeout
         * @return bool
         */
        final public function timeout($timeout)
            {
            if(false == (TIMEOUT::getInstance()->set_timeout($timeout)))
                {
                $this->config_error = TIMEOUT::getInstance()->get_error();
                $this->terminate($this->config_error);
                }
            else return true;
            }

        /**
         * @param int $timeout
         * @return bool
         * @throws \Exception
         */
        final public function reminder($timeout)
            {
            if(false == (TIMEOUT::getInstance()->set_reminder($timeout)))
                {
                $this->config_error = TIMEOUT::getInstance()->get_error();
                $this->terminate($this->config_error);
                }
            else return true;
            }

        /**
         * @param string $from
         * @return bool
         */
        final public function from($from)
            {
            if(false == MAIL::getInstance()->set_from($from))
                {
                $this->config_error = MAIL::getInstance()->get_error();
                $this->terminate($this->config_error);
                }
            else return true;
            }

        /**
         * @param string $admin
         * @return bool
         */
        final public function admin($admin)
            {
            if(false == MAIL::getInstance()->is_ready() AND false == BOOTSTRAP::getInstance()->startmail())
                {
                $this->config_error = new E_INIT('konnte Mail-Admin nicht setzen. Mail-Init fehlerhaft.', false, MAIL::getInstance()->get_init_error());
                $this->terminate($this->config_error);
                }
            elseif(false == (MAIL::getInstance()->set_admin($admin)))
                {
                $this->config_error = MAIL::getInstance()->get_error();
                $this->terminate($this->config_error);
                }
            else return true;
            }

        /**
         * @param string $user
         * @return bool
         */
        final public function user($user)
            {
            if(false == MAIL::getInstance()->is_ready() AND false == BOOTSTRAP::getInstance()->startmail())
                {
                $this->config_error = new E_INIT('konnte Mail-Benutzer nicht setzen. Mail-Init fehlerhaft.', false, MAIL::getInstance()->get_init_error());
                $this->terminate($this->config_error);
                }
            elseif(false == (MAIL::getInstance()->set_user($user)))
                {
                $this->config_error = MAIL::getInstance()->get_error();
                $this->terminate($this->config_error);
                }
            else return true;
            }

        /**
         * @param string $user
         * @param string $email
         * @return bool
         */
        final public function add_cc($user, $email)
            {
            if(false == MAIL::getInstance()->is_ready() AND false == BOOTSTRAP::getInstance()->startmail())
                {
                $this->config_error = new E_INIT('konnte Mail-Benutzer nicht setzen. Mail-Init fehlerhaft.', false, MAIL::getInstance()->get_init_error());
                $this->terminate($this->config_error);
                }
            elseif(false == MAIL::getInstance()->add_cc($user, $email))
                {
                $this->config_error = MAIL::getInstance()->get_error();
                $this->terminate($this->config_error);
                }
            else return true;
            }


        /**
         * @param null $bool
         * @return bool
         */
        final public function merge($flag=NULL)
            {
            if(NULL === $flag)
                {
                return $this->flag_merge;
                }
            else
                {
                $this->flag_merge = (bool) $flag;
                return true;
                }
            }

        /**
         * @param null $env
         * @return bool|void
         */
        final public function locale($env = NULL)
            {
            if(NULL === $env)
                {
                return $this->env;
                }
            elseif('t' == strtolower($env))
                {
                BOOTSTRAP::$date = '\J\a\n\u\a\r\y 1, 1970';
                return true;
                }
            elseif('de' == substr(strtolower($env), 0, 2))
                {
                $this->locale = "de_DE";
                return true;
                }
            else return;
            }

        /**
         * @return bool
         */
        final public function has_errors()
            {
            if($this->config_error) return true;
            else return false;
            }

        // CONFIG GETTERS & SETTERS

        /**
         * @return array
         */
        final public function get_error()
            {
            return $this->config_error;
            }

        } // <-- final class CONFIG

    /**
     * Class ERRORLOG
     * @package SECUREPHP
     */
	final class ERRORLOG extends SINGLETON
	
		{
			
		// ERRORLOG HEAD

        /**
         * @var int;
         */
        private $starttime = 0;

        /**
         * @var static
         */
        private static $instance;
		
		// ERRORLOG MAGIC METHODS
		
		// ERRORLOG METHODS

        /**
         * @return static
         */
        public static function getInstance()
			{
			if (null === static::$instance)
                {
				static::$instance = new static();
			    }
			return static::$instance;
			}

        /**
         * @param \Exception $e
         * @return bool|null
         * @throws E_INIT
         */
        public function log(\Exception $e, $flag_prompt=true)
            {

            // Exception in Fehlernachricht umwandeln
            $message = $this->get_message($e);

            // Im Standardmodus nur nach Log schreiben.
            // Keine Ausgabe .. auch nicht in CLI.
            if(SECUREPHP_HANDLE_MUTE == PROTECT::getInstance()->handle())
                {

                // Nachricht in Error-Log schreiben
                // sofern aktiviert
                if(BOOTSTRAP::getInstance()->get_logfile())
                    {
                    error_log(SECUREPHP_NEW_LINE . $this->html2txt($message));
                    }
                }
            // Wenn nicht in Standardmodus
            // dann Ausgabe prüfen
            else
                {

                // Nachricht in Error-Log schreiben
                // sofern aktiviert
                if(BOOTSTRAP::getInstance()->get_logfile())
                    {
                    error_log(SECUREPHP_NEW_LINE . $this->html2txt($message));
                    }

                // zusätzlicher Log nach STDERR
                // wenn gewünscht.
                if (CONFIG::getInstance()->to_stderr() && defined('STDERR'))
                    {
                    fwrite(STDERR, SECUREPHP_NEW_LINE . $this->html2txt($message));
                    }

                // Display errors when in PROMPT mode
                if(PROTECT::getInstance()->display() OR $flag_prompt)
                    {
                    $head = '';
                    #$head .= '-> todisplay';
                    if(!$this->starttime)
                        {

                        #$head .= '<hr />' . SECUREPHP_LINE_BREAK;
                        #$head .= '<h1> FEHLERBERICHT </h1>' . SECUREPHP_LINE_BREAK;
                        #$head .=  md5(BOOTSTRAP::$starttime) . SECUREPHP_LINE_BREAK;
                        #$head .= '<hr />' . SECUREPHP_LINE_BREAK;
                        #$head .= SECUREPHP_LINE_BREAK;
                        }
                    else
                        {
                        $head = '';
                        #$head .= '-> todisplay';
                        #$head .= '#' . SECUREPHP_LINE_BREAK;
                        }

                    if(BOOTSTRAP::getInstance()->is_cli()) print $this->html2txt($head . $message);
                    else print $head . $message;
                    }
                $this->starttime = BOOTSTRAP::$starttime;
                }
            }

        // ERRORLOG GETTERS & SETTERS

        /**
         * @param \Exception $e
         * @return string
         */
        private function get_message(\Exception $e)
			{
            $message = '';
            $message .= '<pre style="background-color:black; color:white; border:1px solid black; border-radius:10px;">';
            $message .= '/**' . SECUREPHP_LINE_BREAK;
			$message .= '* ' . SECUREPHP . SECUREPHP_LINE_BREAK;
            $message .= '*'. SECUREPHP_LINE_BREAK;
            $message .= '* ' . get_class($e) . SECUREPHP_LINE_BREAK;
            $message .= '* ' . date(BOOTSTRAP::$date) . SECUREPHP_LINE_BREAK;
            $message .= '*' . SECUREPHP_NEW_LINE;
            $message .= '* ' . CONFIG::getInstance()->_('concerned') . ': ' . PROTECT::getInstance()->get_app() . SECUREPHP_LINE_BREAK;
            $message .= '*' . SECUREPHP_NEW_LINE;
            $message .= $e->__toString();
            $message .= '*' . SECUREPHP_LINE_BREAK . '*/';
            $message .= '</pre>';
            $message .= SECUREPHP_LINE_BREAK;
            return $message;
			}

        /**
         * @param $string
         * @return string
         */
        private function html2txt($string)
            {
            $string = preg_replace('/\<hr(\s*)?\/?\>/i', '---------------------------', $string);
            #$string = preg_replace('/\<br(\s*)?\/?\>/i', SECUREPHP_NEW_LINE, $string);
            $string = preg_replace('/#/i', "*", $string);
            return strip_tags($string);
            }

        } // final CLASS ERRORLOG

    /**
     * Class MAIL
     * @package SECUREPHP
     */
	final class MAIL extends SINGLETON
	
		{
			
		// MAIL HEAD

        /**
         * @var array[]
         */
        public $userlist            = ARRAY();

        /**
         * @var int
         */
		private $count              = 0;

        /**
         * @var string
         */
		private $from               = "";

        /**
         * @var string
         */
		private $admin              = "";

        /**
         * @var string
         */
		private $user               = "";

        /**
         * @var false|\Exception
         */
        private $error              = false;

        /**
         * @var false|\Exception
         */
        private $init_error         = false;

        /**
         * @var static
         */
        private static $instance    = NULL;

        /**
         * @var bool
         */
        private $flag_is_ready       = false;
		
		// MAIL MAGIC METHODS
		
		/**
		* Private construct method to prevent cloning of the instance of the
		* *Singleton* instance.
		*
		*/
		final protected function __construct()
			{
			if(!empty(ini_get('sendmail_from')))
				{
				$this->from = ini_get('sendmail_from');
				}
			}
			
		// MAIL METHODS

        /**
         * @return static
         */
        final public static function getInstance()
			{
			if (null === static::$instance) {
				static::$instance = new static();
			}
			return static::$instance;
			}

        /**
         * @return bool
         * @throws E_CONFIG|E_INIT
         */
        final public function init()
            {

            if(true == $this->is_ready())
                {
                return true;
                }
            elseif (false || in_array('mail', explode(';', ini_get('disable_functions'))))
                {
                $this->set_init_error(new E_INIT('Email-Funktion steht nicht zur Verfügung. PHP::mail() ist nicht aktiviert'));
                return false;
                }
            elseif(!$this->get_from())
                {
                $this->set_init_error(new E_CONFIG('Email nicht konfiguriert. sendmail_from fehlerhaft oder nicht angegeben'));
                return false;
                }
            elseif (false == TIMEOUT::getInstance()->init())
                {
                $this->set_init_error(new E_CONFIG('Email-Funktion steht nicht zur Verfügung. Konnte Timeout-Instanz nicht starten'), false, TIMEOUT::getInstance()->get_init_error());
                return false;
                }
            else
                {
                $this->is_ready(true);
                return true;
                }
            }

        /**
         * @return bool
         */
        final public function is_required()
			{
			if(!empty($this->user) || !empty($this->admin) || 0 < count($this->userlist)) return true;
			else return false;
			}

        /**
         * @return bool
         */
        final public function is_ready($flag_ready = NULL)
            {
            if(NULL === $flag_ready) return $this->flag_is_ready;
            else
                {
                $this->flag_is_ready = (bool) $flag_ready;
                return true;
                }
            }

        /**
         * @param string $email
         * @param bool $flag_validate_mx
         * @return bool
         */
        public function validate_email($email, $flag_validate_mx=false)
			{
            if(false == (filter_var(trim($email), FILTER_VALIDATE_EMAIL)))
                {
                $this->set_error(new E_CONFIG($email . ' besitzt ein ungültiges Format'));
                return false;
                }
			elseif ($flag_validate_mx && !checkdnsrr($email, 'MX'))
				{
                $this->set_error(new E_CONFIG($email . ' ist nicht erreichbar'));
				return false;
				}
			else return true;
			}

        /**
         * @param string $user
         * @param \Exception $e
         * @return bool;
         * @throws \Exception
         */
        final public function raise($user, \Exception $e)
            {
            if(SECUREPHP_HANDLE_MUTE == PROTECT::getInstance()->handle()) return NULL;
            elseif(false ==($data = MAIL::getInstance()->get_mail_data($e))) return false;
            elseif('admin' == $user)
                {
                if(empty($this->get_admin())) return NULL;
                else return $this->send($this->get_admin(), $data->header, $data->message);
                }
            elseif('user' == $user)
                {
                if(empty($this->get_user())) return NULL;
                else return $this->send($this->get_user(), $data->header, $data->message);
                }
            elseif(false == ($mail = $this->get_cc_by_name($user)))
                {
                return false;
                }
            else return $this->send($mail, $data->header, $data->message);

            }

        /**
         * @param string $to
         * @param string $subject
         * @param string $message
         * @param string $from
         * @return bool|NULL
         */
        public function send($to, $subject, $message, $from=NULL)
            {

            if(!empty($DEBUG)) return NULL;

            if($this->count > 3)
                {
                $message = 'max. Anzahhl von Emails versendet.';
                ERRORLOG::getInstance()->log(new E_NOTICE($message));
                return false;
                }
            else $this->count++;

            if(empty($to))
                {
                $message = 'Email-Empfänger nicht angegeben. Nachricht: ' . $message;
                ERRORLOG::getInstance()->log(new E_NOTICE($message));
                return false;
                }

            $headers  = 'From: ' . ($from?:$this->get_from()) . SECUREPHP_MAIL_EOL . 'MIME-Version: 1.0' . SECUREPHP_MAIL_EOL . 'Content-type: text/plain; charset=utf-8'. SECUREPHP_MAIL_EOL;

            if(mail($to, $subject, $message, $headers))
                {
                return true;
                }
            else
                {
                $message = 'Email-Versand fehlgeschlagen für ' . $to . '. Nachricht: ' . $message;
                ERRORLOG::getInstance()->log(new E_NOTICE($message));
                return false;
                }
            }

        // MAIL GETTERS & SETTERS

        /**
         * @param \Exception
         * @return bool
         */
        final private function set_init_error(\Exception $e)
            {
            $this->init_error = $e;
            return true;
            }

        /**
         * @return \Exception
         */
        final public function get_init_error()
            {
            return $this->init_error;
            }

        /**
         * @param \Exception
         * @return bool
         */
        final private function set_error($e)
            {
            $this->error = $e;
            return true;
            }

        /**
         * @return \Exception
         */
        final public function get_error()
            {
            return $this->error;
            }

        /**
         * @param \Exception $e
         * @return \stdClass
         */
        final public function get_mail_data(\Exception $e)
            {
            if(is_a($e, 'ErrorTicket'))
                {
                $header = $e->get_mail_header();
                $message = $e->get_mail_message();
                }
            elseif(is_a($e, 'PhpRunTimeError'))
                {
                $header = $e->get_mail_header();
                $message = $e->get_mail_message();
                }
            elseif(is_a($e, 'Exception'))
                {
                $header = SECUREPHP . ': Die Exception vom Typ '.get_class($e).' aufgetreten!';
                $message = '('.gethostname().') ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() . SECUREPHP_MAIL_EOL;
                $message .= "Trace:" . SECUREPHP_MAIL_EOL;
                $message .= $e->getTraceAsString();
                }
            else
                {
                $header = SECUREPHP . ': unbekannter Fehlerbericht!';
                $message = '('.gethostname().') ' . var_export($e, true);
                }
            $return = new \stdClass();
            $return->header = $header;
            $return->message = $message;
            return $return;
            }

        /**
         * @param string $from
         * @return bool
         * @throws E_CONFIG
         */
        final public function set_from($from)
			{
			// Validieren auf z.B. Der Tester <tester@localhost>
			#if(false == $this->check_email($from)) trigger_error('Invalid from address', E_USER_ERROR);
            if(false == ($this->from = $from))
                {
                $this->set_error( new E_CONFIG('Email-From nicht angegeben.'));
                return false;
                }
            else return true;
			}

        /**
         * @return string
         */
        public function get_from()
			{
			return $this->from;
			}

        /**
         * @param string $admin
         * @return bool
         */
        public function set_admin($admin)
			{
            if(false == $this->getInstance()) return false;
            else
                {
                $emails = explode(',', $admin);
                foreach($emails AS $mail)
                    {
                    if(false == $this->validate_email($mail))
                        {
                        $this->set_error(new \E_CONFIG('ungültige Administrator-Email', false, $this->get_error()->getMessage()));
                        return false;
                        }
                    }
                $this->admin = $admin;
                return true;
                }
			}

        /**
         * @return false | string
         */
        public function get_admin()
            {
            return $this->admin?:false;
            }

        /**
         * @param string $users
         * @return bool
         */
        public function set_user($users)
            {
            if(false == $this->getInstance()) return false;
            else
                {
                $emails = explode(',', $users);
                foreach($emails AS $mail)
                    {
                    if(false == $this->validate_email($mail))
                        {
                        $this->set_error(new \CONFIGERROR('Ungültige Mitarbeiter-Email: ', false,  $this->get_error()->getMessage()));
                        return false;
                        }
                    }
                $this->user = $users;
                return true;
                }
            }

        /**
         * @return string|bool
         */
        public function get_user()
			{
			return $this->user?:false;
			}

        /**
         * @param string $name
         * @param string $email
         * @return bool
         * @throw E_CONFIG
         */
        public function add_cc($name, $email)
            {
            if(false == $this->getInstance()) return false;
            else
                {
                if("admin" == $name || "user" == $name || "log" == $name)
                    {
                    $this->set_error(new E_CONFIG($name . ' kann nicht als Benutzername verwendet werden.'));
                    return false;
                    }
                elseif(false == $this->validate_email(trim($email)))
                    {
                    $this->set_error(new E_CONFIG('Ungültige Email für den Benutzer "' . $name . '" (' . $this->get_error()->getMessage() . ')'));
                    return false;
                    }
                else $this->userlist[$name] = $email;
                return true;
                }
            }

        /**
         * @param string $name
         * @return bool
         */
        public function get_cc_email($name)
            {
            if('users' == $name || 'admin' == $name || 'log' == $name)
                {
                $this->set_error(new E_CONFIG($name . 'ist ein ungültiger Benutzername'));
                return false;
                }
            if(!array_key_exists($name, $this->userlist))
                {
                $this->set_error(new E_CONFIG($name . ': kein Benutzer mit diesem Namen vorhanden'));
                return false;
                }
            return $this->userlist[$name];
            }

        /**
         * @param string $user
         * @return bool
         */
        public function get_cc_by_name($user)
			{
			if(array_key_exists($user, $this->userlist)) return $this->userlist[$user];
			else
                {
                $this->set_error(new E_CONFIG($user . 'ist kein vorhandener Benutzername'));
                return false;
                }
			}
		} // <-- final class MAIL

    /**
     * Class TIMEOUT
     * Klasse zum Unterdrücken von Wiederholungsfehlern.
     * Arbeitet z.B. mit der Mail-Klasse zusammen um bei Wiederholungsfehlern die Fehler zeitlich zu bündeln.
     *
     * @package SECUREPHP
     */
    final class TIMEOUT extends SINGLETON

        {

        // WICHTIGER HINWEIS:
        // Bitte keine eigenen dynamischen Werte wie Zeit/Datumsangaben als Angabe in den Fehlerberichten benutzern.
        // Es kann sonst keine eindeutige Fehler-ID erzeugt werden welche die Funktion der TIMEOUT-Klasse
        // in Folge zu nichte macht.

        // TIMEOUT HEAD

        /**
         * @var bool
         */
        private $flag_is_ready  = false;

        /**
         * @var array[]
         */
        private $data           = ARRAY();

        /**
         * @var int|NULL
         */
        private $timeout        = NULL;

        /**
         * @var int|NULL
         */
        private $reminder       = NULL;

        /**
         * @var bool|\Exception
         */
        private $error          = false;

        /**
         * @var bool|\Exception
         */
        private $init_error     = false;

        /**
         * @var static
         */
        private static $instance    = NULL;

        // TIMEOUT MAGIC METHODS

        // TIMEOUT METHODS

        /**
         * @return static
         */
        final public static function getInstance()
            {
            if (null === static::$instance)
                {
                static::$instance = new static();
                }
            return static::$instance;
            }

        /**
         * @return bool
         * @throws E_INIT
         */
        final public function init()
            {
            if($this->is_ready()) return true;
            elseif(false === ($result = DB::getInstance()->server->query("SELECT * FROM timeout")))
                {
                $this->set_init_error(new E_INIT('Timout-Datenbankabfrage ungültig'));
                return false;
                }
            elseif (!($result instanceof \Sqlite3Result))
                {
                $this->set_init_error(new E_INIT('Timout-Datenbankantwort ungültig'));
                return false;
                }
            else
                {
                while($row = $result->fetchArray())
                    {
                    $this->data[$row['id']] = ARRAY($row['lasttime'],$row['attempts'],$row['warning'],$row['file'],$row['line'],$row['timeout'],$row['reminder'],$row['message'],$row['key'],$row['id']);
                    }
                }

            if ("" != $this->data && !is_array($this->data))
                {
                $this->set_init_error(new E_INIT('Timeout-Daten ungültig.'));
                return false;
                }
            else
                {
                $this->is_ready(true);
                return true;
                }
            }


        /**
         * Prüft einen Fehlerbericht vor dem Email-Versand.
         * - vermeidet Wiederholungsfehler durch Timeout-Parameter.
         * - vermeidet das Versenden falscher Fehlerberichte.
         *
         * @param \Exception $e
         * @param int $timeout
         * @return bool
         */
        final public function check(\Exception $e, $timeout=NULL)
            {

            $starttime = BOOTSTRAP::$starttime;
            $file = $e->getFile();
            $line = $e->getLine();
            $message = $e->getMessage();

            // Wenn ein Fehler in der TIMEOUT-Klasse vorliegt sende keine Emails!
            #if($this->flag_error) return false;

            // Überspringe die Timeout-Berechnung und versende Emails immer
            // wenn mittels timeout = 0 die Timeout-Berechnung unterdrückt wurde.
            if(0===$timeout) return true;

            // Wenn kein globaler Timeout-Wert konfiguriert (NULL) ist UND auch der
            // Timeout-Parameter nicht gesetzt (NULL) ist, sende alle Emails.
            elseif(NULL === $timeout AND NULL === $this->timeout) return true;

            // Überschreibe den lokalen Timeout mit dem anosnsten gesetzten globalen Timeout
            // wenn der lokale Timeout-Parameter nicht angegeben (NULL) ist.
            elseif(NULL === $timeout) $timeout = $this->timeout;

            // Individuelle Timeout-Angaben werden noch nicht unterstützt.
            // @todo implement feature
            elseif(!empty($timeout))
                {
                return true;
                }

            // Reminder festlegen
            if($reminder = $this->get_reminder());
            else $reminder = 60 * 30;


            if(false == $this->clear())
                {
                error_log('Fehler beim Löschen der Timer-Db');
                }

            // Der der Timeout-Berechnung zugrunde liegende Timout-Wert entspricht
            // jetzt dem Überschriebenem globalen Timout.

            if(method_exists($e, 'get_md5'))
                {
                $key = $e->get_md5();
                }
            elseif(false == method_exists($e, '__toString'))
                {
                $key = md5(spl_object_hash ($e));
                }
            else
                {
                $key = md5(json_encode((string) $e));
                }

            // Noch kein Eintrag in DB vorhanden
            if(empty($this->data) || !$data = $this->select($key, $file))
                {

                // Zeitstempel wie folgt
                // [0] = lasttime (time letzter Fehler);
                // [1] = attempts (int Anzahl an Wiederholungen)
                // [2] = warning (time letzte Wiederholungs-Email)
                // [3] = file
                // [4] = line
                // [5] = timeout (Speichert den individuellen Timeout) @TODO
                // [6] = reminder (individueller Reminder) @TODO
                // [7] = message
                // [8] = key
                // [9] = id

                $this->add($key, $starttime, 0, 0, $file, $line, $timeout, $reminder, $message);

                return true;
                }

            // Es existiert ein Eintrag.
            // Prüfen ob Wiederholungsfehler oder nicht.

            else
                {

                $lasttime = $data[0];
                $attempts = $data[1];
                $warning = $data[2];
                $reminder = $data[6];
                $id = $data[9];

                // Der Timeout ist noch nicht abgelaufen.
                // Es handelt sich um den selben Fehler im selben Prozess.
                // Sende keine neue Nachricht.
                if($lasttime + $timeout > $starttime)
                    {
                    return false;
                    }

                // Der Timeout ist abgelaufen.
                // Es liegt ein neuer Prozess vor.
                else
                    {
                    // Fehler ist ein Wiederholungsfehler
                    // Weil er beim nächsten Prozess nach Ablauf von Timeout
                    // und vor dem übernächsten Prozess erneut Auftritt.
                    if($starttime - $lasttime <= $timeout * SECUREPHP_TIMEOUT)
                        {
                        if(0 == $attempts)
                            {
                            // Erster Wiederholungsfehler.
                            // Sende eine Nachricht über Dauerfehler.
                            $report = new \TimerAlert('Wiederholungsfehler','Sie werden ab jetzt alle 30 Minuten über den Fehler informiert solange dieser weiterhin vorliegt.');
                            $report->send_to($e->get_send_to());
                            $report->params["md5"]      = $key;
                            $report->params["attempts"] = $attempts . " Wiederholungsfehler bisher";
                            $report->params["lasttime"] = date('d-M-Y H:i:s', $lasttime);
                            $report->params["timeout"]  = $timeout . ' Sekunden';
                            $report->add($e);

                            $this->data[$id][0] = $starttime;
                            $this->data[$id][1]++;
                            $this->data[$id][2] = $starttime;
                            $this->update();

                            return $report;
                            }
                        else
                            {
                            // weiterer Wiederholungsfehler
                            // Wenn 30 Minuten vorbei neue Erinnerung ..
                            if($starttime - $warning > $reminder)
                                {
                                $report = new \TimerAlert('Fehlererinnerung', 'Sie werden weiterhin alle 30 Minuten über den bestehenden Wiederholungsfehler informiert');
                                $report->send_to($e->get_send_to());
                                $report->params["md5"]          = $key;
                                $report->params["attempts"]     = $attempts . ' Wiederholungsfehler bisher';
                                $report->params["lasttime"]     = date('d-M-Y H:i:s', $lasttime);
                                $report->params["timestamp"]    = time();
                                $report->params["timeout"]      = $timeout . ' Sekunden';
                                $report->add($e);

                                $this->data[$id][0] = $starttime;
                                $this->data[$id][1]++;
                                $this->data[$id][2] = $starttime;
                                $this->update();

                                return $report;
                                }
                            else
                                {
                                // Wiederholungsfehler nur verbuchen..
                                $this->data[$id][0] = $starttime;
                                $this->data[$id][1]++;
                                $this->update();
                                return false;
                                }
                            }
                        }
                    // Neuer Fehler wenn innerhalb des Timeouts keine Fehler mehr gemeldet wurden.
                    // Dieser Zustand wird durch clear() hergestellt durch löschen alter Einträge ..
                    elseif($starttime - $lasttime >= $timeout * SECUREPHP_TIMEOUT + 1)
                        {
                        $this->data[$id][0] = $starttime;
                        $this->data[$id][1] = 0;
                        $this->data[$id][2] = 0;
                        $this->update();
                        return true;
                        }
                    }
                }
            }

        final private function add($key, $lasttime, $attempts, $warning, $file, $line, $timeout, $reminder, $message)
            {
            try
                {
                $statement = DB::getInstance()->server->prepare("INSERT INTO timeout (key,lasttime,attempts,warning,file,line,timeout,reminder,message) VALUES (:key, :lasttime, :attempts, :warning,:file, :line, :timeout, :reminder, :message)");
                $statement->bindValue(':key', $key);
                $statement->bindValue(':lasttime', $lasttime);
                $statement->bindValue(':attempts', $attempts);
                $statement->bindValue(':warning', $warning);
                $statement->bindValue(':file', $file);
                $statement->bindValue(':line', $line);
                $statement->bindValue(':timeout', $timeout);
                $statement->bindValue(':reminder', $reminder);
                $statement->bindValue(':message', $message);
                $statement->execute();

                return $this->db->lastInsertRowID();

                }
            catch(\Exception $e)
                {
                $this->set_init_error($e);
                return false;
                }
            }

        /**
         * @return bool
         */
        final private function update()
            {
            foreach($this->data AS $id=>$row)
                {
                $statement = DB::getInstance()->server->prepare("UPDATE timeout SET lasttime = :lasttime, attempts = :attempts, warning = :warning, file = :file, line = :line, timeout = :timeout, reminder = :reminder, message = :message WHERE id = :id");

                $statement->bindValue(':lasttime', $row[0]);
                $statement->bindValue(':attempts', $row[1]);
                $statement->bindValue(':warning', $row[2]);
                $statement->bindValue(':file', $row[3]);
                $statement->bindValue(':line', $row[4]);
                $statement->bindValue(':timeout', $row[5]);
                $statement->bindValue(':reminder', $row[6]);
                $statement->bindValue(':message', $row[7]);
                $statement->bindValue(':id', $id);
                $statement->execute();
                #echo $this->db->lastErrorMsg();
                }
            return true;
            }

        /**
         * @return bool
         */
        final private function clear()
            {
            if(empty($this->data)) return true;
            else
                {
                foreach($this->data AS $id=>$value)
                    {
                    $lasttime = $value[0];
                    $timeout = $value[5];
                    $timeout = $lasttime + ($timeout * SECUREPHP_TIMEOUT);
                    if($timeout < BOOTSTRAP::$starttime)
                        {
                        $_data[] = $id;
                        }
                    }
                if(isset($_data))
                    {
                    foreach($_data AS $id)
                        {
                        unset($this->data[$id]);
                        DB::getInstance()->server->exec("DELETE FROM timeout WHERE id = '$id'");
                        return true;
                        }
                    }
                else return true;
                }
            }

        /**
         * @param string $key
         * @param string $file
         * @return array
         */
        final protected function select($key, $file)
            {
            $a = $this->data;
            $b = array_map(function($row) use ($key, $file)
                {
                if($row[8] == $key AND $row[3] == $file) return $row;
                }, $a);
            return array_shift($b);
            }

        /**
         * @return bool
         */
        final public function is_ready($flag_ready = NULL)
            {
            if(NULL === $flag_ready) return $this->flag_is_ready;
            else
                {
                $this->flag_is_ready = (bool) $flag_ready;
                return true;
                }
            }

        // TIMEOUT GETTERS & SETTERS

        /**
         * @param \Exception $e
         * @return bool
         */
        final private function set_init_error(\Exception $e)
            {
            $this->init_error = $e;
            return true;
            }

        /**
         * @return bool|\Exception
         */
        final public function get_init_error()
            {
            return $this->init_error;
            }

        /**
         * @param \Exception $e
         * @return bool
         */
        final private function set_error(\Exception $e)
            {
            $this->error = $e;
            return true;
            }

        /**
         * @return bool|\Exception
         */
        final public function get_error()
            {
            return $this->error;
            }

        /**
         * @param int $timeout
         * @return bool
         */
        final public function set_timeout($timeout)
            {
            if(!is_int($timeout))
                {
                $this->set_error(new \CONFIGERROR('ungültiger Integer-Wert für Timeout übergeben'));
                return false;
                }
            else
                {
                $this->timeout = $timeout;
                return true;
                }
            }

        /**
         * @return int | NULL
         */
        final public function get_timeout()
            {
            return $this->timeout;
            }

        /**
         * @param int $timeout
         * @return NULL|int
         */
        final public function set_reminder($timeout)
            {
            if(!is_int($timeout))
                {
                $this->set_error(new \CONFIGERROR('ungültiger Integer-Wert für Reminder übergeben'));
                return false;
                }
            else
                {
                $this->reminder = $timeout;
                return true;
                }
            }

        /**
         * @return int | NULL
         */
        final public function get_reminder()
            {
            return $this->reminder;
            }

        } // final class TIMEOUT

    /**
     * Class Database
     * Zugriff auf die SecurePHP-Datenbank.
     * Arbeitet z.B. mit der Mail-Klasse zusammen um bei Wiederholungsfehlern die Fehler zeitlich zu bündeln.
     *
     * @package SECUREPHP
     */

    final class DB extends SINGLETON
        {

        // DB HEAD

        /**
         * @var bool
         */
        private $flag_is_ready = false;

        /**
         * @var \SQLITE
         */
        public $server;

        /**
         * @var static
         */
        private static $instance = NULL;

        /**
         * @var Exception
         */
        private $init_error;

        // DB MAGIC METHODS

        // DB METHODS

        /**
         * @return static|false
         */
        final public static function getInstance()
            {
            if (null === static::$instance)
                {
                static::$instance = new static();
                }
            return static::$instance;
            }

        /**
         * @return bool
         * @throws \INITERROR
         */
        final public function init()
            {
            if($this->is_ready()) return true;
            elseif(!$db = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'securephp.sqlite');
            elseif(!file_exists($db))
                {
                $this->set_init_error(new E_INIT('konnte ' . SECUREPHP . '-Datenbank nicht erstellen.', false, $this->get_init_error()));
                return false;
                }
            else
                {
                try
                    {
                    $this->server = new \SQLite3($db);
                    $this->is_ready(true);
                    return true;
                    }
                catch (\Exception $e)
                    {
                    $this->set_init_error(new E_INIT('konnte ' . SECUREPHP . '-Datenbank nicht starten.', 0, $e));
                    return false;
                    }
                }
            }

        final public function is_ready($flag_ready = NULL)
            {
            if(NULL === $flag_ready) return $this->flag_is_ready;
            else
                {
                $this->flag_is_ready = $flag_ready;
                return true;
                }
            }

        // DB GETTERS & SETTERS

        /**
         * @param \Exception $e
         * @return bool|\Exception
         */
        final public function set_init_error(\Exception $e)
            {
            if($e)
                {
                $this->init_error = $e;
                return true;
                }
            else return false;
            }

        /**
         * @return Exception
         */
        final public function get_init_error()
            {
            return $this->init_error;
            }

        } // final class DB

	}
	
// EOF