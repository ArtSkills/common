<?php
declare(strict_types=1);

namespace ArtSkills\Controller;

use ArtSkills\Lib\Env;
use ArtSkills\Lib\Shell;
use ArtSkills\Lib\Strings;
use Cake\Cache\Cache;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use DateTime;

/**
 * Контроллер для генерации css файлов на основе scss. Данные зависимости прописываются в AssetHelper.php, который автоматически
 * подменияет расширения с scss на css, а в routes.php прописывается обработка по таким запросам.
 * Настройка в файле routes.php:
 * ```php
 * $routes->connect('/css/**', ['controller' => 'Scss', 'action' => 'view'  ]);
 * ```
 * Профиль кеширования прописывается в app.php: `'scssCacheProfile' => 'default', // default по-умолчанию`.
 *
 * Требуется для работы установка команды sass
 *
 * @see https://sass-lang.com/install
 */
class ScssController extends Controller
{
    private const EXTENSION_CSS = '.css';
    private const EXTENSION_SCSS = '.scss';

    /**
     * Обработчик всех запросов по указанному пути
     *
     * @return Response|null
     */
    public function view(): ?Response
    {
        $scssFilePath = Strings::replaceIfEndsWith(parse_url($this->request->getRequestTarget(), PHP_URL_PATH), self::EXTENSION_CSS, self::EXTENSION_SCSS);
        $scssFilePath = str_replace('../', '', Strings::replaceIfStartsWith($scssFilePath, '/', ''));

        $absFilePath = WWW_ROOT . $scssFilePath;
        if (!file_exists($absFilePath)) {
            throw new NotFoundException();
        }

        $cacheProfile = Env::getScssCacheProfile(); // @phpstan-ignore-line
        if (!$cacheProfile) {
            $cacheProfile = 'default';
        }

        $resultText = Cache::remember($absFilePath . '#' . filemtime($absFilePath), function () use ($scssFilePath) {
            $styleFilePath = Strings::replacePostfix($scssFilePath, self::EXTENSION_SCSS, self::EXTENSION_CSS);
            $cmd = 'sass --style=compressed --embed-source-map "' . WWW_ROOT . $scssFilePath . '" "' . WWW_ROOT . $styleFilePath . '"';

            $result = Shell::exec($cmd);
            if (!$result[0]) {
                $this->_throwInternalError(implode("\n", $result[1]));
            }

            $absResultPath = WWW_ROOT . $styleFilePath;
            if (!file_exists($absResultPath)) {
                $this->_throwInternalError("Result CSS file is not created");
            }
            $resultText = file_get_contents($absResultPath);
            unlink($absResultPath);
            return $resultText;
        }, $cacheProfile);

        $this->response = $this->response->withExpires(new DateTime('+1 day'));
        return $this->_sendTextResponse($resultText, 'css');
    }
}
