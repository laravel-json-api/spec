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

namespace LaravelJsonApi\Spec\Validators;

use LaravelJsonApi\Core\Document\Error;
use LaravelJsonApi\Spec\ResourceDocument;
use LaravelJsonApi\Spec\Translator;

class TypeValidator
{

    /**
     * @var Translator
     */
    private Translator $translator;

    /**
     * TypeValidator constructor.
     *
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Validate the `/data/type` member of the document.
     *
     * @param ResourceDocument $document
     * @param \Closure $next
     * @return ResourceDocument
     */
    public function validate(ResourceDocument $document, \Closure $next): ResourceDocument
    {
        $data = $document->data;

        if (!property_exists($data, 'type')) {
            $document->errors()->push(
                $this->translator->memberRequired('/data', 'type')
            );
            return $document;
        }

        if ($error = $this->accept($document->type(), $data->type)) {
            $document->errors()->push($error);
            return $document;
        }

        return $next($document);
    }

    /**
     * @param string $expected
     * @param $value
     * @return Error|null
     */
    private function accept(string $expected, $value): ?Error
    {
        if (!is_string($value)) {
            return $this->translator->memberNotString('/data', 'type');
        }

        if (empty($value)) {
            return $this->translator->memberEmpty('/data', 'type');
        }

        if ($expected !== $value) {
            return $this->translator->resourceTypeNotSupported($value);
        }

        return null;
    }
}
