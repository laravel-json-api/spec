<?php
/*
 * Copyright 2022 Cloud Creativity Limited
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

namespace LaravelJsonApi\Spec\Values;

use Illuminate\Support\Str;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Core\Document\Error;
use LaravelJsonApi\Core\Document\ErrorList;
use LaravelJsonApi\Spec\Specification;
use LaravelJsonApi\Spec\Translator;
use LogicException;

class Identifier extends Value
{

    /**
     * @var Specification
     */
    private Specification $spec;

    /**
     * @var Translator
     */
    private Translator $translator;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var Relation|null
     */
    private ?Relation $relation;

    /**
     * Is the provided id empty?
     *
     * @param string|null $id
     * @return bool
     */
    public static function idIsEmpty(?string $id): bool
    {
        if (null === $id) {
            return true;
        }

        return '0' !== $id && empty(trim($id));
    }

    /**
     * Identifier constructor.
     *
     * @param Specification $spec
     * @param Translator $translator
     * @param string $path
     * @param mixed $value
     * @param Relation|null $relation
     *      the relation the identifier is in.
     */
    public function __construct(
        Specification $spec,
        Translator $translator,
        string $path,
        $value,
        Relation $relation = null
    ) {
        $this->spec = $spec;
        $this->translator = $translator;
        $this->path = $path;
        $this->value = $value;
        $this->relation = $relation;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        if ($this->valid()) {
            return $this->value->type;
        }

        throw new LogicException('Invalid resource identifier.');
    }

    /**
     * @return string
     */
    public function id(): string
    {
        if ($this->valid()) {
            return $this->value->id;
        }

        throw new LogicException('Invalid resource identifier.');
    }

    /**
     * @return ErrorList
     */
    protected function validate(): ErrorList
    {
        $errors = new ErrorList();

        if (!is_object($this->value)) {
            return $errors->push($this->translator->memberNotObject(
                $this->parent(),
                $this->member()
            ));
        }

        if ($error = $this->validateType()) {
            $errors->push($error);
        }

        if ($error = $this->validateId()) {
            $errors->push($error);
        }

        if (property_exists($this->value, 'attributes') || property_exists($this->value, 'relationships')) {
            $errors->push($this->translator->memberNotIdentifier(
                $this->parent(),
                $this->member()
            ));
        }

        if ($errors->isEmpty() && $error = $this->validateTypeAndId()) {
            $errors->push($error);
        }

        return $errors;
    }

    /**
     * Validate just the type member.
     *
     * @return Error|null
     */
    private function validateType(): ?Error
    {
        if (!property_exists($this->value, 'type')) {
            return $this->translator->memberRequired(
                $this->path,
                'type'
            );
        }

        if (!is_string($this->value->type)) {
            return $this->translator->memberNotString(
                $this->path,
                'type'
            );
        }

        if (empty($this->value->type)) {
            return $this->translator->memberEmpty(
                $this->path,
                'type'
            );
        }

        if (!collect($this->spec->types())->contains($this->value->type)) {
            return $this->translator->resourceTypeNotRecognised(
                $this->value->type,
                $this->path
            );
        }

        return null;
    }

    /**
     * Validate just the id member.
     *
     * @return Error|null
     */
    private function validateId(): ?Error
    {
        if (!property_exists($this->value, 'id')) {
            return $this->translator->memberRequired(
                $this->path,
                'id'
            );
        }

        $id = $this->value->id;

        if (!is_string($id)) {
            return $this->translator->memberNotString(
                $this->path,
                'id'
            );
        }

        if (self::idIsEmpty($id)) {
            return $this->translator->memberEmpty(
                $this->path,
                'id'
            );
        }

        return null;
    }

    /**
     * Validations to run when both the type and id are separately valid.
     *
     * @return Error|null
     */
    private function validateTypeAndId(): ?Error
    {
        if ($this->relation && !in_array($this->value->type, $this->relation->allInverse(), true)) {
            return $this->translator->resourceTypeNotSupportedByRelationship(
                $this->relation,
                $this->pathForTypeAndId(),
            );
        }

        if (!$this->spec->exists($this->value->type, $this->value->id)) {
            return $this->translator->resourceDoesNotExist(
                $this->pathForTypeAndId(),
            );
        }

        return null;
    }

    /**
     * Is the identifier in a list of identifiers?
     *
     * @return bool
     */
    private function inList(): bool
    {
        return is_numeric($this->member());
    }

    /**
     * @return bool
     */
    private function inRelationships(): bool
    {
        return Str::contains($this->path, '/relationships/');
    }

    /**
     * Get the path to use if the resource does not exist.
     *
     * @return string
     */
    private function pathForTypeAndId(): string
    {
        if ($this->inList()) {
            return $this->path;
        }

        if ($this->inRelationships()) {
            return $this->parent();
        }

        return $this->path;
    }

}
