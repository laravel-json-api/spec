<?php
/*
 * Copyright 2023 Cloud Creativity Limited
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Spec;

use JsonException;
use LaravelJsonApi\Core\Exceptions\JsonApiException;

class JsonDecoder
{

    /**
     * @var Translator
     */
    private Translator $translator;

    /**
     * JsonDecoder constructor.
     *
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Decode a JSON string to a PHP object.
     *
     * @param string $json
     * @return object
     * @throws JsonApiException
     */
    public function decode(string $json): object
    {
        if (empty(trim($json))) {
            throw JsonApiException::make($this->translator->jsonEmpty());
        }

        try {
            if (is_string($json)) {
                $json = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
            }
        } catch (JsonException $ex) {
            throw JsonApiException::make(
                $this->translator->jsonError($ex),
                $ex,
            );
        }

        if (is_object($json)) {
            return $json;
        }

        throw JsonApiException::make($this->translator->jsonNotObject());
    }
}
