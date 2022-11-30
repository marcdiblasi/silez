<?php

/*
 * This file is part of the Silez framework.
 *
 * Author: Marc DiBlasi <marc.diblasi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Silez\Response;

use Silez\Response;

class JsonResponse extends Response
{
    public function __construct(array $data, int $status = 200, array $headers = [])
    {
        $this->status  = $status;
        $this->data    = json_encode($data);
        $this->headers = $headers;

        $this->headers['Content-Type'] = 'application/json';
    }
}
