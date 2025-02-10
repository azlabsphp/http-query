<?php

declare(strict_types=1);
/*
 * (c) Sidoine Azandrew <contact@liksoft.tg>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Drewlabs\Query\Http\Contracts;

use Drewlabs\Query\Http\Response;

interface ClientInterface
{
    public function sendRequest( string $url, string $method = 'GET', array $body = [], array $headers = []): Response;
}