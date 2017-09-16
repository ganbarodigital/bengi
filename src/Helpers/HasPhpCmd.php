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

namespace GanbaroDigital\Bengi\Helpers;

/**
 * make sure we have a given PHP command
 */
class HasPhpCmd
{
    public static function check($phpVersion, $phpCmd)
    {
        $shellCmd = $phpCmd . ' -r "exit(0);" 2>&1';

        $onFailure = function($errorMessage) use ($phpVersion, $shellCmd) {
            // tidy up for output
            $errorMessage = trim($errorMessage);

            echo "*** error: invalid PHP CLI configured" . PHP_EOL
            . PHP_EOL
            . "There was a problem trying to run the PHP runtime for PHP v{$phpVersion}" . PHP_EOL
            . "- command: {$shellCmd}" . PHP_EOL
            . "- error  : {$errorMessage}" . PHP_EOL
            . PHP_EOL
            . "You may need to update your bengi.json, or install the missing version" . PHP_EOL
            . "of PHP, to fix this problem." . PHP_EOL;

            exit(1);
        };
        $runFunc = function() use ($shellCmd, $onFailure) {
            // let's get what we need
            $output = shell_exec($shellCmd);
            if (!empty($output)) {
                $onFailure($output);
            }
        };

        return TrapLegacyErrors::call($runFunc, $onFailure);
    }
}