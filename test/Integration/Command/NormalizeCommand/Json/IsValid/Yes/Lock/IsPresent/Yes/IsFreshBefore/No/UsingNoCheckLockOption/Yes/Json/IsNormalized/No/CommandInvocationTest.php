<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2023 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/composer-normalize
 */

namespace Ergebnis\Composer\Normalize\Test\Integration\Command\NormalizeCommand\Json\IsValid\Yes\Lock\IsPresent\Yes\IsFreshBefore\No\UsingNoCheckLockOption\Yes\Json\IsNormalized\No;

use Ergebnis\Composer\Normalize\Command;
use Ergebnis\Composer\Normalize\NormalizePlugin;
use Ergebnis\Composer\Normalize\Test;
use Ergebnis\Composer\Normalize\Version;
use PHPUnit\Framework;
use Symfony\Component\Console;

#[Framework\Attributes\CoversClass(Command\NormalizeCommand::class)]
#[Framework\Attributes\CoversClass(NormalizePlugin::class)]
#[Framework\Attributes\UsesClass(Version::class)]
final class CommandInvocationTest extends Test\Integration\Command\NormalizeCommand\AbstractTestCase
{
    #[Framework\Attributes\DataProviderExternal(Test\DataProvider\Command\NormalizeCommandProvider::class, 'commandInvocation')]
    public function testSucceeds(Test\Util\CommandInvocation $commandInvocation): void
    {
        $scenario = self::createScenario(
            $commandInvocation,
            __DIR__ . '/fixture',
        );

        $initialState = $scenario->initialState();

        self::assertComposerJsonFileExists($initialState);
        self::assertComposerLockFileExists($initialState);
        self::assertComposerLockFileNotFresh($initialState);

        $application = self::createApplicationWithNormalizeCommandAsProvidedByNormalizePlugin();

        $input = new Console\Input\ArrayInput($scenario->consoleParametersWith([
            '--no-check-lock' => true,
        ]));

        $output = new Console\Output\BufferedOutput();

        $exitCode = $application->run(
            $input,
            $output,
        );

        $expected = \sprintf(
            'Successfully normalized %s.',
            $scenario->composerJsonFileReference(),
        );

        self::assertStringContainsString($expected, $output->fetch());
        self::assertExitCodeSame(0, $exitCode);

        $currentState = $scenario->currentState();

        self::assertComposerJsonFileModified($initialState, $currentState);
        self::assertComposerLockFileModified($initialState, $currentState);
        self::assertComposerLockFileFresh($currentState);
    }
}
