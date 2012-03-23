<?php
/*
 * This object contains a list of context items, or NULL to signify a wildcard (all/any)
 * The class simply adds a more user-friendly interface to the underlying mechanism
 * and adds a merge feature so that smaller lists can potentially be added to a larger
 * list
 * 
 * When merging, if either list is already set to wildcard (all/any), this object's list
 * will also be wildcard (all/any)
 * 
 * A Context Filter is used when you are at a particular point in a hierachy and want to
 * know what you have access to at the next level down.  For instance, if you are at B2B
 * TMC level and you want to know which businesses you can administer.  Rather than iterating
 * through each business asking "can I do this?", you get the Context Filter for the action
 * and context, asking for the "business" part.  The Context Filter will contain either a list
 * of businesses you have permission for (which may be empty, signifying no permission), or
 * a wildcard, signifying that you have permission for all businesses within the TMC.
 *
 * @author Steve Criddle
 */
class ChaperoneContextFilter {
    
    private $contextFilterList = array();         // An array of items, or NULL signifying all/any
    
    public function addItem($value) {
        
        // Disallow invalid values
        if (!is_string($value) AND !is_integer($value)) {
            require_once('ChaperoneException.php');
            throw new ChaperoneException('Filter item "'.$value.'" is invalid');
        }
        
        // If the list is wildcard, we don't need to add items
        if ($this->contextFilterList === NULL) return;

        // Only add the item if it doesn't already exist (saves shuffling the keys around)
        if (!array_key_exists($value, $this->contextFilterList))
            $this->contextFilterList[$value] = TRUE;
    }

    public function addWildcard() {
        $this->contextFilterList = NULL;
    }

    public function merge(ChaperoneContextFilter $other) {

        // If we already allow everything, there's no need to merge
        if ($this->contextFilterList === NULL)
            return;

        // If the other list allows everything, we now allow everything
        if ($other->contextFilterList === NULL) {
            $this->contextFilterList = NULL;
            return;
        }

        // If the other list is empty, there is nothing to do
        if (count($other->contextFilterList) === 0)
            return;

        // Merge arrays.  We're only really interested in the keys
        // We don't use array_merge() here because it would renumber numberic keys
        foreach ($other->contextFilterList AS $key=>$value) {
            $this->addItem($key);
        }
    }
    
    public function isWildcard() {
        return ($this->contextFilterList === NULL);
    }

    public function isEmpty() {
        return (($this->contextFilterList !== NULL) AND (count($this->contextFilterList) === 0));
    }

    public function getItems() {
        if ($this->contextFilterList === NULL)
            return NULL;
        return array_keys($this->contextFilterList);
    }
}
?>