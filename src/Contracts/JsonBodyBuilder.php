<?php

declare(strict_types=1);
/*
 * (c) Sidoine Azandrew <contact@liksoft.tg>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Drewlabs\Query\Http\Contracts;

interface JsonBodyBuilder
{
	/**
	 * Rerturns the json representation of the builder instance
	 * 
	 * @return array|string
	 */
	public function json();

}