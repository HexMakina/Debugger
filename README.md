# Debugger
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/HexMakina/Debugger/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/HexMakina/Debugger/?branch=main)

wrapper for var_dump

## Usage
Call Debugger::init(); to load the class.
Debugger class file has 2 namespaces, the debugger itself in HexMakina\Debugger, and in the root namespace, 4 shortcuts:

```
vd($var, $label); // nice var_dump
dd($var, $label); // nice var_dump AND die
vdt($var, $label); // nice var_dump with a stack trace
ddt($var, $label); // nice var dump with a stack trace AND die
```

For instance, in bootstrap.php, the output of 
```vd('test'); ```

would be:
```
**************
[bootstrap.php            16]       ?::vd(1627941733.4195)
string(4) "test"
```
