# Debugger
[![Latest Stable Version](http://poser.pugx.org/hexmakina/debugger/v)](https://packagist.org/packages/hexmakina/debugger)
[![License](http://poser.pugx.org/hexmakina/debugger/license)](https://packagist.org/packages/hexmakina/debugger)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/HexMakina/Debugger/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/HexMakina/Debugger/?branch=main)
<img src="https://img.shields.io/badge/PSR-4-brightgreen" alt="PSR-4 Compliant" />
<img src="https://img.shields.io/badge/PSR-12-brightgreen" alt="PSR-12 Compliant" />
<img src="https://img.shields.io/badge/PHP-7.0-brightgreen" alt="PHP 7.0 Required" />

wrapper for var_dump, with an optional stack trace but mandatory nice looks
tired of checking your source for var_dump() output? 
wondering how that bug came into existence ?
Debugger to the rescue

## Usage
Instantiate Debugger; to load the class
```
new \HexMakina\Debugger\Debugger();
```

Debugger class file has 2 namespaces, the debugger itself in HexMakina\Debugger, and in the root namespace, 4 shortcuts:

```
vd($var, $label); // nice var_dump
dd($var, $label); // nice var_dump AND die
vdt($var, $label); // nice var_dump with a stack trace
ddt($var, $label); // nice var dump with a stack trace AND die
```

For instance, in bootstrap.php, where the variable $foo is a string with a value of 'bar', the output of ```vd($foo);``` would be:
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
string(4) "test"
```
We now see the whole path the program took before reaching the debugging command


If we went deeper into the code, the output of ```vdt($foo);``` in a sub-sub-sub-sub-.. routine, would be the following:
```
[index.php                 2]                            ?::require(/var/www/dev.engine/koral/lareponse/koral/bootstrap.php)
[bootstrap.php            44]                         HexMakina\koral\Controllers\Home::bootstrap(HexMakina\kadro\Controllers\ReceptionController, HexMakina\kadro\Auth\Operator)
[Home.class.ph  23]                      HexMakina\koral\Controllers\Home::common_viewport(HexMakina\kadro\Controllers\ReceptionController, HexMakina\kadro\Auth\Operator)
[Home.class.ph  34]                   HexMakina\Crudites\TightModel::filter()
[TightModel.class.php    326]                HexMakina\kadro\Auth\Operator::query_retrieve(Array #0, Array #0)
[Operator.class.php       71]             HexMakina\Crudites\TightModel::table()
[TightModel.class.php    480]          HexMakina\Crudites\Crudites::inspect(kadro_operator)
[Crudites.class.php       31]       ?::vdt(1627942013.3913)
string(4) "test"
```

Easy debugging & nice formatting, that's Debugger for you.
