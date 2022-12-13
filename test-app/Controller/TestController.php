<?php
declare(strict_types=1);

namespace TestApp\Controller;

use ArtSkills\Controller\Controller;
use ArtSkills\Error\InternalException;
use ArtSkills\Error\UserException;
use Exception;
use PHPUnit\Framework\AssertionFailedError;

class TestController extends Controller
{

    /** @inheritdoc */
    protected array $_jsonResponseActions = ['getStandardErrorJsonConfigured'];

    /** @inheritDoc */
    public function initialize()
    {
        $this->loadComponent('Flash');
        $this->loadComponent('RequestHandler', [
            'enableBeforeRedirect' => false,
        ]);
        parent::initialize();
    }

    /**
     * Успешный JSON ответ
     *
     * @return null
     */
    public function getJsonOk()
    {
        return $this->_sendJsonOk(['testProperty' => 123]);
    }

    /**
     * Сообщение об ошибке
     *
     * @return NULL
     */
    public function getJsonError()
    {
        return $this->_sendJsonError('Тестовая ошибка', ['errorProperty' => 123]);
    }

    /**
     * JSON ответ из ValueObject
     */
    public function getValueObjectJson()
    {
        return $this->_sendJsonOk((new TestValueObject()));
    }

    /**
     * JSON ответ из ValueObject с трейтами
     */
    public function getTraitedValueObjectJson()
    {
        return $this->_sendJsonOk((new TestValueObjectWithTrait()));
    }

    /**
     * JSON ответ из ApiResponse
     */
    public function getApiResponseJson()
    {
        return $this->_sendJsonOk((new TestApiResponse()));
    }

    /**
     * Null в ответ
     *
     * @return null
     */
    public function getEmptyJson()
    {
        return $this->_sendJsonResponse([]);
    }

    /**
     * Ошибка из ексепшна
     *
     * @return null
     */
    public function getJsonException()
    {
        return $this->_sendJsonException(new Exception('test exception'), ['someData' => 'test']);
    }

    /**
     * Ошибки phpunit прокидываются дальше
     *
     * @return null
     */
    public function getJsonExceptionUnit()
    {
        return $this->_sendJsonException(new AssertionFailedError('test unit exception'), ['someData' => 'test']);
    }

    /**
     * Стандартная обработка ошибок, json
     *
     * @throws UserException
     */
    public function getStandardErrorJson(): void
    {
        $this->_setIsJsonAction();
        $this->_throwUserError('test json message');
    }

    /**
     * Стандартная обработка ошибок, json, немного сконфигурированная обработка
     *
     * @throws UserException
     */
    public function getStandardErrorJsonConfigured()
    {
        throw UserException::instance('log message')
            ->setUserMessage('user message')
            ->setLogScope('some scope')
            ->setLogAddInfo('some info')
            ->setAlert(false);
    }

    /**
     * Стандартная обработка ошибок, html, flash
     *
     * @throws UserException
     */
    public function getStandardErrorFlash()
    {
        $this->_throwUserError('test flash message');
    }

    /**
     * Стандартная обработка ошибок, flash, редирект
     *
     * @throws UserException
     */
    public function getStandardErrorRedirect()
    {
        $this->_throwUserErrorRedirect('test other flash message', '/test/getJsonOk');
    }

    /**
     * Внутренняя ошибка, отдаёт 5хх
     *
     * @throws InternalException
     */
    public function getInternalError()
    {
        $this->_throwInternalError('test internal error');
    }

    /**
     * Внутренняя ошибка, отдаёт json
     *
     * @throws InternalException
     */
    public function getInternalErrorJson()
    {
        $this->_setIsJsonAction();
        $this->_throwInternalError('test json message');
    }

    /**
     * Внутренняя ошибка, json, проверяем трейс
     *
     * @throws InternalException
     */
    public function getInternalErrorJsonTrace()
    {
        $this->_setIsJsonAction();
        throw new InternalException('test trace');
    }

    /** Тестируем обращение к экшну с разными регистрами букв */
    public function testName()
    {
    }
}
