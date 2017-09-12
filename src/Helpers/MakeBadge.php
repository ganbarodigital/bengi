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
 * generate a badge to include in the docs
 */
class MakeBadge
{
    public static function using($text, $value, $color, $pathToBadges, $style="flat-square")
    {
        // we cache the badges on disk to avoid hitting shields.io all
        // the time
        $badgeFilename = BuildBadgeFilename::from($text, $value, $pathToBadges, $style);
        if (file_exists($badgeFilename)) {
            return;
        }

        $badge = false;
        $retryCount = 5;
        while (!$badge || empty($badge)) {
            $downloadFunc = function() use (&$badge, $text, $value, $color, $style) {
                return file_get_contents("https://img.shields.io/badge/{$text}-{$value}-{$color}.svg?style={$style}");
            };
            $onFailure = function($errorMessage) {
                echo "*** warning: there was a problem downloading a badge" . PHP_EOL
                . PHP_EOL
                . "The error message was:" . PHP_EOL
                . '- ' . $errorMessage . PHP_EOL
                . PHP_EOL
                . "Retrying ..." . PHP_EOL;

                return false;
            };
            $badge = TrapLegacyErrors::call($downloadFunc, $onFailure);
            if (!$badge || empty($badge)) {
                $retryCount--;

                if ($retryCount === 0) {
                    echo "*** error: too many retries; giving up" . PHP_EOL;
                    exit(1);
                }

                // if we get here, we're going to give it another go
                sleep(1);
            }
        }

        MakePath::to($badgeFilename);
        file_put_contents($badgeFilename, $badge);

        // echo "- downloaded badge {$badgeFilename}" . PHP_EOL;
    }
}