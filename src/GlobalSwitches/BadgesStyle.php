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

namespace GanbaroDigital\Bengi\GlobalSwitches;

use GanbaroDigital\Bengi\Config;
use Phix_Project\CliEngine;
use Phix_Project\CliEngine\CliResult;
use Phix_Project\CliEngine\CliSwitch;

/**
 * change the default style for downloaded badges
 */
class BadgesStyle extends CliSwitch
{
    public function __construct($additionalContext)
    {
        // define our name, and our description
        $this->setName('style');
        $this->setShortDescription('the badge style to use');
        $this->setLongDesc(
            "Use this switch to tell us which style of shields.io badge"
            . " you want to use."
            . PHP_EOL . PHP_EOL
            . "bengi downloads badges from http://shields.io to include in"
            . " your documentation. Supported styles include:" . PHP_EOL
            . PHP_EOL
            . "- plastic" . PHP_EOL
            . "- flat" . PHP_EOL
            . "- flat-square" . PHP_EOL
            . "- social" . PHP_EOL
            . PHP_EOL
            . "Note: If you want to change badge styles, you will need to"
            . " manually remove all the badges that have been downloaded."
            . " bengi won't remove them for you."
        );

        // how do you access this?
        $this->addLongSwitch('badge-style');

        // what is our parameter?
        $this->setRequiredArg('<style>', "the badge style to use");
        $this->setArgHasDefaultValueOf(Config\GetBadgesStyle::from($additionalContext->config));
    }

    public function process(CliEngine $engine, $invokes = 1, $params = array(), $isDefaultParam = false, $additionalContext = null)
    {
        // update our config
        Config\SetBadgesStyle::to($additionalContext->config, $params[0]);

        // tell the engine that it is done
        return new CliResult(CliResult::PROCESS_CONTINUE);
    }
}