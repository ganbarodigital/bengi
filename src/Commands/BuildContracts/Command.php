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

namespace GanbaroDigital\Bengi\Commands\BuildContracts;

use GanbaroDigital\Bengi\Config;
use GanbaroDigital\Bengi\Helpers;
use Phix_Project\CliEngine;
use Phix_Project\CliEngine\CliCommand;
use Phix_Project\CliEngine\CliResult;

/**
 * container for a classmap (as found in a composer project)
 */
class Command extends CliCommand
{
    public function __construct($additionalContext)
    {
        // define the command
        $this->setName('build-contracts');
        $this->setShortDescription('convert PHPUnit results into test contracts');
        $this->setLongDescription(
            "Use this command to take the testdox output from PHPUnit"
            ." and add it to your docs folder as Markdown."
            .PHP_EOL
        );

        // add in any switches we support
        $this->addSwitches([
            new PathToFile($additionalContext)
        ]);
    }

    public function processCommand(CliEngine $engine, $params = array(), $additionalContext = null)
    {
        // where are we looking?
        $testdoxFilename = Config\GetTestdoxTxtPath::from($additionalContext->config);

        // do we have one?
        if (!file_exists($testdoxFilename)) {
            echo "*** error: no such file '{$testdoxFilename}'" . PHP_EOL
            . PHP_EOL
            . "Possible solutions:" . PHP_EOL
            . "- have you run PHPUnit yet?" . PHP_EOL
            . "- do you need to use the '-f' switch instead?" . PHP_EOL;

            exit(1);
        }

        // where might the existing contracts be?
        $pathToContracts = Config\GetContractsPath::from($additionalContext->config);

        // remove all the old contract files
        // in case some of them are no longer needed
        foreach (Helpers\FindFiles::from($pathToContracts, []) as $filename) {
            Helpers\UnlinkFile::called($filename);
        }
        foreach (Helpers\FindFolders::from($pathToContracts) as $filename) {
            Helpers\UnlinkFolder::called($filename);
        }

        $prefix = <<<EOS
## Behavoural Contract

Here is the behavioural contract, enforced by our unit tests:


EOS;
        $suffix = <<<EOS

{% include ".i/boilerplate/behavioural-contract.twig" %}

EOS;

        // let's see what PHPUnit has told us
        $fd = fopen($testdoxFilename, 'r');

        $contractName=null;
        $contractDetails=[];

        while ($line = fgets($fd)) {
            // blank line
            if (empty(trim($line)) && count($contractDetails) > 0) {

                $path = $this->determineTargetFile($pathToContracts, $contractName);
                Helpers\MakePath::to($path);
                file_put_contents(
                    $path,
                    $prefix
                    . '    ' . $contractName . PHP_EOL
                    . implode(PHP_EOL, $contractDetails)
                    . PHP_EOL
                    . $suffix
                );

                // reset for the next one
                $contractName = null;
                $contractDetails = [];
            }
            else if ($line{0} == ' ') {
                $contractDetails[] = '     ' . trim($line);
            }
            else {
                $contractName = $this->determineContractName($line);
            }
        }
    }

    protected function determineContractName($line)
    {
        $contractName = trim($line);

        $parts = explode("\\", $contractName);
        if (count($parts) > 1) {
            if (substr($parts[0], -4) !== 'Test') {
                echo "*** fixme: $contractName is not in the 'Test' namespace" . PHP_EOL;
            }
            else {
                $parts[0] = substr($parts[0], 0, -4);
            }
        }
        else {
            if (strtolower($parts[0]{0}) !== ($parts[0]{0})) {
                echo "*** fixme: $contractName starts with a capital letter" . PHP_EOL;
                $parts[0] = strtolower($parts[0]{0}) . substr($parts[0], 1);
            }
            $parts[0] .= '()';
        }

        return implode("\\", $parts);
    }

    protected function determineTargetFile($basePath, $contractName)
    {
        $retval = $basePath . '/' . str_replace('\\', '/', $contractName);
        if (substr($retval, -2) === '()') {
            $retval = substr($retval, 0, -2);
        }

        return $retval . '.twig';
    }
}


