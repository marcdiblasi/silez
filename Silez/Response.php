<?php

/*
 * This file is part of the Silez framework.
 *
 * Author: Marc DiBlasi <marc.diblasi@gmail.com>
 *
 * For the full copyright and license information, please view the MIT.LICENSE
 * file that was distributed with this source code.
 */

namespace Silez;

class Response extends \Exception
{
    public int $status;
    public string $data;
    public array $headers;

    public function __construct($data = '', $status = 200, $headers = [])
    {
        $this->data    = $data;
        $this->status  = $status;
        $this->headers = $headers;

        $this->message = $data;
        $this->code    = $status;
    }

    public function __toString() : string
    {
        return $this->data;
    }
}
