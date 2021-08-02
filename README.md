# Debugger
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/HexMakina/Debugger/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/HexMakina/Debugger/?branch=main)

wrapper for var_dump

## Usage
Call Debugger::init(); to load the class.
Class has 2 namespace, the debugger itself, and in the root namespace, 4 shortcuts:

vd($var, $label); // var_dump
dd($var, $label); // var_dump AND die
vdt($var, $label); // var_dump with a stack trace
ddt($var, $label); // var dump with a stack trace AND die

