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

use LaravelJsonApi\Spec\ResourceDocument;
use LaravelJsonApi\Spec\Translator;

class DataValidator
{

    /**
     * @var Translator
     */
    private Translator $translator;

    /**
     * DataMember constructor.
     *
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Validate the `/data` member of the document.
     *
     * @param ResourceDocument $document
     * @param \Closure $next
     * @return ResourceDocument
     */
    public function validate(ResourceDocument $document, \Closure $next): ResourceDocument
    {
        if (!isset($document->data)) {
            $document->errors()->push(
                $this->translator->memberRequired('/', 'data')
            );
            return $document;
        }

        $data = $document->data;

        if (is_object($data)) {
            return $next($document);
        }

        $document->errors()->push(
            $this->translator->memberNotObject('/', 'data')
        );

        return $document;
    }
}
