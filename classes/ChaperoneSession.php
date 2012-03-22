<?php
require_once('ChaperoneAction.php');
require_once('ChaperoneContextRuleSet.php');
require_once('ChaperoneRole.php');
require_once('ChaperoneRuleSet.php');
/**
 * This class defines a session within Chaperone.  The class creates a singleton
 * object when needed.  This is create either from $_SESSION (if it exists), or
 * a brand new instance.
 * 
 * The object is serialized and written to $_SESSION['CHAPERONE'] (configurable)
 * when save() is called.  This is why a singleton is used rather than a static
 * class (since you need to serialize an object).  Serialization handles multiple
 * references to the same object (which will happen with the same role being
 * defined for multiple actions).
 *
 * @author steve
 */
class ChaperoneSession {
    const identifier='CHAPERONE';                                               // Key in $_SESSION

    private static $instance = NULL;                                            // Static, so won't be serialized

    private $email = NULL;                                                      // Email address (userid, essentially) of user
    
    // Chaperone Action Array is a multi dimensional array.
    // First level is the action name
    // Second level is just keyed by number
    // Third level has references to the rcrs (role_context_rule_set) object and action object
    private $actionContextRuleSetArray = array();

    /*
     *  Private constructor prevents external instantiation
     */
    private function __construct() {}


    /*
     * Returns the current session.  If there is already an instance, that is returned
     * If there is no instance, will attempt to get one from $_SESSION
     * 
     * @returns ChaperoneSession
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

        // Clear email address
        $this->email = NULL;

        // Clear our Action Context RuleSet array
        $this->actionContextRuleSetArray = array();

        // If the PHP session currently has a ChaperoneSession, unset it
        if (array_key_exists(self::identifier, $_SESSION))
            unset($_SESSION[self::identifier]);
    }

    
    /*
     * Checks whether the user is logged in.  Does this by simply checking whether the email address is set
     * 
     * @returns boolean
     */
    public function isLoggedIn() {
        return ($this->email !== NULL);
    }

    
    /*
     * Sets the email address - essentially a userid
     * Once set, it cannot be changed.  If you need to reset, you must clear() and set it again
     * 
     * @param   string                      $email
     * @throws  ChaperoneException          Cannot set email address twice
     */
    public function setEmailAddress($email) {
        
        // Prevent email address being set twice.  We'll be a bit forgiving and ignore you trying to set it to the existing value
        if (($this->email !== NULL) AND ($this->email != $email)) {
            require_once('ChaperoneException.php');
            throw new ChaperoneException('Cannot set email address twice');
        }

        $this->email = $email;
    }
    
    
    /*
     * Gets the user's email address - essentially a userid
     * 
     * @returns string
     */
    public function getEmailAddress() {
        return $this->email;
    }
    

    /*
     * Attaches a role/context to the current session
     * 
     * @param   string                      $role
     * @param   array (optional)            $contextArray
     * @throws  ChaperoneException          User must be logged in before attaching roles
     * @throws  ChaperoneException          
     */
    public function attachRole($role, $contextArray=array()) {

        // Email address must be set before attaching roles (log in before attaching)
        if (!$this->isLoggedIn()) {
            require_once('ChaperoneException.php');
            throw new ChaperoneException('User must be logged in before attaching roles');
        }
        
        try {
            // Get role
            $roleObj = ChaperoneRole::loadByName($role);

            // Get context ruleset for role using the supplied context
            $rcrsObj = $roleObj->getContextRuleSet($contextArray);

        } catch (ChaperoneException $e) {
            require_once('ChaperoneException.php');
            throw new ChaperoneException($e->getMessage());
        }

        // Get actions and rulesets for the role
        $actionArray = $roleObj->getActions();

        // Put actions into the Action Context RuleSet array, indexing by action name
        foreach ($actionArray AS $actionObj) {
            $actionName = $actionObj->getFullName();
            if (!array_key_exists($actionName, $this->actionContextRuleSetArray)) {
                $this->actionContextRuleSetArray[$actionName] = array();
            }
            $this->actionContextRuleSetArray[$actionName][] = array('rcrs'=>$rcrsObj, 'action'=>$actionObj);
        }

        // Save to session
        $this->save();
    }

