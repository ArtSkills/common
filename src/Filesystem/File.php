<?php

namespace ArtSkills\Filesystem;

use ArtSkills\Error\InternalException;
use ArtSkills\Lib\Env;
use ArtSkills\Lib\Strings;

class File extends \Cake\Filesystem\File
{

	/**
	 * Зипует файлы
	 *
	 * @param string[]|string $files - файлы, которые нужно зипануть
	 * @param string|null $newFile - полное имя нового файла. если не передать, то будет self::DOWNLOAD_PATH . uniqid()
	 * @param bool $deleteOld - удалять ли файлы
	 * @return string Имя файла
	 * @throws InternalException
	 */
	public static function zip($files, $newFile = null, $deleteOld = false)
	{
		$files = (array)$files;

		if (empty($newFile)) {
			$defaultPath = Env::getDownloadPath();
			if (empty($defaultPath)) {
				throw new InternalException('Путь не задан явно и нет пути по-умолчанию');
			}
			Folder::createIfNotExists($defaultPath);
			$newFile = $defaultPath . uniqid();
		}
		if (!preg_match('/\.zip$/i', $newFile)) {
			$newFile .= '.zip';
		}

		$tmpDir = TMP . uniqid();
		mkdir($tmpDir);
		$zipFiles = [];
		$currentDir = getcwd();
		chdir($tmpDir);
		foreach ($files as $sourceFile) {
			$tmpFile = Strings::lastPart(DS, $sourceFile);
			$zipFiles[] = $tmpFile;
			if ($deleteOld) {
				rename($sourceFile, $tmpFile);
			} else {
				if (is_dir($sourceFile)) {
					exec("cp -r $sourceFile $tmpFile");
				} else {
					copy($sourceFile, $tmpFile);
				}
			}
		}
		if (file_exists($newFile)) {
			unlink($newFile);
		}

		exec('zip -r "' . $newFile . '" "' . implode('" "', $zipFiles) . '"');
		chdir($currentDir);
		exec("rm -rf $tmpDir");
		return $newFile;
	}

	/**
	 * Распаковать архив
	 * по умолчанию рядом с архивом
	 *
	 * @param string $pathToFile
	 * @param null|string $unzipFolder
	 * @throws \Exception
	 */
	public static function unZip($pathToFile, $unzipFolder = null)
	{
		$extension = strstr(pathinfo($pathToFile)['basename'], '.');
		if (!empty($unzipFolder)) {
			!file_exists($unzipFolder) ? mkdir($unzipFolder) : null;
		}
		switch ($extension) {
			case '.tar.gz':
				$unpackPath = !empty($unzipFolder) ? $unzipFolder : dirname($pathToFile);
				exec('tar -xf ' . $pathToFile . ' -C ' . $unpackPath);
				break;
			default:
				$unpackPath = !empty($unzipFolder) ? $unzipFolder : dirname($pathToFile);
				exec('unzip ' . $pathToFile . ' -d ' . $unpackPath);
				break;
		}
	}
}
