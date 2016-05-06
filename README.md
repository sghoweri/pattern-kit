## Add pattern kit to composer.json

```
"require": {
    "pattern-builder/pattern-kit": "@dev"
},
"repositories": [
  {
    "type": "vcs",
    "url": "https://github.com/PatternBuilder/pattern-kit"
  }
]
```

## Add index.php

- File must be at/above your css/js/img assets
- Update paths to point to vendor folder

```
<?php

require_once __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/pattern-builder/pattern-kit/src/app.php';

$app['http_cache']->run();

```


## Add .pk-config.yml next to composer.json

- Create arrays of paths to your data, schema, template, docs and styleguide files
- Set the file extensions for each file type
- List categories in order you'd like them to appear in navigation
- Create arrays of assets for css, js and footer js (including live reload if necessary)

```

title: Project Title

paths:
  data:
    - /path/to/sample/data
  schemas:
    - /path/to/schemas
  templates:
    - /path/to/templates
  docs:
    - /path/to/schemas-docs
  sg:
    - /path/to/stylelguide/docs
extensions:
  data: .docs.json
  schemas: .json
  templates: .twig
  docs: .docs.md
  sg: .sg.md
categories:
    - Pattern
    - Sub Pattern
    - Layout
    - Component
    - Atom
assets:
  css:
    - path/to/css
    - path/to/othercss
  js:
    - path/to/js
    - path/to/otherjs
  footer_js:
    - path/to/footer_js
    - path/to/otherfooter_js
    - //localhost:1336/livereload.js
```

Point MAMP or local PHP server at your index.php file

php -S 0:9001 -t ./