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

use InvalidArgumentException;

final class JsonApiSpec
{

    /**
     * The namespace for translations.
     *
     * @var string
     */
    public static string $translationNamespace = 'jsonapi-spec';

    /**
     * The specification class to use.
     *
     * @var string
     */
    public static string $specification = ServerSpecification::class;

    /**
     * Set the translation namespace.
     *
     * @param string $namespace
     * @return static
     */
    public static function useTranslationNamespace(string $namespace): self
    {
        if (empty($namespace)) {
            throw new InvalidArgumentException('Expecting a non-empty string.');
        }

        self::$translationNamespace = $namespace;

        return new self();
    }

    /**
     * Set the specification class to use.
     *
     * @param string $fqn
     * @return static
     */
    public static function useSpecification(string $fqn): self
    {
        if (empty($fqn)) {
            throw new InvalidArgumentException('Expecting a non-empty string.');
        }

        self::$specification = $fqn;

        return new self();
    }
}
