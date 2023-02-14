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

use LogicException;

class RelationBuilder extends Builder
{

    /**
     * @var string|null
     */
    private ?string $expectedType = null;

    /**
     * @var string|null
     */
    private ?string $expectedId = null;

    /**
     * Expect the document to be the provided resource type and id.
     *
     * @param string $resourceType
     * @param string $relation
     * @return $this
     */
    public function expects(string $resourceType, string $relation): self
    {
        $this->expectedType = $resourceType;
        $this->expectedId = $relation;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function create(object $json): Document
    {
        if ($this->expectedType) {
            return new RelationDocument(
                $json,
                $this->expectedType,
                $this->expectedId
            );
        }

        throw new LogicException('No expected resource type and relation name set.');
    }

    /**
     * @inheritDoc
     */
    protected function pipes(): array
    {
        return [
            Validators\RelationValidator::class,
        ];
    }
}
