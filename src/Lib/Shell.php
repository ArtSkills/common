<?php
namespace ArtSkills\Lib;

use ArtSkills\Traits\Library;

class Shell
{
	use Library;

	/**
	 * Выполнить команду в консоли
	 *
	 * @param string|string[] $commands
	 * @param bool $withErrors перенаправлять stderr в stdout
	 * @param bool $stopOnFail если передан список команд, то останавливаться ли на ошибке (склеивать команды через && или ;)
	 * @return array [успех, вывод, результирующая команда, код возврата]
	 */
	public static function exec($commands, $withErrors = true, $stopOnFail = true) {
		$commands = (array)$commands;
		if ($stopOnFail) {
			$glue = ' && ';
		} else {
			$glue = ' ; ';
		}
		if ($withErrors) {
			$errorRedirect = ' 2>&1';
			$glue = $errorRedirect . $glue;
			$resultCommand = implode($glue, $commands) . $errorRedirect;
		} else {
			$resultCommand = implode($glue, $commands);
		}
		return self::_exec($resultCommand);
	}

	/**
	 * Запустить и не ждать выполнения
	 *
	 * @param string $command
	 * @param string $outputRedirect
	 */
	public static function execInBackground($command, $outputRedirect = '/dev/null') {
		exec('nohup ' . $command . ' 2>&1 > ' . escapeshellarg($outputRedirect) . ' &');
	}

	/**
	 * Выполнить команду в консоли из определённого места
	 *
	 * @param string $directory
	 * @param string|string[] $commands
	 * @param bool $withErrors перенаправлять stderr в stdout
	 * @param bool $stopOnFail если передан список команд, то останавливаться ли на ошибке (склеивать команды через && или ;)
	 * Но если свалится смена директорий, то дальше не пойдёт независимо от этого параметра
	 * @return array [успех, вывод, результирующая команда, код возврата]
	 */
	public static function execFromDir($directory, $commands, $withErrors = true, $stopOnFail = true) {
		$commands = (array)$commands;
		if (!empty($directory) && (getcwd() !== $directory)) {
			$cdCommand = 'cd ' . escapeshellarg($directory) . ($withErrors ? ' 2>&1' : '') . ' && ';
			$commands[0] = $cdCommand . $commands[0];
		}
		return self::exec($commands, $withErrors, $stopOnFail);
	}

	/**
	 * Обёртка вызова exec для целей мока в тесте
	 *
	 * @param string $command
	 * @return array [успех, вывод, результирующая команда, код возврата]
	 */
	private static function _exec($command) {
		exec($command, $output, $returnCode);
		return [$returnCode === 0, $output, $command, $returnCode];
	}

}