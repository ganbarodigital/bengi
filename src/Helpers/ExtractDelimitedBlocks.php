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
 * break up content based on the delimiters in there
 */
class ExtractDelimitedBlocks
{
    public static function from(array $lines, array $markers)
    {
        // how will we find the markers?
        list($startRegex, $endRegex) = static::buildRegexes($markers);

        // this will hold the separated out content
        $blocks=[];

        // this will hold a list of which keys are active atm
        $addKeys = [];

        // let's go looking!
        foreach ($lines as $line)
        {
            // does the line have any delimiters sets?
            preg_match($startRegex, $line, $startMatches);
            preg_match($endRegex, $line, $endMatches);

            // filter out non-numeric keys
            $startMatches = array_filter($startMatches, 'is_string', ARRAY_FILTER_USE_KEY);
            $endMatches = array_filter($endMatches, 'is_string', ARRAY_FILTER_USE_KEY);

            // filter out empty values
            $startMatches = array_filter($startMatches);
            $endMatches = array_filter($endMatches);

            // have we finished with any blocks?
            foreach ($endMatches as $block => $delimiter) {
                unset($addKeys[$block]);
            }

            // add this line to any active blocks
            //
            // if we've been given a filter function, we use that
            foreach ($addKeys as $block => $dummy) {
                $blocks[$block][] = isset($markers[$block][2]) ? $markers[$block][2]($line) : $line;
            }

            // have we started capturing any new blocks?
            foreach ($startMatches as $block => $delimiter) {
                $addKeys[$block] = true;
            }
        }

        // all done
        return $blocks;
    }

    protected static function buildRegexes(array $markers)
    {
        // what regexes will we use to search for blocks?
        $startRegex = "¬";
        $endRegex = '¬';

        foreach ($markers as $blockName => $delimiters) {
            $startRegexes[] = "(?<{$blockName}>{$delimiters[0]})";
            $endRegexes[] = "(?<{$blockName}>{$delimiters[1]})";
        }

        return [
            '/' . implode('|', $startRegexes) . "/",
            '/' . implode('|', $endRegexes) . "/",
        ];
    }
}