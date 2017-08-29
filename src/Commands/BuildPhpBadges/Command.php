<?php

/**
 * Copyright (c) 2017-present Ganbaro Digital Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @author    Stuart Herbert <stuherbert@ganbarodigital.com>
 * @copyright 2017-present Ganbaro Digital Ltd www.ganbarodigital.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://ganbarodigital.github.io/bengi
 */

namespace GanbaroDigital\Bengi\Commands\BuildPhpBadges;

use GanbaroDigital\Bengi\Helpers;
use Phix_Project\CliEngine;
use Phix_Project\CliEngine\CliCommand;
use Phix_Project\CliEngine\CliResult;

/**
 * create badges to include in your Markdown
 */
class Command extends CliCommand
{
    public function __construct()
    {
        // define the command
        $this->setName('build-php-badges');
        $this->setShortDescription('create supported/unsupported badges to include in your Markdown');
        $this->setLongDescription(
            "Use this command to create 'PHP badges': image files that you"
            ." can include in your docs to show which versions of PHP your"
            ." code is compatible with."
            .PHP_EOL
        );
    }

    public function processCommand(CliEngine $engine, $params = array(), $additionalContext = null)
    {
        // where are we going to put the badges?
        $pathToBadges = $engine->options->docsPath . '/.i/badges';

        $phpVersions = [
            'PHP_5.6',
            'PHP_7.0',
            'PHP_7.1',
            'PHP_7.2'
        ];

        foreach ($phpVersions as $phpVersion)
        {
            Helpers\MakeBadge::using($phpVersion, 'supported', 'brightgreen', $pathToBadges);
            Helpers\MakeBadge::using($phpVersion, 'deprecated', 'yellow', $pathToBadges);
            Helpers\MakeBadge::using($phpVersion, 'unsupported', 'orange', $pathToBadges);
            Helpers\MakeBadge::using($phpVersion, 'untested', 'orange', $pathToBadges);
            Helpers\MakeBadge::using($phpVersion, 'incompatible', 'red', $pathToBadges);
        }
    }
}

