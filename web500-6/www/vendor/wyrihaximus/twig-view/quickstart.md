## Change AppView
```PHP
// src/View/AppView.php
namespace App\View;

use WyriHaximus\TwigView\View\TwigView;

class AppView extends TwigView
{
     // Code ...
}
```

## Load Helpers
```PHP
// src/View/AppView.php
namespace App\View;

use WyriHaximus\TwigView\View\TwigView;

class AppView extends TwigView
{
    public function initialize()
    {
        $this->loadHelper('Html');
        $this->loadHelper('Form');
    }
```

Note: TwigView will look for its templates with the extension `.twig` and then for `.tpl` (deprecated).

## Create the default layout to be used by TwigView named `default.twig` instead of `default.ctp`
Layout example
```Twig
<!DOCTYPE html>
<html>
<head>
    {{ Html.charset()|raw }}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        {{ __('MySite') }}
        {{ _view.fetch('title')|raw }}
    </title>
    {{ Html.meta('icon')|raw }}

    {{ Html.css('app.css')|raw }}
    {{ Html.script('app')|raw }}

    {{ _view.fetch('meta')|raw }}
    {{ _view.fetch('css')|raw }}
    {{ _view.fetch('script')|raw }}
</head>
<body>

    <header>
        {{ _view.fetch('header')|raw }}
    </header>

    <section>
        <h1 class="page-title">{{ _view.fetch('title') }}</h1>
        {{ _view.fetch('content')|raw }}
    </section>

</body>
```

## Create a view template
in Template/Controller/action.twig
```Twig

{{ _view.start('header') }}
    <p>It's my header</p>
{{ _view.end() }}

{{ _view.assign('title', "it's my title") }}

<p>It's my content</p>
```
