require.config(
  baseUrl: '/js'
  paths:
    jquery: [
      "http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min"
      "lib/jquery.min"
    ]
    bootstrap: 'lib/bootstrap.min'
    app_modules: 'app_bundle'

  shim:
    bootstrap:
      deps: ['jquery']
  deps: ['app_modules', 'jquery', 'bootstrap']
)