<?php

namespace ArtSkills\TestSuite\HttpClientMock;

use Cake\Http\Client\Request;
use Cake\Http\Client\Response;
use PHPUnit\Framework\ExpectationFailedException;

class HttpClientMocker
{
	/**
	 * Коллекция мокнутых вызовов
	 *
	 * @var HttpClientMockerEntity[]
	 */
	private static $_mockCallList = [];

	/**
	 * Сниф запросов и ответов
	 *
	 * @var array
	 */
	private static $_sniffList = [];

	/**
	 * Добавляем элемент
	 *
	 * @param array $element {
	 * @var Request $request
	 * @var Response $response
	 * }
	 */
	public static function addSniff($element)
	{
		self::$_sniffList[] = $element;
	}

	/**
	 * Выгружаем весь список запросов
	 *
	 * @return array
	 */
	public static function getSniffList()
	{
		return self::$_sniffList;
	}

	/**
	 * Чистим всё
	 *
	 * @param bool $hasFailed завалился ли тест
	 */
	public static function clean($hasFailed = false)
	{
		self::$_sniffList = [];

		try {
			if (!$hasFailed) {
				foreach (self::$_mockCallList as $mock) {
					$mock->callCheck();
				}
			}
		} finally {
			self::$_mockCallList = [];
		}
	}

	/**
	 * Мокаем HTTP запрос
	 *
	 * @param string|array $url Полная строка, либо массив вида ['урл', ['arg1' => 1, ...]]
	 * @param string $method
	 * @return HttpClientMockerEntity
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 */
	public static function mock($url, $method)
	{
		$mockId = self::_buildKey($url, $method);
		if (isset(self::$_mockCallList[$mockId])) {
			throw new ExpectationFailedException($method . ' ' . $url . ' is already mocked');
		}

		self::$_mockCallList[$mockId] = new HttpClientMockerEntity($url, $method);
		return self::$_mockCallList[$mockId];
	}

	/**
	 * Мок гет запроса
	 *
	 * @param string $url
	 * @param array $uriArgs
	 * @return HttpClientMockerEntity
	 */
	public static function mockGet($url, array $uriArgs = [])
	{
		if (count($uriArgs)) {
			$mockedUrl = $url . ((strpos($url, '?') === false) ? '?' : '&') . http_build_query($uriArgs);
		} else {
			$mockedUrl = $url;
		}

		return self::mock($mockedUrl, Request::METHOD_GET);
	}

	/**
	 * Мок пост запроса
	 *
	 * @param string $url
	 * @param array|string $expectedPostArgs
	 * @return HttpClientMockerEntity
	 */
	public static function mockPost($url, array $expectedPostArgs = [])
	{
		$mock = self::mock($url, Request::METHOD_POST);
		if (count($expectedPostArgs)) {
			$mock->expectBody($expectedPostArgs);
		}
		return $mock;
	}

	/**
	 * Проверяем на мок и возвращаем результат
	 *
	 * @param Request $request
	 * @return null|array ['response' => , 'status' => ]
	 */
	public static function getMockedData(Request $request)
	{
		foreach (self::$_mockCallList as $mock) {
			$url = (string)$request->getUri();
			$method = $request->getMethod();

			if ($mock->check($url, $method)) {
				$response = $mock->doAction($request);
				// doAction вызывается до getReturnStatusCode, потому что в нём статус может измениться
				$statusCode = $mock->getReturnStatusCode();
				return ['response' => $response, 'status' => $statusCode];
			}
		}

		return null;
	}

	/**
	 * Формируем уникальный ключ
	 *
	 * @param string $url
	 * @param string $method
	 * @return string
	 */
	private static function _buildKey($url, $method)
	{
		return $url . '#' . $method;
	}
}