    /*
     * Saves the current session to $_SESSION
     */
    private function save() {
        // Serialize self and put into session
        $_SESSION[self::identifier] = serialize($this);
    }


    /*
     * Helper function.  Gets the full name of a given resource
     * 
     * @param   string                      $resource
     * @returns string
     */
    private function getFullName($resource) {
        require_once('ChaperoneNamespace.php');
        $resourceArray = ChaperoneNamespace::splitResourceName($resource);
        return $resourceArray['namespace'].'.'.$resourceArray['resourceName'];

    }
    /*
     * Determines whether the current session has permission to perform the specified action in the specified context
     * 
     * @param   string                      $action
     * @param   array (optional)            $contextArray
     * @returns boolean
     */
    public function isActionAllowed($action, $contextArray=array()) {
        
        $actionFullName = $this->getFullName($action);

        // If we have no entries for the action, permission is denied
        if (!array_key_exists($actionFullName, $this->actionContextRuleSetArray)) return FALSE;

        // Get a reference to the array entry for the specified action
        $rcrsActionArray =& $this->actionContextRuleSetArray[$actionFullName];

        // Iterate through entries for that action
        foreach ($rcrsActionArray AS $rcrsAction) {

            // Get a reference to the Action object (which has rulesets attached)
            $actionObj =& $rcrsAction['action'];

            // Get a reference to the Role Context RuleSet object
            $rcrsObj =& $rcrsAction['rcrs'];

            // If the Role Context RuleSet is allowed to perform the action, permission is granted
            if ($actionObj->isRoleContextRuleSetAllowed($rcrsObj, $contextArray))
                return TRUE;
        }

        // No RuleSets were satisfied.  Permission denied
        return FALSE;
    }
    
    
    /*
     * For a given action and context item (and optional context), returns a list
     * of which items (if any) the user can access.  This saves having to cycle
     * through each item and asking "am I allowed to do this?"
     * 
     * @param   string                      $action
     * @param   string                      $contextItem
     * @param   array (optional)            $contextArray
     * @returns array/null
     * @throws  ChaperoneException          Context item exists in the context array
     */
    public function getContextValueList($action, $contextItem, $contextArray=array()) {

        // Sanity check that the requested context item is not in the context array
        if (array_key_exists($contextItem, $contextArray)) {
            require_once('ChaperoneException.php');
            throw new ChaperoneException('Context item "'.$contextItem.'" exists in the context array');
        }

        // Context list to store results in
        require_once('ChaperoneContextValueList.php');
        $contextValueListObj = new ChaperoneContextValueList();

        // Deal with namespace if it is not specified in the action name
        $actionFullName = $this->getFullName($action);

        // If we have no entries for the action, we have permission to nothing
        if (!array_key_exists($actionFullName, $this->actionContextRuleSetArray)) return $contextValueListObj;

        // Get a reference to the array entry for the specified action
        $crsActionArray =& $this->actionContextRuleSetArray[$actionFullName];

        // Iterate through entries
        foreach ($crsActionArray AS $crsAction) {

            // Get a reference to the Role Context RuleSet object
            $rcrsObj =& $crsAction['rcrs'];

            // If there is no rule for the requested Context Item, move on to the next item (if any)
            if (!$rcrsObj->isRuleFor($contextItem))
                continue;

            // Get a reference to the Action object (which has rulesets attached)
            $actionObj =& $crsAction['action'];

            // Get context list for the action
            $returnedContextValueListObj = $actionObj->getContextValueList($contextItem, $rcrsObj, $contextArray);

            // If a wildcard context list is returned, make our context list a wildcard and return it.  No need to look any further
            if ($returnedContextValueListObj->isWildcard()) {
                $contextValueListObj->addWildcard();
                return $contextValueListObj;
            }
            
            // Otherwise, merge the returned list into the current one
            $contextValueListObj->merge($returnedContextValueListObj);
        }

        // Return the Context List.  It should be a list (possibly empty), but not a wildcard
        return $contextValueListObj;
    }
}
?>