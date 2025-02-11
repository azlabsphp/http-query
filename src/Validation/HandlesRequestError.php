<?php

declare(strict_types=1);

namespace Drewlabs\Query\Http\Validation;

trait HandlesRequestError
{

    /** @var bool */
    private $failsOnError = false;


    /**
     * Instruct rule to fail on request error.
     * By default validation will pass if request ends with an exception. This function is
     * therefore added to disable the default behaviour which may cause the validation to 
     * fail on error.
     * 
     * @return static 
     */
    public function withFailureOnError()
    {
        $this->failsOnError = true;
        return $this;
    }
}
