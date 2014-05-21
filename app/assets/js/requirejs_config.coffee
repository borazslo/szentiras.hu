require.config(
  baseUrl: '/js'
  paths:
    jquery: [
      "//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min"
      "lib/jquery.min"
    ]
    'jquery.ui.autocomplete': [
      "lib/jquery.ui.autocomplete.min"
    ]
    'jquery.ui.widget': [
      "lib/jquery.ui.widget.min"
    ]
    'jquery.ui.position': [
      "lib/jquery.ui.position.min"
    ]
    'jquery.ui.menu': [
      "lib/jquery.ui.menu.min"
      ]
    'jquery.ui.core': [
      "lib/jquery.ui.core.min"
    ]

    bootstrap: "lib/bootstrap.min"
    app_modules: 'app_bundle'

  shim:
    bootstrap: ['jquery']
    'jquery.ui.menu': ['jquery.ui.core', 'jquery.ui.widget', 'jquery.ui.position']
    'jquery.ui.autocomplete':
      deps: ['jquery', 'jquery.ui.core', 'jquery.ui.widget', 'jquery.ui.position', 'jquery.ui.menu']
      exports: 'autocomplete'

  deps: ['app_modules', 'jquery']
)