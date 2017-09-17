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

namespace GanbaroDigital\Bengi\Commands\RunExamples;

class RunExample
{
    public static function using($filename, array $blocks, $defaultPhpVersions, array $supportedPhpVersions, array $phpDetails)
    {
        // our return value
        $capturedOutput = [];

        // which versions of PHP are we going to run?
        $phpTargets = ExtractPhpTargetsList::from($blocks['targets'], $defaultPhpVersions, $supportedPhpVersions);

        // run the example against each given PHP version
        //
        // we run each one in a separate process:
        //
        // - it avoids nasty fatal error problems
        // - it avoids nasty problems with global variables and other state
        foreach ($phpTargets as $phpVersion => $command) {
            $key = $phpDetails[$phpVersion]['fullVersion'];
            $command = $command . ' ' . $filename . ' 2>&1';
            $capturedOutput[$key] = trim(shell_exec($command));
        }

        // did we get duplicate output from different PHP versions?
        //
        // it's more readable if we can combine those
        $capturedOutput = CombineExampleOutput::using($capturedOutput);

        // all done
        return $capturedOutput;
    }
}

