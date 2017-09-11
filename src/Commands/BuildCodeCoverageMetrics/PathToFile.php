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

use GanbaroDigital\Bengi\Config;
use Phix_Project\CliEngine;
use Phix_Project\CliEngine\CliResult;
use Phix_Project\CliEngine\CliSwitch;

/**
 * switch used to tell us which file to load
 */
class PathToFile extends CliSwitch
{
    public function __construct($additionalContext)
    {
        // define our name, and our description
        $this->setName('file');
        $this->setShortDescription('the clover XML file to use');
        $this->setLongDesc(
            "Use this switch to tell us which file contains the XML output"
            . " that you want to use."
            . PHP_EOL . PHP_EOL
            . "PHPUnit can produce an XML file containing code coverage"
            . " metrics for your tests and source code. This file also"
            . " includes some additional metrics such as the CRAP index."
            .PHP_EOL . PHP_EOL
            . " By default, we look for this file in the location that"
            . " Ganbaro Digital's phpunix.xml.dist file always puts it."
            . " If you have your own convention, use this switch to tell us"
            . " where to load the XML from."
        );

        // how do you access this?
        $this->addShortSwitch('f');
        $this->addLongSwitch('file');

        // what is our parameter?
        $this->setRequiredArg('<file>', "the file to read from");
        $this->setArgHasDefaultValueOf(Config\GetCloverXmlPath::from($additionalContext->config));
    }

    public function process(CliEngine $engine, $invokes = 1, $params = array(), $isDefaultParam = false, $additionalContext = null)
    {
        // update our config
        Config\SetCloverXmlPath::in($additionalContext->config, $params[0]);

        // tell the engine that it is done
        return new CliResult(CliResult::PROCESS_CONTINUE);
    }
}