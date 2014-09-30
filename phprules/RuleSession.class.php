<?php //namespace

class RuleSession {

    var $workingMemory;
    var $ruleBase;
    var $maxRulesFiring = 3000;

    function RuleSession(&$ruleBase, &$workingMemory) {
        $this->ruleBase = &$ruleBase;
        $this->workingMemory = &$workingMemory;
    }

    function fire() {
        $invocations = array();
        $rules = array();
        $highestPriority = -1;

        foreach ($this->ruleBase->getRules() as $rule) {
            $posibleInvocations = $this->workingMemory->getRuleInvocations($rule->context);
            $checkedInvocations = $rule->checkAll($posibleInvocations);

            if (count($checkedInvocations) > 0) {
                $invocations[$rule->name] = $checkedInvocations;

                if (array_key_exists('priority', $rule->config))
                    $priority = (int) $rule->config['priority'];
                else
                    $priority = 0;

                if (!array_key_exists($priority, $rules))
                    $rules[$priority] = array();


                array_push($rules[$priority], $rule);

                if ($priority > $highestPriority)
                    $highestPriority = $priority;
            }
        }

        if ($highestPriority > -1) {
          //  print_r($invocations);exit;
            $rule = $rules[$highestPriority][array_rand($rules[$highestPriority])];
            $invocation = $invocations[$rule->name][array_rand($invocations[$rule->name])];
            
            $this->workingMemory->invokeRule($rule, $invocation);
        }

        return $highestPriority > -1;
    }

    function fireNext() {
        return $this->fire();
    }

    function fireAll() {
        $loop = 1;

        while ($this->fire() && $loop < $this->maxRulesFiring)
            $loop++;

        if ($loop >= $this->maxRulesFiring) {
            print "Max firing count for all rules reached!\n";
            error_log("phprules: Max firing count for all rules reached!");
            return false;
        }

        return true;
    }

}
