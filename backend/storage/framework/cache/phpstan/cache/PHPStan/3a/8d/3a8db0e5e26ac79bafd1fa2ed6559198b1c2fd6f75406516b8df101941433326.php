<?php declare(strict_types = 1);

// odsl-/Users/user/Documents/GitHub/taleed-procurement-spa/backend/app/Models/AppUser.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\AppUser
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.73.0.5-8.4.14-a4514d7e450452ac06d24e7fce4d8c6c70210da7a8a9d150ebf01ae695245b61',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\AppUser',
        'filename' => '/Users/user/Documents/GitHub/taleed-procurement-spa/backend/app/Models/AppUser.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\AppUser',
    'shortName' => 'AppUser',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * A procurement application user (Champion, Analyst or Super Admin).
 *
 * Authenticated only through the `web` guard. Never a Statamic user and never
 * granted control-panel access (decisions.md D-03).
 *
 * @property string $id
 * @property string $role
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 21,
    'endLine' => 57,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Foundation\\Auth\\User',
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
      0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
      1 => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUlids',
      2 => 'Illuminate\\Notifications\\Notifiable',
      3 => 'Laravel\\Fortify\\TwoFactorAuthenticatable',
    ),
    'immediateConstants' => 
    array (
      'ROLES' => 
      array (
        'declaringClassName' => 'App\\Models\\AppUser',
        'implementingClassName' => 'App\\Models\\AppUser',
        'name' => 'ROLES',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '[\'champion\', \'analyst\', \'admin\']',
          'attributes' => 
          array (
            'startLine' => 26,
            'endLine' => 26,
            'startTokenPos' => 77,
            'startFilePos' => 763,
            'endTokenPos' => 85,
            'endFilePos' => 794,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 26,
        'endLine' => 26,
        'startColumn' => 5,
        'endColumn' => 58,
      ),
    ),
    'immediateProperties' => 
    array (
      'table' => 
      array (
        'declaringClassName' => 'App\\Models\\AppUser',
        'implementingClassName' => 'App\\Models\\AppUser',
        'name' => 'table',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '\'app_users\'',
          'attributes' => 
          array (
            'startLine' => 28,
            'endLine' => 28,
            'startTokenPos' => 94,
            'startFilePos' => 821,
            'endTokenPos' => 94,
            'endFilePos' => 831,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 28,
        'endLine' => 28,
        'startColumn' => 5,
        'endColumn' => 35,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'fillable' => 
      array (
        'declaringClassName' => 'App\\Models\\AppUser',
        'implementingClassName' => 'App\\Models\\AppUser',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'name\', \'email\', \'job_title\']',
          'attributes' => 
          array (
            'startLine' => 37,
            'endLine' => 37,
            'startTokenPos' => 105,
            'startFilePos' => 1113,
            'endTokenPos' => 113,
            'endFilePos' => 1142,
          ),
        ),
        'docComment' => '/**
 * Only profile fields are mass assignable. Role, organization, export
 * permission, active and verification state are set explicitly by
 * authorized server code (never from request input).
 *
 * @var list<string>
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 37,
        'endLine' => 37,
        'startColumn' => 5,
        'endColumn' => 57,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'hidden' => 
      array (
        'declaringClassName' => 'App\\Models\\AppUser',
        'implementingClassName' => 'App\\Models\\AppUser',
        'name' => 'hidden',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'password\', \'remember_token\', \'two_factor_secret\', \'two_factor_recovery_codes\']',
          'attributes' => 
          array (
            'startLine' => 40,
            'endLine' => 40,
            'startTokenPos' => 124,
            'startFilePos' => 1199,
            'endTokenPos' => 135,
            'endFilePos' => 1278,
          ),
        ),
        'docComment' => '/** @var list<string> */',
        'attributes' => 
        array (
        ),
        'startLine' => 40,
        'endLine' => 40,
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
      'casts' => 
      array (
        'name' => 'casts',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 42,
        'endLine' => 51,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\AppUser',
        'implementingClassName' => 'App\\Models\\AppUser',
        'currentClassName' => 'App\\Models\\AppUser',
        'aliasName' => NULL,
      ),
      'isStaff' => 
      array (
        'name' => 'isStaff',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 53,
        'endLine' => 56,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\AppUser',
        'implementingClassName' => 'App\\Models\\AppUser',
        'currentClassName' => 'App\\Models\\AppUser',
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