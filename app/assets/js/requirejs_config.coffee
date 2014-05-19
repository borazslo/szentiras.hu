require.config(
  baseUrl: '/js'
  paths:
    jquery: [
      "http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min"
      "lib/jquery.min"
    ]
    bootstrap: 'lib/bootstrap.min'
    typeahead: 'lib/typeahead.jquery.min'
    app_modules: 'app_bundle'

  shim:
    bootstrap: ['jquery'],
    typeahead: ['jquery']

  deps: ['app_modules', 'jquery', 'bootstrap', 'typeahead']
)