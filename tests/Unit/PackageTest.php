<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Spec\Tests\Unit;

use LaravelJsonApi\Spec\ServiceProvider;
use PHPUnit\Framework\TestCase;

class PackageTest extends TestCase
{

    public function test(): void
    {
        $json = json_decode(file_get_contents(__DIR__ . '/../../composer.json'), true);

        $this->assertArrayHasKey('laravel', $json['extra']);
        $this->assertSame(
            ['providers' => [ServiceProvider::class]],
            $json['extra']['laravel']
        );
    }
}
