<?php

namespace HexMakina\Debugger
{
    class Debugger
    {
        private static array $skip_classes = [__CLASS__];

        public function setSkipClasses($skip_classes): void
        {
            foreach ($skip_classes as $skip_class) {
                self::$skip_classes[] = $skip_class;
            }

            self::$skip_classes = array_unique(self::$skip_classes);
        }

        // -- visual dump (display depends on env)
        // return the var itself, for easy code debugging
        public static function visualDump($var, $var_name = null, $full_backtrace = false)
        {

            if ($var instanceof \Throwable) {
                $traces = $var->getTrace();
                $dump = self::formatExceptionAsTrace($var);
            } else {
                $traces = debug_backtrace();

                ob_start();
                var_dump($var);
                $dump = ob_get_clean();
            }

            $traces = self::purgeTraces($traces);
            $message = self::toHTML($dump, $var_name, $traces, $full_backtrace);

            self::displayErrors($message);
            return $var;
        }

        private static function formatExceptionAsTrace($var): string
        {
            return self::traceToString([
              'class' => get_class($var),
              'line' => $var->getLine(),
              'file' => $var->getFile(),
              'function' => 'getCode',
              'args' => [$var->getCode()]
            ]) . PHP_EOL . $var->getMessage();
        }

        // should we display something ?
        public static function displayErrors($error_message = null): void
        {
            if (empty($error_message)) {
                return;
            }
            if (ini_get('display_errors') != '1') {
                return;
            }
            echo $error_message;
        }

        /**
         * @return array<int|string, mixed>
         */
        private static function purgeTraces($traces): array
        {
            return array_filter($traces, static fn($trace): bool => empty($traces[$i]['class']) || !in_array($traces[$i]['class'], self::$skip_classes));
        }

        // -- formatting
        public static function toText($var_dump, $var_name, $backtrace, $full_backtrace): string
        {
            return PHP_EOL
            . "******* "
            . (empty($var_name) ? $backtrace[1]['function'] . '()' : sprintf(' (%s) ', $var_name))
            . " *******"
            . PHP_EOL
            . self::tracesToString($backtrace, $full_backtrace)
            . PHP_EOL
            . $var_dump;
        }

        public static function toHTML($var_dump, $var_name, $backtrace, $full_backtrace): string
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

        // reduce_file_depth_to allows for short filepath, cause it gets crazy sometimes
        private static function formatFilename($file, $reduce_file_depth_to = 5): string
        {
            return implode('/', array_slice(explode('/', $file), -$reduce_file_depth_to, $reduce_file_depth_to));
        }

        // -- formatting : nice backtrace
        private static function tracesToString($traces, $full_backtrace): string
        {
            $formated_traces = [];

            foreach ($traces as $trace) {
                $formated_traces [] = self::traceToString($trace);
                if ($full_backtrace === false) {
                    break;
                }
            }

            return implode(PHP_EOL, array_reverse($formated_traces));
        }

        private static function traceToString($trace): string
        {
            $function_name = $trace['function'] ?? '?';
            $class_name = $trace['class'] ?? '';

            if (self::isShortcutCall($function_name)) {
                $args = date_format(date_create(), 'ymd:his');
            } else {
                $args = self::traceArgsToString($trace['args'] ?? []);
            }

            $call_file = isset($trace['file']) ? self::formatFilename($trace['file'], 2) : '?';
            $call_line = $trace['line'] ?? '?';

            return sprintf(
                '[%-33.33s %3s]  %s%s(%s)',
                $call_file,
                $call_line,
                sprintf('%s::', $class_name),
                $function_name,
                $args
            );
        }

        private static function traceArgsToString($trace_args): string
        {
            $ret = [];
            foreach ($trace_args as $trace_arg) {
                $ret[] = self::traceArgToString($trace_arg);
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

        private static function isShortcutCall($function_name): bool
        {
            return in_array($function_name, ['vd', 'dd','vdt', 'ddt']);
        }
    }
}

namespace
{
    use HexMakina\Debugger\Debugger;

    if (!function_exists('vd')) {
        function vd($var, $var_name = null): void
        {
            Debugger::visualDump($var, $var_name, false);
        }
    }

    if (!function_exists('dd')) {
        function dd($var, $var_name = null): void
        {
            Debugger::visualDump($var, $var_name, false);
            die;
        }
    }

    if (!function_exists('vdt')) {
        function vdt($var, $var_name = null): void
        {
            Debugger::visualDump($var, $var_name, true);
        }
    }

    if (!function_exists('ddt')) {
        function ddt($var, $var_name = null): void
        {
            Debugger::visualDump($var, $var_name, true);
            die;
        }
    }
}
