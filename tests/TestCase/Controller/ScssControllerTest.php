<?php
declare(strict_types=1);

namespace ArtSkills\Test\TestCase\Controller;

use ArtSkills\TestSuite\AppControllerTestCase;
use ArtSkills\TestSuite\Mock\MethodMocker;
use ArtSkills\TestSuite\PermanentMocks\MockConsoleIo;
use ArtSkills\TestSuite\PermanentMocks\MockLog;

class ScssControllerTest extends AppControllerTestCase
{
    private const TEST_FILE_NAME = 'css/test.scss';

    /** @inheritDoc */
    public function setUp()
    {
        file_put_contents(WWW_ROOT . self::TEST_FILE_NAME, '.style1 {font-weight: bold}');
        parent::setUp();
    }

    /** @inheritDoc */
    public function tearDown()
    {
        unlink(WWW_ROOT . self::TEST_FILE_NAME);
        parent::tearDown();
    }

    /** Тест генератора css */
    public function test(): void
    {
        MethodMocker::mock(MockLog::class, 'write')
            ->singleCall()
            ->willReturnValue(true);

        $this->get('/css/123.css');

        $this->get('/' . self::TEST_FILE_NAME);
        $stringBody = (string)$this->_response->getBody();
        self::assertContains(".style1{font-weight:bold}", $stringBody);
        self::assertContains('sourceMappingURL', $stringBody);
    }
}
