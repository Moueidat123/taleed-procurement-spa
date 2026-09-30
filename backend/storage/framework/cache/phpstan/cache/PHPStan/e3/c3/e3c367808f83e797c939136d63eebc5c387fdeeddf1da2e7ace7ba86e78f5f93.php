<?php declare(strict_types = 1);

// osfsl-/Users/user/Documents/GitHub/taleed-procurement-spa/backend/vendor/composer/../statamic/cms/src/Data/StoresComputedFieldCallbacks.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Statamic\Data\StoresComputedFieldCallbacks
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-16fa5d3f0e440d089cbeaba95b691c0a33b99a5e186b64e2c7b13aec79fb671e-8.4.14-6.73.0.5',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Statamic\\Data\\StoresComputedFieldCallbacks',
        'filename' => '/Users/user/Documents/GitHub/taleed-procurement-spa/backend/vendor/composer/../statamic/cms/src/Data/StoresComputedFieldCallbacks.php',
      ),
    ),
    'namespace' => 'Statamic\\Data',
    'name' => 'Statamic\\Data\\StoresComputedFieldCallbacks',
    'shortName' => 'StoresComputedFieldCallbacks',
    'isInterface' => false,
    'isTrait' => true,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 8,
    'endLine' => 32,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => NULL,
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
      'computedFieldCallbacks' => 
      array (
        'declaringClassName' => 'Statamic\\Data\\StoresComputedFieldCallbacks',
        'implementingClassName' => 'Statamic\\Data\\StoresComputedFieldCallbacks',
        'name' => 'computedFieldCallbacks',
        'modifiers' => 2,
        'type' => NULL,
        'default' => NULL,
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 10,
        'endLine' => 10,
        'startColumn' => 5,
        'endColumn' => 38,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
    ),
    'immediateMethods' => 
    array (
      'computed' => 
      array (
        'name' => 'computed',
        'parameters' => 
        array (
          'field' => 
          array (
            'name' => 'field',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 15,
            'endLine' => 15,
            'startColumn' => 30,
            'endColumn' => 35,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'callback' => 
          array (
            'name' => 'callback',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 15,
                'endLine' => 15,
                'startTokenPos' => 46,
                'startFilePos' => 269,
                'endTokenPos' => 46,
                'endFilePos' => 272,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'Closure',
                      'isIdentifier' => false,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 15,
            'endLine' => 15,
            'startColumn' => 38,
            'endColumn' => 62,
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
 * @param  string|array  $field
 */',
        'startLine' => 15,
        'endLine' => 26,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Statamic\\Data',
        'declaringClassName' => 'Statamic\\Data\\StoresComputedFieldCallbacks',
        'implementingClassName' => 'Statamic\\Data\\StoresComputedFieldCallbacks',
        'currentClassName' => 'Statamic\\Data\\StoresComputedFieldCallbacks',
        'aliasName' => NULL,
      ),
      'getComputedCallbacks' => 
      array (
        'name' => 'getComputedCallbacks',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Support\\Collection',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 28,
        'endLine' => 31,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Statamic\\Data',
        'declaringClassName' => 'Statamic\\Data\\StoresComputedFieldCallbacks',
        'implementingClassName' => 'Statamic\\Data\\StoresComputedFieldCallbacks',
        'currentClassName' => 'Statamic\\Data\\StoresComputedFieldCallbacks',
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