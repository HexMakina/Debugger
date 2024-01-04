# Debugger
[![Latest Stable Version](http://poser.pugx.org/hexmakina/debugger/v)](https://packagist.org/packages/hexmakina/debugger)
[![License](http://poser.pugx.org/hexmakina/debugger/license)](https://packagist.org/packages/hexmakina/debugger)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/HexMakina/Debugger/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/HexMakina/Debugger/?branch=main)
<a href="https://codeclimate.com/github/HexMakina/Debugger/maintainability"><img src="https://api.codeclimate.com/v1/badges/f6003cfa22da322d7b19/maintainability" /></a>
<img src="https://img.shields.io/badge/PSR-4-brightgreen" alt="PSR-4 Compliant" />
<img src="https://img.shields.io/badge/PSR-12-brightgreen" alt="PSR-12 Compliant" />
<img src="https://img.shields.io/badge/PHP-7.0-brightgreen" alt="PHP 7.0 Required" />

wrapper for var_dump, optional full stack trace, mandatory good looks
- it tells you were the call was initiated so you don't ever have to look for a forgotten var_dump()
- it shows you the trace of calls that lead to the dump
- for each trace, it presents you with the filename, the class name and the function with params

no more wondering how a bug came into being.


## Usage
Instantiate Debugger; to load the class and shortcuts
```
new \HexMakina\Debugger\Debugger();
```

Debugger class file has 2 namespaces, the debugger itself in HexMakina\Debugger, and in the root namespace, 4 shortcuts:
```
vd($var, $label);  // visual dump
dd($var, $label);  // visual dump AND die();
vdt($var, $label); // visual dump with full trace
ddt($var, $label); // visual dump with full trace AND die();
```

For instance, dumping the variable $foo, in file bootstrap.php, line 16, the output of ```vd($foo);``` would be:
```
**************
[bootstrap.php            16]       ?::vd(1627941733.4195)
string(4) "bar"
```

and the output of ```vd($foo, 'a label for easy spotting');``` would be:
```
******* (a label for easy spotting) *******
[bootstrap.php            16]       ?::vd(1627942158.3575)
string(3) "bar"
```

but ```vdt($foo);``` would be:
```
**************
[index.php                 2]          ?::require(/var/www/dev.engine/koral/lareponse/koral/bootstrap.php)
[bootstrap.php            16]       ?::vdt(1627941924.2403)
string(4) "bar"
```
We now see the whole path the program took before reaching the debugging command


Deeper into the code, in a sub-sub-sub-sub-.. routine, the output of ```vdt($foo, 'inspecting foo');``` would be:
```
******* (inspecting) *******
[index.php                35]  HexMakina\koral\Controllers\Home::bootstrap()
[Home.php                 28]  App\Controllers\Home::common_viewport(HexMakina\kadro\Controllers\Reception)
[Home.php                 18]  HexMakina\TightORM\TableModel::filter()
[TableModel.php          184]  HexMakina\koral\Models\Worker::query_retrieve(Array #0, Array #0)
[Worker.php               78]  HexMakina\TightORM\TightModel::query_retrieve(Array #0, Array #0)
[TightModel.php          160]  HexMakina\TightORM\TightModelSelector::__construct(HexMakina\koral\Models\Worker)
[TightModelSelector.php   20]  HexMakina\TightORM\TableModel::table()
[Crudites.php             31]  ::vdt(1631261380.9958)
string(6) "bar"
```

Easy debugging & old-school formatting, that's Debugger for you.
