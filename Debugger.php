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

        // -- visual dump (display depends on env)
        // return the var itself, for easy code debugging
        public static function visualDump($var, $var_name = null, $full_backtrace = false)
        {
            $dump = self::dump($var);
            $backtrace = self::traces($var);
            $message = self::toHTML($dump, $var_name, $backtrace, $full_backtrace);

            self::displayErrors($message);
            return $var;
        }

        // should we display something ?
        public static function displayErrors($error_message = null)
        {
            if (!empty($error_message) && ini_get('display_errors') == '1') {
                echo $error_message;
            }
        }

        // creates a dump according to variable type (Throwables & anything else)
        private static function dump($var): string
        {
            if ($var instanceof \Throwable) {
                return self::formatThrowable($var);
            }

            ob_start();
            var_dump($var);
            return ob_get_clean();
        }

        public static function traces($var)
        {
            $traces = $var instanceof \Throwable ? $var->getTrace() : debug_backtrace();

            // removes all internal calls
            while (!empty($traces[0]['class']) && $traces[0]['class'] == __CLASS__) {
                array_shift($traces);
            }

            return $traces;
        }

        // -- formatting

        // -- formatting : first line of \Throwable-based error
        public static function formatThrowable(\Throwable $err)
        {
            return sprintf(
                '%s (%d) in file %s:%d' . PHP_EOL . '%s',
                get_class($err),
                $err->getCode(),
                self::formatFilename($err->getFile()),
                $err->getLine(),
                $err->getMessage()
            );
        }

        // reduce_file_depth_to allows for short filepath, cause it gets crazy sometimes
        public static function formatFilename($file, $reduce_file_depth_to = 5)
        {
            return implode('/', array_slice(explode('/', $file), -$reduce_file_depth_to, $reduce_file_depth_to));
        }

        // -- formatting : nice backtrace
        public static function tracesToString($traces, $full_backtrace)
        {
            $formated_traces = [];

            foreach ($traces as $depth => $trace) {
                if (!empty($trace_string = self::traceToString($trace))) {
                    $formated_traces [] = $trace_string;
                }
                if (!empty($formated_traces) && $full_backtrace === false) {
                    break;
                }
            }

            return implode(PHP_EOL, array_reverse($formated_traces));
        }

        private static function traceToString($trace)
        {
            $function_name = $trace['function'] ?? '?';
            $class_name = $trace['class'] ?? '';

            if (self::isShortcutCall($function_name)) {
                $args = date_format(date_create(), 'ymd:his');
            } else {
                $args = self::traceArgsToString($trace['args'] ?? []);
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

        // private static function isInternalFunctionCall($class_name, $function_name): bool
        // {
        //     return $class_name === __CLASS__ && in_array($function_name, self::$meta_methods);
        // }

        private static function isShortcutCall($function_name): bool
        {
            return in_array($function_name, ['vd', 'dd','vdt', 'ddt']);
        }


        public static function toText($var_dump, $var_name, $backtrace, $full_backtrace)
        {
            return PHP_EOL
            . "******* "
            . (empty($var_name) ? $backtrace[1]['function'] . '()' : " ($var_name) ")
            . " *******"
            . PHP_EOL
            . self::tracesToString($backtrace, $full_backtrace)
            . PHP_EOL
            . $var_dump;
        }

        public static function toHTML($var_dump, $var_name, $backtrace, $full_backtrace)
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

            return sprintf(
                '<pre style="%s">%s</pre>',
                implode(';', $css),
                self::toText($var_dump, $var_name, $backtrace, $full_backtrace)
            );
        }
    }
}
namespace
{
    use HexMakina\Debugger\Debugger;

    if (!function_exists('vd')) {
        function vd($var, $var_name = null)
        {
            Debugger::visualDump($var, $var_name, false);
        }
    }
    if (!function_exists('dd')) {
        function dd($var, $var_name = null)
        {
            Debugger::visualDump($var, $var_name, false);
            die;
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
            Debugger::visualDump($var, $var_name, true);
            die;
        }
    }
}
