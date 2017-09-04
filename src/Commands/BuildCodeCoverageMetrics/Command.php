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

namespace GanbaroDigital\Bengi\Commands\BuildCodeCoverageMetrics;

use GanbaroDigital\Bengi\Helpers;
use Phix_Project\CliEngine;
use Phix_Project\CliEngine\CliCommand;
use Phix_Project\CliEngine\CliResult;

/**
 * container for a classmap (as found in a composer project)
 */
class Command extends CliCommand
{
    public function __construct()
    {
        // define the command
        $this->setName('build-code-coverage-metrics');
        $this->setShortDescription('convert PHPUnit code coverage metrics into documentation and badges');
        $this->setLongDescription(
            "Use this command to take the clover XML output from PHPUnit"
            ." and add it to your docs folder as Markdown."
            .PHP_EOL
        );

        // add in any switches we support
        $this->addSwitches([
            new PathToFile
        ]);
    }

    public function processCommand(CliEngine $engine, $params = array(), $additionalContext = null)
    {
        // where are we looking?
        $cloverFilename = $engine->options->cloverFilename;

        // do we have one?
        if (!file_exists($cloverFilename)) {
            echo "*** error: no such file '{$cloverFilename}'" . PHP_EOL
            . PHP_EOL
            . "Possible solutions:" . PHP_EOL
            . "- have you run PHPUnit yet?" . PHP_EOL
            . "- do you need to use the '-f' switch instead?" . PHP_EOL;

            exit(1);
        }

        // what can it tell us?
        list($classMetrics, $functionMetrics) = Helpers\ExtractCodeCoverageMetrics::from($cloverFilename);

        // where might the existing metrics docs be?
        $pathToMetrics = $engine->options->docsPath . '/.i/code-metrics';

        // remove all the old contract files
        // in case some of them are no longer needed
        foreach (Helpers\FindFiles::from($pathToMetrics, []) as $filename) {
            Helpers\UnlinkFile::called($filename);
        }
        foreach (Helpers\FindFolders::from($pathToMetrics) as $filename) {
            Helpers\UnlinkFolder::called($filename);
        }


        // where are we going to put the badges?
        $pathToBadges = $engine->options->docsPath . '/.i/badges';


        // write out the class stats
        foreach ($classMetrics as $className => $methods) {
            foreach ($methods as $methodName => $methodDetails) {
                // skip over private methods
                //
                // they go undocumented
                if ($methodDetails['visibility'] !== 'private' && $methodDetails['visibility'] != 'public') {
                    continue;
                }

                $docPath = "{$pathToMetrics}/{$className}.{$methodName}";
                $docPath = str_replace('\\', '/', $docPath);

                // we need to create the images
                $codeCoverage = 0.0;
                if ($methodDetails['noOfLines'] > 0) {
                    $codeCoverage = round($methodDetails['noOfCoveredLines'] / $methodDetails['noOfLines'] * 100.0, 2);
                }
                $color='brightgreen';
                if ($codeCoverage < 50) {
                    $color = 'red';
                }
                else if ($codeCoverage < 100) {
                    $color = 'yellow';
                }

                $coverageBadge = Helpers\LoadBadge::using('coverage', $codeCoverage, $color, $pathToBadges);

                $complexity = $methodDetails['complexity'];
                $color = 'brightgreen';
                if ($complexity > 4) {
                    $color = 'yellow';
                }
                else if ($complexity > 8) {
                    $color = 'red';
                }

                $complexityBadge = Helpers\LoadBadge::using('complexity', $complexity, $color, $pathToBadges);


                $crap = $methodDetails['CRAP'];
                $color = 'brightgreen';
                if ($crap > 4) {
                    $color = 'yellow';
                }
                else if ($crap > 8) {
                    $color = 'red';
                }

                $crapBadge = Helpers\LoadBadge::using('CRAP', $crap, $color, $pathToBadges);

                // make it easy to include
                $stats = <<<EOS
<div class="code-metrics">
{$coverageBadge}&nbsp;{$complexityBadge}&nbsp;{$crapBadge}
</div>

EOS;
                $docFilename = $docPath . '.twig';
                Helpers\MakePath::to($docFilename);
                Helpers\TrapLegacyErrors::call(
                    function() use ($docFilename, $stats) {
                        file_put_contents($docFilename, $stats);
                    },
                    function($errorMessage) use($docFilename) {
                        echo "*** error: cannot write to file '{$docFilename}'" . PHP_EOL
                        . PHP_EOL
                        . "Error message is:" . PHP_EOL
                        . "- {$errorMessage}" . PHP_EOL;

                        exit(1);
                    }
                );
            }
        }

        // we cannot write out the coverage for functions,
        // as this isn't present in the clover stats

    }
}


