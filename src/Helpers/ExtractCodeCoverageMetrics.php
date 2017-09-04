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

use DateTime;

/**
 * make (some) sense of a phpunit XML code coverage metrics file
 */
class ExtractCodeCoverageMetrics
{
    public static function from(string $path)
    {
        // let's load it up
        $xml = simplexml_load_file($path);

        // global stats
        $when = DateTime::createFromFormat("U", (string)$xml->project['timestamp']);

        // extract the per-method, per-function stats
        $functionStats=[];
        $classStats=[];

        foreach ($xml->project->children() as $projectNode) {
            switch ((string)$projectNode->getName()) {
                case 'file':
                    // echo "Found a file node" . PHP_EOL;
                    // do nothing for now - the clover file does not contain
                    // code coverage information for individual functions
                    break;
                case 'package':
                    // echo "Found a package node" . PHP_EOL;
                    foreach ($projectNode->file as $fileNode) {
                        //echo "Parsing file node" . PHP_EOL;
                        $classStats = array_merge_keys($classStats, static::parsePackageFileNode($fileNode));
                    }
                    break;
                default:
                    //echo $projectNode->getName() . PHP_EOL;
            }
        }

        // all done
        return [$classStats, $functionStats];
    }

    // parse the contents of a file
    protected static function parsePackageFileNode($fileNode)
    {
        $retval = [];
        $className = null;
        $metrics = [];

        $currentMethod = null;
        $currentVisibility = null;

        foreach ($fileNode->children() as $node) {
            // echo $node->getName() . PHP_EOL;
            switch ((string)$node->getName()) {
                case 'class':
                    // which class are we looking at?
                    $partialClassName = (string)$node['name'];
                    $namespace = (string)$node['namespace'];
                    $className = $namespace . '\\' . $partialClassName;

                    // what metrics are reported?
                    foreach ($node->metrics->attributes() as $name => $value) {
                        $metrics[(string)$name] = intval($value);
                    }

                    break;
                case 'line':
                    // parse the line
                    switch ((string)$node['type']) {
                        case 'method':
                            $currentMethod = (string)$node['name'];
                            $retval[$className][$currentMethod] = [
                                'visibility' => (string)$node['visibility'],
                                'complexity' => intval($node['complexity']),
                                'CRAP' => intval($node['crap']),
                                'noOfLines' => 0,
                                'noOfCoveredLines' => 0,
                            ];
                            break;

                        case 'stmt':
                            $retval[$className][$currentMethod]['noOfLines']++;
                            if (intval($node['count']) > 0) {
                                $retval[$className][$currentMethod]['noOfCoveredLines']++;
                            }
                            break;
                    }
                    break;
            }
        }

        // all done
        return $retval;
    }
}