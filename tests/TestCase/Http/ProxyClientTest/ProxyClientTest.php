<?php
declare(strict_types=1);

namespace ArtSkills\Test\TestCase\Http\ProxyClientTest;

use ArtSkills\Http\ProxyClient;
use ArtSkills\TestSuite\AppTestCase;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;

class ProxyClientTest extends AppTestCase
{
    /**
     * Тестируем, что подставляются прокси
     *
     * @return void
     */
    public function testProxyClient(): void
    {
        $proxyClient = new ProxyClient();
        self::assertEquals(Configure::read(ProxyClient::CONFIG_FIELD_NAME), $proxyClient->getConfig('proxy'));
    }

    /**
     * Тестируем, что прокси используется
     * Тут должно выпасть исключение, т.к. прокси, что в конфиге тестов быть не должно
     *
     * @return void
     */
    public function testProxyUsed(): void
    {
        $proxyClient = new ProxyClient();

        $proxyAddress = Configure::read(ProxyClient::CONFIG_FIELD_NAME)['proxy'];
        $this->expectException(Exception::class);
        $this->expectExceptionMessageRegExp("/$proxyAddress/");
        $proxyClient->get('https://eggheads.solutions');
    }
}
