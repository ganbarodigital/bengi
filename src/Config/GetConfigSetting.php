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

namespace GanbaroDigital\Bengi\Config;

use GanbaroDigital\DataContainers\Filters\FilterDotNotationPath;

/**
 * extract a setting from our loaded config
 *
 * this will expand any {dot.notation.path} sections
 */
class GetConfigSetting
{
    public static function from($config, $path, $defaultValue)
    {
        // step 1: do we have a value at the expected place?
        try {
            $value = FilterDotNotationPath::from($config, $path);
        }
        catch (\Exception $e) {
            $value = $defaultValue;
        }

        // step 2: does it contain a path that needs expanding?
        if (!is_string($value)) {
            return $value;
        }
        if (!preg_match_all('/{([^}]+)}/', $value, $matches, PREG_OFFSET_CAPTURE)) {
            // do, it does not
            return $value;
        }

        // if we get here, then we have a set of paths to expand
        $offsetShift = 0;
        foreach ($matches[0] as $matchIndex => $match) {
            $matchPath = $matches[1][$matchIndex][0];
            $matchLen = strlen($match[0]);
            $matchValue = GetConfigSetting::from($config, $matchPath, '');

            $value = substr($value, 0, $match[1] + $offsetShift) . $matchValue . substr($value, $match[1] + $matchLen + $offsetShift);

            // the offsets that our REGEX found are now inaccurage,
            // we have just changed the length of our $value string
            $offsetShift = $offsetShift + strlen($matchValue) - $matchLen;
        }

        // all done
        return $value;
    }
}