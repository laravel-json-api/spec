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

use LaravelJsonApi\Contracts\Spec\ResourceDocumentComplianceChecker as ResourceDocumentComplianceCheckerContract;
use LaravelJsonApi\Contracts\Support\Result as ResultContract;
use LaravelJsonApi\Core\Document\Input\Values\ResourceId;
use LaravelJsonApi\Core\Document\Input\Values\ResourceType;
use LaravelJsonApi\Core\Exceptions\JsonApiException;
use LaravelJsonApi\Core\Support\Result;

class ResourceDocumentComplianceChecker implements ResourceDocumentComplianceCheckerContract
{
    /**
     * ResourceDocumentComplianceChecker constructor
     *
     * @param ResourceBuilder $builder
     */
    public function __construct(private readonly ResourceBuilder $builder)
    {
    }

    /**
     * @inheritDoc
     */
    public function mustSee(string|ResourceType $type, ResourceId|string $id = null): static
    {
        $id = ($id === null) ? null : (string) $id;

        $this->builder->expects((string) $type, $id);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function check(string $json): ResultContract
    {
        try {
            $result = $this->builder->build($json);
        } catch (JsonApiException $ex) {
            return Result::failed($ex->getErrors());
        }

        if ($result->invalid()) {
            return Result::failed($result->errors());
        }

        return Result::ok();
    }
}
