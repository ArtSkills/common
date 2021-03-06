<?php

namespace ArtSkills\Test\TestCase\Lib\EmailTest;

use ArtSkills\Mailer\Email;
use ArtSkills\Mailer\Transport\TestEmailTransport;
use ArtSkills\TestSuite\AppTestCase;

class EmailTest extends AppTestCase
{
	/**
	 * Тест на замену домена в адресе почты
	 * И работу перма-мокалки
	 */
	public function testChangeRecipient()
	{
		$text = 'test email text';
		$email = new Email();
		$email->setFrom('test@artskills.ru')
			->setTo('test@artskills.ru', 'test-name')
			->addTo('test@yandex.ru', 'yandex-test')
			->addTo('other@artskills.ru', 'artskills')
			->send($text);
		$emailData = TestEmailTransport::getMessages();
		$expectedData = [
			'to' => [
				'test@artskills-studio.ru' => 'test-name',
				'test@yandex.ru' => 'yandex-test',
				'other@artskills-studio.ru' => 'artskills',
			],
			'subject' => '',
			'template' => '',
			'layout' => 'default',
			'vars' => [],
			'message' => [$text, '', ''],
		];
		self::assertEquals([$expectedData], $emailData, 'Не изменился адрес почты');
	}

}