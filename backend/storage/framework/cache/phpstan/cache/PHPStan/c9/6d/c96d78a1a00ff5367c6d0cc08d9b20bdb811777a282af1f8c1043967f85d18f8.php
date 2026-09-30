<?php declare(strict_types = 1);

// odsl-/Users/user/Documents/GitHub/taleed-procurement-spa/backend/app/Console/Commands/CreateCmsAdministrator.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Console\Commands\CreateCmsAdministrator
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.73.0.5-8.4.14-670170daad10b8c870ae26c751d2d5e4f6a9606fb397735c592ece1c3a0ad98d',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Console\\Commands\\CreateCmsAdministrator',
        'filename' => '/Users/user/Documents/GitHub/taleed-procurement-spa/backend/app/Console/Commands/CreateCmsAdministrator.php',
      ),
    ),
    'namespace' => 'App\\Console\\Commands',
    'name' => 'App\\Console\\Commands\\CreateCmsAdministrator',
    'shortName' => 'CreateCmsAdministrator',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * Creates THE single Statamic Core control-panel administrator (decisions.md D-04).
 *
 * Unlike `please make:user`, this never offers to enable Pro and refuses when a
 * CMS user already exists. Application users are never created here.
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 17,
    'endLine' => 66,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Console\\Command',
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
      'signature' => 
      array (
        'declaringClassName' => 'App\\Console\\Commands\\CreateCmsAdministrator',
        'implementingClassName' => 'App\\Console\\Commands\\CreateCmsAdministrator',
        'name' => 'signature',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '\'procurement:cms:create-admin
        {email : Email of the single CMS administrator}
        {--name=CMS Administrator : Display name}
        {--password= : Local/testing only; production prompts without echo}\'',
          'attributes' => 
          array (
            'startLine' => 19,
            'endLine' => 22,
            'startTokenPos' => 47,
            'startFilePos' => 484,
            'endTokenPos' => 47,
            'endFilePos' => 695,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 19,
        'endLine' => 22,
        'startColumn' => 5,
        'endColumn' => 77,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'description' => 
      array (
        'declaringClassName' => 'App\\Console\\Commands\\CreateCmsAdministrator',
        'implementingClassName' => 'App\\Console\\Commands\\CreateCmsAdministrator',
        'name' => 'description',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '\'Create the single Statamic Core CMS administrator (refuses if one exists)\'',
          'attributes' => 
          array (
            'startLine' => 24,
            'endLine' => 24,
            'startTokenPos' => 56,
            'startFilePos' => 728,
            'endTokenPos' => 56,
            'endFilePos' => 802,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 24,
        'endLine' => 24,
        'startColumn' => 5,
        'endColumn' => 105,
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
      'handle' => 
      array (
        'name' => 'handle',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'int',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 26,
        'endLine' => 65,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Console\\Commands',
        'declaringClassName' => 'App\\Console\\Commands\\CreateCmsAdministrator',
        'implementingClassName' => 'App\\Console\\Commands\\CreateCmsAdministrator',
        'currentClassName' => 'App\\Console\\Commands\\CreateCmsAdministrator',
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