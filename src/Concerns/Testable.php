<?php

declare(strict_types=1);
/*
 * (c) Sidoine Azandrew <contact@liksoft.tg>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Drewlabs\RestQuery\Concerns;

trait Testable
{
    /**
     * 
     * @var bool
     */
    private $testing = false;

    /**
     * Marks the current class execution context to be a test execution context
     * 
     * @return $this 
     */
    public function test()
    {
        $this->testing = true;
        return $this;
    }

    /**
     * Boolean flag indicating if running test on the class instance
     * 
     * @return bool 
     */
    public function runningTest()
    {
        return boolval($this->testing);

    }
}