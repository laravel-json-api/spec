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

class RelationDocument extends Document
{

    /**
     * @var string
     */
    private string $resourceType;

    /**
     * @var string|null
     */
    private string $relation;

    /**
     * RelationDocument constructor.
     *
     * @param object $document
     * @param string $resourceType
     * @param string $relation
     */
    public function __construct(object $document, string $resourceType, string $relation)
    {
        parent::__construct($document);
        $this->resourceType = $resourceType;
        $this->relation = $relation;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return $this->resourceType;
    }

    /**
     * @return string
     */
    public function relation(): string
    {
        return $this->relation;
    }

}
