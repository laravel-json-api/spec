<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Spec;

use LaravelJsonApi\Contracts\Spec\ResourceDocumentComplianceChecker as ResourceDocumentComplianceCheckerContract;
use LaravelJsonApi\Contracts\Support\Result as ResultContract;
use LaravelJsonApi\Core\Exceptions\JsonApiException;
use LaravelJsonApi\Core\Support\Result;
use LaravelJsonApi\Core\Values\ResourceId;
use LaravelJsonApi\Core\Values\ResourceType;

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
    public function mustSee(ResourceType|string $type, ResourceId|string $id = null): static
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
