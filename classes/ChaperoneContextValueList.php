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
 * @author steve
 */
class ChaperoneContextValueList {
    
    private $contextValueList = array();         // An array of items, or NULL signifying all/any
    
    public function addItem($value) {
        
        // If the list is wildcard, we don't need to add items
        if ($this->contextValueList === NULL) return;

        // Only add the item if it doesn't already exist (saves shuffling the keys around)
        if (!array_key_exists($value, $this->contextValueList))
            $this->contextValueList[$value] = TRUE;
    }

    public function addWildcard() {
        $this->contextValueList = NULL;
    }

    public function merge(ChaperoneContextValueList $other) {

        // If we already allow everything, there's no need to merge
        if ($this->contextValueList === NULL)
            return;

        // If the other list allows everything, we now allow everything
        if ($other->contextValueList === NULL) {
            $this->contextValueList = NULL;
            return;
        }

        // If the other list is empty, there is nothing to do
        if (count($other->contextValueList) === 0)
            return;

        // Merge arrays.  We're only really interested in the keys
        // We don't use array_merge() here because it would renumber numberic keys
        foreach ($other->contextValueList AS $key=>$value) {
            $this->addItem($key);
        }
    }
    
    public function isWildcard() {
        return ($this->contextValueList === NULL);
    }

    public function isEmpty() {
        return (($this->contextValueList !== NULL) AND (count($this->contextValueList) === 0));
    }

    public function getItems() {
        if ($this->contextValueList === NULL)
            return NULL;
        return array_keys($this->contextValueList);
    }
}
?>