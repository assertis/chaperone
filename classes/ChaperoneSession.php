<?php
require_once('ChaperoneAction.php');
require_once('ChaperoneContextRuleSet.php');
require_once('ChaperoneRole.php');
require_once('ChaperoneRuleSet.php');
/**
 * Description of ChaperoneSession
 *
 * @author steve
 */
class ChaperoneSession {
    const identifier='CHAPERONE';                                               // Key in $_SESSION

    private static $instance = NULL;                                            // Static, so won't be serialized

    // Chaperone Action Array is a multi dimensional array.
    // First level is the action name
    // Second level is just keyed by number
    // Third level has references to the context_rule_set object and action object
    private $crsActionArray = array();

    /*
     *  Private constructor prevents external instantiation
     */
    private function __construct() {}


    /*
     * Returns the current session.  If there is already an instance, that is returned
     * If there is no instance, will attempt to get one from $_SESSION
     */
    public static function getSession() {
        
        // If there is not an instance of the session, create one
        if (self::$instance === NULL) {
            if (!headers_sent()) session_start();
            $sessionObj = NULL;
            
            // If it looks as though there's a ChaperoneSession in $_SESSION, attempt to use it
            if (array_key_exists(self::identifier, $_SESSION)) {

                // Unserialize may fail if corrupt.  Wrong object may be in the session
                try {
                    $sessionObj = unserialize($_SESSION[self::identifier]);
                    if (!($sessionObj instanceof ChaperoneSession)) $sessionObj = NULL;
                } catch (Exception $e) {
                    $sessionObj = NULL;
                }
            }
            
            // Either use the object we unserialized or create a new session.
            // We don't save because we've either just loaded it, or it's empty
            self::$instance = ($sessionObj === NULL) ? new self() : $sessionObj;
            unset($sessionObj);
        }

        // Return instance of session
        return self::$instance;
    }

    
    /*
     * Clears the current Chaperone Session
     */
    public function clear() {

        // Clear our Context RuleSet array
        $this->crsActionArray = array();

        // If the PHP session currently has a ChaperoneSession, unset it
        if (array_key_exists(self::identifier, $_SESSION))
            unset($_SESSION[self::identifier]);
    }


    /*
     * Attaches a role/context to the current session
     */
    public function attachRole($role, $contextArray=array()) {

        try {
            // Get role
            $roleObj = ChaperoneRole::loadByName('tmcadmin');

            // Get context ruleset for role using the supplied context
            $crsObj = $roleObj->getContextRuleSet($contextArray);
        } catch (Exception $e) {
            return FALSE;
        }

        // Get actions and rulesets for the role
        $actionArray = $roleObj->getActions();

        // Put actions into the session array, indexing by action name
        foreach ($actionArray AS $actionObj) {
            $actionName = $actionObj->getFullName();
            if (!array_key_exists($actionName, $this->crsActionArray)) {
                $this->crsActionArray[$actionName] = array();
            }
            $this->crsActionArray[$actionName][] = array('context_rule_set'=>$crsObj, 'action'=>$actionObj);
        }

        // Save to session
        $this->save();
    }

    private function save() {
        // Serialize self and put into session
        $_SESSION[self::identifier] = serialize($this);
    }


    /*
     * Determines whether the current session has permission to perform the specified action in the specified context
     */
    public function actionCheck($action, $contextArray=array()) {
        
        // If we have no entries for the action, permission is denied
        if (!array_key_exists($action, $this->crsActionArray)) return FALSE;

        // Get a reference to the array entry for the specified action
        $crsActionArray =& $this->crsActionArray[$action];

        // Iterate through entries
        foreach ($crsActionArray AS $crsAction) {

            // Get a reference to the Action object (which has rulesets attached)
            $actionObj =& $crsAction['action'];

            // Get a reference to the Context RuleSet object
            $crsObj =& $crsAction['context_rule_set'];

            if ($actionObj->ruleSetCheck($crsObj, $contextArray))
                return TRUE;
        }

        // No RuleSets were satisfied.  Permission denied
        return FALSE;
    }
}
?>