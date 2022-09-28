<?php

/*
 * This file is part of the Silez framework.
 *
 * Author: Marc DiBlasi <marc.diblasi@gmail.com>
 *
 * For the full copyright and license information, please view the MIT.LICENSE
 * file that was distributed with this source code.
 */

namespace Silez\Response;

use Silez\Response;

class RedirectResponse extends Response
{
    public function __construct(string $url, int $status = 302)
    {
        $this->data                = "Redirecting to <a href=\"$url\">$url</a>";
        $this->status              = $status;
        $this->headers['Location'] = $url;
    }
}
