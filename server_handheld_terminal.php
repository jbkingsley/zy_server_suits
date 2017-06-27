<?php
/**
 * run with command
 * php start.php start
 */

ini_set('display_errors', 'on');
use Workerman\Worker;

if(strpos(strtolower(PHP_OS), 'win') === 0)
{
    exit("start.php not support windows, please use start_for_win.bat\n");
}

// 检查扩展
if(!extension_loaded('pcntl'))
{
    exit("Please install pcntl extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
}

if(!extension_loaded('posix'))
{
    exit("Please install posix extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
}

// 标记是全局启动
define('GLOBAL_START', 1);

function autoLoadByNamespace($name)
{
    $class_file = __DIR__ . DIRECTORY_SEPARATOR . 'Applications' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
    if (is_file($class_file)) {
        require_once($class_file);
        if (class_exists($name, false)) {
            return true;
        }
    }
    return false;
}
spl_autoload_register('autoLoadByNamespace');

require_once __DIR__ . '/vendor/autoload.php';

//加载进程间通讯服务
foreach(glob(__DIR__.'/Channel/start*.php') as $start_file)
{
    require_once $start_file;
}

// 加载所有Applications/HandHeldTerminal/start.php，以便启动所有服务
foreach(glob(__DIR__.'/Applications/HandHeldTerminal/start*.php') as $start_file)
{
    require_once $start_file;
}
// 运行所有服务
Worker::runAll();
