<?php

namespace GlobalPayments\Api\Utils\Logging;

use DateTime;
use RuntimeException;

class Logger
{
    const INFO_LOG_LEVEL = 'info';

    protected array $options = array(
        'extension' => 'txt',
        'dateFormat' => 'Y-m-d G:i:s.u',
        'filename' => false,
        'flushFrequency' => false,
        'prefix' => 'log_',
        'logFormat' => false,
        'appendContext' => true,
    );

    /**
     * Path to the log file
     * @var string
     */
    private ?string $logFilePath = null;

    /**
     * The number of lines logged in this instance's lifetime
     * @var int
     */
    private int $logLineCount = 0;

    /**
     * This holds the file handle for this instance's log file
     * @var resource
     */
    private $fileHandle = null;

    /**
     * This holds the last line logged to the logger
     *  Used for unit tests
     * @var string
     */
    private string $lastLine = '';

    /**
     * Octal notation for default permissions of the log file
     * @var integer
     */
    private int $defaultPermissions = 0777;

    /**
     * Log level priorities for formatting
     * @var array
     */
    private array $logLevels = [
        'emergency' => 0,
        'alert' => 1,
        'critical' => 2,
        'error' => 3,
        'warning' => 4,
        'notice' => 5,
        'info' => 6,
        'debug' => 7
    ];

    /**
     * Class constructor
     *
     * @param string $logDirectory File path to the logging directory
     */
    public function __construct(string $logDirectory)
    {
        $logDirectory = rtrim($logDirectory, DIRECTORY_SEPARATOR);
        if (!file_exists($logDirectory)) {
            mkdir($logDirectory, $this->defaultPermissions, true);
        }

        if (strpos($logDirectory, 'php://') === 0) {
            $this->setLogToStdOut($logDirectory);
            $this->setFileHandle('w+');
        } else {
            $this->setLogFilePath($logDirectory);
            if (file_exists($this->logFilePath) && !is_writable($this->logFilePath)) {
                throw new RuntimeException('The file could not be written to. Check that appropriate permissions have been set.');
            }
            $this->setFileHandle('a');
        }

        if (!$this->fileHandle) {
            throw new RuntimeException('The file could not be opened. Check permissions.');
        }
    }

    /**
     * @param string $stdOutPath
     */
    public function setLogToStdOut(string $stdOutPath): void
    {
        $this->logFilePath = $stdOutPath;
    }

    /**
     * @param string $logDirectory
     */
    public function setLogFilePath(string $logDirectory): void
    {
        if ($this->options['filename']) {
            if (strpos($this->options['filename'], '.log') !== false || strpos($this->options['filename'],
                    '.txt') !== false) {
                $this->logFilePath = $logDirectory . DIRECTORY_SEPARATOR . $this->options['filename'];
            } else {
                $this->logFilePath = $logDirectory . DIRECTORY_SEPARATOR . $this->options['filename'] . '.' . $this->options['extension'];
            }
        } else {
            $this->logFilePath = $logDirectory . DIRECTORY_SEPARATOR . $this->options['prefix'] . date('Y-m-d') . '.' . $this->options['extension'];
        }
    }

    /**
     * @param $writeMode
     *
     * @internal param resource $fileHandle
     */
    public function setFileHandle(string $writeMode): void
    {
        $this->fileHandle = fopen($this->logFilePath, $writeMode);
    }


    /**
     * Class destructor
     */
    public function __destruct()
    {
        if ($this->fileHandle) {
            fclose($this->fileHandle);
        }
    }

    /**
     * Sets the date format used by all instances of KLogger
     *
     * @param string $dateFormat Valid format string for date()
     */
    public function setDateFormat(string $dateFormat): void
    {
        $this->options['dateFormat'] = $dateFormat;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, string $message, array $context = array()): void
    {
        $message = $this->formatMessage($level, $message, $context);
        $this->write($message);
    }

    /**
     * Writes a line to the log without prepending a status or timestamp
     *
     * @param string $message Line to write to the log
     * @return void
     */
    public function write(string $message): void
    {
        if (null !== $this->fileHandle) {
            if (fwrite($this->fileHandle, $message) === false) {
                throw new RuntimeException('The file could not be written to. Check that appropriate permissions have been set.');
            } else {
                $this->lastLine = trim($message);
                $this->logLineCount++;

                if ($this->options['flushFrequency'] && $this->logLineCount % $this->options['flushFrequency'] === 0) {
                    fflush($this->fileHandle);
                }
            }
        }
    }

    /**
     * Get the file path that the log is currently writing to
     *
     * @return string
     */
    public function getLogFilePath(): ?string
    {
        return $this->logFilePath;
    }

    /**
     * Get the last line logged to the log file
     *
     * @return string
     */
    public function getLastLogLine(): string
    {
        return $this->lastLine;
    }

    /**
     * Formats the message for logging.
     *
     * @param string $level The Log Level of the message
     * @param string $message The message to log
     * @param array $context The context
     * @return string
     */
    protected function formatMessage($level, string $message, array $context): string
    {
        if ($this->options['logFormat']) {
            $parts = array(
                'date' => $this->getTimestamp(),
                'level' => strtoupper($level),
                'level-padding' => str_repeat(' ', 9 - strlen($level)),
                'priority' => $this->logLevels[$level],
                'message' => $message,
                'context' => json_encode($context),
            );
            $message = $this->options['logFormat'];
            foreach ($parts as $part => $value) {
                $message = str_replace('{' . $part . '}', $value, $message);
            }

        } else {
            $message = "[{$this->getTimestamp()}] [{$level}] {$message}";
        }

        if ($this->options['appendContext'] && !empty($context)) {
            $message .= PHP_EOL . $this->indent($this->contextToString($context));
        }

        return $message . PHP_EOL;

    }

    /**
     * Gets the correctly formatted Date/Time for the log entry.
     *
     * PHP DateTime is dump, and you have to resort to trickery to get microseconds
     * to work correctly, so here it is.
     *
     * @return string
     */
    private function getTimestamp(): string
    {
        $originalTime = microtime(true);
        $micro = sprintf("%06d", ($originalTime - floor($originalTime)) * 1000000);
        $date = new DateTime(date('Y-m-d H:i:s.' . $micro, (int) $originalTime));

        return $date->format($this->options['dateFormat']);
    }

    /**
     * Takes the given context and coverts it to a string.
     *
     * @param array $context The Context
     * @return string
     */
    protected function contextToString(array $context): string
    {
        $export = '';
        foreach ($context as $key => $value) {
            $export .= "{$key}: ";
            $export .= preg_replace(array(
                '/=>\s+([a-zA-Z])/im',
                '/array\(\s+\)/im',
                '/^  |\G  /m'
            ), array(
                '=> $1',
                'array()',
                '    '
            ), str_replace('array (', 'array(', var_export($value, true)));
            $export .= PHP_EOL;
        }
        return str_replace(array('\\\\', '\\\''), array('\\', '\''), rtrim($export));
    }

    /**
     * Indents the given string with the given indent.
     *
     * @param string $string The string to indent
     * @param string $indent What to use as the indent.
     * @return string
     */
    protected function indent(string $string, string $indent = '    '): string
    {
        return $indent . str_replace("\n", "\n" . $indent, $string);
    }

    public function info(string $message, array $context = array()): void
    {
        $this->log(self::INFO_LOG_LEVEL,$message, $context);
    }
}