<?php
declare(strict_types=1);

namespace ArtSkills\Test\TestCase\Shell\ValueObjectDocumentationShellTest;

use ArtSkills\Shell\ValueObjectDocumentationShell;
use ArtSkills\TestSuite\AppTestCase;
use Cake\Error\BaseErrorHandler;
use Eggheads\Mocks\MethodMocker;
use Cake\Console\Shell;
use Cake\Log\Log;
use OpenApi\Loggers\DefaultLogger;
use Psr\Log\LoggerInterface;

class ValueObjectDocumentationShellTest extends AppTestCase
{
    /**
     * @throws \Exception
     */
    public function testMain(): void
    {
        $shell = new ValueObjectDocumentationShell();
        $resultFile = __DIR__ . DS . 'results.txt';
        file_put_contents($resultFile, '1');

        MethodMocker::mock(Shell::class, 'out')
            ->singleCall()
            ->expectArgs("You has new data, sync $resultFile from server");

        MethodMocker::mock(DefaultLogger::class, 'log')
            ->expectCall(3)
            ->expectArgsList([
                ['warning', '@OA\Items() is required when @OA\Property(property="intFloatArrayProperty") has type "array" in \ArtSkills\Test\TestCase\Shell\ValueObjectDocumentationShellTest\Entities\Object1->intFloatArrayProperty in /var/www/tests/TestCase/Shell/ValueObjectDocumentationShellTest/Entities/Object1.php on line 11', []],
                ['warning', '@OA\Items() is required when @OA\Property(property="intFloatArrayRevertedProperty") has type "array" in \ArtSkills\Test\TestCase\Shell\ValueObjectDocumentationShellTest\Entities\Object1->intFloatArrayRevertedProperty in /var/www/tests/TestCase/Shell/ValueObjectDocumentationShellTest/Entities/Object1.php on line 11', []],
                ['warning', '@OA\Items() is required when @OA\Property(property="nullableIntFloatArrayProperty") has type "array" in \ArtSkills\Test\TestCase\Shell\ValueObjectDocumentationShellTest\Entities\Object1->nullableIntFloatArrayProperty in /var/www/tests/TestCase/Shell/ValueObjectDocumentationShellTest/Entities/Object1.php on line 11', []],
            ]);

        MethodMocker::mock(Log::class, 'error')
            ->expectCall(4)
            ->expectArgsList([
                ['Incorrect property type for ArtSkills\Test\TestCase\Shell\ValueObjectDocumentationShellTest\Entities\Object1::prop1'],
                ['Incorrect property type for ArtSkills\Test\TestCase\Shell\ValueObjectDocumentationShellTest\Entities\Object1::intFloatArrayProperty'],
                ['Incorrect property type for ArtSkills\Test\TestCase\Shell\ValueObjectDocumentationShellTest\Entities\Object1::intFloatArrayRevertedProperty'],
                ['Incorrect property type for ArtSkills\Test\TestCase\Shell\ValueObjectDocumentationShellTest\Entities\Object1::nullableIntFloatArrayProperty'],
            ]);

        $shell->main(__DIR__ . DS . 'Entities', $resultFile);
        self::assertFileEquals(__DIR__ . DS . 'expected.txt', $resultFile);
        unlink($resultFile);
    }
}
