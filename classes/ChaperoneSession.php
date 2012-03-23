<?php
require_once('ChaperoneAction.php');
require_once('ChaperoneContextRuleSet.php');
require_once('ChaperoneRole.php');
require_once('ChaperoneRuleSet.php');
/**
 * This class handles the logic for a session.  An email address must be specified
 * when starting a session.  Roles can then be attached to the session and permissions
 * can then be tested.
 * 
 * This class does NOT handle the session within $_SESSION.  That is done by
 * ChaperoneCurrentSession.  The roles are deliberately split so that you can create
 * session objects separately to the currently logged-in user if you need to test
 * permissions independantly (eg. testing whether a user with a particular role
 * can access something)
 *
 * @author steve
 */
class ChaperoneSession {

    private $email = NULL;                                                      // Email address (userid, essentially) of user
    
    // Chaperone Action Context RuleSet Array is a multi dimensional array.
    // First level is the action name
    // Second level is just keyed by number
    // Third level has references to the rcrs (role_context_rule_set) object and action object
    private $actionContextRuleSetArray = array();

    /*
     * Constructor.  For now we don't validate the email address
     */
    public function __construct($email) {
        $this->email = $email;
    }
    
    
    /*
     * Clears the Chaperone Session.  Used to invalidate objects that may still be referenced but overridden
     */
    public function clear() {

        // Clear email address
        $this->email = NULL;

        // Clear our Action Context RuleSet array
        $this->actionContextRuleSetArray = array();
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
     * @throws  ChaperoneException          
     */
    public function attachRole($role, $contextArray=array()) {

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
    }


    /*
     * Determines whether the current session has permission to perform the specified action in the specified context
     * 
     * @param   string                      $action
     * @param   array (optional)            $contextArray
     * @returns boolean
     */
    public function isActionAllowed($action, $contextArray=array()) {
        
        // $action may or may not contain a namespace.  Get the full resource name
        require_once('ChaperoneNamespace.php');
        $actionFullName = ChaperoneNamespace::getFullName($action);

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
     * @returns ChaperoneContextValueList
     * @throws  ChaperoneException          Context item exists in the context array
     */
    public function getAllowedContextValues($action, $contextItem, $contextArray=array()) {

        // Sanity check that the requested context item is not in the context array
        if (array_key_exists($contextItem, $contextArray)) {
            require_once('ChaperoneException.php');
            throw new ChaperoneException('Context item "'.$contextItem.'" exists in the context array');
        }

        // Context list to store results in
        require_once('ChaperoneContextValueList.php');
        $contextValueListObj = new ChaperoneContextValueList();

        // $action may or may not contain a namespace.  Get the full resource name
        require_once('ChaperoneNamespace.php');
        $actionFullName = ChaperoneNamespace::getFullName($action);

        // If we have no entries for the action, we don't have permission to anything
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
            $returnedContextValueListObj = $actionObj->getAllowedContextValues($contextItem, $rcrsObj, $contextArray);

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