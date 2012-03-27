<?php
require_once('ChaperoneSession.php');
/**
 * This class handles the current Chaperone session within $_SESSION.
 * getSession() is a factory method that returns a single session object or NULL if there is no session
 * This session is cached, so subsequent calls to getSession() will return the same reference
 *
 * @author Steve Criddle
 */
class ChaperoneCurrentSession {

    // Key in $_SESSION where the Chaperone session is held
    const sessionIdentifier='CHAPERONE';

    private static $sessionObj=NULL;

    
    /*
     * Private constructor prevents instantiation
     */
    private function __construct() {}


    /*
     * Checks whether the user is logged in.  Does this by simply checking whether the email address is set
     * 
     * @returns boolean
     */
    public static function isLoggedIn() {
        return (self::getSession() !== NULL);
    }


    /*
     * Returns the current session.  If there is already an instance, that is returned
     * If there is no instance, will attempt to get one from $_SESSION
     * 
     * @returns ChaperoneSession
     */
    private static function getSession() {
        
        // If we have a session object, return it
        if (self::$sessionObj !== NULL) return self::$sessionObj;

        // Start the PHP session if it hasn't already been started
        if (!headers_sent()) session_start();
            
        // If it looks as though there's a ChaperoneSession in $_SESSION, attempt to use it
        if (array_key_exists(self::sessionIdentifier, $_SESSION)) {

            // Unserialize may fail if corrupt.  Wrong object may be in the session
            try {
                $sessionObj = unserialize($_SESSION[self::sessionIdentifier]);
                if ($sessionObj instanceof ChaperoneSession) {
                    self::$sessionObj = $sessionObj;
                    return $sessionObj;
                }
            } catch (Exception $e) {}
        }

        // No session to return
        return NULL;
    }


    /*
     * Wrapper for getSession() that throws an exception if no session is returned
     * 
     * @returns ChaperoneSession
     * @throws  ChaperoneException          Session not found
     */
    private static function getSessionOrThrowException() {
        $sessionObj = self::getSession();
        if ($sessionObj === NULL) {
            require_once('ChaperoneException.php');
            throw new ChaperoneException('Session not found');
        }
        return $sessionObj;
    }


    /*
     * Starts a new session for the given email address.  If a session previously existed, it is replaced
     */
    public static function newSession($email) {

        // Start the PHP session if it hasn't already been started
        if (!headers_sent()) session_start();

        self::$sessionObj = new ChaperoneSession($email);
        self::saveSession();
    }
    
    
    /*
     * Clears the current Chaperone Session by dropping it
     */
    public static function clearSession() {

        // Start the PHP session if it hasn't already been started
        if (!headers_sent()) session_start();

        self::$sessionObj = NULL;

        // If the PHP session currently has a ChaperoneSession, unset it
        if (array_key_exists(self::sessionIdentifier, $_SESSION))
            unset($_SESSION[self::sessionIdentifier]);
    }
 
    
    /*
     * Saves the current session to $_SESSION
     */
    public static function saveSession() {

        // Ensure we have a session
        self::getSessionOrThrowException();

        // Serialize session object and put it into session
        $_SESSION[self::sessionIdentifier] = serialize(self::$sessionObj);
    }
    
    /*
     * Pass-through methods
     */
    public static function getEmailAddress() {
        return self::getSessionOrThrowException()->getEmailAddress();
    }

    public static function isActionAllowed($action, $contextArray=array()) {
        return self::getSessionOrThrowException()->isActionAllowed($action, $contextArray);
    }

    public static function getContextFilter($action, $contextItem, $contextArray=array()) {
        return self::getSessionOrThrowException()->getContextFilter($action, $contextItem, $contextArray);
    }

    /*
     * Pass-through method when attaching role.  Saves afterwards because the session will have changed
     */
    public static function attachRole($role, $contextArray=array()) {
        self::getSessionOrThrowException()->attachRole($role, $contextArray);
        
        // Session has potentially changed, so save it
        self::saveSession();
    }
}
?>