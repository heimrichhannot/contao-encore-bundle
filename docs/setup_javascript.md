# Setup Javascript

This documents add some information and help about setting up the webpack/encore/javascript parts of your project/bundle.

## JavaScript entries

Require SASS/CSS is simple as: 

```javascript
require('../scss/project.scss');
```

Use jQuery: 

```javascript
let $ = require('jquery');

// assign jQuery to a global for legacy modules
window.$ = window.jQuery = $;
```
> If you use jQuery in webpack, you can deactivate it in the contao page layout in order to avoid including it twice.


## Setup babel/corejs polyfill

To get your project working in older browser, you maybe want to use the babel polyfill. As of babel 7.4 `@babel/polyfill` [was deprecated in favor of corejs](https://babeljs.io/blog/2019/03/19/7.4.0) in favor of corejs. Here is our setup/migration guide.

1. Add corejs 3 and regenerator runtime to package.json. `npm i --save core-js regenerator-runtime` or: 

    ```json
    {
      "dependencies": {
        "core-js": "^3.1.4",
        "regenerator-runtime": "^0.13.2"
      }
    }
    ```

2. Update your webpack config: remove babel-poly entry and set corejs version in your babel config:

    ```js
    Encore
    // To be removed from webpack.config.js:
    .addEntry('babel-polyfill', [
            '@babel/polyfill'
        ])
     // Update babel config
    .configureBabel({},{
        useBuiltIns: 'entry',
        corejs: 3,
    })
    ```

3. Create an polyfill entry(for example `corejs-polyfill.js`) and just add these two imports:

    ```js
    import "core-js/stable";
    import "regenerator-runtime/runtime";
    ```
    
    Register the entry in your project bundle or in your webpack config. 
    
4. Update your yarn dependencies (`yarn install` or `composer update` (if you use foxy)) and compile. Set `corejs-polyfill` as active entry in your layout.