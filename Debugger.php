<?php

namespace HexMakina\Debugger
{
    class Debugger
    {
        private static $meta_methods = [];

        public function __construct()
        {
            $debugger = new \ReflectionClass(__CLASS__);
            $methods = $debugger->getMethods();
            self::$meta_methods = array_map(function ($m) {
                return $m->name;
            }, $methods);
        }

        public static function displayErrors($error_message = null)
        {
            $should_display = ini_get('display_errors') == '1';

            if ($should_display && !empty($error_message)) {
                echo self::toHTML($error_message);
            }
        }

        // -- dump on variable type (Throwables, array, anything else)
        private static function dump($var, $var_name = null, $full_backtrace = true)
        {
            if (is_object($var) && (is_subclass_of($var, 'Error') || is_subclass_of($var, 'Exception'))) {
                $backtrace = $var->getTrace();
                $full_backtrace = true;
                $var_dump  = self::formatThrowable($var);
            } else {
                $backtrace = debug_backtrace();

                ob_start();
                var_dump($var);
                $var_dump = ob_get_clean();
            }

            return PHP_EOL
            . "*******"
            . (empty($var_name) ? '' : " ($var_name) ")
            . "*******"
            . PHP_EOL
            . self::tracesToString($backtrace, $full_backtrace)
            . PHP_EOL
            . $var_dump;
        }

        // -- visual dump (depends on env)
        public static function visualDump($var, $var_name = null, $full_backtrace = false)
        {
            self::displayErrors(self::dump($var, $var_name, $full_backtrace));
            return $var;
        }

        // -- visual dump and DIE
        public static function visualDumpAndDie($var, $var_name = null, $full_backtrace = true)
        {
            self::visualDump($var, $var_name, $full_backtrace);
            die;
        }


      // -- formatting

      // -- formatting : first line of \Throwable-based error
        public static function formatThrowable(\Throwable $err)
        {
            return PHP_EOL . sprintf(
                '%s (%d) in file %s:%d' . PHP_EOL . '%s',
                get_class($err),
                $err->getCode(),
                self::formatFilename($err->getFile()),
                $err->getLine(),
                $err->getMessage()
            );
        }

        public static function formatFilename($file, $reduce_file_depth_to = 5)
        {
            return implode('/', array_slice(explode('/', $file), -$reduce_file_depth_to, $reduce_file_depth_to));
        }

      // -- formatting : nice backtrace
        public static function tracesToString($traces, $full_backtrace)
        {
            $formated_traces = [];
            
            foreach ($traces as $depth => $trace) {
                if(!empty($trace_string = self::traceToString($trace))){
                    $formated_traces []= $trace_string;
                }

                if ($full_backtrace === false) {
                    break;
                }
            }

            return implode(PHP_EOL, array_reverse($formated_traces));
        }

        private static function traceToString($trace)
        {
            $function_name = $trace['function'] ?? '?';
            $class_name = $trace['class'] ?? '';

            if (self::isInternalFunctionCall($class_name, $function_name)) {
                return '';
            }

            if (!self::isShortcutCall($function_name) && isset($trace['args'])) {
                $args = self::traceArgsToString($trace['args']);
            } else {
                $args = microtime(true);
            }

            $call_file = isset($trace['file']) ? basename($trace['file']) : '?';
            $call_line = $trace['line'] ?? '?';

            return sprintf(
                '[%-23.23s %3s]  %s%s(%s)',
                $call_file,
                $call_line,
                "$class_name::",
                $function_name,
                $args
            );
        }
        private static function traceArgsToString($trace_args)
        {
            $ret = [];
            foreach ($trace_args as $arg) {
                $ret[] = self::traceArgToString($arg);
            }
            return implode(', ', $ret);
        }

        private static function traceArgToString($arg)
        {
            $ret = 'unknown type';

            if (is_null($arg)) {
                $ret = 'NULL';
            } elseif (is_bool($arg)) {
                $ret = 'bool:' . ((int)$arg);
            } elseif (is_scalar($arg)) {
                $ret = $arg;
            } elseif (is_object($arg)) {
                $ret = get_class($arg);
            } elseif (is_array($arg)) {
                $ret = 'Array #' . count($arg);
            }
            return $ret;
        }

        private static function isInternalFunctionCall($class_name, $function_name): bool
        {
            return $class_name === __CLASS__ && in_array($function_name, self::$meta_methods);
        }

        private static function isShortcutCall($function_name): bool
        {
            return in_array($function_name, ['vd', 'dd','vdt', 'ddt']);
        }

        private static function toHTML($message)
        {
            $css = [
            'text-align:left',
            'z-index:9999',
            'background-color:#FFF',
            'color:#000',
            'padding:0.5em',
            'font-size:0.7em',
            'margin:0 0 1em 0',
            'font-family:courier'
            ];

            return sprintf('<pre style="%s">%s</pre>', implode(';', $css), $message);
        }
    }
}
namespace
{
    use \HexMakina\Debugger\Debugger;

    if (!function_exists('vd')) {
        function vd($var, $var_name = null)
        {
            Debugger::visualDump($var, $var_name, false);
        }
    }
    if (!function_exists('dd')) {
        function dd($var, $var_name = null)
        {
            Debugger::visualDumpAndDie($var, $var_name, false);
        }
    }
    if (!function_exists('vdt')) {
        function vdt($var, $var_name = null)
        {
            Debugger::visualDump($var, $var_name, true);
        }
    }
    if (!function_exists('ddt')) {
        function ddt($var, $var_name = null)
        {
            Debugger::visualDumpAndDie($var, $var_name, true);
        }
    }
}
