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

use GanbaroDigital\Bengi\Config;
use GanbaroDigital\Bengi\Helpers;
use Phix_Project\CliEngine;
use Phix_Project\CliEngine\CliCommand;
use Phix_Project\CliEngine\CliResult;

/**
 * run your code examples under all supported PHP versions
 */
class Command extends CliCommand
{
    public function __construct($additionalContext)
    {
        // define the command
        $this->setName('run-examples');
        $this->setShortDescription('generate docs from running PHP examples');
        $this->setLongDescription(
            "Use this command to run your PHP examples, and turn the output"
            ." into Markdown you can include in your documentation."
            .PHP_EOL
        );

        // add in any switches we support
        $this->addSwitches([
            new PathToSources($additionalContext),
            new PathToDest($additionalContext),
        ]);
    }

    public function processCommand(CliEngine $engine, $params = array(), $additionalContext = null)
    {
        // shorthand
        $examplesSourcePath = Config\GetExamplesSourcePath::from($additionalContext->config);
        $examplesDestPath = Config\GetExamplesDestPath::from($additionalContext->config);
        $defaultPhpVersions = Config\GetDefaultPhpVersionsList::from($additionalContext->config);
        $phpVersions = Config\GetSupportedPhpVersionsList::from($additionalContext->config);
        Helpers\HasPhpVersions::check($phpVersions);
        $phpDetails = Helpers\BuildListOfPhpVersions::from($phpVersions);

        // step 1: remove all the old example docs
        // in case some of them are no longer needed
        foreach (Helpers\FindFiles::from($examplesDestPath, []) as $filename) {
            Helpers\UnlinkFile::called($filename);
        }
        foreach (Helpers\FindFolders::from($examplesDestPath) as $filename) {
            Helpers\UnlinkFolder::called($filename);
        }

        // step 2: run the examples
        foreach (Helpers\FindCodeExamples::from($examplesSourcePath) as $filename) {
            // what's in the file?
            $blocks = ExtractBlocksFromExample::using($filename);

            // what happens when we run this example?
            $capturedOutput = RunExample::using(
                $filename,
                $blocks,
                $defaultPhpVersions,
                $phpVersions,
                $phpDetails
            );

            // what else do we need, to create the doc?
            $exampleTitle = ExtractExampleTitle::from($filename);
            $exampleId = ExtractExampleDomId::from($filename);

            // build the doc
            $doc = BuildDocumentedExample::from(
                $exampleTitle,
                $exampleId,
                trim(implode("\n", $blocks['preamble'])),
                trim(implode("\n", $blocks['example'])),
                $capturedOutput
            );

            // write it out to disk
            $destFilename = BuildExampleDocPath::from($filename, $examplesSourcePath, $examplesDestPath);
            Helpers\MakePath::to($destFilename);
            file_put_contents($destFilename, $doc);
        }

        // step 3: deal with any '.inc.php' files too
        //
        // we want to have the option of including them in the docs
        // if desired
        foreach(Helpers\FindCodeExamplesIncludeFiles::from($examplesSourcePath) as $filename) {
            $doc = BuildDocumentedExampleInc::from($filename);

            $destFilename = BuildExampleDocPath::from($filename, $examplesSourcePath, $examplesDestPath);
            Helpers\MakePath::to($destFilename);
            file_put_contents($destFilename, $doc);
        }
    }
}