<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\PhpUnit\Extension;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;
use PHPUnit\Metadata\Group;
use Symfony\Bridge\PhpUnit\Attribute\TimeSensitive;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Bridge\PhpUnit\Metadata\AttributeReader;

/**
 * @internal
 */
class DisableClockMockSubscriber implements FinishedSubscriber
{
    public function __construct(
        private AttributeReader $reader,
    ) {
    }

    public function notify(Finished $event): void
    {
        $test = $event->test();

        if (!$test instanceof TestMethod) {
            return;
        }

        foreach ($test->metadata() as $metadata) {
            if ($metadata instanceof Group && 'time-sensitive' === $metadata->groupName()) {
                ClockMock::withClockMock(false);
                break;
            }
        }

        if ($this->reader->forClassAndMethod($test->className(), $test->methodName(), TimeSensitive::class)) {
            ClockMock::withClockMock(false);
        }
    }
}
