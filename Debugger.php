<?php

namespace HexMakina\Debugger
{
    trait Debugger
    {
        public function __debugInfo() : array
        {
            return [json_encode(get_object_vars($this))];
        }

        public static function init()
        {
          // just to load the class, required to get the shortcuts defined in namespace \
        }

        public static function display_errors($error_message = null)
        {
            $should_display = ini_get('display_errors') == '1';

            if ($should_display && !empty($error_message)) {
                echo('<pre style="text-align:left; z-index:9999; background-color:#FFF; color:#000; padding:0.5em; font-size:0.7em; margin:0 0 1em 0; font-family:courier;">'.$error_message.'</pre>');
            }
        }

      // -- visual dump (depends on env)
        public static function vd($var, $var_name = null, $full_backtrace = false)
        {
            self::display_errors(self::dump($var, $var_name, $full_backtrace));
        }

      // -- visual dump and DIE
        public static function dd($var, $var_name = null, $full_backtrace = true)
        {
            self::vd($var, $var_name, $full_backtrace);
            die;
        }

      // -- dump on variable type (Throwables, array, anything else)
        public static function dump($var, $var_name = null, $full_backtrace = true)
        {
            if (is_object($var) && (is_subclass_of($var, 'Error') || is_subclass_of($var, 'Exception'))) {
                $backtrace = $var->getTrace();
                $full_backtrace = true;
                $var_dump  = self::format_throwable_message(get_class($var), $var->getCode(), $var->getFile(), $var->getLine(), $var->getMessage());
            } else {
                $backtrace = debug_backtrace();

                ob_start();
                var_dump($var);
                $var_dump = ob_get_clean();
            }

            return PHP_EOL."*******".(empty($var_name) ? '' : " ($var_name) ")."*******".PHP_EOL.self::format_trace($backtrace, $full_backtrace).PHP_EOL.$var_dump;
        }

      // -- formatting

      // -- formatting : first line of \Throwable-based error
        public static function format_throwable_message($class, $code, $file, $line, $message)
        {
            return sprintf(PHP_EOL.'%s (%d) in file %s:%d'.PHP_EOL.'%s', $class, $code, self::format_file($file), $line, $message);
        }

      // -- formatting : shorten file path to [self::REDUCE_FILE_PATH_DEPTH_TO] elements
        public static function format_file($file, $reduce_file_depth_to = 5)
        {
            return implode('/', array_slice(explode('/', $file), -$reduce_file_depth_to, $reduce_file_depth_to));
        }

      // -- formatting : nice backtrace
        public static function format_trace($traces, $full_backtrace)
        {
            $formated_traces = [];

            foreach ($traces as $depth => $trace) {
                $function_name = $trace['function'] ?? '?';
                $class_name = $trace['class'] ?? '?';

                if (self::is_debugger_function($class_name, $function_name)) {
                    continue;
                }

                if (!self::is_debugger_call($function_name) && isset($trace['args'])) {
                    $args = self::trace_args_to_string($trace['args']);
                } else {
                    $args = microtime(true);
                }

                $call_file = isset($trace['file']) ? basename($trace['file']) : '?';
                $call_line = $trace['line'] ?? '?';

                $formated_traces []= sprintf('[%-23.23s %3s]  %s%s(%s)', $call_file, $call_line, "$class_name::", $function_name, $args);

                if ($full_backtrace === false) {
                    break;
                }
            }

            return implode(PHP_EOL, array_reverse($formated_traces));
        }

        private static function trace_args_to_string($trace_args)
        {
            $ret = [];
            foreach ($trace_args as $arg) {
                if (is_null($arg)) {
                    $ret[]= 'NULL';
                } elseif (is_bool($arg)) {
                    $ret[]= 'bool:'.((int)$arg);
                } elseif (is_scalar($arg)) {
                    $ret[]= $arg;
                } elseif (is_object($arg)) {
                    $ret[]= get_class($arg);
                } elseif (is_array($arg)) {
                    $ret[]= 'Array #'.count($arg);
                } else {
                    $ret[] = 'unknown type';
                }
            }
            $ret = implode(', ', $ret);
            return $ret;
        }

        private static function is_debugger_function($class_name, $function_name)
        {
            return $class_name === __CLASS__ && in_array($function_name, ['dump', 'vd', 'dd']);
        }

        private static function is_debugger_call($function_name)
        {
            return in_array($function_name, ['vd', 'dd','vdt', 'ddt']);
        }
    }
}

namespace
{
    if (!function_exists('vd')) {
        function vd($var, $var_name = null)
        {
            \HexMakina\Debugger\Debugger::vd($var, $var_name, false);
        }
    }
    if (!function_exists('dd')) {
        function dd($var, $var_name = null)
        {
            \HexMakina\Debugger\Debugger::dd($var, $var_name, false);
        }
    }
    if (!function_exists('vdt')) {
        function vdt($var, $var_name = null)
        {
            \HexMakina\Debugger\Debugger::vd($var, $var_name, true);
        }
    }
    if (!function_exists('ddt')) {
        function ddt($var, $var_name = null)
        {
            \HexMakina\Debugger\Debugger::dd($var, $var_name, true);
        }
    }
}
