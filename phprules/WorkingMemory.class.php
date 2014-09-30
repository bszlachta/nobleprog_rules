<?php //namespace

class WorkingMemory {
    var $rule_fired_counter = 0;
    var $facts = array();
    private $action = array();

    function insert(&$fact) {
        $this->insertFact($fact);
    }

    function removeFact(&$fact) {
        $class = get_class($fact);

        if (array_key_exists($class, $this->facts)) {
            unset($this->facts[$class][$fact->getObjectId()]);
        }
    }

    function insertFact(&$fact) {
        $class = get_class($fact);

        if (!array_key_exists($class, $this->facts))
            $this->facts[$class] = array();

        $this->facts[$class][$fact->getObjectId()] = $fact;
    }

    function insertActionFassade($key, &$action) {
        $this->action[$key] = $action;
    }

    function getRuleInvocations($context) {
        $invocations = array();

        $this->setNextClass($invocations, array(), $context);

        return $invocations;
    }

//needs to be more efficient
    function setNextClass(&$invocations, $invocation, $context) {
        if (count($context) == 0) {
            array_push($invocations, $invocation);
            return;
        } else {
            $contextKeys = array_keys($context);
            $var = $contextKeys[0];
            $class = $context[$var];
            unset($context[$var]);
            if (isset($this->facts[$class])) {
                foreach ($this->facts[$class] as $object) {
                    $invocation[$var] = $object;
                    $this->setNextClass($invocations, $invocation, $context);
                }
            }
        }
    }

    function &getActionFassades() {
        return $this->action;
    }

    function invokeRule($rule, $objects) {
        $this->rule_fired_counter++ ;
        //$action = $this->action;
        $filled = $rule->action;
//        echo time() . " ";
//        echo $rule->name . "\n";

        foreach (array_keys($objects) as $key)
            $filled = preg_replace("/\\" . $key . "([^\w]|$)/", '\$objects[\'' . $key . '\']\1', $filled);

        eval($filled);
    }

    function getFacts() {
        return $this->facts;
    }

}
