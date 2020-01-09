# Configuration

The complete bundle configuration: 
  
```yaml
huh_encore:
  entries:

    # Prototype
    -
      name:                 ~ # Required
      file:                 ~ # Required
      requires_css:         ~
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