<?php
namespace Robo\Task;

use Robo\Result;
use Robo\Task\Shared\TaskException;
use Robo\Task\Shared\TaskInterface;

trait Composer {

    /**
     * @param null $pathToComposer
     * @return ComposerInstallTask
     */
    protected function taskComposerInstall($pathToComposer = null)
    {
        return new ComposerInstallTask($pathToComposer);
    }

    protected function taskComposerUpdate($pathToComposer = null)
    {
        return new ComposerUpdateTask($pathToComposer);
    }
}

abstract class BaseComposerTask
{
    use \Robo\Output;
    use \Robo\Task\Shared\Process;

    protected $prefer;
    protected $dev = true;

    /**
     * adds `prefer-dist` option to composer
     *
     * @return $this
     */
    public function preferDist()
    {
        $this->prefer = '--prefer-dist';
        return $this;
    }

    /**
     * adds `prefer-source` option to composer
     *
     * @return $this
     */
    public function preferSource()
    {
        $this->prefer = '--prefer-source';
        return $this;
    }

    /**
     * adds `no-dev` option to composer
     *
     * @return $this
     */
    public function noDev()
    {
        $this->dev = false;
        return $this;
    }

    public function __construct($pathToComposer = null)
    {
        if ($pathToComposer) {
            $this->command = $pathToComposer;
        } elseif (file_exists('composer.phar')) {
            $this->command = 'php composer.phar';
        } elseif (is_executable('/usr/bin/composer')) {
            $this->command = '/usr/bin/composer';
        } elseif (is_executable('/usr/local/bin/composer')) {
			$this->command = '/usr/local/bin/composer';
		} else {
            throw new TaskException(__CLASS__,"Neither local composer.phar nor global composer installation not found");
        }
    }

    public function getCommand()
    {
        $options = $this->prefer;
        $this->dev ?: $options.= " --no-dev";
        return "{$this->command} {$this->action} $options";
    }
}

/**
 * Composer Install
 *
 * ``` php
 * <?php
 * // simple execution
 * $this->taskComposerInstall()->run();
 *
 * // prefer dist with custom path
 * $this->taskComposerInstall('path/to/my/composer.phar')
 *      ->preferDist()
 *      ->run();
 * ?>
 * ```
 */
class ComposerInstallTask extends BaseComposerTask implements TaskInterface {

    protected $action = 'install';

    public function run()
    {
        $command = $this->getCommand();
        $this->printTaskInfo('Installing Packages: ' . $command);
        return $this->executeCommand($command);
    }

}

/**
 * Composer Update
 *
 * ``` php
 * <?php
 * // simple execution
 * $this->taskComposerUpdate()->run();
 *
 * // prefer dist with custom path
 * $this->taskComposerUpdate('path/to/my/composer.phar')
 *      ->preferDist()
 *      ->run();
 * ?>
 * ```
 */
class ComposerUpdateTask extends BaseComposerTask implements TaskInterface {

    protected $action = 'update';

    public function run()
    {
        $command = $this->getCommand();
        $this->printTaskInfo('Updating Packages: '.$command);
        return $this->executeCommand($command);
    }

}