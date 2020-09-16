<?php

namespace App\Commands\Base;

use App\Commands\Command;
use App\Domains\Monitor\Services\AlertService;
use Carbon\Carbon;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BaseCommand extends Command
{
    const BIZ_INFO_STACK_SIZE_MAX = 3;

    const TASK_LEVEL_CNT_MAX = 3;

    protected $name = 'base';

    protected $description = 'Base Command';

    //For emergency or special case
    protected $logSwitch = true;

    protected $outputSwitch = true;

    //For emergency or special case
    protected $cmdProxySwitch = true;

    protected $progressSwitch = false;

    protected $bizInfoStack = [];

    //Don't set or change in sub class
    protected $bizInfoStackLeak = false;

    protected $outputPrefix = '';

    protected $rootInfoAlert = false;

    protected $taskLevel = 0;

    protected $taskLevelCnt = [];

    protected function shouldLog()
    {
        return $this->logSwitch;
    }

    protected function shouldCallCmdProxy()
    {
        return $this->cmdProxySwitch;
    }

    protected function shouldShowProgress() {
        return $this->progressSwitch;
    }

    protected function newLine()
    {
        $this->line('');
    }

    /**
     * @param $raw
     * @param string $level
     * @param string $name
     * @return mixed
     */
    protected function log($raw, $level = 'info', $name = '')
    {
        $name = $name ?: $this->getDefaultNameWithLevel();

        $logContent = json_encode([
            'script_name' => $name,
            'log' => $raw,
        ]);

        //Output to storage log
        return call_user_func_array([\Log::class, $level], [$logContent]);
    }

    protected function logInfo($raw, $name = '')
    {
        return $this->log($raw, 'info', $name);
    }

    protected function logError($raw, $name = '')
    {
        return $this->log($raw, 'error', $name);
    }

    protected function logDebug($raw, $name = '')
    {
        return $this->log($raw, 'debug', $name);
    }

    protected function logWarning($raw, $name = '')
    {
        return $this->log($raw, 'warning', $name);
    }

    /**
     * {@inheritDoc}
     *
     * After the log is outputted, this command will exit.
     *
     * @param $raw
     * @param string $level
     * @param string $name
     */
    protected function exitLog($raw, $level = 'info', $name = '')
    {
        $this->log($raw, $level, $name);
        if (!in_array($level, ['info', 'debug'])) {
            $this->exitFailed();
        } else {
            $this->exitSuccess();
        }
    }

    protected function exitLogInfo($raw, $name = '')
    {
        $this->exitLog($raw, 'info', $name);
    }

    protected function exitLogError($raw, $name = '')
    {
        $this->exitLog($raw, 'error', $name);
    }

    protected function exitLogDebug($raw, $name = '')
    {
        $this->exitLog($raw, 'debug', $name);
    }

    protected function exitLogWarning($raw, $name = '')
    {
        $this->exitLog($raw, 'warning', $name);
    }

    protected function exitSuccess()
    {
        exit(0);
    }

    protected function exitFailed()
    {
        exit(1);
    }

    protected function exitInfo($info)
    {
        $this->info($info);
        $this->exitSuccess();
    }

    protected function exitError($info)
    {
        $this->error($info);
        $this->exitFailed();
    }

    protected function getTaskLevelCnt()
    {
        return $this->taskLevelCnt[$this->taskLevel] ?? 0;
    }

    protected function incrTaskLevelCnt()
    {
        $taskLevel = $this->taskLevel;
        if (isset($this->taskLevelCnt[$taskLevel])) {
            $taskLevelCnt = $this->taskLevelCnt[$taskLevel];

            //overflow check
            if ($taskLevelCnt !== 'inf') {
                if ($taskLevelCnt < PHP_INT_MAX) {
                    $this->taskLevelCnt[$taskLevel]++;
                } else {
                    $this->taskLevelCnt[$taskLevel] = 'inf';
                }
            }
        } else {
            $this->taskLevelCnt[$taskLevel] = 1;
        }
    }

    protected function getDefaultNameWithLevel()
    {
        if ($this->taskLevel <= 1) {
            return $this->name;
        } else {
            return $this->name . ' sub task ' . ((string)($this->taskLevel - 1)) .
                ' no.' . ((string)$this->getTaskLevelCnt());
        }
    }

    /**
     * @param callable $callback Business logic handler
     * @param string $name The name of the task
     * @param bool $toAlert Whether to send the task info via email
     * @param bool $logWithStack Whether to merge the task info to parent task info
     * @param bool $output Whether to output the task info to console
     * @param bool $toLog Whether to record the task info to log files
     * @return mixed|null
     * @throws \Throwable
     */
    protected function callWithLog(
        $callback,
        $name = '',
        $toAlert = false,
        $logWithStack = false,
        $output = true,
        $toLog = true
    )
    {
        $callFunc = function () use ($callback) {
            return call_user_func($callback);
        };

        $parentBizInfoStackLeak = $this->bizInfoStackLeak;
        if (count($this->bizInfoStack) < self::BIZ_INFO_STACK_SIZE_MAX) {
            array_push($this->bizInfoStack, ['sub_tasks' => []]);
            $this->outputPrefix .= '====';
            $this->taskLevel++;
            $pushedNewStack = true;
            if ($this->bizInfoStackLeak === true) {
                $this->bizInfoStackLeak = false;
            }
        } else {
            $this->warn($this->outputPrefix . $name . ' biz info stack size overflow');
            $this->logWarning($name . ' biz info stack size overflow', $name);
            $pushedNewStack = false;
            if ($this->bizInfoStackLeak === false) {
                $this->bizInfoStackLeak = true;
            }
        }

        $this->incrTaskLevelCnt();
        $taskLevelCnt = $this->getTaskLevelCnt();

        //The flag may be changed in biz logic, so the flag should be set before calling biz logic.
        if ($toAlert) {
            $this->toAlert();
        }

        //Depends on task level and task level count
        $name = $name ?: $this->getDefaultNameWithLevel();

        //For human reading
        $startTime = microtime(true);
        $startMemoryUsage = memory_get_usage();
        $this->newLine();
        if ($output) {
            $this->info($this->outputPrefix . ' SCRIPT NAME: ' . $name);
        }
        $startDateTime = Carbon::now()->toDateTimeString();
        if ($output) {
            $this->info($this->outputPrefix . ' START TIME(' . $name . '): ' . $startDateTime);
        }
        $this->newLine();

        $exception = null;

        try {
            $callResult = call_user_func($callFunc);
        } catch (\Throwable $e) {
            $exception = $e;
            $callResult = null;
        }

        $this->newLine();
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $memoryUsage = memory_get_usage() - $startMemoryUsage;
        $finishDateTime = Carbon::now()->toDateTimeString();

        if ($pushedNewStack) {
            $bizInfo = array_pop($this->bizInfoStack);
            if ($this->bizInfoStackLeak === true) {
                $this->bizInfoStackLeak = false;
            }
        } else {
            $bizInfo = [];
            if (!$parentBizInfoStackLeak) {
                $this->bizInfoStackLeak = false;
            }
        }

        $logInfo = array_merge([
            'name' => $name,
            'start_time' => $startDateTime,
            'finish_time' => $finishDateTime,
            'duration' => $duration,
            'memory_usage' => $memoryUsage,
        ], $bizInfo);

        if (!is_null($exception)) {
            $logInfo['exception_msg'] = $exception->getMessage();
            $logInfo['exception_trace'] = $exception->getTraceAsString();
        }

        if ($pushedNewStack) {
            if (($taskLevelCnt !== 'inf') && ($taskLevelCnt <= self::TASK_LEVEL_CNT_MAX)) {
                if ($logWithStack) {
                    $parentBizInfo = array_pop($this->bizInfoStack);
                    if ($parentBizInfo) {
                        $parentBizInfo['sub_tasks'][] = $logInfo;
                        array_push($this->bizInfoStack, $parentBizInfo);
                    }
                }
            }
        }

        //Snapshot info with alert switch
        $alertInfo = $logInfo;

        //For log collector
        if ($toLog) {
            if (isset($logInfo['to_alert'])) {
                unset($logInfo['to_alert']);
            }
            $this->logInfo(
                json_encode($logInfo),
                $name
            );
        }
        $this->newLine();

        //For human reading
        if ($output) {
            $this->info($this->outputPrefix . ' DURATION(' . $name . '): ' . ((string)$duration) . 's');
            $this->info($this->outputPrefix . ' MEMORY USAGE(' . $name . '): ' . ((string)$memoryUsage) . 'BYTES');
            $this->info($this->outputPrefix . ' FINISH TIME(' . $name . '): ' . $finishDateTime);
        }
        $this->newLine();

        $this->sendAlertInfo($alertInfo, $name);

        if ($pushedNewStack) {
            $this->outputPrefix = substr($this->outputPrefix, 4);
            $this->taskLevel--;
        }

        if (!is_null($exception)) {
            throw $exception;
        }

        return $callResult;
    }

    protected function sendAlertInfo($alertInfo, $name = '')
    {
        if (empty($alertInfo['to_alert'])) {
            return;
        } else {
            unset($alertInfo['to_alert']);
        }

        $name = $name ?: $this->getDefaultNameWithLevel();
        $this->callAlertService($alertInfo, $name);
    }

    protected function callAlertService($alertInfo, $name = '')
    {
        //TODO implements
    }

    /**
     * @param $key
     * @param $value
     * @param bool $overrideKey
     * @throws \Exception
     */
    protected function addBizInfo($key, $value, $overrideKey = false)
    {
        if ($this->bizInfoStackLeak) {
            return;
        }

        if (!$overrideKey) {
            if (in_array(
                $key,
                [
                    'start_time', 'finish_time', 'duration', 'memory_usage', 'exception_msg', 'exception_trace', 'sub_tasks',
                    'to_alert', 'name',
                ]
            )) {
                throw new \Exception(
                    'Key cannot be one oƒ \'start_time\', \'finish_time\', \'duration\', \'memory_usage\', ' .
                    '\'exception_msg\', \'exception_trace\', \'sub_tasks\', \'to_alert\', \'name\''
                );
            }
        }

        $alertInfo = array_pop($this->bizInfoStack);
        if ($alertInfo) {
            $alertInfo[$key] = $value;
            array_push($this->bizInfoStack, $alertInfo);
        }
    }

    /**
     * @throws \Exception
     */
    protected function toAlert()
    {
        $this->addBizInfo('to_alert', true, true);
    }

    /**
     * @throws \Exception
     */
    protected function dontAlert()
    {
        $this->addBizInfo('to_alert', false, true);
    }

    /**
     * {@inheritDoc}
     *
     * if the callback argument called 'progress' is not null,
     * you can call the advance method of progress.
     *
     * @param $callback
     * @param $total
     * @return mixed
     */
    protected function callWithProgress($callback, $total)
    {
        $progress = null;

        $callFunc = function ($progress) use ($callback) {
            return call_user_func_array($callback, [$progress]);
        };

        if (!$this->shouldShowProgress()) {
            return call_user_func_array($callFunc, [$progress]);
        }

        $progress = $this->output->createProgressBar($total);
        $progress->start();
        $result = call_user_func_array($callFunc, [$progress]);
        $progress->finish();
        return $result;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     * @throws \Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //LogWithStack is invalid in the root of stack
        return $this->callWithLog(function () use ($input, $output) {
            return parent::execute($input, $output);
        }, $this->name, $this->rootInfoAlert, false, $this->outputSwitch, $this->shouldLog());
    }

    protected function configure()
    {
        parent::configure();
        $this->addOption(
            'proxy_cmd',
            'proxy_cmd',
            InputOption::VALUE_OPTIONAL
        )->addOption(
            'test_base_cmd',
            'test_base_cmd',
            InputOption::VALUE_OPTIONAL,
            '',
            '0'
        );
    }

    protected function testBaseCommand($taskId = 2)
    {
//        if ($taskId > 103) {
//            return;
//        }
//
//        $this->callWithLog(function () use ($taskId) {
//            $this->line($taskId);
//            $this->addBizInfo('foo' . ((string)$taskId), 'bar' . ((string)$taskId));
//            $this->testBaseCommand($taskId + 1);
//        }, '', false, true);
    }

    protected function testBaseCommand2()
    {
//        for ($i = 1; $i <= 103; ++$i) {
//            $this->callWithLog(function () use ($i) {
//                $this->line($i);
//                $this->addBizInfo('foo' . ((string)$i), 'bar' . ((string)$i));
//            }, '', false, true);
//        }
    }

    protected function testBaseCommand3($taskId = 2)
    {
//        if ($taskId > 103) {
//            return;
//        }
//
//        $this->callWithLog(function () use ($taskId) {
//            $this->line($taskId);
//            $this->addBizInfo('foo' . ((string)$taskId), 'bar' . ((string)$taskId));
//
//            if ($taskId === 6) {
//                for ($i = 1; $i <= 103; ++$i) {
//                    $this->callWithLog(function () use ($i, $taskId) {
//                        $this->line($i);
//                        $this->addBizInfo(
//                            'foo' . ((string)$taskId) . '-' . ((string)$i),
//                            'bar' . ((string)$taskId) . '-' . ((string)$i)
//                        );
//                    }, '', false, true);
//                }
//            }
//
//            $this->testBaseCommand3($taskId + 1);
//        }, '', false, true);
    }

    protected function testBaseCommand4($taskId = 2)
    {
//        if ($taskId > 106) {
//            return;
//        }
//
//        $this->callWithLog(function () use ($taskId) {
//            $this->line($taskId);
//            $this->testBaseCommand4($taskId + 1);
//            $this->addBizInfo('foo' . ((string)$taskId), 'bar' . ((string)$taskId));
//        }, '', false, true);
    }

    protected function testBaseCommand5($taskId = 2)
    {
//        if ($taskId > 103) {
//            return;
//        }
//
//        $this->callWithLog(function () use ($taskId) {
//            if ($taskId === 1) {
//                for ($i = 1; $i <= 100; ++$i) {
//                    $this->callWithLog(function () use ($i, $taskId) {
//                        $this->line($i);
//                        $this->addBizInfo(
//                            'foo' . ((string)$taskId) . '-' . ((string)$i),
//                            'bar' . ((string)$taskId) . '-' . ((string)$i)
//                        );
//                    }, '', false, true);
//                }
//            }
//
//            $this->testBaseCommand5($taskId + 1);
//
//            $this->line($taskId);
//            $this->addBizInfo('foo' . ((string)$taskId), 'bar' . ((string)$taskId));
//        }, '', false, true);
    }

    /**
     * {@inheritDoc}
     *
     * If you call the base command with proxy_cmd option, the command which has the name as the option will
     * be called.
     *
     * @return int|null
     * @throws \Throwable
     */
    public function handle()
    {
//        if ($this->option('test_base_cmd') === '1') {
//            $this->testBaseCommand();
//            $this->testBaseCommand2();
//            $this->testBaseCommand3();
//            $this->testBaseCommand4();
//            $this->testBaseCommand5();
//        }

        if (!$this->shouldCallCmdProxy()) {
            return null;
        }

        if (!is_null($cmd = $this->option('proxy_cmd'))) {
            try {
                return $this->call($cmd, $this->arguments());
            } catch (CommandNotFoundException $e) {
                //
            } catch (\Throwable $e) {
                throw $e;
            }
        }

        return null;
    }
}
