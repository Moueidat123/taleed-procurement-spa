<?php declare(strict_types = 1);

// osfsl-/Users/user/Documents/GitHub/taleed-procurement-spa/backend/vendor/composer/../statamic/cms/src/Data/DataCollection.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Statamic\Data\DataCollection
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-8ac4d4aae1398a7639dd6a0fee850acc4a424dae2329de735f9df8ca8685c976-8.4.14-6.73.0.5',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Statamic\\Data\\DataCollection',
        'filename' => '/Users/user/Documents/GitHub/taleed-procurement-spa/backend/vendor/composer/../statamic/cms/src/Data/DataCollection.php',
      ),
    ),
    'namespace' => 'Statamic\\Data',
    'name' => 'Statamic\\Data\\DataCollection',
    'shortName' => 'DataCollection',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * An abstract collection of data types.
 *
 * @phpstan-consistent-constructor
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 19,
    'endLine' => 243,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Support\\Collection',
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
    ),
    'immediateMethods' => 
    array (
      'limit' => 
      array (
        'name' => 'limit',
        'parameters' => 
        array (
          'limit' => 
          array (
            'name' => 'limit',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 26,
            'endLine' => 26,
            'startColumn' => 27,
            'endColumn' => 32,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Limit a collection.
 *
 * @return static
 */',
        'startLine' => 26,
        'endLine' => 29,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Statamic\\Data',
        'declaringClassName' => 'Statamic\\Data\\DataCollection',
        'implementingClassName' => 'Statamic\\Data\\DataCollection',
        'currentClassName' => 'Statamic\\Data\\DataCollection',
        'aliasName' => NULL,
      ),
      'multisort' => 
      array (
        'name' => 'multisort',
        'parameters' => 
        array (
          'sort' => 
          array (
            'name' => 'sort',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 40,
            'endLine' => 40,
            'startColumn' => 31,
            'endColumn' => 35,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Sort a collection by multiple fields.
 *
 * Accepts a string like "title:desc|foo:asc"
 * The keys are optional. "title:desc|foo" is fine.
 *
 * @param  string  $sort
 * @return static
 */',
        'startLine' => 40,
        'endLine' => 77,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Statamic\\Data',
        'declaringClassName' => 'Statamic\\Data\\DataCollection',
        'implementingClassName' => 'Statamic\\Data\\DataCollection',
        'currentClassName' => 'Statamic\\Data\\DataCollection',
        'aliasName' => NULL,
      ),
      'getSortableValues' => 
      array (
        'name' => 'getSortableValues',
        'parameters' => 
        array (
          'sort' => 
          array (
            'name' => 'sort',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 87,
            'endLine' => 87,
            'startColumn' => 42,
            'endColumn' => 46,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'a' => 
          array (
            'name' => 'a',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 87,
            'endLine' => 87,
            'startColumn' => 49,
            'endColumn' => 50,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'b' => 
          array (
            'name' => 'b',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 87,
            'endLine' => 87,
            'startColumn' => 53,
            'endColumn' => 54,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the values from two content objects to be sorted against each other.
 *
 * @param  string  $sort  The field to be searched
 * @param  \\Statamic\\Contracts\\Data\\Data  $a  The first data object
 * @param  \\Statamic\\Contracts\\Data\\Data  $b  The second data object
 * @return array
 */',
        'startLine' => 87,
        'endLine' => 93,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Statamic\\Data',
        'declaringClassName' => 'Statamic\\Data\\DataCollection',
        'implementingClassName' => 'Statamic\\Data\\DataCollection',
        'currentClassName' => 'Statamic\\Data\\DataCollection',
        'aliasName' => NULL,
      ),
      'getSortableValue' => 
      array (
        'name' => 'getSortableValue',
        'parameters' => 
        array (
          'sort' => 
          array (
            'name' => 'sort',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 95,
            'endLine' => 95,
            'startColumn' => 41,
            'endColumn' => 45,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'item' => 
          array (
            'name' => 'item',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 95,
            'endLine' => 95,
            'startColumn' => 48,
            'endColumn' => 52,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 95,
        'endLine' => 106,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Statamic\\Data',
        'declaringClassName' => 'Statamic\\Data\\DataCollection',
        'implementingClassName' => 'Statamic\\Data\\DataCollection',
        'currentClassName' => 'Statamic\\Data\\DataCollection',
        'aliasName' => NULL,
      ),
      'normalizeSortableValue' => 
      array (
        'name' => 'normalizeSortableValue',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 114,
            'endLine' => 114,
            'startColumn' => 47,
            'endColumn' => 52,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Make sure the sortable value is in a format suitable for sorting.
 *
 * @param  mixed  $value
 * @return mixed
 */',
        'startLine' => 114,
        'endLine' => 125,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Statamic\\Data',
        'declaringClassName' => 'Statamic\\Data\\DataCollection',
        'implementingClassName' => 'Statamic\\Data\\DataCollection',
        'currentClassName' => 'Statamic\\Data\\DataCollection',
        'aliasName' => NULL,
      ),
      'actions' => 
      array (
        'name' => 'actions',
        'parameters' => 
        array (
          'actions' => 
          array (
            'name' => 'actions',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 135,
            'endLine' => 135,
            'startColumn' => 29,
            'endColumn' => 36,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Walk over an array of methods and attempt to run each one.
 *
 * @param  array  $actions
 * @return \\Statamic\\Data\\DataCollection
 *
 * @throws \\Statamic\\Exceptions\\MethodNotFoundException
 */',
        'startLine' => 135,
        'endLine' => 148,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Statamic\\Data',
        'declaringClassName' => 'Statamic\\Data\\DataCollection',
        'implementingClassName' => 'Statamic\\Data\\DataCollection',
        'currentClassName' => 'Statamic\\Data\\DataCollection',
        'aliasName' => NULL,
      ),
      'supplement' => 
      array (
        'name' => 'supplement',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 157,
            'endLine' => 157,
            'startColumn' => 32,
            'endColumn' => 35,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'callable' => 
          array (
            'name' => 'callable',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 157,
                'endLine' => 157,
                'startTokenPos' => 735,
                'startFilePos' => 4319,
                'endTokenPos' => 735,
                'endFilePos' => 4322,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 157,
            'endLine' => 157,
            'startColumn' => 38,
            'endColumn' => 53,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Add a new key to each item of the collection.
 *
 * @param  string|callable  $key  New key to add, or a function to return an array of new values
 * @param  mixed  $callable  Function to return the new value when specifying a key
 * @return \\Statamic\\Data\\DataCollection
 */',
        'startLine' => 157,
        'endLine' => 171,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Statamic\\Data',
        'declaringClassName' => 'Statamic\\Data\\DataCollection',
        'implementingClassName' => 'Statamic\\Data\\DataCollection',
        'currentClassName' => 'Statamic\\Data\\DataCollection',
        'aliasName' => NULL,
      ),
      'supplementMany' => 
      array (
        'name' => 'supplementMany',
        'parameters' => 
        array (
          'callable' => 
          array (
            'name' => 'callable',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'callable',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 179,
            'endLine' => 179,
            'startColumn' => 36,
            'endColumn' => 53,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Add a new set of keys to each item of the collection.
 *
 * @param  callable  $callable  Function to return an array of new values
 * @return static
 */',
        'startLine' => 179,
        'endLine' => 188,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Statamic\\Data',
        'declaringClassName' => 'Statamic\\Data\\DataCollection',
        'implementingClassName' => 'Statamic\\Data\\DataCollection',
        'currentClassName' => 'Statamic\\Data\\DataCollection',
        'aliasName' => NULL,
      ),
      'toArray' => 
      array (
        'name' => 'toArray',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the collection as a plain array.
 *
 * @return array
 */',
        'startLine' => 195,
        'endLine' => 198,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Statamic\\Data',
        'declaringClassName' => 'Statamic\\Data\\DataCollection',
        'implementingClassName' => 'Statamic\\Data\\DataCollection',
        'currentClassName' => 'Statamic\\Data\\DataCollection',
        'aliasName' => NULL,
      ),
      'toArrayWith' => 
      array (
        'name' => 'toArrayWith',
        'parameters' => 
        array (
          'keys' => 
          array (
            'name' => 'keys',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 206,
            'endLine' => 206,
            'startColumn' => 33,
            'endColumn' => 37,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the collection as a plain array using only selected keys.
 *
 * @param  array  $keys
 * @return array
 */',
        'startLine' => 206,
        'endLine' => 229,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Statamic\\Data',
        'declaringClassName' => 'Statamic\\Data\\DataCollection',
        'implementingClassName' => 'Statamic\\Data\\DataCollection',
        'currentClassName' => 'Statamic\\Data\\DataCollection',
        'aliasName' => NULL,
      ),
      'preProcessForIndex' => 
      array (
        'name' => 'preProcessForIndex',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 231,
        'endLine' => 242,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Statamic\\Data',
        'declaringClassName' => 'Statamic\\Data\\DataCollection',
        'implementingClassName' => 'Statamic\\Data\\DataCollection',
        'currentClassName' => 'Statamic\\Data\\DataCollection',
        'aliasName' => NULL,
      ),
    ),
    'traitsData' => 
    array (
      'aliases' => 
      array (
      ),
      'modifiers' => 
      array (
      ),
      'precedences' => 
      array (
      ),
      'hashes' => 
      array (
      ),
    ),
  ),
));