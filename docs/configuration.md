# Configuration

The complete bundle configuration: 
  
```yaml
# Default configuration for extension with alias: "huh_encore"
huh_encore:

    # Add javascript files which should be registered as encore entries.
    js_entries:

        # Prototype
        -

            # Will be shown in contao backend and will be used as alias/identifier in the database.
            name:                 ~ # Required

            # Path to the Javascript file.
            file:                 ~ # Required

            # Set to true, if entry requires css.
            requires_css:         ~

            # Set to true, if entry should added to the encoreHeadScripts section in your page layout instead to the bottom (CSS will always be added to the head).
            head:                 ~
    templates:
        imports:

            # Prototype
            -
                name:                 ~ # Required
                template:             ~ # Required

    # A list of keys that should be stripped from the global contao arrays. Here you can add assets, that you serve with webpack, so they won't be loaded twice or on the wrong page. IMPORTANT: The strings defined here must match the array keys in Contao's global arrays
    unset_global_keys:

        # Assets will be stripped from $GLOBALS['TL_JAVASCRIPT']
        js:                   []

        # Assets will be stripped from $GLOBALS['TL_JQUERY']
        jquery:               []

        # Assets will be stripped from $GLOBALS['TL_USER_CSS'] and $GLOBALS['TL_CSS']
        css:                  []

    # Remove jQuery from global array, if addJQuery is enabled in layout section.
    unset_jquery:         false
    encore:               # Deprecated (Configs within encore key are deprecated and will be removed in next major version.)
        entries:

            # Prototype
            -
                name:                 ~ # Required
                file:                 ~ # Required
                requiresCss:          ~
                head:                 ~
        templates:
            imports:

                # Prototype
                -
                    name:                 ~ # Required
                    template:             ~ # Required
        legacy:
            js:                   []
            jquery:               []
            css:                  []

```